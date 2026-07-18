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
<title>Reservations - Dambou Pro</title>
<meta name="theme-color" content="#00BFA5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #00BFA5; --primary-dark: #00897B; --text-dark: #2D3748; --text-medium: #718096;
    --text-light: #A0AEC0; --background: #F7F8FA; --card-border: #E2E8F0;
    --warning: #DD6B20; --success: #38A169; --error: #E53E3E;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 780px; margin: 0 auto; padding: 24px 24px 60px; }
  h1 { font-size: 22px; font-weight: 800; margin-bottom: 16px; }

  .tabs { display: flex; gap: 4px; background: white; border: 1px solid var(--card-border); border-radius: 12px; padding: 4px; margin-bottom: 20px; }
  .tab-btn { flex: 1; padding: 10px; border: none; background: none; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--text-medium); border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }
  .tab-btn.active { background: var(--primary); color: white; }
  .tab-badge { background: rgba(221,107,32,0.15); color: var(--warning); font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 10px; }
  .tab-btn.active .tab-badge { background: rgba(255,255,255,0.25); color: white; }

  #loading, #empty-state { text-align: center; padding: 60px 20px; color: var(--text-medium); font-size: 14px; }

  .day-group { margin-bottom: 20px; }
  .day-label { font-size: 12px; font-weight: 800; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
  .day-label.today { color: var(--primary-dark); }

  .resa-card { background: white; border: 1px solid var(--card-border); border-left: 3px solid; border-radius: 14px; padding: 13px 15px; margin-bottom: 8px; cursor: pointer; text-decoration: none; color: var(--text-dark); display: block; transition: box-shadow 0.15s; }
  .resa-card:hover { box-shadow: 0 4px 14px -6px rgba(0,0,0,0.12); }
  .resa-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
  .resa-time { font-size: 15px; font-weight: 800; min-width: 44px; }
  .resa-info { flex: 1; min-width: 0; }
  .resa-client { font-size: 13px; font-weight: 700; }
  .resa-meta { font-size: 12px; color: var(--text-medium); margin-top: 1px; }
  .resa-emp { display: flex; align-items: center; gap: 5px; font-size: 11px; color: var(--text-medium); margin-top: 4px; }
  .resa-emp-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
  .resa-badge { font-size: 10px; font-weight: 800; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
  .resa-note-flag { font-size: 11px; color: var(--primary-dark); margin-top: 4px; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <a class="back-link" href="/pro/planning">Voir le planning</a>
  </div>

  <div class="container">
    <h1>Reservations</h1>

    <div class="tabs">
      <button class="tab-btn active" data-status="pending">En attente <span class="tab-badge" id="badge-pending" style="display:none">0</span></button>
      <button class="tab-btn" data-status="confirmed">Confirmees</button>
      <button class="tab-btn" data-status="all">Toutes</button>
    </div>

    <div id="loading">Chargement...</div>
    <div id="list-container" style="display:none"></div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import { loadReservations } from '/pro/js/reservations.js';
    import { clientName, bookingPhone, bookingEmployeeIds, toDateKey, formatDateLong } from '/pro/js/planning.js';

    let business = null;
    let currentStatus = 'pending';

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }
    function currencySymbol() {
      return { EUR: '\u20ac', MAD: 'DH', CHF: 'CHF', XOF: 'FCFA' }[(business && business.currency_code) || 'EUR'] || '\u20ac';
    }

    const STATUS_LABELS = {
      pending: 'En attente', confirmed: 'Confirme', completed: 'Termine',
      cancelled: 'Annule', no_show: 'No-show',
    };
    const STATUS_COLORS = {
      pending: '#DD6B20', confirmed: '#38A169', completed: '#718096',
      cancelled: '#E53E3E', no_show: '#A0AEC0',
    };

    async function loadAndRender() {
      document.getElementById('loading').style.display = 'block';
      document.getElementById('list-container').style.display = 'none';

      const reservations = await loadReservations(business.id, currentStatus);

      // Badge "en attente" toujours calcule a part pour rester visible sur l'onglet,
      // meme quand on est sur un autre onglet.
      if (currentStatus !== 'pending') {
        loadReservations(business.id, 'pending').then((pending) => {
          const badge = document.getElementById('badge-pending');
          if (pending.length > 0) { badge.textContent = pending.length; badge.style.display = 'inline-block'; }
          else badge.style.display = 'none';
        });
      } else {
        const badge = document.getElementById('badge-pending');
        if (reservations.length > 0) { badge.textContent = reservations.length; badge.style.display = 'inline-block'; }
        else badge.style.display = 'none';
      }

      const container = document.getElementById('list-container');
      document.getElementById('loading').style.display = 'none';

      if (reservations.length === 0) {
        container.innerHTML = '<div id="empty-state">Aucune reservation sur cette periode.</div>';
        container.style.display = 'block';
        return;
      }

      // Grouper par jour
      const byDay = {};
      reservations.forEach((r) => {
        if (!byDay[r.booking_date]) byDay[r.booking_date] = [];
        byDay[r.booking_date].push(r);
      });

      const todayKey = toDateKey(new Date());
      container.innerHTML = '';
      Object.keys(byDay).sort().forEach((dateKey) => {
        const group = document.createElement('div');
        group.className = 'day-group';
        const label = document.createElement('div');
        label.className = 'day-label' + (dateKey === todayKey ? ' today' : '');
        label.textContent = (dateKey === todayKey ? "Aujourd'hui - " : '') + formatDateLong(new Date(dateKey + 'T00:00:00'));
        group.appendChild(label);

        byDay[dateKey].sort((a, b) => (a.start_time || '').localeCompare(b.start_time || '')).forEach((r) => {
          group.appendChild(renderCard(r));
        });
        container.appendChild(group);
      });
      container.style.display = 'block';
    }

    function renderCard(b) {
      const svc = b.services;
      const color = STATUS_COLORS[b.status] || '#718096';
      const empIds = bookingEmployeeIds(b);
      const emp = empIds.length ? (b.employees && b.employees.id === empIds[0] ? b.employees :
        (b.booking_employees || []).map((be) => be.employees).find((e) => e && e.id === empIds[0])) : null;

      const a = document.createElement('a');
      a.className = 'resa-card';
      a.style.borderLeftColor = color;
      a.href = '/pro/planning?date=' + encodeURIComponent(b.booking_date) + '&open=' + encodeURIComponent(b.id);
      a.innerHTML =
        '<div class="resa-top">' +
        '<div class="resa-time">' + escapeHtml((b.start_time || '').substring(0, 5)) + '</div>' +
        '<div class="resa-info">' +
        '<div class="resa-client">' + escapeHtml(clientName(b)) + '</div>' +
        '<div class="resa-meta">' + escapeHtml(svc ? svc.name : 'Rendez-vous') + (svc && svc.duration ? ' &middot; ' + svc.duration + ' min' : '') + (svc && svc.price ? ' &middot; ' + Math.round(svc.price) + ' ' + currencySymbol() : '') + '</div>' +
        (emp ? '<div class="resa-emp"><span class="resa-emp-dot" style="background:' + (emp.color || '#00BFA5') + '"></span>' + escapeHtml((emp.first_name || '') + ' ' + (emp.last_name || '')) + '</div>' : '') +
        (b.notes ? '<div class="resa-note-flag">&#128172; Note du client</div>' : '') +
        '</div>' +
        '<span class="resa-badge" style="background:' + color + '1A; color:' + color + '">' + (STATUS_LABELS[b.status] || b.status) + '</span>' +
        '</div>';
      return a;
    }

    document.querySelectorAll('.tab-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        currentStatus = btn.dataset.status;
        loadAndRender();
      });
    });

    (async () => {
      const session = await requireAuth();
      if (!session) return;
      business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('loading').textContent = 'Aucun etablissement associe a ce compte.';
        return;
      }
      await loadAndRender();
    })();
  </script>
</body>
</html>
