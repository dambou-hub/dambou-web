<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$SUPABASE_URL = 'https://unwrghiiocaztnecmpeh.supabase.co';
$SUPABASE_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InVud3JnaGlpb2NhenRuZWNtcGVoIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjQ2Mjc4NTUsImV4cCI6MjA4MDIwMzg1NX0.m9s85OKGVTQbItxB8bHaCpfpvICRf5tWSztUyLvOeZw';

// \$slug est fourni par index.php parent

function supabase_get($url, $key, $endpoint) {
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", [
                "apikey: $key",
                "Authorization: Bearer $key",
                "Content-Type: application/json",
            ])
        ]
    ];
    $context = stream_context_create($opts);
    $res = @file_get_contents("$url/rest/v1/$endpoint", false, $context);
    if ($res === false) return [];
    return json_decode($res, true) ?? [];
}

$biz = supabase_get($SUPABASE_URL, $SUPABASE_KEY,
    "businesses?slug=eq.$slug&is_active=eq.true&select=id,name,description,category,address,phone,logo_url,cover_url,currency_code,closure_message&limit=1");

if (empty($biz)) {
    http_response_code(404);
    die('Business introuvable — slug: ' . htmlspecialchars($slug));
}
$b = $biz[0];

$bizId = $b['id'];
$name = htmlspecialchars($b['name'] ?? '');
$desc = htmlspecialchars($b['description'] ?? '');
$addrRaw = $b['address'] ?? '';
$address = is_array($addrRaw) 
    ? htmlspecialchars($addrRaw['formatted'] ?? $addrRaw['city'] ?? '') 
    : htmlspecialchars($addrRaw);
$phone = htmlspecialchars($b['phone'] ?? '');
$logo = $b['logo_url'] ?? '';
$cover = $b['cover_url'] ?? '';
$closure = $b['closure_message'] ?? '';
$currency = $b['currency_code'] ?? 'EUR';
$currencySymbol = match($currency) { 'MAD' => 'DH', 'USD' => '$', 'GBP' => '£', default => '€' };

$cats = supabase_get($SUPABASE_URL, $SUPABASE_KEY,
    "categories?business_id=eq.$bizId&is_activity=eq.false&select=id,name&order=sort_order");

$products = supabase_get($SUPABASE_URL, $SUPABASE_KEY,
    "products?business_id=eq.$bizId&is_active=eq.true&select=id,name,description,price,image_url,category_id&order=sort_order");

$services = supabase_get($SUPABASE_URL, $SUPABASE_KEY,
    "services?business_id=eq.$bizId&is_active=eq.true&select=id,name,description,price,image_url,category_id&order=sort_order");

// Fusionner produits et services
$allItems = array_merge($products, $services);
$modules = supabase_get($SUPABASE_URL, $SUPABASE_KEY,
    "modules?business_id=eq.$bizId&is_enabled=eq.true&select=module_type,online_enabled");

$hasOrders = false; $hasBooking = false;
foreach ($modules as $m) {
    if ($m['module_type'] === 'orders' && ($m['online_enabled'] ?? true)) $hasOrders = true;
    if ($m['module_type'] === 'booking' && ($m['online_enabled'] ?? true)) $hasBooking = true;
}

$catMap = [];
foreach ($cats as $c) $catMap[$c['id']] = $c['name'];
$byCategory = [];
foreach ($allItems as $p) {
    $cid = $p['category_id'] ?? 'other';
    $byCategory[$cid][] = $p;
}

