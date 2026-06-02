<?php
// ============================================================
// dambou.fr/abonnement — Page de souscription Stripe Checkout
// À placer dans public_html/abonnement.php sur Hostinger
// ============================================================

// Price IDs Stripe par pays (à remplir avec tes vrais Price IDs)
$STRIPE_PRICE_IDS = [
  'fr'  => 'price_XXXXXXXXXX',  // 29€/mois — France/Europe
  'be'  => 'price_XXXXXXXXXX',  // 29€/mois — Belgique
  'ch'  => 'price_XXXXXXXXXX',  // CHF/mois  — Suisse
  'ma'  => 'price_XXXXXXXXXX',  // 199 DH/mois — Maroc
  'sn'  => 'price_XXXXXXXXXX',  // XOF/mois  — Sénégal
  'ci'  => 'price_XXXXXXXXXX',  // XOF/mois  — Côte d'Ivoire
];

$STRIPE_SECRET_KEY = 'sk_live_XXXXXXXXXX'; // Clé secrète Stripe LIVE
$APP_URL = 'https://dambou.fr';

// Récupérer les paramètres passés par l'app
$business_id = $_GET['business_id'] ?? '';
$country     = strtolower($_GET['country'] ?? 'fr');
$email       = $_GET['email'] ?? '';
$action      = $_GET['action'] ?? 'subscribe'; // 'subscribe' | 'manage'

if (empty($business_id)) {
  die('Paramètre business_id manquant');
}

// Choisir le bon Price ID selon le pays
$price_id = $STRIPE_PRICE_IDS[$country] ?? $STRIPE_PRICE_IDS['fr'];

// ── CRÉER UNE SESSION STRIPE CHECKOUT ──────────────────────
require_once __DIR__ . '/vendor/stripe/stripe-php/init.php';
\Stripe\Stripe::setApiKey($STRIPE_SECRET_KEY);

try {
  $session = \Stripe\Checkout\Session::create([
    'mode'                 => 'subscription',
    'payment_method_types' => ['card'],
    'line_items'           => [[
      'price'    => $price_id,
      'quantity' => 1,
    ]],
    'customer_email'       => $email ?: null,
    'client_reference_id'  => $business_id, // On retrouve le business dans le webhook
    'success_url'          => $APP_URL . '/abonnement/success?session_id={CHECKOUT_SESSION_ID}&business_id=' . urlencode($business_id),
    'cancel_url'           => $APP_URL . '/abonnement/cancel',
    'metadata'             => [
      'business_id' => $business_id,
      'country'     => $country,
    ],
    'subscription_data'    => [
      'metadata' => [
        'business_id' => $business_id,
      ],
      'trial_period_days' => 0, // Pas de trial ici, géré côté app
    ],
  ]);

  // Rediriger vers Stripe Checkout
  header('Location: ' . $session->url);
  exit;

} catch (\Exception $e) {
  http_response_code(500);
  echo '<p style="font-family:sans-serif;color:red">Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
