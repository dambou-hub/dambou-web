<?php
// dambou.fr/abonnement-success
$session_id  = $_GET['session_id'] ?? '';
$business_id = $_GET['business_id'] ?? '';
$options_raw = $_GET['options'] ?? '';
$options     = array_filter(explode(',', $options_raw));

$has_stripe   = in_array('stripe_connect', $options);
$has_terminal = in_array('terminal', $options);

$SUPABASE_URL     = 'https://unwrghiiocaztnecmpeh.supabase.co';
$SUPABASE_ANON    = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVud3JnaGlpb2NhenRuZWNtcGVoIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ2Mjc4NTUsImV4cCI6MjA4MDIwMzg1NX0.m9s85OKGVTQbItxB8bHaCpfpvICRf5tWSztUyLvOeZw';

// Generer le lien onboarding Stripe Connect si necessaire
$stripe_onboarding_url = '';
if (($has_stripe || $has_terminal) && !empty($business_id)) {
  // 1. Recuperer le stripe_account_id du business depuis Supabase
  $ch = curl_init($SUPABASE_URL . '/rest/v1/businesses?id=eq.' . urlencode($business_id) . '&select=stripe_account_id');
  curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array(
      'apikey: ' . $SUPABASE_ANON,
      'Authorization: Bearer ' . $SUPABASE_ANON,
    )
  ));
  $biz_result = json_decode(curl_exec($ch), true);
  curl_close($ch);

  $stripe_account_id = $biz_result[0]['stripe_account_id'] ?? '';

  if (!empty($stripe_account_id)) {
    // 2. Creer le lien onboarding via Edge Function
    $ch = curl_init($SUPABASE_URL . '/functions/v1/stripe-payment');
    curl_setopt_array($ch, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_HTTPHEADER => array(
        'apikey: ' . $SUPABASE_ANON,
        'Authorization: Bearer ' . $SUPABASE_ANON,
        'Content-Type: application/json',
      ),
      CURLOPT_POSTFIELDS => json_encode(array(
        'action'     => 'create_onboarding_link',
        'account_id' => $stripe_account_id,
      ))
    ));
    $ef_result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    $stripe_onboarding_url = $ef_result['url'] ?? '';
  }
}

$step = 2;
if ($has_stripe || $has_terminal) $step = 3;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bienvenue sur Dambou Pro !</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7fa; color: #1a1a2e; min-height: 100vh; }
.header { background: linear-gradient(135deg, #00BFA5, #008f7a); padding: 40px 20px 56px; text-align: center; color: white; }
.logo { font-size: 28px; font-weight: 900; letter-spacing: -1px; }
.container { max-width: 520px; margin: -28px auto 48px; padding: 0 16px; }
.card { background: white; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); margin-bottom: 16px; padding: 24px 20px; }
.success-icon { text-align: center; font-size: 56px; margin-bottom: 12px; }
.success-title { text-align: center; font-size: 20px; font-weight: 800; margin-bottom: 8px; }
.success-sub { text-align: center; font-size: 14px; color: #666; line-height: 1.6; }
.section-title { font-size: 15px; font-weight: 800; margin-bottom: 16px; }
.step { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 16px; }
.step-num { width: 30px; height: 30px; background: #00BFA5; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; flex-shrink: 0; }
.step-title { font-size: 14px; font-weight: 700; margin-bottom: 3px; }
.step-desc { font-size: 12px; color: #666; line-height: 1.5; }
.btn { display: block; width: 100%; padding: 15px; border-radius: 12px; border: none; font-size: 15px; font-weight: 700; cursor: pointer; text-align: center; text-decoration: none; margin-bottom: 10px; }
.btn-primary { background: #00BFA5; color: white; }
.btn-secondary { background: white; color: #00BFA5; border: 2px solid #00BFA5; }
.btn-stripe { background: #635BFF; color: white; }
.btn:hover { opacity: 0.9; }
.note { font-size: 11px; color: #aaa; text-align: center; margin-top: 6px; line-height: 1.5; }
.footer { text-align: center; padding: 20px; font-size: 11px; color: #aaa; }
.footer a { color: #00BFA5; text-decoration: none; }
</style>
</head>
<body>

<div class="header">
  <div class="logo">DAMBOU Pro</div>
</div>

<div class="container">

  <!-- Confirmation -->
  <div class="card">
    <div class="success-icon">&#10003;</div>
    <div class="success-title">Abonnement active !</div>
    <div class="success-sub">
      Bienvenue sur Dambou Pro. Retournez sur l'application pour commencer.
    </div>
  </div>

  <!-- Etapes suivantes -->
  <div class="card">
    <div class="section-title">Vos prochaines etapes</div>

    <div class="step">
      <div class="step-num">1</div>
      <div>
        <div class="step-title">Ouvrez l'application Dambou</div>
        <div class="step-desc">Votre abonnement est automatiquement actif dans l'app.</div>
      </div>
    </div>

    <?php if ($has_stripe || $has_terminal): ?>
    <div class="step">
      <div class="step-num">2</div>
      <div>
        <div class="step-title">Connectez votre compte Stripe</div>
        <div class="step-desc">
          Indispensable pour recevoir les paiements de vos clients.
          La procedure prend environ 5 minutes (piece d'identite + RIB).
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($has_terminal): ?>
    <div class="step">
      <div class="step-num"><?php echo $step; ?></div>
      <div>
        <div class="step-title">Commandez votre lecteur WisePad 3</div>
        <div class="step-desc">59 EUR, livre directement par Stripe. Bluetooth, sans contact, Apple Pay.</div>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Bouton Stripe Connect si option choisie -->
  <?php if ($has_stripe || $has_terminal): ?>
  <div class="card">
    <div class="section-title">Connecter votre compte Stripe</div>
    <p style="font-size:13px;color:#666;margin-bottom:16px;line-height:1.6">
      Pour recevoir les paiements de vos clients (en ligne ou par terminal), 
      vous devez connecter votre compte Stripe. C'est rapide et securise.
    </p>
    <?php if (!empty($stripe_onboarding_url)): ?>
    <a href="<?php echo htmlspecialchars($stripe_onboarding_url); ?>" class="btn btn-stripe">
      Connecter mon compte Stripe
    </a>
    <?php else: ?>
    <a href="dambouapp://modules/stripe_payment" class="btn btn-stripe">
      Configurer Stripe dans l'app
    </a>
    <p class="note">Allez dans Modules &gt; Paiement en ligne dans l'application</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Commander terminal -->
  <?php if ($has_terminal): ?>
  <div class="card">
    <div class="section-title">Commander votre lecteur de carte</div>
    <p style="font-size:13px;color:#666;margin-bottom:16px;line-height:1.6">
      Le lecteur WisePad 3 est vendu 59 EUR directement par Stripe.
      Il accepte les cartes a puce, sans contact, Apple Pay et Google Pay via Bluetooth.
    </p>
    <a href="https://dashboard.stripe.com/terminal/shop" target="_blank" class="btn btn-primary">
      Commander sur Stripe (59 EUR)
    </a>
    <p class="note">Une fois recu, configurez-le depuis Modules &gt; Terminal dans l'app.</p>
  </div>
  <?php endif; ?>

  <!-- Retour app -->
  <a href="dambouapp://home" class="btn btn-primary">
    Retourner dans l'application
  </a>

  <div class="footer">
    Des questions ? <a href="mailto:contact@dambou.fr">contact@dambou.fr</a><br><br>
    <a href="/cgu">CGU</a> &middot; <a href="/privacy">Confidentialite</a>
  </div>

</div>
</body>
</html>
