<?php
$uri = $_SERVER['REQUEST_URI'];
$uriPath = parse_url($uri, PHP_URL_PATH);
$rawParts = trim($uriPath, '/') === '' ? [] : explode('/', trim($uriPath, '/'));
$parts = array_map('strtolower', $rawParts);
if (!empty($parts)) {
    $lastIdx = count($parts) - 1;
    // Retirer une eventuelle extension .php tapee par l'utilisateur
    $parts[$lastIdx] = preg_replace('/\.php$/', '', $parts[$lastIdx]);
}
$fullPath = implode('/', $parts);

$slug = '';
if (count($parts) >= 1 && !empty($parts[0])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', $parts[0]);
}

// Pages reservees (servies directement, pas traitees comme slug de business)
// Comparaison sur le chemin complet, donc les sous-chemins (ex: pro/login) fonctionnent.
$reservedPages = [
    'privacy'             => 'privacy.php',
    'cgu'                 => 'cgu.php',
    'abonnement'          => 'abonnement.php',
    'abonnement/success'  => 'abonnement/success.php',
    'mentions'            => 'mentions.php',
    'admin'               => 'admin.html',
    'pro'                 => 'pro/dashboard.php',
    'pro/login'           => 'pro/login.php',
    'pro/inscription'     => 'pro/inscription.php',
    'pro/planning'        => 'pro/planning.php',
    'pro/caisse'          => 'pro/caisse.php',
    'pro/business'        => 'pro/business.php',
    'pro/modules'         => 'pro/modules.php',
    'pro/stats'           => 'pro/stats.php',
    'pro/reservations'    => 'pro/reservations.php',
    'pro/employees'       => 'pro/employees.php',
    'pro/catalogue'       => 'pro/catalogue.php',
    'pro/stock'           => 'pro/stock.php',
    'pro/clients'         => 'pro/clients.php',
    'pro/client'          => 'pro/client.php',
    'pro/manual-client'   => 'pro/manual-client.php',
];
if (isset($reservedPages[$fullPath])) {
    $pageFile = __DIR__ . '/' . $reservedPages[$fullPath];
    if (file_exists($pageFile)) {
        include $pageFile;
        exit;
    }
}

// Chemins /pro/... non reconnus ci-dessus : ne jamais les traiter comme slug business
if (!empty($parts) && $parts[0] === 'pro') {
    http_response_code(404);
    die('Page introuvable.');
}

if (!empty($slug) && count($parts) === 1) {
    $file = __DIR__ . '/pro/page.php';
    if (!file_exists($file)) {
        die("Fichier introuvable : " . $file);
    }
    include $file;
    exit;
}

