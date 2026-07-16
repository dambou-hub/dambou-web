<?php
$uri = $_SERVER['REQUEST_URI'];
$uriPath = parse_url($uri, PHP_URL_PATH);
$rawParts = trim($uriPath, '/') === '' ? [] : explode('/', trim($uriPath, '/'));
$parts = array_map('strtolower', $rawParts);
if (!empty($parts)) {
    $lastIdx = count($parts) - 1;
    // Retirer une éventuelle extension .php tapée par l'utilisateur
    $parts[$lastIdx] = preg_replace('/\.php$/', '', $parts[$lastIdx]);
}
$fullPath = implode('/', $parts);

$slug = '';
if (count($parts) >= 1 && !empty($parts[0])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', $parts[0]);
}

// Pages réservées (servies directement, pas traitées comme slug de business)
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
    'pro/planning'        => 'pro/planning.php',
    'pro/reservations'    => 'pro/reservations.php',
    'pro/catalogue'       => 'pro/catalogue.php',
    'pro/clients'         => 'pro/clients.php',
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
  <title>Dambou — Gérez votre activité simplement</title>
  <meta name="theme-color" content="#1D9E75">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, sans-serif; background: #1D9E75; color: white; min-height: 100vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 24px; }
    h1 { font-size: 32px; font-weight: 900; margin-bottom: 12px; }
    p { font-size: 16px; opacity: 0.85; margin-bottom: 32px; }
    .btn { display: inline-block; background: white; color: #1D9E75; padding: 14px 28px; border-radius: 14px; font-size: 15px; font-weight: 700; text-decoration: none; margin: 8px; }
  </style>
</head>
<body>
  <div>
    <div style="font-size:64px;margin-bottom:16px;">🌿</div>
    <h1>Dambou</h1>
    <p>Gérez votre activité simplement.<br>Réservations, commandes, caisse.</p>
    <a class="btn" href="https://play.google.com/store/apps/details?id=com.num0.dambou">📱 Android</a>
    <a class="btn" href="https://apps.apple.com/app/dambou/idXXXXXXXXX">🍎 iPhone</a>
  </div>
</body>
</html>