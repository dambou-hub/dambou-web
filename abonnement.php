<?php
// ============================================================
// dambou.fr/abonnement — Page de souscription Dambou Pro
// ============================================================

// ── Price IDs Stripe ────────────────────────────────────────
$PRICES = [
  'eur' => [
    'monthly' => 'price_1TkIC3Lh7m1wDGRjQ146c59B', // 29€/mois
    'yearly'  => 'price_1TkIDkLh7m1wDGRjIETOsaAt', // 299€/an
    'symbol'  => '€', 'monthly_amount' => 29, 'yearly_amount' => 299,
    'currency' => 'EUR',
  ],
  'mad' => [
    'monthly' => 'price_1TkIFeLh7m1wDGRjvP6hOJmQ', // 199 DH/mois
    'yearly'  => 'price_1TkIGeLh7m1wDGRjipyypKjR', // 1990 DH/an
    'symbol'  => 'DH', 'monthly_amount' => 199, 'yearly_amount' => 1990,
    'currency' => 'MAD',
  ],
  'chf' => [
    'monthly' => 'price_1TkIHnLh7m1wDGRjM8QJmhPy', // 29 CHF/mois
    'yearly'  => 'price_1TkIIgLh7m1wDGRjYbSjlAEL', // 290 CHF/an
    'symbol'  => 'CHF', 'monthly_amount' => 29, 'yearly_amount' => 290,
    'currency' => 'CHF',
  ],
];

// Mapping pays → devise
$COUNTRY_CURRENCY = [
  'fr' => 'eur', 'be' => 'eur', 'lu' => 'eur',
  'ma' => 'mad',
  'ch' => 'chf',
  // Défaut
];

$STRIPE_SECRET_KEY = getenv('STRIPE_SECRET_KEY') ?: 'sk_test_XXXXXXXXXX';

// ── Paramètres URL ───────────────────────────────────────────
$business_id = $_GET['business_id'] ?? '';
$country     = strtolower($_GET['country'] ?? 'fr');
$email       = $_GET['email'] ?? '';
$plan        = $_GET['plan'] ?? 'monthly'; // monthly | yearly
$options     = explode(',', $_GET['options'] ?? ''); // stripe_connect, terminal

$currency_code = $COUNTRY_CURRENCY[$country] ?? 'eur';
$price_data = $PRICES[$currency_code];

