<?php
// dambou.fr/abonnement/success  Page de confirmation après paiement Stripe
$session_id  = $_GET['session_id'] ?? '';
$business_id = $_GET['business_id'] ?? '';
$options     = explode(',', $_GET['options'] ?? '');

$has_stripe  = in_array('stripe_connect', $options);
$has_terminal = in_array('terminal', $options);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bienvenue sur Dambou Pro !</title>
<style>
  :root { --primary: #00BFA5; --primary-dark: #008f7a; --text: #1a1a2e; --text-med: #666; --bg: #f5f7fa; --white: #fff; --border: #e8ecf0; --shadow: 0 2px 12px rgba(0,0,0,0.08); --radius: 16px; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
  .header { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); padding: 32px 20px 48px; text-align: center; color: white; }
  .logo { font-size: 32px; font-weight: 900; letter-spacing: -1px; }
  .container { max-width: 480px; margin: -24px auto 40px; padding: 0 16px; }
  .card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-bottom: 16px; padding: 24px 20px; }
  .success-icon { text-align: center; font-size: 56px; margin-bottom: 12px; }
  .success-title { text-align: center; font-size: 20px; font-weight: 800; color: var(--text); margin-bottom: 8px; }
  .success-sub { text-align: center; font-size: 14px; color: var(--text-med); line-height: 1.5; }
  .steps-title { font-size: 15px; font-weight: 800; margin-bottom: 14px; }
  .step { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
  .step-num { width: 28px; height: 28px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; flex-shrink: 0; }
  .step-content { flex: 1; }
  .step-title { font-size: 14px; font-weight: 700; }
  .step-desc { font-size: 12px; color: var(--text-med); margin-top: 2px; line-height: 1.4; }
  .btn { display: block; width: 100%; padding: 14px; border-radius: 12px; border: none; font-size: 15px; font-weight: 700; cursor: pointer; text-align: center; text-decoration: none; margin-bottom: 10px; }
  .btn-primary { background: var(--primary); color: white; }
  .btn-secondary { background: white; color: var(--primary); border: 2px solid var(--primary); }
  .btn:hover { opacity: 0.9; }
  .footer { text-align: center; padding: 20px; font-size: 11px; color: var(--text-med); }
  .footer a { color: var(--primary); text-decoration: none; }
</style>
</head>
<body>

<div class="header">
  <div class="logo"> DAMBOU Pro</div>
</div>

<div class="container">

  <!-- Confirmation -->
  <div class="card">
    <div class="success-icon"></div>
    <div class="success-title">Bienvenue sur Dambou Pro !</div>
    <div class="success-sub">Votre abonnement est activé. Retournez sur l'application pour commencer.</div>
  </div>

  <!-- Prochaines étapes -->
  <div class="card">
    <div class="steps-title">Vos prochaines étapes</div>

    <div class="step">
      <div class="step-num">1</div>
      <div class="step-content">
        <div class="step-title"> Ouvrez l'application Dambou</div>
        <div class="step-desc">Votre abonnement est automatiquement activé dans l'app.</div>
      </div>
    </div>

    <?php if ($has_stripe || $has_terminal): ?>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-content">
        <div class="step-title"> Configurez votre compte Stripe</div>
        <div class="step-desc">Allez dans Modules  Paiement en ligne  "Connecter Stripe" pour recevoir vos paiements.</div>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($has_terminal): ?>
    <div class="step">
      <div class="step-num"><?= ($has_stripe || $has_terminal) ? 3 : 2 ?></div>
      <div class="step-content">
        <div class="step-title"> Activez votre terminal de paiement</div>
        <div class="step-desc">Commandez votre lecteur WisePad 3 (59) depuis Modules  Terminal de paiement.</div>
      </div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Options activer -->
  <?php if ($has_stripe): ?>
  <div class="card">
    <div class="steps-title"> Activer le paiement en ligne</div>
    <p style="font-size:13px;color:var(--text-med);margin-bottom:16px;line-height:1.5">
      Connectez votre compte Stripe pour accepter les paiements en ligne de vos clients.
      La procédure prend environ 5 minutes.
    </p>
    <a href="dambouapp://modules/stripe_payment" class="btn btn-primary">
      Configurer Stripe dans l'app 
    </a>
    <p style="font-size:11px;color:var(--text-med);text-align:center">
      Ou allez dans Modules  Paiement en ligne dans l'application
    </p>
  </div>
  <?php endif; ?>

  <?php if ($has_terminal): ?>
  <div class="card">
    <div class="steps-title"> Commander votre lecteur</div>
    <p style="font-size:13px;color:var(--text-med);margin-bottom:16px;line-height:1.5">
      Le lecteur WisePad 3 est vendu 59 directement par Stripe. Il accepte les cartes à puce,
      sans contact, Apple Pay et Google Pay via Bluetooth.
    </p>
    <a href="https://dashboard.stripe.com/terminal/shop" target="_blank" class="btn btn-primary">
      Commander sur Stripe (59) 
    </a>
    <a href="dambouapp://modules/terminal" class="btn btn-secondary" style="margin-top:8px">
      Configurer dans l'app 
    </a>
  </div>
  <?php endif; ?>

  <!-- Retour app -->
  <a href="dambouapp://home" class="btn btn-primary">
    Retourner sur l'application 
  </a>

  <div class="footer">
    Des questions ? <a href="mailto:contact@dambou.fr">contact@dambou.fr</a><br><br>
    <a href="https://dambou.fr/cgu">CGU</a> · <a href="https://dambou.fr/privacy">Confidentialité</a>
  </div>

</div>
</body>
</html>
