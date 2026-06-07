<?php
$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($uri, '/'));
$slug = '';

if (count($parts) >= 1 && !empty($parts[0])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower($parts[0]));
}

// Pages réservées (servies directement, pas traitées comme slug de business)
$reservedPages = [
    'privacy'    => 'privacy.php',
    'cgu'        => 'cgu.php',
    'abonnement' => 'abonnement.php',
    'mentions'   => 'mentions.php',
    'admin'      => 'admin.html',
];
$first = strtolower($parts[0] ?? '');
// Retirer une éventuelle extension .php tapée par l'utilisateur
$first = preg_replace('/\.php$/', '', $first);
if (isset($reservedPages[$first])) {
    $pageFile = __DIR__ . '/' . $reservedPages[$first];
    if (file_exists($pageFile)) {
        include $pageFile;
        exit;
    }
}

if (!empty($slug)) {
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