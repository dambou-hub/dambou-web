<?php
// dambou.fr/stripe-webhook.php
// Endpoint appele directement par Stripe (jamais par un navigateur) a
// chaque evenement de paiement -- c'est la SEULE facon fiable de savoir
// qu'un abonnement a ete paye : contrairement a la redirection success_url,
// cet appel se fait cote serveur, independamment de ce que fait le
// navigateur du client, et sa signature est verifiee cryptographiquement
// pour etre sur qu'il vient bien de Stripe (personne ne peut la simuler
// sans connaitre le webhook_secret).
//
// A configurer une fois sur Stripe : Developpeurs > Webhooks > Ajouter un
// endpoint > URL = https://dambou.fr/stripe-webhook.php, evenements a
// ecouter : checkout.session.completed, invoice.payment_succeeded,
// invoice.payment_failed, customer.subscription.deleted.

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

function respond($code, $msg) {
  http_response_code($code);
  echo $msg;
  exit;
}

$configFile = __DIR__ . '/stripe-config.php';
if (!file_exists($configFile)) {
  error_log('stripe-webhook: stripe-config.php absent');
  respond(500, 'Config manquante');
}
$config = include $configFile;
$webhookSecret = $config['webhook_secret'] ?? '';
$serviceRoleKey = $config['supabase_service_role'] ?? '';
if (empty($webhookSecret) || empty($serviceRoleKey)) {
  error_log('stripe-webhook: webhook_secret ou supabase_service_role manquant dans stripe-config.php');
  respond(500, 'Config incomplete');
}

// -----------------------------------------------------
// Verification de la signature Stripe (implementation manuelle de
// l'algorithme documente par Stripe, pas besoin du SDK complet).
// -----------------------------------------------------
$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

function verifyStripeSignature($payload, $sigHeader, $secret, $tolerance = 300) {
  $parts = array();
  foreach (explode(',', $sigHeader) as $item) {
    $kv = explode('=', $item, 2);
    if (count($kv) === 2) $parts[$kv[0]] = $kv[1];
  }
  if (empty($parts['t']) || empty($parts['v1'])) return false;

  $timestamp = $parts['t'];
  $signature = $parts['v1'];

  // Protection contre le rejeu d'anciennes requetes
  if (abs(time() - (int)$timestamp) > $tolerance) return false;

  $signedPayload = $timestamp . '.' . $payload;
  $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);

  return hash_equals($expectedSignature, $signature);
}

if (!verifyStripeSignature($payload, $sigHeader, $webhookSecret)) {
  error_log('stripe-webhook: signature invalide');
  respond(400, 'Signature invalide');
}

$event = json_decode($payload, true);
if (!$event || empty($event['type'])) {
  respond(400, 'Payload invalide');
}

// -----------------------------------------------------
// Mise a jour de businesses via l'API REST Supabase (service_role,
// contourne les RLS -- ce script n'a pas de session utilisateur).
// -----------------------------------------------------
$SUPABASE_URL = 'https://unwrghiiocaztnecmpeh.supabase.co';

function updateBusiness($businessId, $data, $serviceRoleKey, $supabaseUrl) {
  if (empty($businessId)) return false;
  $ch = curl_init($supabaseUrl . '/rest/v1/businesses?id=eq.' . urlencode($businessId));
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'PATCH',
    CURLOPT_HTTPHEADER => array(
      'apikey: ' . $serviceRoleKey,
      'Authorization: Bearer ' . $serviceRoleKey,
      'Content-Type: application/json',
      'Prefer: return=minimal',
    ),
    CURLOPT_POSTFIELDS => json_encode($data),
  ));
  curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return $httpCode >= 200 && $httpCode < 300;
}

// Retrouve le business a partir d'un stripe_customer_id (utile pour les
// evenements qui n'ont pas directement le business_id en metadata, ex:
// les renouvellements/annulations post-inscription).
function findBusinessByCustomerId($customerId, $serviceRoleKey, $supabaseUrl) {
  if (empty($customerId)) return null;
  $ch = curl_init($supabaseUrl . '/rest/v1/businesses?stripe_customer_id=eq.' . urlencode($customerId) . '&select=id');
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
      'apikey: ' . $serviceRoleKey,
      'Authorization: Bearer ' . $serviceRoleKey,
    ),
  ));
  $result = json_decode(curl_exec($ch), true);
  curl_close($ch);
  return $result[0]['id'] ?? null;
}

$obj = $event['data']['object'] ?? array();

switch ($event['type']) {

  case 'checkout.session.completed':
    // Premier paiement reussi -- active l'abonnement.
    $businessId = $obj['metadata']['business_id'] ?? '';
    updateBusiness($businessId, array(
      'subscription_status'     => 'active',
      'stripe_customer_id'      => $obj['customer'] ?? null,
      'stripe_subscription_id'  => $obj['subscription'] ?? null,
    ), $serviceRoleKey, $SUPABASE_URL);
    break;

  case 'invoice.payment_succeeded':
    // Renouvellement mensuel/annuel reussi -- garde l'abonnement actif.
    $customerId = $obj['customer'] ?? '';
    $businessId = findBusinessByCustomerId($customerId, $serviceRoleKey, $SUPABASE_URL);
    if ($businessId) {
      updateBusiness($businessId, array('subscription_status' => 'active'), $serviceRoleKey, $SUPABASE_URL);
    }
    break;

  case 'invoice.payment_failed':
    // Echec de prelevement (carte expiree, fonds insuffisants...).
    $customerId = $obj['customer'] ?? '';
    $businessId = findBusinessByCustomerId($customerId, $serviceRoleKey, $SUPABASE_URL);
    if ($businessId) {
      updateBusiness($businessId, array('subscription_status' => 'past_due'), $serviceRoleKey, $SUPABASE_URL);
    }
    break;

  case 'customer.subscription.deleted':
    // Abonnement annule ou definitivement termine.
    $customerId = $obj['customer'] ?? '';
    $businessId = findBusinessByCustomerId($customerId, $serviceRoleKey, $SUPABASE_URL);
    if ($businessId) {
      updateBusiness($businessId, array('subscription_status' => 'cancelled'), $serviceRoleKey, $SUPABASE_URL);
    }
    break;
}

respond(200, 'OK');
