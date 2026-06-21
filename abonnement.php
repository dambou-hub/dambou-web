<?php
// dambou.fr/abonnement
$PRICES = array(
  'eur' => array(
    'monthly' => 'price_1TkIC3Lh7m1wDGRjQ146c59B',
    'yearly'  => 'price_1TkIDkLh7m1wDGRjIETOsaAt',
    'symbol'  => 'EUR',
    'monthly_amount' => '29',
    'yearly_amount'  => '299',
    'monthly_label'  => '29 EUR / mois',
    'yearly_label'   => '299 EUR / an',
    'equiv_label'    => 'soit 24,90 EUR / mois - 2 mois offerts',
    'flag'           => 'France / Europe',
  ),
  'mad' => array(
    'monthly' => 'price_1TkIFeLh7m1wDGRjvP6hOJmQ',
    'yearly'  => 'price_1TkIGeLh7m1wDGRjipyypKjR',
    'symbol'  => 'DH',
    'monthly_amount' => '199',
    'yearly_amount'  => '1990',
    'monthly_label'  => '199 DH / mois',
    'yearly_label'   => '1990 DH / an',
    'equiv_label'    => 'soit 165 DH / mois - 2 mois offerts',
    'flag'           => 'Maroc',
  ),
  'chf' => array(
    'monthly' => 'price_1TkIHnLh7m1wDGRjM8QJmhPy',
    'yearly'  => 'price_1TkIIgLh7m1wDGRjYbSjlAEL',
    'symbol'  => 'CHF',
    'monthly_amount' => '29',
    'yearly_amount'  => '290',
    'monthly_label'  => '29 CHF / mois',
    'yearly_label'   => '290 CHF / an',
    'equiv_label'    => 'soit 24,17 CHF / mois - 2 mois offerts',
    'flag'           => 'Suisse',
  ),
);

// Detection pays par IP
function detect_country() {
  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
  if (empty($ip)) return 'fr';
  // Utiliser ip-api.com (gratuit, 1000 req/min)
  $response = @file_get_contents('http://ip-api.com/json/' . $ip . '?fields=countryCode');
  if ($response) {
    $data = json_decode($response, true);
    return strtolower($data['countryCode'] ?? 'fr');
  }
  return 'fr';
}

$COUNTRY_TO_CURRENCY = array(
  'fr' => 'eur', 'be' => 'eur', 'lu' => 'eur', 'de' => 'eur',
  'es' => 'eur', 'it' => 'eur', 'pt' => 'eur', 'nl' => 'eur',
  'ma' => 'mad',
  'ch' => 'chf',
);

// Parametre URL prioritaire (depuis l'app)
$country_param = strtolower($_GET['country'] ?? '');
if ($country_param) {
  $country = $country_param;
} else {
  $country = detect_country();
}
$currency_code = $COUNTRY_TO_CURRENCY[$country] ?? 'eur';
$price = $PRICES[$currency_code];

$business_id = $_GET['business_id'] ?? '';
$email       = $_GET['email'] ?? '';

$STRIPE_SECRET_KEY = getenv('STRIPE_SECRET_KEY') ?: '';

