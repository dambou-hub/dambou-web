<?php
// dambou.fr/abonnement/success — Page de confirmation après paiement
// Le webhook Stripe met déjà à jour Supabase en arrière-plan
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Abonnement activé — Dambou</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: -apple-system, sans-serif; background: #f5f5f5;
           display:flex; align-items:center; justify-content:center; min-height:100vh; }
    .card { background:white; border-radius:20px; padding:40px 32px;
            max-width:400px; width:90%; text-align:center; box-shadow:0 4px 24px rgba(0,0,0,0.08); }
    .emoji { font-size:56px; margin-bottom:16px; }
    h1 { font-size:22px; font-weight:800; color:#1a1a1a; margin-bottom:8px; }
    p  { color:#666; font-size:15px; line-height:1.5; margin-bottom:24px; }
    .btn { display:inline-block; background:#00BFA5; color:white; padding:14px 28px;
           border-radius:12px; text-decoration:none; font-weight:700; font-size:15px; }
  </style>
</head>
<body>
  <div class="card">
    <div class="emoji">🎉</div>
    <h1>Abonnement activé !</h1>
    <p>Votre abonnement Dambou Pro est maintenant actif. Retournez dans l'application pour continuer.</p>
    <a href="dambou://" class="btn">Retour à l'app</a>
  </div>
</body>
</html>