// ── POST : Créer session Stripe Checkout ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $selected_plan = $_POST['plan'] ?? 'monthly';
  $selected_options = $_POST['options'] ?? [];
  $price_id = $price_data[$selected_plan];

  // Appel API Stripe
  $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => $STRIPE_SECRET_KEY . ':',
    CURLOPT_POSTFIELDS => http_build_query([
      'mode' => 'subscription',
      'line_items[0][price]' => $price_id,
      'line_items[0][quantity]' => 1,
      'success_url' => 'https://dambou.fr/abonnement/success?session_id={CHECKOUT_SESSION_ID}&business_id=' . urlencode($business_id) . '&options=' . urlencode(implode(',', $selected_options)),
      'cancel_url' => 'https://dambou.fr/abonnement?business_id=' . urlencode($business_id) . '&country=' . urlencode($country),
      'customer_email' => $email ?: null,
      'metadata[business_id]' => $business_id,
      'metadata[country]' => $country,
      'metadata[options]' => implode(',', $selected_options),
      'allow_promotion_codes' => 'true',
    ]),
  ]);
  $result = json_decode(curl_exec($ch), true);
  curl_close($ch);

  if (isset($result['url'])) {
    header('Location: ' . $result['url']);
    exit;
  } else {
    $error = $result['error']['message'] ?? 'Erreur Stripe';
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dambou Pro — Abonnement</title>
<style>
  :root {
    --primary: #00BFA5;
    --primary-dark: #008f7a;
    --text: #1a1a2e;
    --text-med: #666;
    --bg: #f5f7fa;
    --white: #ffffff;
    --border: #e8ecf0;
    --shadow: 0 2px 12px rgba(0,0,0,0.08);
    --radius: 16px;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: var(--bg); color: var(--text); min-height: 100vh; }

  .header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    padding: 32px 20px 48px; text-align: center; color: white;
  }
  .logo { font-size: 32px; font-weight: 900; letter-spacing: -1px; }
  .logo span { opacity: 0.8; }
  .header h1 { font-size: 22px; font-weight: 700; margin-top: 8px; opacity: 0.95; }

  .container { max-width: 480px; margin: -24px auto 40px; padding: 0 16px; }

  .card {
    background: var(--white); border-radius: var(--radius);
    box-shadow: var(--shadow); overflow: hidden; margin-bottom: 16px;
  }
  .card-title {
    padding: 16px 20px 12px; font-size: 15px; font-weight: 800;
    border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 8px;
  }

  /* Sélecteur pays */
  .country-select {
    display: flex; gap: 8px; padding: 16px; flex-wrap: wrap;
  }
  .country-btn {
    flex: 1; min-width: 80px; padding: 10px 8px; border-radius: 10px;
    border: 2px solid var(--border); background: white; cursor: pointer;
    font-size: 13px; font-weight: 600; text-align: center; transition: all 0.15s;
  }
  .country-btn.active { border-color: var(--primary); background: rgba(0,191,165,0.08); color: var(--primary); }
  .country-btn:hover { border-color: var(--primary); }

  /* Toggle mensuel/annuel */
  .plan-toggle { display: flex; margin: 16px; background: var(--bg); border-radius: 12px; padding: 4px; gap: 4px; }
  .plan-btn {
    flex: 1; padding: 10px; border-radius: 9px; border: none;
    background: transparent; cursor: pointer; font-size: 13px; font-weight: 600;
    color: var(--text-med); transition: all 0.15s; text-align: center;
  }
  .plan-btn.active { background: white; color: var(--primary); box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
  .badge-save {
    display: inline-block; background: #FF6B35; color: white;
    font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 20px; margin-left: 4px;
  }

  /* Prix */
  .price-display { text-align: center; padding: 8px 20px 20px; }
  .price-amount { font-size: 48px; font-weight: 900; color: var(--primary); line-height: 1; }
  .price-period { font-size: 14px; color: var(--text-med); margin-top: 4px; }
  .price-equiv { font-size: 12px; color: var(--text-med); margin-top: 2px; }

  /* Features */
  .features { padding: 0 20px 20px; }
  .feature { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 10px; }
  .feature-icon { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
  .feature-text { font-size: 13px; color: var(--text-med); line-height: 1.4; }
  .feature-text strong { color: var(--text); }

  /* Options */
  .options-list { padding: 12px 20px 16px; display: flex; flex-direction: column; gap: 10px; }
  .option-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px 14px; border-radius: 12px; border: 2px solid var(--border);
    cursor: pointer; transition: all 0.15s; background: white;
  }
  .option-item.checked { border-color: var(--primary); background: rgba(0,191,165,0.06); }
  .option-check {
    width: 22px; height: 22px; border-radius: 6px; border: 2px solid var(--border);
    flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    margin-top: 1px; transition: all 0.15s;
  }
  .option-item.checked .option-check { background: var(--primary); border-color: var(--primary); color: white; }
  .option-content { flex: 1; }
  .option-title { font-size: 14px; font-weight: 700; color: var(--text); }
  .option-desc { font-size: 12px; color: var(--text-med); margin-top: 2px; line-height: 1.4; }
  .option-price { font-size: 12px; font-weight: 700; color: var(--primary); margin-top: 4px; }

  /* Bouton */
  .submit-btn {
    display: block; width: calc(100% - 32px); margin: 0 16px 20px;
    padding: 16px; background: var(--primary); color: white; border: none;
    border-radius: 14px; font-size: 16px; font-weight: 800; cursor: pointer;
    transition: background 0.15s; text-align: center;
  }
  .submit-btn:hover { background: var(--primary-dark); }
  .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

  .trial-note {
    text-align: center; font-size: 12px; color: var(--text-med);
    padding: 0 20px 16px; line-height: 1.5;
  }

  .error-msg {
    margin: 0 16px 16px; padding: 12px 16px; background: #fff5f5;
    border: 1px solid #feb2b2; border-radius: 10px;
    font-size: 13px; color: #c53030;
  }

  .footer { text-align: center; padding: 20px; font-size: 11px; color: var(--text-med); }
  .footer a { color: var(--primary); text-decoration: none; }
</style>
</head>
<body>

<div class="header">
  <div class="logo">🥟 DAMBOU<span> Pro</span></div>
  <h1>Abonnement professionnel</h1>
</div>

<div class="container">

<?php if (isset($error)): ?>
<div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" id="subscribeForm">

  <!-- Sélecteur pays -->
  <div class="card">
    <div class="card-title">🌍 Votre pays</div>
    <div class="country-select">
      <button type="button" class="country-btn <?= $currency_code === 'eur' ? 'active' : '' ?>" onclick="setCurrency('eur')">🇫🇷 France / Europe</button>
      <button type="button" class="country-btn <?= $currency_code === 'mad' ? 'active' : '' ?>" onclick="setCurrency('mad')">🇲🇦 Maroc</button>
      <button type="button" class="country-btn <?= $currency_code === 'chf' ? 'active' : '' ?>" onclick="setCurrency('chf')">🇨🇭 Suisse</button>
    </div>
  </div>

  <!-- Choix du plan -->
  <div class="card">
    <div class="card-title">📅 Votre plan</div>

    <div class="plan-toggle">
      <button type="button" class="plan-btn active" id="btn-monthly" onclick="setPlan('monthly')">
        Mensuel
      </button>
      <button type="button" class="plan-btn" id="btn-yearly" onclick="setPlan('yearly')">
        Annuel <span class="badge-save">-14%</span>
      </button>
    </div>

    <div class="price-display">
      <div class="price-amount" id="priceAmount">29</div>
      <div class="price-period" id="pricePeriod">€ / mois</div>
      <div class="price-equiv" id="priceEquiv"></div>
    </div>

    <div class="features">
      <div class="feature">
        <span class="feature-icon">🛒</span>
        <div class="feature-text"><strong>Commandes en ligne</strong> — vos clients commandent depuis leur téléphone</div>
      </div>
      <div class="feature">
        <span class="feature-icon">📅</span>
        <div class="feature-text"><strong>Réservations</strong> — agenda et gestion des rendez-vous</div>
      </div>
      <div class="feature">
        <span class="feature-icon">💳</span>
        <div class="feature-text"><strong>Caisse (POS)</strong> — encaissement sur place avec reçu</div>
      </div>
      <div class="feature">
        <span class="feature-icon">⭐</span>
        <div class="feature-text"><strong>Fidélité</strong> — programme de points automatique</div>
      </div>
      <div class="feature">
        <span class="feature-icon">📊</span>
        <div class="feature-text"><strong>Statistiques</strong> — CA, clients, exports CSV</div>
      </div>
      <div class="feature">
        <span class="feature-icon">🌐</span>
        <div class="feature-text"><strong>Page vitrine</strong> — dambou.fr/votre-nom</div>
      </div>
    </div>
  </div>

  <!-- Options -->
  <div class="card">
    <div class="card-title">⚡ Options (facultatif)</div>
    <div class="options-list">

      <!-- Option paiement en ligne -->
      <div class="option-item" id="opt-stripe" onclick="toggleOption('stripe_connect')">
        <div class="option-check" id="check-stripe_connect"></div>
        <div class="option-content">
          <div class="option-title">💳 Paiement en ligne par carte</div>
          <div class="option-desc">Vos clients paient à la commande — carte bancaire, Apple Pay, Google Pay</div>
          <div class="option-price">Inclus dans l'abonnement · 1,4% + 0,10€ / transaction</div>
        </div>
      </div>

      <!-- Option terminal -->
      <div class="option-item" id="opt-terminal" onclick="toggleOption('terminal')">
        <div class="option-check" id="check-terminal"></div>
        <div class="option-content">
          <div class="option-title">🖨️ Terminal de paiement (WisePad 3)</div>
          <div class="option-desc">Encaissez en face à face par carte, sans contact, Apple Pay — lecteur Bluetooth</div>
          <div class="option-price">Lecteur : 59€ · 1,4% + 0,10€ / transaction</div>
        </div>
      </div>

    </div>
  </div>

  <input type="hidden" name="plan" id="planInput" value="monthly">
  <input type="hidden" name="currency" id="currencyInput" value="<?= htmlspecialchars($currency_code) ?>">
  <input type="hidden" name="options[]" id="optionsInput" value="">

  <button type="submit" class="submit-btn" id="submitBtn">
    Commencer l'essai gratuit — 2 mois offerts 🎉
  </button>

  <p class="trial-note">
    2 mois gratuits, sans carte bancaire requise.<br>
    Résiliez à tout moment, sans engagement.
  </p>

</form>

<div class="footer">
  <a href="https://dambou.fr/cgu">CGU</a> · <a href="https://dambou.fr/privacy">Confidentialité</a>
</div>

</div>

<script>
// ── Données prix ─────────────────────────────────────────────
const PRICES = {
  eur: { monthly: 29, yearly: 299, symbol: '€', label: 'France / Europe', period_m: '€ / mois', period_y: '€ / an', equiv_y: 'soit 24,90€ / mois — 2 mois offerts' },
  mad: { monthly: 199, yearly: 1990, symbol: ' DH', label: 'Maroc', period_m: 'DH / mois', period_y: 'DH / an', equiv_y: 'soit 165,83 DH / mois — 2 mois offerts' },
  chf: { monthly: 29, yearly: 290, symbol: ' CHF', label: 'Suisse', period_m: 'CHF / mois', period_y: 'CHF / an', equiv_y: 'soit 24,17 CHF / mois — 2 mois offerts' },
};

let currentCurrency = '<?= $currency_code ?>';
let currentPlan = 'monthly';
let selectedOptions = [];

function setCurrency(currency) {
  currentCurrency = currency;
  document.getElementById('currencyInput').value = currency;
  // Mettre à jour les boutons pays
  document.querySelectorAll('.country-btn').forEach(b => b.classList.remove('active'));
  event.target.classList.add('active');
  updatePrice();
}

function setPlan(plan) {
  currentPlan = plan;
  document.getElementById('planInput').value = plan;
  document.getElementById('btn-monthly').classList.toggle('active', plan === 'monthly');
  document.getElementById('btn-yearly').classList.toggle('active', plan === 'yearly');
  updatePrice();
}

function updatePrice() {
  const p = PRICES[currentCurrency];
  const isYearly = currentPlan === 'yearly';
  document.getElementById('priceAmount').textContent = isYearly ? p.yearly : p.monthly;
  document.getElementById('pricePeriod').textContent = isYearly ? p.period_y : p.period_m;
  document.getElementById('priceEquiv').textContent = isYearly ? p.equiv_y : '';
}

function toggleOption(option) {
  const idx = selectedOptions.indexOf(option);
  if (idx === -1) {
    selectedOptions.push(option);
  } else {
    selectedOptions.splice(idx, 1);
  }
  // Mettre à jour UI
  const item = document.getElementById('opt-' + (option === 'stripe_connect' ? 'stripe' : 'terminal'));
  const check = document.getElementById('check-' + option);
  if (selectedOptions.includes(option)) {
    item.classList.add('checked');
    check.innerHTML = '✓';
  } else {
    item.classList.remove('checked');
    check.innerHTML = '';
  }
  // Mettre à jour le champ hidden
  document.getElementById('optionsInput').value = selectedOptions.join(',');
  // Mettre à jour le bouton
  updateSubmitBtn();
}

function updateSubmitBtn() {
  const btn = document.getElementById('submitBtn');
  const p = PRICES[currentCurrency];
  const amount = currentPlan === 'yearly' ? p.yearly : p.monthly;
  const sym = p.symbol;
  const period = currentPlan === 'yearly' ? '/an' : '/mois';
  btn.textContent = `Commencer l'essai gratuit — 2 mois offerts 🎉`;
}

// Init
updatePrice();
</script>

</body>
</html>