// POST - creer session Stripe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($STRIPE_SECRET_KEY)) {
  $plan        = $_POST['plan'] ?? 'monthly';
  $options_raw = $_POST['options'] ?? '';
  $price_id    = $price[$plan];

  $post_data = http_build_query(array(
    'mode'                     => 'subscription',
    'line_items[0][price]'     => $price_id,
    'line_items[0][quantity]'  => '1',
    'success_url'              => 'https://dambou.fr/abonnement-success?session_id={CHECKOUT_SESSION_ID}&business_id=' . urlencode($business_id) . '&options=' . urlencode($options_raw),
    'cancel_url'               => 'https://dambou.fr/abonnement?country=' . urlencode($country) . '&business_id=' . urlencode($business_id),
    'customer_email'           => $email ?: null,
    'metadata[business_id]'    => $business_id,
    'metadata[country]'        => $country,
    'metadata[options]'        => $options_raw,
    'allow_promotion_codes'    => 'true',
  ));

  $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_USERPWD, $STRIPE_SECRET_KEY . ':');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  $result = json_decode(curl_exec($ch), true);
  curl_close($ch);

  if (!empty($result['url'])) {
    header('Location: ' . $result['url']);
    exit;
  } else {
    $stripe_error = $result['error']['message'] ?? 'Erreur inconnue';
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dambou Pro - Abonnement</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #f5f7fa;
  color: #1a1a2e;
  min-height: 100vh;
}
.header {
  background: linear-gradient(135deg, #00BFA5, #008f7a);
  padding: 40px 20px 56px;
  text-align: center;
  color: white;
}
.logo { font-size: 28px; font-weight: 900; letter-spacing: -1px; }
.header h1 { font-size: 20px; font-weight: 600; margin-top: 8px; opacity: 0.9; }
.container {
  max-width: 520px;
  margin: -28px auto 48px;
  padding: 0 16px;
}
.card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.08);
  margin-bottom: 16px;
  overflow: hidden;
}
.card-title {
  padding: 16px 20px 12px;
  font-size: 14px;
  font-weight: 800;
  border-bottom: 1px solid #eee;
  color: #1a1a2e;
}
.country-badge {
  margin: 16px 20px;
  display: inline-block;
  background: #f0faf9;
  border: 1px solid #00BFA5;
  border-radius: 8px;
  padding: 8px 14px;
  font-size: 14px;
  font-weight: 600;
  color: #00BFA5;
}
.plan-toggle {
  display: flex;
  margin: 12px 20px 16px;
  background: #f5f7fa;
  border-radius: 12px;
  padding: 4px;
  gap: 4px;
}
.plan-btn {
  flex: 1;
  padding: 10px 8px;
  border-radius: 9px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  color: #888;
  transition: all 0.15s;
  text-align: center;
}
.plan-btn.active {
  background: white;
  color: #00BFA5;
  box-shadow: 0 1px 6px rgba(0,0,0,0.1);
}
.badge-save {
  display: inline-block;
  background: #FF6B35;
  color: white;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 20px;
  margin-left: 4px;
}
.price-display {
  text-align: center;
  padding: 4px 20px 20px;
}
.price-amount {
  font-size: 52px;
  font-weight: 900;
  color: #00BFA5;
  line-height: 1;
}
.price-period {
  font-size: 15px;
  color: #666;
  margin-top: 6px;
}
.price-equiv {
  font-size: 12px;
  color: #00BFA5;
  margin-top: 4px;
  font-weight: 600;
}
.features {
  padding: 4px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.feature {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}
.feature-icon {
  width: 24px;
  height: 24px;
  background: #f0faf9;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
}
.feature-text {
  font-size: 13px;
  color: #555;
  line-height: 1.4;
  padding-top: 3px;
}
.feature-text strong { color: #1a1a2e; }
.options-list {
  padding: 12px 20px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.option-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px;
  border-radius: 12px;
  border: 2px solid #e8ecf0;
  cursor: pointer;
  transition: all 0.15s;
}
.option-item.checked {
  border-color: #00BFA5;
  background: rgba(0,191,165,0.05);
}
.option-check {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  border: 2px solid #ddd;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: white;
  transition: all 0.15s;
  margin-top: 1px;
}
.option-item.checked .option-check {
  background: #00BFA5;
  border-color: #00BFA5;
}
.option-title {
  font-size: 14px;
  font-weight: 700;
  color: #1a1a2e;
}
.option-desc {
  font-size: 12px;
  color: #666;
  margin-top: 3px;
  line-height: 1.4;
}
.option-price {
  font-size: 11px;
  font-weight: 700;
  color: #00BFA5;
  margin-top: 4px;
}
.submit-btn {
  display: block;
  width: calc(100% - 32px);
  margin: 0 16px 12px;
  padding: 16px;
  background: #00BFA5;
  color: white;
  border: none;
  border-radius: 14px;
  font-size: 16px;
  font-weight: 800;
  cursor: pointer;
  transition: background 0.15s;
}
.submit-btn:hover { background: #008f7a; }
.trial-note {
  text-align: center;
  font-size: 12px;
  color: #888;
  padding: 0 20px 20px;
  line-height: 1.6;
}
.error-box {
  margin: 0 16px 16px;
  padding: 12px 16px;
  background: #fff5f5;
  border: 1px solid #fca5a5;
  border-radius: 10px;
  font-size: 13px;
  color: #dc2626;
}
.footer {
  text-align: center;
  padding: 20px;
  font-size: 11px;
  color: #aaa;
}
.footer a { color: #00BFA5; text-decoration: none; }
@media (min-width: 600px) {
  .container { padding: 0 24px; }
  .price-amount { font-size: 64px; }
}
</style>
</head>
<body>

<div class="header">
  <div class="logo">DAMBOU Pro</div>
  <h1>Abonnement professionnel</h1>
</div>

<div class="container">

<?php if (!empty($stripe_error)): ?>
<div class="error-box">Erreur : <?php echo htmlspecialchars($stripe_error); ?></div>
<?php endif; ?>

<form method="POST" id="mainForm">
<input type="hidden" name="plan" id="planInput" value="monthly">
<input type="hidden" name="options" id="optionsInput" value="">

<!-- Pays detecte -->
<div class="card">
  <div class="card-title">Votre pays</div>
  <div class="country-badge"><?php echo htmlspecialchars($price['flag']); ?></div>
</div>

<!-- Plan -->
<div class="card">
  <div class="card-title">Votre plan</div>
  <div class="plan-toggle">
    <button type="button" class="plan-btn active" id="btn-monthly" onclick="setPlan('monthly')">Mensuel</button>
    <button type="button" class="plan-btn" id="btn-yearly" onclick="setPlan('yearly')">Annuel <span class="badge-save">-14%</span></button>
  </div>
  <div class="price-display">
    <div class="price-amount" id="priceAmount"><?php echo $price['monthly_amount']; ?></div>
    <div class="price-period" id="pricePeriod"><?php echo htmlspecialchars($price['symbol']); ?> / mois</div>
    <div class="price-equiv" id="priceEquiv"></div>
  </div>
  <div class="features">
    <div class="feature">
      <div class="feature-icon">+</div>
      <div class="feature-text"><strong>Commandes en ligne</strong> - vos clients commandent depuis leur telephone</div>
    </div>
    <div class="feature">
      <div class="feature-icon">+</div>
      <div class="feature-text"><strong>Reservations</strong> - agenda et gestion des rendez-vous</div>
    </div>
    <div class="feature">
      <div class="feature-icon">+</div>
      <div class="feature-text"><strong>Caisse (POS)</strong> - encaissement sur place avec recu</div>
    </div>
    <div class="feature">
      <div class="feature-icon">+</div>
      <div class="feature-text"><strong>Fidelite</strong> - programme de points automatique</div>
    </div>
    <div class="feature">
      <div class="feature-icon">+</div>
      <div class="feature-text"><strong>Statistiques</strong> - CA, clients, exports CSV</div>
    </div>
    <div class="feature">
      <div class="feature-icon">+</div>
      <div class="feature-text"><strong>Page vitrine</strong> - dambou.fr/votre-nom</div>
    </div>
  </div>
</div>

<!-- Options -->
<div class="card">
  <div class="card-title">Options (facultatif)</div>
  <div class="options-list">
    <div class="option-item" id="opt-stripe" onclick="toggleOption('stripe_connect')">
      <div class="option-check" id="check-stripe_connect"></div>
      <div>
        <div class="option-title">Paiement en ligne par carte</div>
        <div class="option-desc">Vos clients paient a la commande - carte bancaire, Apple Pay, Google Pay</div>
        <div class="option-price">Inclus dans l'abonnement - 1,4% + 0,10 <?php echo htmlspecialchars($price['symbol']); ?> / transaction</div>
      </div>
    </div>
    <div class="option-item" id="opt-terminal" onclick="toggleOption('terminal')">
      <div class="option-check" id="check-terminal"></div>
      <div>
        <div class="option-title">Terminal de paiement (WisePad 3)</div>
        <div class="option-desc">Encaissez en face a face par carte, sans contact, Apple Pay - lecteur Bluetooth</div>
        <div class="option-price">Lecteur : 59 <?php echo htmlspecialchars($price['symbol']); ?> - 1,4% + 0,10 <?php echo htmlspecialchars($price['symbol']); ?> / transaction</div>
      </div>
    </div>
  </div>
</div>

<button type="submit" class="submit-btn">Commencer - 2 mois gratuits</button>

<p class="trial-note">
  2 mois gratuits, sans carte bancaire requise.<br>
  Resiliez a tout moment, sans engagement.
</p>

</form>

<div class="footer">
  <a href="/cgu">CGU</a> &middot; <a href="/privacy">Confidentialite</a>
</div>

</div>

<script>
var PRICES = {
  monthly_amount: "<?php echo $price['monthly_amount']; ?>",
  yearly_amount:  "<?php echo $price['yearly_amount']; ?>",
  symbol:         "<?php echo $price['symbol']; ?>",
  equiv_label:    "<?php echo addslashes($price['equiv_label']); ?>"
};

var currentPlan = 'monthly';
var selectedOptions = [];

function setPlan(plan) {
  currentPlan = plan;
  document.getElementById('planInput').value = plan;
  document.getElementById('btn-monthly').className = 'plan-btn' + (plan === 'monthly' ? ' active' : '');
  document.getElementById('btn-yearly').className = 'plan-btn' + (plan === 'yearly' ? ' active' : '');
  // Reconstruire innerHTML du bouton yearly avec le badge
  document.getElementById('btn-yearly').innerHTML = plan === 'yearly'
    ? 'Annuel <span class="badge-save">-14%</span>'
    : 'Annuel <span class="badge-save">-14%</span>';
  if (plan === 'yearly') {
    document.getElementById('priceAmount').textContent = PRICES.yearly_amount;
    document.getElementById('pricePeriod').textContent = PRICES.symbol + ' / an';
    document.getElementById('priceEquiv').textContent = PRICES.equiv_label;
  } else {
    document.getElementById('priceAmount').textContent = PRICES.monthly_amount;
    document.getElementById('pricePeriod').textContent = PRICES.symbol + ' / mois';
    document.getElementById('priceEquiv').textContent = '';
  }
}

function toggleOption(option) {
  var idx = selectedOptions.indexOf(option);
  if (idx === -1) {
    selectedOptions.push(option);
  } else {
    selectedOptions.splice(idx, 1);
  }
  var isChecked = selectedOptions.indexOf(option) !== -1;
  var itemId = option === 'stripe_connect' ? 'opt-stripe' : 'opt-terminal';
  document.getElementById(itemId).className = 'option-item' + (isChecked ? ' checked' : '');
  document.getElementById('check-' + option).textContent = isChecked ? 'v' : '';
  document.getElementById('optionsInput').value = selectedOptions.join(',');
}
</script>
</body>
</html>