// Page d'accueil
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dambou - G&eacute;rez votre activit&eacute; simplement</title>
<meta name="theme-color" content="#00BFA5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #00BFA5;
    --primary-dark: #00897B;
    --primary-light: #64DFCE;
    --text-dark: #2D3748;
    --text-medium: #718096;
    --text-light: #A0AEC0;
    --background: #F7F8FA;
    --card-border: #E2E8F0;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; color: var(--text-dark); background: var(--background); line-height: 1.5; }
  .wrap { max-width: 1120px; margin: 0 auto; padding: 0 32px; }

  nav { display: flex; align-items: center; justify-content: space-between; padding: 22px 0; }
  .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 18px; }
  .nav-brand img { height: 28px; width: auto; }
  .nav-links { display: flex; gap: 30px; }
  .nav-links a { color: var(--text-medium); text-decoration: none; font-size: 14px; font-weight: 500; }
  .nav-cta { background: var(--primary); color: white; padding: 11px 22px; border-radius: 14px; text-decoration: none; font-size: 14px; font-weight: 600; }

  .hero { padding: 56px 0 40px; }
  .hero-grid { display: grid; grid-template-columns: 1.05fr 0.95fr; gap: 48px; align-items: center; }
  .eyebrow { display: inline-flex; align-items: center; gap: 8px; background: rgba(0,191,165,0.1); color: var(--primary-dark); font-size: 13px; font-weight: 600; padding: 7px 14px; border-radius: 8px; margin-bottom: 20px; }
  h1.headline { font-size: 40px; font-weight: 700; line-height: 1.16; letter-spacing: -0.5px; margin-bottom: 18px; color: var(--text-dark); }
  h1.headline span { color: var(--primary-dark); }
  .hero p.sub { font-size: 17px; color: var(--text-medium); font-weight: 400; max-width: 460px; margin-bottom: 30px; }
  .store-row { display: flex; gap: 12px; margin-bottom: 24px; }
  .store-btn { display: flex; align-items: center; gap: 10px; background: var(--text-dark); color: white; padding: 12px 20px; border-radius: 14px; text-decoration: none; font-size: 13px; }
  .store-btn .store-sub { display: block; font-size: 9px; opacity: 0.75; text-transform: uppercase; letter-spacing: 0.5px; }
  .store-btn .store-main { display: block; font-size: 14px; font-weight: 600; }
  .trust-line { font-size: 13px; color: var(--text-medium); }
  .trust-line strong { color: var(--primary-dark); font-weight: 600; }

  .stat-card { background: linear-gradient(180deg, var(--primary), var(--primary-dark)); border-radius: 20px; padding: 28px 26px; color: white; }
  .stat-card .stat-label { font-size: 14px; font-weight: 500; opacity: 0.85; margin-bottom: 6px; }
  .stat-card .stat-value { font-size: 40px; font-weight: 800; margin-bottom: 4px; }
  .stat-card .stat-sub { font-size: 13px; opacity: 0.75; margin-bottom: 20px; }
  .stat-rows { background: rgba(255,255,255,0.15); border-radius: 8px; padding: 14px 16px; }
  .stat-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 14px; font-weight: 600; }
  .stat-row + .stat-row { border-top: 1px solid rgba(255,255,255,0.18); }
  .stat-row .row-label { display: flex; align-items: center; gap: 8px; opacity: 0.95; font-weight: 500; }

  .features { padding: 56px 0 8px; }
  .section-head { text-align: center; max-width: 540px; margin: 0 auto 36px; }
  .section-head .eyebrow { display: inline-flex; }
  .section-head h2 { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
  .section-head p { color: var(--text-medium); font-size: 15px; }
  .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
  .feature-card { background: white; border: 1px solid var(--card-border); border-radius: 16px; padding: 22px 20px; }
  .feature-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; font-size: 21px; }
  .feature-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 5px; }
  .feature-card p { font-size: 13px; color: var(--text-medium); line-height: 1.5; }

  .proof { padding: 56px 0; text-align: center; }
  .proof p.label { font-size: 12px; color: var(--text-medium); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; font-weight: 600; }
  .proof-row { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; }
  .proof-pill { border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 8px; }

  .pricing-band { background: var(--text-dark); border-radius: 20px; margin: 0 32px 72px; padding: 52px 40px; text-align: center; color: white; max-width: 1056px; margin-left: auto; margin-right: auto; }
  .pricing-band h2 { font-size: 26px; font-weight: 700; margin-bottom: 10px; }
  .pricing-band .price-line { font-size: 15px; color: rgba(255,255,255,0.7); margin-bottom: 26px; }
  .pricing-band .price-line strong { color: var(--primary-light); font-size: 17px; font-weight: 600; }
  .pricing-cta { display: inline-block; background: var(--primary); color: white; padding: 14px 30px; border-radius: 14px; text-decoration: none; font-weight: 600; font-size: 14px; }

  footer { border-top: 1px solid var(--card-border); padding: 26px 0; }
  .footer-row { display: flex; align-items: center; justify-content: space-between; }
  .footer-links { display: flex; gap: 22px; }
  .footer-links a { color: var(--text-medium); text-decoration: none; font-size: 13px; }
  .footer-copy { color: var(--text-medium); font-size: 13px; }

  @media (max-width: 860px) {
    .hero-grid { grid-template-columns: 1fr; }
    .feature-grid { grid-template-columns: repeat(2, 1fr); }
    .nav-links { display: none; }
    h1.headline { font-size: 32px; }
  }
</style>
</head>
<body>

<div class="wrap">
  <nav>
    <div class="nav-brand"><img src="/assets/icon.png" alt=""> Dambou</div>
    <div class="nav-links">
      <a href="#fonctionnalites">Fonctionnalit&eacute;s</a>
      <a href="/abonnement">Tarifs</a>
      <a href="mailto:contact@dambou.fr">Contact</a>
    </div>
    <a class="nav-cta" href="/pro/login">Espace Pro</a>
  </nav>
</div>

