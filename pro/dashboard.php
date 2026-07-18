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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #00BFA5;
    --primary-dark: #00897B;
    --text-dark: #2D3748;
    --text-medium: #718096;
    --text-light: #A0AEC0;
    --background: #F7F8FA;
    --card-border: #E2E8F0;
    --error: #E53E3E;

    --c-planning: #00BFA5;
    --c-reservations: #F4A261;
    --c-catalogue: #6C63FF;
    --c-clients: #3182CE;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Inter', -apple-system, sans-serif;
    background: var(--background);
    color: var(--text-dark);
    min-height: 100vh;
  }
  .topbar {
    background: #ffffff;
    border-bottom: 1px solid var(--card-border);
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; }
  .brand img { height: 26px; width: auto; }
  .logout-btn {
    background: none;
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-medium);
    cursor: pointer;
    font-family: inherit;
  }
  .logout-btn:hover { border-color: var(--error); color: var(--error); }

  .container { max-width: 1100px; margin: 0 auto; padding: 32px 24px; }
  #loading { text-align: center; padding: 80px 20px; color: var(--text-medium); }

  #content { display: none; }
  .welcome { margin-bottom: 28px; }
  .welcome h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
  .welcome p { font-size: 14px; color: var(--text-medium); }

  .nav-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
  }
  .nav-card {
    background: #ffffff;
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 22px;
    text-decoration: none;
    color: var(--text-dark);
    display: block;
    transition: border-color 0.15s;
  }
  .nav-card:hover { border-color: var(--primary); }
  .nav-card .icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 19px; margin-bottom: 14px; }
  .nav-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
  .nav-card p { font-size: 13px; color: var(--text-medium); }

  .error-box {
    background: rgba(229,62,62,0.08);
    color: var(--error);
    padding: 16px;
    border-radius: 12px;
    font-size: 14px;
    text-align: center;
  }
</style>
</head>
<body>
  <div class="topbar">
    <div class="brand"><img src="/assets/icon.png" alt=""> Dambou Pro</div>
    <button class="logout-btn" id="logout-btn">Deconnexion</button>
  </div>

  <div class="container">
    <div id="loading">Chargement...</div>
    <div id="content">
      <div class="welcome">
        <h1 id="business-name">-</h1>
        <p>Bienvenue dans votre espace de gestion Dambou.</p>
      </div>

      <div class="nav-grid" id="nav-grid">
        <a class="nav-card" href="/pro/planning" id="card-planning">
          <div class="icon" style="background:rgba(0,191,165,0.1)">&#128197;</div>
          <h3>Planning</h3>
          <p>Vue semaine et mois de vos rendez-vous</p>
        </a>
        <a class="nav-card" href="/pro/reservations" id="card-reservations">
          <div class="icon" style="background:rgba(244,162,97,0.1)">&#128203;</div>
          <h3>Reservations</h3>
          <p>Liste et gestion de vos reservations</p>
        </a>
        <a class="nav-card" href="/pro/catalogue">
          <div class="icon" style="background:rgba(108,99,255,0.1)">&#129534;</div>
          <h3>Catalogue</h3>
          <p>Produits et services proposes</p>
        </a>
        <a class="nav-card" href="/pro/caisse" id="card-caisse">
          <div class="icon" style="background:rgba(82,183,136,0.1)">&#128179;</div>
          <h3>Caisse</h3>
          <p>Vente sur place et encaissement</p>
        </a>
        <a class="nav-card" href="/pro/clients">
          <div class="icon" style="background:rgba(49,130,206,0.1)">&#128101;</div>
          <h3>Clients</h3>
          <p>Fiches et historique de vos clients</p>
        </a>
        <a class="nav-card" href="/pro/business">
          <div class="icon" style="background:rgba(221,107,32,0.1)">&#127970;</div>
          <h3>Mon business</h3>
          <p>Infos, photos, adresse et horaires</p>
        </a>
      </div>
    </div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser, getActiveModules, logout } from '/pro/js/auth.js';

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

      // Planning et Reservations ne s'affichent que si le module "booking" est actif
      // (meme logique que _buildQuickActions() dans pro_home_screen.dart -- un food truck
      // comme Freddy'Z Pizz n'a pas ce module et ne doit pas voir ces cards).
      const activeModules = await getActiveModules(business.id);
      const hasBooking = activeModules.some((m) => m.module_type === 'booking');
      if (!hasBooking) {
        document.getElementById('card-planning').remove();
        document.getElementById('card-reservations').remove();
      }
      const hasPos = activeModules.some((m) => m.module_type === 'pos');
      if (!hasPos) {
        document.getElementById('card-caisse').remove();
      }

      loadingEl.style.display = 'none';
      contentEl.style.display = 'block';
    })();

    document.getElementById('logout-btn').addEventListener('click', () => {
      logout();
    });
  </script>
</body>
</html>
