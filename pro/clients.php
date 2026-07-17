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
<title>Clients - Dambou Pro</title>
<meta name="theme-color" content="#00BFA5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #00BFA5; --primary-dark: #00897B; --text-dark: #2D3748; --text-medium: #718096;
    --text-light: #A0AEC0; --background: #F7F8FA; --card-border: #E2E8F0;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .top-links a { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .top-links a:hover { color: var(--primary); }

  .container { max-width: 700px; margin: 0 auto; padding: 24px 24px 60px; }
  h1 { font-size: 22px; font-weight: 800; margin-bottom: 16px; }
  .search-input { width: 100%; padding: 13px 14px; border: 1px solid var(--card-border); border-radius: 12px; font-size: 14px; font-family: inherit; margin-bottom: 6px; }
  .hint { font-size: 11px; color: var(--text-light); margin-bottom: 18px; }

  .section-label { font-size: 11px; font-weight: 800; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin: 16px 0 8px; }
  .client-tile { display: flex; align-items: center; gap: 12px; padding: 12px; background: white; border: 1px solid var(--card-border); border-radius: 14px; cursor: pointer; text-decoration: none; color: var(--text-dark); margin-bottom: 8px; }
  .client-tile:hover { border-color: var(--primary); }
  .client-avatar { width: 36px; height: 36px; border-radius: 50%; background: rgba(0,191,165,0.12); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800; flex-shrink: 0; }
  .client-info { flex: 1; min-width: 0; }
  .client-name-row { display: flex; align-items: center; gap: 6px; }
  .client-name { font-size: 14px; font-weight: 700; }
  .client-badge { font-size: 9px; font-weight: 800; color: var(--primary-dark); background: rgba(0,191,165,0.1); padding: 2px 6px; border-radius: 5px; }
  .client-phone { font-size: 12px; color: var(--text-medium); }
  #empty-state { text-align: center; padding: 40px 20px; color: var(--text-light); font-size: 13px; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <div class="top-links"><a href="/pro">Tableau de bord</a></div>
  </div>

  <div class="container">
    <h1>Clients</h1>
    <input type="text" class="search-input" id="search-input" placeholder="Nom, telephone...">
    <div class="hint">Recherche parmi les clients ayant deja une commande, reservation ou achat chez vous.</div>
    <div id="results"></div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import { searchClients } from '/pro/js/planning.js';

    let business = null;

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }

    function clientLink(id, name, phone, badge, href) {
      const a = document.createElement('a');
      a.className = 'client-tile';
      a.href = href;
      a.innerHTML =
        '<div class="client-avatar">' + escapeHtml((name || '?').charAt(0).toUpperCase()) + '</div>' +
        '<div class="client-info"><div class="client-name-row"><span class="client-name">' + escapeHtml(name) + '</span>' +
        (badge ? '<span class="client-badge">Dambou</span>' : '') + '</div>' +
        (phone ? '<div class="client-phone">' + escapeHtml(phone) + '</div>' : '') + '</div>';
      return a;
    }

    const searchInput = document.getElementById('search-input');
    let debounceTimer = null;
    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      const query = searchInput.value;
      debounceTimer = setTimeout(() => runSearch(query), 350);
    });

    async function runSearch(query) {
      const container = document.getElementById('results');
      if (query.trim().length < 2) {
        container.innerHTML = '<div id="empty-state">Tapez au moins 2 caracteres pour rechercher.</div>';
        return;
      }
      container.innerHTML = '<div id="empty-state">Recherche...</div>';
      const results = await searchClients(business.id, query);
      container.innerHTML = '';

      if (!results.dambou.length && !results.manual.length) {
        container.innerHTML = '<div id="empty-state">Aucun client trouve.</div>';
        return;
      }

      if (results.dambou.length) {
        const label = document.createElement('div');
        label.className = 'section-label';
        label.textContent = 'Clients Dambou';
        container.appendChild(label);
        results.dambou.forEach((u) => {
          const name = ((u.first_name || '') + ' ' + (u.last_name || '')).trim() || 'Client Dambou';
          container.appendChild(clientLink(u.id, name, u.phone || '', true, '/pro/client?id=' + encodeURIComponent(u.id)));
        });
      }
      if (results.manual.length) {
        const label = document.createElement('div');
        label.className = 'section-label';
        label.textContent = 'Fiches manuelles';
        container.appendChild(label);
        results.manual.forEach((cl) => {
          const name = ((cl.first_name || '') + ' ' + (cl.last_name || '')).trim() || 'Client';
          container.appendChild(clientLink(cl.id, name, cl.phone || '', false, '/pro/manual-client?id=' + encodeURIComponent(cl.id)));
        });
      }
    }

    (async () => {
      const session = await requireAuth();
      if (!session) return;
      business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('results').innerHTML = '<div id="empty-state">Aucun etablissement associe a ce compte.</div>';
        return;
      }
      document.getElementById('results').innerHTML = '<div id="empty-state">Tapez au moins 2 caracteres pour rechercher.</div>';
    })();
  </script>
</body>
</html>
