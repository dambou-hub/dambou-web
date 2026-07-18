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
  .brand img { height: 26px; width: auto; border-radius: 6px; object-fit: cover; }
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

  .ca-card { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 18px; padding: 20px 24px; color: white; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; }
  .ca-card .label { font-size: 13px; opacity: 0.85; margin-bottom: 4px; }
  .ca-card .value { font-size: 30px; font-weight: 900; }
  .ca-card a { color: white; opacity: 0.85; font-size: 12px; font-weight: 700; text-decoration: none; border: 1px solid rgba(255,255,255,0.4); padding: 8px 14px; border-radius: 10px; }
  .ca-card a:hover { opacity: 1; border-color: white; }

  .pending-section { margin-bottom: 28px; }
  .pending-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
  .pending-head h2 { font-size: 15px; font-weight: 800; display: flex; align-items: center; gap: 8px; }
  .pending-count { background: var(--warning-bg, rgba(221,107,32,0.12)); color: #DD6B20; font-size: 12px; font-weight: 800; padding: 2px 9px; border-radius: 20px; }
  .pending-card { background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 14px 16px; margin-bottom: 10px; }
  .pending-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
  .pending-client { font-size: 14px; font-weight: 700; }
  .pending-meta { font-size: 12px; color: var(--text-medium); margin-top: 2px; }
  .pending-phone { font-size: 12px; color: var(--primary-dark); text-decoration: none; font-weight: 600; }
  .pending-date { font-size: 12px; font-weight: 700; color: var(--text-dark); background: var(--background); padding: 4px 10px; border-radius: 8px; }
  .pending-conflict { background: rgba(221,107,32,0.08); color: #a3591e; font-size: 11px; padding: 6px 10px; border-radius: 8px; margin: 8px 0; }
  .pending-actions { display: flex; gap: 8px; margin-top: 10px; }
  .pending-actions button { flex: 1; padding: 9px; border-radius: 10px; border: 1px solid var(--card-border); background: white; font-family: inherit; font-size: 12px; font-weight: 700; cursor: pointer; }
  .pending-actions button.confirm { background: var(--primary); color: white; border-color: var(--primary); }
  .pending-actions button.confirm:disabled { opacity: 0.5; cursor: not-allowed; }
  .pending-actions button.cancel { color: var(--error); }
  #pending-empty { text-align: center; padding: 20px; color: var(--text-light); font-size: 13px; background: white; border: 1px dashed var(--card-border); border-radius: 14px; }
</style>
</head>
<body>
  <div class="topbar">
    <div class="brand"><img src="/assets/icon.png" alt="" id="topbar-logo"> <span id="topbar-name">Dambou Pro</span></div>
    <button class="logout-btn" id="logout-btn">Deconnexion</button>
  </div>

  <div class="container">
    <div id="loading">Chargement...</div>
    <div id="content">
      <div class="welcome">
        <h1 id="business-name">-</h1>
        <p>Bienvenue dans votre espace de gestion Dambou.</p>
      </div>

      <div class="ca-card" id="ca-card" style="display:none">
        <div>
          <div class="label">Chiffre d'affaires aujourd'hui</div>
          <div class="value" id="ca-value">-</div>
        </div>
        <a href="/pro/stats">Voir les stats &rarr;</a>
      </div>

      <div class="pending-section" id="pending-section" style="display:none">
        <div class="pending-head">
          <h2>Reservations a confirmer <span class="pending-count" id="pending-count">0</span></h2>
        </div>
        <div id="pending-list"></div>
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
        <a class="nav-card" href="/pro/stats" id="card-stats">
          <div class="icon" style="background:rgba(21,101,192,0.1)">&#128202;</div>
          <h3>Statistiques</h3>
          <p>Chiffre d'affaires et articles vendus</p>
        </a>
        <a class="nav-card" href="/pro/modules">
          <div class="icon" style="background:rgba(94,53,177,0.1)">&#9881;&#65039;</div>
          <h3>Modules</h3>
          <p>Activez ce dont vous avez besoin</p>
        </a>
      </div>
    </div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser, getActiveModules, logout } from '/pro/js/auth.js';
    import { loadPendingBookings, checkConflictForPendingBooking, confirmBooking, cancelBooking, clientName, bookingPhone } from '/pro/js/planning.js';
    import { loadStats } from '/pro/js/stats.js';

    const loadingEl = document.getElementById('loading');
    const contentEl = document.getElementById('content');
    let business = null;
    let hasBookingModule = false;

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }
    function currencySymbol() {
      return { EUR: '\u20ac', MAD: 'DH', CHF: 'CHF', XOF: 'FCFA' }[(business && business.currency_code) || 'EUR'] || '\u20ac';
    }

    async function loadCaAujourdhui() {
      try {
        const stats = await loadStats(business.id, 0);
        document.getElementById('ca-value').textContent = Math.round(stats.totalGlobal) + ' ' + currencySymbol();
        document.getElementById('ca-card').style.display = 'flex';
      } catch (err) {
        console.error('Erreur CA du jour:', err);
      }
    }

    async function loadPending() {
      if (!hasBookingModule) return;
      const pending = await loadPendingBookings(business.id, 8);
      const section = document.getElementById('pending-section');
      const list = document.getElementById('pending-list');
      const count = document.getElementById('pending-count');

      if (pending.length === 0) {
        section.style.display = 'none';
        return;
      }
      section.style.display = 'block';
      count.textContent = pending.length;
      list.innerHTML = '';

      for (const b of pending) {
        const svc = b.services;
        const phone = bookingPhone(b);
        const card = document.createElement('div');
        card.className = 'pending-card';
        card.innerHTML =
          '<div class="pending-top">' +
          '<div><div class="pending-client">' + escapeHtml(clientName(b)) + '</div>' +
          '<div class="pending-meta">' + escapeHtml(svc ? svc.name : 'Rendez-vous') + (phone ? ' &middot; <a class="pending-phone" href="tel:' + escapeHtml(phone) + '">' + escapeHtml(phone) + '</a>' : '') + '</div></div>' +
          '<div class="pending-date">' + escapeHtml(b.booking_date) + ' ' + escapeHtml((b.start_time || '').substring(0, 5)) + '</div>' +
          '</div>' +
          '<div class="pending-actions">' +
          '<button class="cancel" data-action="cancel" data-id="' + b.id + '">Refuser</button>' +
          '<button class="confirm" data-action="confirm" data-id="' + b.id + '">Confirmer</button>' +
          '</div>';
        list.appendChild(card);

        // Verification de conflit -- amelioration par rapport a l'app mobile qui confirme
        // sans aucune verification. On ne bloque pas, on informe juste le pro.
        checkConflictForPendingBooking(business.id, b).then((warning) => {
          if (!warning) return;
          const warn = document.createElement('div');
          warn.className = 'pending-conflict';
          warn.textContent = '\u26A0 ' + warning;
          card.querySelector('.pending-actions').insertAdjacentElement('beforebegin', warn);
        });
      }

      list.querySelectorAll('[data-action="confirm"]').forEach((btn) => {
        btn.addEventListener('click', async () => {
          btn.disabled = true;
          try {
            await confirmBooking(btn.dataset.id);
            await loadPending();
          } catch (err) {
            console.error(err);
            btn.disabled = false;
          }
        });
      });
      list.querySelectorAll('[data-action="cancel"]').forEach((btn) => {
        btn.addEventListener('click', async () => {
          if (!confirm('Refuser cette reservation ?')) return;
          btn.disabled = true;
          try {
            await cancelBooking(btn.dataset.id);
            await loadPending();
          } catch (err) {
            console.error(err);
            btn.disabled = false;
          }
        });
      });
    }

    (async () => {
      const session = await requireAuth();
      if (!session) return; // requireAuth redirige deja vers /pro/login

      business = await getBusinessForUser(session.user.id);

      if (!business) {
        loadingEl.innerHTML = '<div class="error-box">Aucun etablissement associe a ce compte.</div>';
        return;
      }

      document.getElementById('business-name').textContent = business.name || 'Votre etablissement';
      if (business.logo_url) {
        document.getElementById('topbar-logo').src = business.logo_url;
      }
      document.getElementById('topbar-name').textContent = business.name || 'Dambou Pro';

      // Planning et Reservations ne s'affichent que si le module "booking" est actif
      // (meme logique que _buildQuickActions() dans pro_home_screen.dart -- un food truck
      // comme Freddy'Z Pizz n'a pas ce module et ne doit pas voir ces cards).
      const activeModules = await getActiveModules(business.id);
      hasBookingModule = activeModules.some((m) => m.module_type === 'booking');
      if (!hasBookingModule) {
        document.getElementById('card-planning').remove();
        document.getElementById('card-reservations').remove();
      }
      const hasPos = activeModules.some((m) => m.module_type === 'pos');
      if (!hasPos) {
        document.getElementById('card-caisse').remove();
      }
      const hasStats = activeModules.some((m) => m.module_type === 'stats');
      if (!hasStats) {
        document.getElementById('card-stats').remove();
      }

      loadingEl.style.display = 'none';
      contentEl.style.display = 'block';

      await Promise.all([loadCaAujourdhui(), loadPending()]);

      // Rafraichissement automatique -- pas de Realtime Supabase pour l'instant
      // (aucune config supplementaire requise cote Supabase, quasi-instantane
      // pour ce cas d'usage).
      setInterval(() => {
        loadCaAujourdhui();
        loadPending();
      }, 30000);
    })();

    document.getElementById('logout-btn').addEventListener('click', () => {
      logout();
    });
  </script>
</body>
</html>
