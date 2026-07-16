<?php
// Fichier en ASCII uniquement (contrainte Hostinger).
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Pro - Dambou</title>
<meta name="theme-color" content="#00BFA5">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #f5f7fa;
    color: #1a1a2e;
    min-height: 100vh;
  }
  .topbar {
    background: #ffffff;
    border-bottom: 1px solid #e8ecf0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 18px; }
  .brand .dot { width: 10px; height: 10px; border-radius: 50%; background: #00BFA5; }
  .logout-btn {
    background: none;
    border: 1px solid #e8ecf0;
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    color: #666666;
    cursor: pointer;
    font-family: inherit;
  }
  .logout-btn:hover { border-color: #dc2626; color: #dc2626; }

  .container { max-width: 1100px; margin: 0 auto; padding: 32px 24px; }
  #loading { text-align: center; padding: 80px 20px; color: #666666; }

  #content { display: none; }
  .welcome { margin-bottom: 28px; }
  .welcome h1 { font-size: 24px; font-weight: 800; margin-bottom: 4px; }
  .welcome p { font-size: 14px; color: #666666; }

  .nav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
  }
  .nav-card {
    background: #ffffff;
    border: 1px solid #e8ecf0;
    border-radius: 16px;
    padding: 24px;
    text-decoration: none;
    color: #1a1a2e;
    display: block;
    transition: box-shadow 0.15s, border-color 0.15s;
  }
  .nav-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); border-color: #00BFA5; }
  .nav-card .icon { font-size: 28px; margin-bottom: 12px; }
  .nav-card h3 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
  .nav-card p { font-size: 13px; color: #666666; }

  .error-box {
    background: #fdecea;
    color: #dc2626;
    padding: 16px;
    border-radius: 12px;
    font-size: 14px;
    text-align: center;
  }
</style>
</head>
<body>
  <div class="topbar">
    <div class="brand"><span class="dot"></span> Dambou Pro</div>
    <button class="logout-btn" id="logout-btn">Deconnexion</button>
  </div>

  <div class="container">
    <div id="loading">Chargement...</div>
    <div id="content">
      <div class="welcome">
        <h1 id="business-name">-</h1>
        <p>Bienvenue dans votre espace de gestion Dambou.</p>
      </div>

      <div class="nav-grid">
        <a class="nav-card" href="/pro/planning">
          <div class="icon">&#128197;</div>
          <h3>Planning</h3>
          <p>Vue semaine et mois de vos rendez-vous</p>
        </a>
        <a class="nav-card" href="/pro/reservations">
          <div class="icon">&#128203;</div>
          <h3>Reservations</h3>
          <p>Liste et gestion de vos reservations</p>
        </a>
        <a class="nav-card" href="/pro/catalogue">
          <div class="icon">&#128717;</div>
          <h3>Catalogue</h3>
          <p>Produits et services proposes</p>
        </a>
        <a class="nav-card" href="/pro/clients">
          <div class="icon">&#128101;</div>
          <h3>Clients</h3>
          <p>Fiches et historique de vos clients</p>
        </a>
      </div>
    </div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser, logout } from '/pro/js/auth.js';

    const loadingEl = document.getElementById('loading');
    const contentEl = document.getElementById('content');

    (async () => {
      const session = await requireAuth();
      if (!session) return; // requireAuth redirige deja vers /pro/login

      const business = await getBusinessForUser(session.user.id);

      if (!business) {
        loadingEl.innerHTML = '<div class="error-box">Aucun etablissement associe a ce compte.</div>';
        return;
      }

      document.getElementById('business-name').textContent = business.name || 'Votre etablissement';

      loadingEl.style.display = 'none';
      contentEl.style.display = 'block';
    })();

    document.getElementById('logout-btn').addEventListener('click', () => {
      logout();
    });
  </script>
</body>
</html>