<section class="hero">
  <div class="wrap">
    <div class="hero-grid">
      <div>
        <div class="eyebrow">L'appli tout-en-un pour les pros ind&eacute;pendants</div>
        <h1 class="headline">Votre activit&eacute;, <span>pilot&eacute;e depuis votre poche.</span></h1>
        <p class="sub">Commandes, r&eacute;servations, caisse, fid&eacute;lit&eacute; et statistiques : Dambou r&eacute;unit tout ce qu'il faut pour g&eacute;rer votre business au quotidien, sans prise de t&ecirc;te.</p>
        <div class="store-row">
          <a class="store-btn" href="https://apps.apple.com/app/dambou/idXXXXXXXXX"><span><span class="store-sub">T&eacute;l&eacute;charger sur</span><span class="store-main">App Store</span></span></a>
          <a class="store-btn" href="https://play.google.com/store/apps/details?id=com.num0.dambou"><span><span class="store-sub">Disponible sur</span><span class="store-main">Google Play</span></span></a>
        </div>
        <p class="trust-line"><strong>2 mois gratuits</strong>, sans carte bancaire</p>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total encaiss&eacute;</div>
        <div class="stat-value">463,52&nbsp;&euro;</div>
        <div class="stat-sub">juin 2026</div>
        <div class="stat-rows">
          <div class="stat-row"><span class="row-label">Commandes Dambou</span><span>280,76&nbsp;&euro;</span></div>
          <div class="stat-row"><span class="row-label">Caisse sur place</span><span>182,76&nbsp;&euro;</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="features" id="fonctionnalites">
  <div class="wrap">
    <div class="section-head">
      <div class="eyebrow">Fonctionnalit&eacute;s</div>
      <h2>Bien plus qu'une caisse</h2>
      <p>Six modules qui couvrent tout votre quotidien, d&eacute;j&agrave; utilis&eacute;s par des food trucks, coiffeurs et artisans.</p>
    </div>
    <div class="feature-grid">
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(244,162,97,0.1)">&#128722;</div>
        <h3>Commandes</h3>
        <p>Re&ccedil;ues en temps r&eacute;el, avec notification instantan&eacute;e d&egrave;s qu'un client commande.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(0,191,165,0.1)">&#128197;</div>
        <h3>R&eacute;servations</h3>
        <p>Agenda clair par employ&eacute;, confirmation automatique pour vos clients.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(82,183,136,0.1)">&#128179;</div>
        <h3>Caisse</h3>
        <p>Encaissement sur place, carte ou esp&egrave;ces, sans doublon avec les commandes en ligne.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(233,30,140,0.1)">&#127873;</div>
        <h3>Fid&eacute;lit&eacute;</h3>
        <p>Points cumul&eacute;s automatiquement, r&eacute;compenses simples &agrave; configurer.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(0,151,167,0.1)">&#128202;</div>
        <h3>Statistiques</h3>
        <p>Chiffre d'affaires par jour, semaine ou mois, export en un clic.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon" style="background:rgba(108,99,255,0.1)">&#129534;</div>
        <h3>Catalogue</h3>
        <p>Produits et services &agrave; jour, visibles sur votre page en ligne.</p>
      </div>
    </div>
  </div>
</section>

<section class="proof">
  <div class="wrap">
    <p class="label">D&eacute;j&agrave; utilis&eacute; par</p>
    <div class="proof-row">
      <div class="proof-pill" style="background:rgba(244,162,97,0.1);color:#a3591e">FREDDY'Z PIZZ, food truck</div>
      <div class="proof-pill" style="background:rgba(233,30,140,0.1);color:#a3115f">Salon de massage, Marrakech</div>
      <div class="proof-pill" style="background:rgba(108,99,255,0.1);color:#4a41c9">Orabel, salon de beaut&eacute;</div>
    </div>
  </div>
</section>

<div class="pricing-band">
  <h2>Un tarif simple, sans surprise</h2>
  <p class="price-line"><strong>29 EUR / mois</strong> apr&egrave;s 2 mois d'essai gratuit, sans carte bancaire</p>
  <a class="pricing-cta" href="/abonnement">D&eacute;marrer gratuitement</a>
</div>

<footer>
  <div class="wrap footer-row">
    <div class="footer-copy">&#169; 2026 Dambou</div>
    <div class="footer-links">
      <a href="/privacy">Confidentialit&eacute;</a>
      <a href="/cgu">CGU</a>
      <a href="mailto:contact@dambou.fr">Contact</a>
    </div>
  </div>
</footer>

</body>
</html>