$pageTitle = "$name — Dambou";
$deepLink = "dambou://business/$bizId";
$appStoreUrl = "https://apps.apple.com/app/dambou/idXXXXXXXXX";
$playStoreUrl = "https://play.google.com/store/apps/details?id=com.num0.dambou";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title><?= $pageTitle ?></title>
  <meta name="description" content="<?= $desc ?: $name ?>">
  <meta property="og:title" content="<?= $name ?> sur Dambou">
  <meta property="og:description" content="<?= $desc ?: 'Découvrez notre catalogue et commandez facilement.' ?>">
  <?php if ($logo): ?><meta property="og:image" content="<?= htmlspecialchars($logo) ?>"><?php endif; ?>
  <meta property="og:url" content="https://dambou.fr/pro/<?= $slug ?>">
  <meta name="theme-color" content="#1D9E75">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root { --primary: #1D9E75; --primary-dark: #166a50; --bg: #F5F7F5; --white: #ffffff; --text: #1a1a1a; --text-med: #666; --text-light: #999; --border: #e8ede8; --radius: 16px; --shadow: 0 2px 12px rgba(0,0,0,0.08); }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); padding-bottom: 100px; }
    .cover { width: 100%; height: 200px; object-fit: cover; display: block; }
    .cover-placeholder { width: 100%; height: 160px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
    .biz-header { background: var(--white); padding: 16px; display: flex; gap: 14px; align-items: flex-start; box-shadow: var(--shadow); }
    .biz-logo { width: 64px; height: 64px; border-radius: 14px; object-fit: cover; border: 2px solid var(--border); flex-shrink: 0; }
    .biz-logo-placeholder { width: 64px; height: 64px; border-radius: 14px; background: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0; }
    .biz-info { flex: 1; min-width: 0; }
    .biz-name { font-size: 20px; font-weight: 800; color: var(--text); line-height: 1.2; }
    .biz-desc { font-size: 13px; color: var(--text-med); margin-top: 4px; line-height: 1.4; }
    .biz-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
    .biz-chip { font-size: 12px; color: var(--text-med); display: flex; align-items: center; gap: 4px; }
    .closure-banner { margin: 12px 16px; background: #FFF3E0; border: 1.5px solid #FFB74D; border-radius: 12px; padding: 12px 14px; display: flex; gap: 10px; align-items: flex-start; }
    .closure-banner span { font-size: 13px; color: #E65100; font-weight: 600; line-height: 1.4; }
    .actions { display: flex; gap: 10px; padding: 12px 16px; }
    .btn { flex: 1; padding: 11px; border-radius: 12px; font-size: 14px; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; border: none; display: flex; align-items: center; justify-content: center; gap: 6px; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-outline { background: var(--white); color: var(--primary); border: 1.5px solid var(--primary); }
    .btn.disabled { opacity: 0.4; pointer-events: none; }
    .section { padding: 0 16px; margin-top: 20px; }
    .cat-title { font-size: 16px; font-weight: 800; color: var(--text); margin: 18px 0 10px; padding-bottom: 6px; border-bottom: 2px solid var(--primary); display: inline-block; }
    .product-card { background: var(--white); border-radius: var(--radius); border: 1px solid var(--border); margin-bottom: 10px; overflow: hidden; display: flex; align-items: center; gap: 12px; padding: 12px; box-shadow: var(--shadow); }
    .product-img { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
    .product-img-placeholder { width: 60px; height: 60px; border-radius: 10px; background: var(--bg); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .product-info { flex: 1; min-width: 0; }
    .product-name { font-size: 14px; font-weight: 700; color: var(--text); }
    .product-desc { font-size: 12px; color: var(--text-med); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .product-price { font-size: 15px; font-weight: 800; color: var(--primary); flex-shrink: 0; }
    .sticky-banner { position: fixed; bottom: 0; left: 0; right: 0; background: var(--white); border-top: 1px solid var(--border); padding: 12px 16px; box-shadow: 0 -4px 20px rgba(0,0,0,0.12); z-index: 100; }
    .sticky-inner { display: flex; align-items: center; gap: 12px; max-width: 480px; margin: 0 auto; }
    .sticky-logo { width: 40px; height: 40px; border-radius: 10px; background: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: white; font-size: 20px; }
    .sticky-text { flex: 1; }
    .sticky-text strong { font-size: 13px; display: block; color: var(--text); }
    .sticky-text span { font-size: 11px; color: var(--text-med); }
    .download-btn { background: var(--primary); color: white; border: none; padding: 10px 18px; border-radius: 10px; font-size: 13px; font-weight: 700; cursor: pointer; flex-shrink: 0; text-decoration: none; display: block; }
  </style>
</head>
<body>

<?php if ($cover): ?>
  <img class="cover" src="<?= htmlspecialchars($cover) ?>" alt="<?= $name ?>">
<?php else: ?>
  <div class="cover-placeholder"></div>
<?php endif; ?>

<div class="biz-header">
  <?php if ($logo): ?>
    <img class="biz-logo" src="<?= htmlspecialchars($logo) ?>" alt="<?= $name ?>">
  <?php else: ?>
    <div class="biz-logo-placeholder">🏪</div>
  <?php endif; ?>
  <div class="biz-info">
    <div class="biz-name"><?= $name ?></div>
    <?php if ($desc): ?><div class="biz-desc"><?= $desc ?></div><?php endif; ?>
    <div class="biz-meta">
      <?php if ($address): ?><span class="biz-chip">📍 <?= $address ?></span><?php endif; ?>
      <?php if ($phone): ?><span class="biz-chip">📞 <a href="tel:<?= $phone ?>" style="color:var(--text-med);text-decoration:none;"><?= $phone ?></a></span><?php endif; ?>
    </div>
  </div>
</div>

<?php if ($closure): ?>
<div class="closure-banner">
  <span>🔒</span>
  <span><?= htmlspecialchars($closure) ?></span>
</div>
<?php endif; ?>

<?php if ($hasOrders || $hasBooking): ?>
<div class="actions">
  <?php if ($hasBooking): ?>
    <a href="<?= $deepLink ?>?action=book" class="btn btn-outline <?= $closure ? 'disabled' : '' ?>">📅 Réserver</a>
  <?php endif; ?>
  <?php if ($hasOrders): ?>
    <a href="<?= $deepLink ?>?action=order" class="btn btn-primary <?= $closure ? 'disabled' : '' ?>">🛒 Commander</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="section">
  <?php if (empty($byCategory)): ?>
    <p style="color:var(--text-med);text-align:center;padding:32px 0;">Catalogue bientôt disponible</p>
  <?php else: ?>
    <?php foreach ($byCategory as $catId => $prods):
      $catName = $catMap[$catId] ?? '';
    ?>
      <?php if ($catName): ?><div class="cat-title"><?= htmlspecialchars($catName) ?></div><?php endif; ?>
      <?php foreach ($prods as $p):
        $pname = htmlspecialchars($p['name'] ?? '');
        $pdesc = htmlspecialchars($p['description'] ?? '');
        $price = number_format((float)($p['price'] ?? 0), 2, ',', ' ');
        $img = $p['image_url'] ?? '';
      ?>
        <div class="product-card">
          <?php if ($img): ?>
            <img class="product-img" src="<?= htmlspecialchars($img) ?>" alt="<?= $pname ?>">
          <?php else: ?>
            <div class="product-img-placeholder">🍽️</div>
          <?php endif; ?>
          <div class="product-info">
            <div class="product-name"><?= $pname ?></div>
            <?php if ($pdesc): ?><div class="product-desc"><?= $pdesc ?></div><?php endif; ?>
          </div>
          <div class="product-price"><?= $price ?> <?= $currencySymbol ?></div>
        </div>
      <?php endforeach; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="sticky-banner">
  <div class="sticky-inner">
    <div class="sticky-logo">🌿</div>
    <div class="sticky-text">
      <strong>Commander sur Dambou</strong>
      <span>Téléchargez l'app gratuitement</span>
    </div>
    <a class="download-btn" id="downloadBtn" href="#">Télécharger</a>
  </div>
</div>

<script>
  const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
  const isAndroid = /Android/.test(navigator.userAgent);
  const appStoreUrl = "<?= $appStoreUrl ?>";
  const playStoreUrl = "<?= $playStoreUrl ?>";
  const deepLink = "<?= $deepLink ?>";

  document.getElementById('downloadBtn').addEventListener('click', function(e) {
    e.preventDefault();
    if (isIOS) {
      window.location = deepLink;
      setTimeout(() => { window.location = appStoreUrl; }, 1500);
    } else if (isAndroid) {
      window.location = deepLink;
      setTimeout(() => { window.location = playStoreUrl; }, 1500);
    } else {
      window.open(playStoreUrl, '_blank');
    }
  });

  document.querySelectorAll('a[href^="dambou://"]').forEach(link => {
    link.addEventListener('click', function(e) {
      if (!isIOS && !isAndroid) {
        e.preventDefault();
        document.getElementById('downloadBtn').click();
      }
    });
  });
</script>
</body>
</html>
