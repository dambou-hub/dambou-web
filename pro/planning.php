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
<title>Planning - Dambou Pro</title>
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
    --warning: #DD6B20;
    --success: #38A169;
    --error: #E53E3E;
    --noshow: #A0AEC0;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }

  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 1240px; margin: 0 auto; padding: 24px 24px 60px; }

  .date-nav { display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 10px 12px; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
  .date-nav-left { display: flex; align-items: center; gap: 10px; }
  .nav-btn { width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--card-border); background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; color: var(--text-dark); flex-shrink: 0; font-family: inherit; }
  .nav-btn:hover { border-color: var(--primary); color: var(--primary); }
  .date-label { font-size: 15px; font-weight: 700; text-transform: capitalize; min-width: 200px; text-align: center; }
  .closed-badge { font-size: 10px; font-weight: 700; color: var(--error); background: rgba(229,62,62,0.08); padding: 3px 8px; border-radius: 6px; margin-left: 6px; }
  .date-nav-right { display: flex; align-items: center; gap: 10px; }
  .view-toggle { display: flex; gap: 4px; background: var(--background); border-radius: 10px; padding: 3px; }
  .view-toggle button { border: none; background: transparent; padding: 7px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; color: var(--text-medium); font-family: inherit; cursor: pointer; }
  .view-toggle button.active { background: white; color: var(--primary-dark); }
  .today-btn { font-size: 12px; font-weight: 700; color: var(--primary); background: rgba(0,191,165,0.1); border: none; border-radius: 8px; padding: 8px 14px; cursor: pointer; font-family: inherit; }

  #loading, #empty-employees { text-align: center; padding: 60px 20px; color: var(--text-medium); font-size: 14px; }

  .agenda { background: white; border: 1px solid var(--card-border); border-radius: 16px; overflow: hidden; overflow-x: auto; }
  .agenda-head { display: grid; border-bottom: 1px solid var(--card-border); min-width: 640px; }
  .agenda-head .corner { border-right: 1px solid var(--card-border); }
  .col-head { display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-right: 1px solid var(--card-border); min-width: 0; }
  .col-head:last-child { border-right: none; }
  .emp-avatar { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 800; flex-shrink: 0; }
  .col-name { font-size: 12px; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .col-count { font-size: 10px; color: var(--text-medium); font-weight: 600; }

  .agenda-body { display: grid; position: relative; min-width: 640px; }
  .time-col { border-right: 1px solid var(--card-border); position: relative; }
  .time-mark { position: absolute; left: 0; right: 0; }
  .time-mark span { position: absolute; top: -7px; right: 8px; font-size: 10px; color: var(--text-light); font-weight: 600; background: white; padding: 0 2px; }

  .day-col { position: relative; border-right: 1px solid var(--card-border); }
  .day-col:last-child { border-right: none; }
  .day-col.drop-hover { background: rgba(0,191,165,0.06); }

  .drop-indicator {
    position: absolute; left: 2px; right: 2px; height: 2px; background: var(--primary); z-index: 5;
    pointer-events: none; display: flex; align-items: center; justify-content: flex-end;
  }
  .drop-indicator::after {
    content: ''; position: absolute; left: -3px; top: -3px; width: 8px; height: 8px; border-radius: 50%; background: var(--primary);
  }
  .drop-indicator span {
    background: var(--primary); color: white; font-size: 10px; font-weight: 800;
    padding: 2px 6px; border-radius: 6px; transform: translateY(-50%); margin-right: 4px;
  }

  .appt { position: absolute; left: 3px; right: 3px; border-radius: 8px; border-left: 3px solid; padding: 3px 7px; overflow: hidden; cursor: pointer; z-index: 2; }
  .appt:hover { z-index: 3; box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
  .appt.dragging { opacity: 0.4; }
  .appt .t { font-size: 10px; font-weight: 800; }
  .appt .c { font-size: 11px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .appt .s { font-size: 10px; color: var(--text-medium); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

  .agenda.week-mode { overflow-x: visible; }

  .week-day-col { padding: 8px 6px; display: flex; flex-direction: column; gap: 6px; min-height: 80px; }
  .week-chip { border-left: 3px solid; border-radius: 6px; padding: 5px 8px; cursor: pointer; display: flex; align-items: center; gap: 6px; }
  .week-chip:hover { filter: brightness(0.97); }
  .wc-time { font-size: 10px; font-weight: 800; flex-shrink: 0; }
  .wc-name { font-size: 11px; font-weight: 600; color: var(--text-dark); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
  .week-empty { font-size: 11px; color: var(--text-light); text-align: center; padding: 10px 0; }

  .legend { display: flex; gap: 18px; margin-top: 16px; flex-wrap: wrap; }
  .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--text-medium); font-weight: 600; }
  .legend-dot { width: 8px; height: 8px; border-radius: 50%; }

  /* Panneau d'actions (remplace le bottom sheet mobile) */
  .overlay { position: fixed; inset: 0; background: rgba(45,55,72,0.35); display: none; align-items: center; justify-content: center; z-index: 50; padding: 20px; }
  .overlay.visible { display: flex; }
  .panel { background: white; border-radius: 18px; width: 100%; max-width: 380px; padding: 20px; max-height: 90vh; overflow-y: auto; }
  .panel-head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
  .panel-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
  .panel-info { flex: 1; min-width: 0; }
  .panel-client { font-size: 15px; font-weight: 800; }
  .panel-service { font-size: 12px; color: var(--text-medium); }
  .panel-phone { font-size: 11px; color: #25D366; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 2px; }
  .panel-price { font-size: 17px; font-weight: 900; white-space: nowrap; }
  .panel-divider { height: 1px; background: var(--card-border); margin: 6px 0; }
  .action-tile { display: flex; align-items: center; gap: 12px; padding: 13px 4px; cursor: pointer; border: none; background: none; width: 100%; text-align: left; font-family: inherit; font-size: 14px; font-weight: 700; color: var(--text-dark); }
  .action-tile .a-icon { width: 20px; text-align: center; flex-shrink: 0; }
  .action-tile:hover { opacity: 0.7; }

  .confirm-box { display: none; }
  .confirm-box.visible { display: block; padding: 12px 0; }
  .confirm-actions { display: flex; gap: 8px; margin-top: 10px; }
  .confirm-actions button { flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--card-border); background: white; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
  .confirm-actions button.danger { background: var(--error); color: white; border-color: var(--error); }

  .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: var(--text-dark); color: white; padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: 600; z-index: 60; display: none; }
  .toast.visible { display: block; }
  input:disabled { background: var(--background); color: var(--text-medium); cursor: not-allowed; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <a class="back-link" href="/pro">Retour au tableau de bord</a>
  </div>

  <div class="container">
    <div class="date-nav">
      <div class="date-nav-left">
        <button class="nav-btn" id="prev-btn">&larr;</button>
        <div class="date-label" id="date-label">-</div>
        <button class="nav-btn" id="next-btn">&rarr;</button>
      </div>
      <div class="date-nav-right">
        <div class="view-toggle">
          <button class="active" id="view-day">Jour</button>
          <button id="view-week">Semaine</button>
        </div>
        <button class="today-btn" id="today-btn">Aujourd'hui</button>
        <button class="today-btn" id="new-booking-btn" style="background:var(--primary); color:white;">+ Nouvelle reservation</button>
      </div>
    </div>

    <div id="loading">Chargement du planning...</div>
    <div class="agenda" id="agenda" style="display:none">
      <div class="agenda-head" id="agenda-head"></div>
      <div class="agenda-body" id="agenda-body"></div>
    </div>

    <div class="legend">
      <div class="legend-item"><div class="legend-dot" style="background:#38A169"></div>Confirme / paye</div>
      <div class="legend-item"><div class="legend-dot" style="background:#DD6B20"></div>En attente</div>
      <div class="legend-item"><div class="legend-dot" style="background:#A0AEC0"></div>No-show</div>
    </div>
  </div>

  <!-- Panneau d'actions sur un RDV -->
  <div class="overlay" id="panel-overlay">
    <div class="panel" id="panel">
      <div class="panel-head">
        <div class="panel-icon" id="panel-icon"></div>
        <div class="panel-info">
          <div class="panel-client" id="panel-client"></div>
          <div class="panel-service" id="panel-service"></div>
          <a class="panel-phone" id="panel-phone" href="#" style="display:none"></a>
        </div>
        <div class="panel-price" id="panel-price"></div>
      </div>
      <div class="panel-divider"></div>
      <div id="panel-actions"></div>

      <div class="confirm-box" id="confirm-cancel-box">
        <div style="font-size:13px; color:var(--text-medium)">Annuler ce rendez-vous ?</div>
        <div class="confirm-actions">
          <button id="confirm-cancel-no">Non</button>
          <button class="danger" id="confirm-cancel-yes">Oui, annuler</button>
        </div>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <!-- Nouvelle reservation -->
  <div class="overlay" id="new-overlay">
    <div class="panel" id="new-panel" style="max-width:420px">
      <div class="panel-head" style="align-items:flex-start">
        <div class="panel-info">
          <div class="panel-client" id="new-modal-title">Nouvelle reservation</div>
          <div class="panel-service" id="new-date-label"></div>
        </div>
        <button class="nav-btn" id="new-close-btn" style="border:none">&times;</button>
      </div>
      <div class="panel-divider"></div>

      <form id="new-booking-form">
        <div style="margin-bottom:12px">
          <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:5px">Date</label>
          <input type="date" id="new-date" required style="width:100%; padding:11px 12px; border:1px solid var(--card-border); border-radius:12px; font-size:14px; font-family:inherit">
        </div>

        <div style="margin-bottom:12px">
          <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:5px">Heure</label>
          <input type="time" id="new-time" required style="width:100%; padding:11px 12px; border:1px solid var(--card-border); border-radius:12px; font-size:15px; font-family:inherit; font-weight:700; color:var(--primary-dark)">
        </div>

        <div style="margin-bottom:12px">
          <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:5px">Service</label>
          <select id="new-service" required style="width:100%; padding:11px 12px; border:1px solid var(--card-border); border-radius:12px; font-size:14px; font-family:inherit">
            <option value="">Choisir un service</option>
          </select>
        </div>

        <div style="margin-bottom:12px" id="new-employees-wrap">
          <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:5px">Employe</label>
          <div id="new-employees-chips" style="display:flex; flex-wrap:wrap; gap:8px"></div>
        </div>

        <div style="margin-bottom:16px">
          <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:5px">Client</label>
          <div id="client-selector-box" style="display:flex; align-items:center; gap:10px; padding:11px 12px; border:1.5px solid var(--card-border); border-radius:12px; cursor:pointer;">
            <span id="client-box-placeholder" style="color:var(--text-light); font-size:14px; flex:1">Rechercher ou creer un client...</span>
            <div id="client-box-selected" style="display:none; align-items:center; gap:10px; flex:1; min-width:0">
              <div id="csb-avatar" style="width:30px; height:30px; border-radius:50%; background:rgba(0,191,165,0.15); color:var(--primary-dark); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; flex-shrink:0"></div>
              <div style="flex:1; min-width:0">
                <div style="display:flex; align-items:center; gap:6px">
                  <span id="csb-name" style="font-size:13px; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap"></span>
                  <span id="csb-badge" style="display:none; font-size:9px; font-weight:800; color:var(--primary-dark); background:rgba(0,191,165,0.1); padding:2px 5px; border-radius:4px">Dambou</span>
                </div>
                <div id="csb-phone" style="font-size:11px; color:var(--text-medium)"></div>
              </div>
              <button type="button" id="csb-clear" style="border:none; background:none; font-size:18px; color:var(--text-light); cursor:pointer; padding:0 4px">&times;</button>
            </div>
          </div>
        </div>

        <div class="error-msg" id="new-error" style="display:none; background:rgba(229,62,62,0.08); color:var(--error); font-size:13px; padding:10px 12px; border-radius:8px; margin-bottom:12px"></div>

        <button type="submit" id="new-submit-btn" style="width:100%; padding:13px; background:var(--primary); color:white; border:none; border-radius:14px; font-size:14px; font-weight:700; font-family:inherit; cursor:pointer">Creer la reservation</button>
      </form>
    </div>
  </div>

  <!-- Recherche / creation client (au dessus du modal reservation) -->
  <div class="overlay" id="client-search-overlay" style="z-index:70">
    <div class="panel" id="client-search-panel" style="max-width:400px; display:flex; flex-direction:column; max-height:80vh">
      <div class="panel-head" style="align-items:flex-start; margin-bottom:12px">
        <div class="panel-info">
          <div class="panel-client">Choisir un client</div>
        </div>
        <button class="nav-btn" id="client-search-close" style="border:none">&times;</button>
      </div>

      <input type="text" id="client-search-input" placeholder="Nom ou telephone..." autocomplete="off"
        style="width:100%; padding:11px 12px; border:1px solid var(--card-border); border-radius:12px; font-size:14px; font-family:inherit; margin-bottom:10px">

      <div id="client-results" style="overflow-y:auto; flex:1; min-height:60px"></div>

      <button type="button" id="client-create-toggle" class="action-tile" style="border-top:1px solid var(--card-border); margin-top:8px; padding-top:14px; color:var(--primary-dark)">
        <span class="a-icon">+</span><span>Creer une fiche client</span>
      </button>

      <div id="client-create-form" style="display:none">
        <input type="text" id="cc-first-name" placeholder="Prenom" required style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:8px">
        <input type="text" id="cc-last-name" placeholder="Nom (optionnel)" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:8px">
        <input type="tel" id="cc-phone" placeholder="Telephone" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:8px">
        <input type="email" id="cc-email" placeholder="Email (optionnel)" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:10px">
        <button type="button" id="cc-save-btn" style="width:100%; padding:11px; background:var(--primary); color:white; border:none; border-radius:12px; font-size:13px; font-weight:700; font-family:inherit; cursor:pointer">Creer et selectionner</button>
      </div>
    </div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import {
      toDateKey, formatDateLong, loadEmployees, loadBookingsForDay, loadBookingsForRange,
      clientName, bookingEmployeeIds, bookingPhone, getDayHours, timeToMinutes, layoutOverlaps,
      hasConflict, reassignEmployee, updateBookingTime, confirmBooking, cancelBooking, restoreNoShow, markNoShow,
      loadServices, searchClients, createManualClient, createBooking, updateBooking,
    } from '/pro/js/planning.js';

    let business = null;
    let employees = [];
    let selectedDate = new Date();
    let viewMode = 'day'; // 'day' | 'week'
    let dayBookings = []; // donnees du jour affiche (utilisees pour la verif de conflits)
    let currentBooking = null;
    let currentGridStartMin = 0;

    const PX_PER_MIN = 1.2;
    const loadingEl = document.getElementById('loading');
    const agendaEl = document.getElementById('agenda');
    const headEl = document.getElementById('agenda-head');
    const bodyEl = document.getElementById('agenda-body');
    const dateLabelEl = document.getElementById('date-label');

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }

    function showToast(message) {
      const t = document.getElementById('toast');
      t.textContent = message;
      t.classList.add('visible');
      setTimeout(() => t.classList.remove('visible'), 3000);
    }

    function statusColor(booking) {
      if (booking.status === 'no_show') return '#A0AEC0';
      if (booking.status === 'pending') return '#DD6B20';
      if (booking.is_paid) return '#38A169';
      return null; // utiliser la couleur employe
    }

    // -----------------------------------------------------
    // VUE JOUR
    // -----------------------------------------------------
    async function renderDayView() {
      agendaEl.classList.remove('week-mode');
      const hours = getDayHours(business, selectedDate);
      const startMin = timeToMinutes(hours.start);
      const endMin = timeToMinutes(hours.end);
      const totalMin = Math.max(endMin - startMin, 60);
      const gridHeight = totalMin * PX_PER_MIN;
      currentGridStartMin = startMin;

      dateLabelEl.innerHTML = escapeHtml(formatDateLong(selectedDate)) +
        (hours.isOpen === false ? '<span class="closed-badge">Ferme</span>' : '');

      const dateKey = toDateKey(selectedDate);
      dayBookings = await loadBookingsForDay(business.id, dateKey);

      loadingEl.style.display = 'none';

      if (employees.length === 0) {
        agendaEl.style.display = 'none';
        loadingEl.style.display = 'block';
        loadingEl.textContent = "Aucun employe actif. Ajoutez votre equipe depuis l'application mobile.";
        return;
      }
      agendaEl.style.display = 'block';

      const cols = employees.length;
      headEl.style.gridTemplateColumns = '56px repeat(' + cols + ', 1fr)';
      bodyEl.style.gridTemplateColumns = '56px repeat(' + cols + ', 1fr)';

      headEl.innerHTML = '<div class="corner"></div>' + employees.map((emp) => {
        const count = dayBookings.filter((b) => bookingEmployeeIds(b).includes(emp.id)).length;
        const color = emp.color || '#00BFA5';
        return '<div class="col-head"><div class="emp-avatar" style="background:' + color + '">' +
          escapeHtml((emp.first_name || '?').charAt(0).toUpperCase()) + '</div>' +
          '<div><div class="col-name">' + escapeHtml(emp.first_name || '') + '</div>' +
          '<div class="col-count">' + count + ' RDV</div></div></div>';
      }).join('');

      bodyEl.innerHTML = '';
      bodyEl.style.height = gridHeight + 'px';

      // Colonne des heures
      const timeCol = document.createElement('div');
      timeCol.className = 'time-col';
      for (let m = Math.ceil(startMin / 60) * 60; m <= endMin; m += 60) {
        const mark = document.createElement('div');
        mark.className = 'time-mark';
        mark.style.top = ((m - startMin) * PX_PER_MIN) + 'px';
        const h = String(Math.floor(m / 60)).padStart(2, '0');
        mark.innerHTML = '<span>' + h + ':00</span>';
        timeCol.appendChild(mark);
      }
      bodyEl.appendChild(timeCol);

      employees.forEach((emp) => {
        const col = document.createElement('div');
        col.className = 'day-col';
        col.dataset.employeeId = emp.id;
        attachDropZone(col, emp.id);

        const empBookings = dayBookings.filter((b) => bookingEmployeeIds(b).includes(emp.id));
        const layouts = layoutOverlaps(empBookings.map((b) => ({
          start: timeToMinutes((b.start_time || '').substring(0, 5)),
          end: timeToMinutes((b.end_time || '').substring(0, 5)) || 0,
        })));
        empBookings.forEach((b, i) => {
          const el = renderApptBlock(b, emp.color || '#00BFA5', startMin, layouts[i]);
          col.appendChild(el);
        });
        bodyEl.appendChild(col);
      });
    }

    function renderApptBlock(booking, employeeColor, gridStartMin, layout) {
      const start = timeToMinutes((booking.start_time || '').substring(0, 5));
      const end = timeToMinutes((booking.end_time || '').substring(0, 5)) || (start + 30);
      const top = (start - gridStartMin) * PX_PER_MIN;
      const height = Math.max((end - start) * PX_PER_MIN, 22);
      const accent = statusColor(booking); // null si rien de particulier a signaler
      const mainColor = accent || employeeColor;

      const lay = layout || { col: 0, total: 1 };
      const widthPct = 100 / lay.total;
      const leftPct = lay.col * widthPct;

      const svc = booking.services;
      const el = document.createElement('div');
      el.className = 'appt';
      el.style.top = top + 'px';
      el.style.height = height + 'px';
      el.style.left = 'calc(' + leftPct + '% + 3px)';
      el.style.width = 'calc(' + widthPct + '% - 6px)';
      el.style.right = 'auto';
      el.style.background = mainColor + '17';
      el.style.borderLeftColor = employeeColor;
      if (accent) el.style.borderTop = '3px solid ' + accent;
      el.draggable = true;
      el.dataset.bookingId = booking.id;

      el.innerHTML =
        '<div class="t" style="color:' + mainColor + '">' + (booking.start_time || '').substring(0, 5) + '</div>' +
        (height > 34 ? '<div class="c">' + escapeHtml(clientName(booking)) + '</div>' : '') +
        (height > 48 && svc ? '<div class="s">' + escapeHtml(svc.name) + '</div>' : '');

      el.addEventListener('click', () => openPanel(booking, employeeColor));
      el.addEventListener('dragstart', (e) => {
        el.classList.add('dragging');
        e.dataTransfer.setData('text/plain', booking.id);
      });
      el.addEventListener('dragend', () => el.classList.remove('dragging'));

      return el;
    }

    function attachDropZone(col, employeeId) {
      const indicator = document.createElement('div');
      indicator.className = 'drop-indicator';
      indicator.style.display = 'none';
      col.appendChild(indicator);

      function snappedMinutesFromEvent(e) {
        const rect = col.getBoundingClientRect();
        const offsetY = e.clientY - rect.top;
        const rawMin = currentGridStartMin + (offsetY / PX_PER_MIN);
        return Math.max(Math.round(rawMin / 5) * 5, currentGridStartMin);
      }

      col.addEventListener('dragover', (e) => {
        e.preventDefault();
        col.classList.add('drop-hover');
        const snapped = snappedMinutesFromEvent(e);
        indicator.style.display = 'block';
        indicator.style.top = ((snapped - currentGridStartMin) * PX_PER_MIN) + 'px';
        const h = String(Math.floor(snapped / 60)).padStart(2, '0');
        const m = String(snapped % 60).padStart(2, '0');
        indicator.innerHTML = '<span>' + h + ':' + m + '</span>';
      });
      col.addEventListener('dragleave', (e) => {
        if (e.target === col) {
          col.classList.remove('drop-hover');
          indicator.style.display = 'none';
        }
      });
      col.addEventListener('drop', async (e) => {
        e.preventDefault();
        col.classList.remove('drop-hover');
        indicator.style.display = 'none';

        const bookingId = e.dataTransfer.getData('text/plain');
        const booking = dayBookings.find((b) => b.id === bookingId);
        if (!booking) return;

        const oldStart = timeToMinutes((booking.start_time || '').substring(0, 5));
        const oldEnd = timeToMinutes((booking.end_time || '').substring(0, 5)) || (oldStart + 30);
        const duration = oldEnd - oldStart;

        const newStart = snappedMinutesFromEvent(e);
        const newEnd = newStart + duration;

        const employeeChanged = !bookingEmployeeIds(booking).includes(employeeId);
        const timeChanged = newStart !== oldStart;
        if (!employeeChanged && !timeChanged) return; // depose au meme endroit, rien a faire

        const conflictCheckEnabled = business.check_employee_conflicts !== false;
        if (conflictCheckEnabled && hasConflict(dayBookings, employeeId, newStart, newEnd, bookingId)) {
          showToast('Conflit : cet employe a deja un RDV sur ce creneau.');
          return;
        }

        const startStr = String(Math.floor(newStart / 60)).padStart(2, '0') + ':' + String(newStart % 60).padStart(2, '0') + ':00';
        const endStr = String(Math.floor(newEnd / 60)).padStart(2, '0') + ':' + String(newEnd % 60).padStart(2, '0') + ':00';

        try {
          if (timeChanged) await updateBookingTime(bookingId, startStr, endStr);
          if (employeeChanged) await reassignEmployee(bookingId, employeeId);
          showToast('Rendez-vous deplace.');
          await renderDayView();
        } catch (err) {
          console.error(err);
          showToast('Erreur lors du deplacement.');
        }
      });
    }

    // -----------------------------------------------------
    // VUE SEMAINE (7 jours, tous les employes melanges par couleur)
    // -----------------------------------------------------
    function startOfWeek(date) {
      const d = new Date(date);
      const day = d.getDay(); // 0 = dimanche
      const diff = day === 0 ? -6 : 1 - day; // lundi = debut de semaine
      d.setDate(d.getDate() + diff);
      d.setHours(0, 0, 0, 0);
      return d;
    }

    async function renderWeekView() {
      const monday = startOfWeek(selectedDate);
      const sunday = new Date(monday);
      sunday.setDate(sunday.getDate() + 6);
      const afterSunday = new Date(sunday);
      afterSunday.setDate(afterSunday.getDate() + 1);

      dateLabelEl.textContent = 'Semaine du ' + monday.getDate() + ' au ' + sunday.getDate() +
        ' ' + formatDateLong(sunday).split(' ').slice(2).join(' ');

      const weekBookings = await loadBookingsForRange(business.id, toDateKey(monday), toDateKey(afterSunday));
      const employeeById = {};
      employees.forEach((e) => { employeeById[e.id] = e; });

      loadingEl.style.display = 'none';
      agendaEl.style.display = 'block';
      agendaEl.classList.add('week-mode');

      const days = [];
      for (let i = 0; i < 7; i++) {
        const d = new Date(monday);
        d.setDate(d.getDate() + i);
        days.push(d);
      }

      headEl.style.gridTemplateColumns = 'repeat(7, 1fr)';
      bodyEl.style.gridTemplateColumns = 'repeat(7, 1fr)';
      bodyEl.style.height = 'auto';

      const dayLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
      const todayKey = toDateKey(new Date());

      headEl.innerHTML = days.map((d, i) => {
        const key = toDateKey(d);
        const count = weekBookings.filter((b) => b.booking_date === key).length;
        const isToday = key === todayKey;
        return '<div class="col-head" style="justify-content:center' + (isToday ? ';background:rgba(0,191,165,0.06)' : '') + '">' +
          '<div style="text-align:center"><div class="col-name">' + dayLabels[i] + ' ' + d.getDate() + '</div>' +
          '<div class="col-count">' + count + ' RDV</div></div></div>';
      }).join('');

      bodyEl.innerHTML = days.map((d) => {
        const key = toDateKey(d);
        const dayItems = weekBookings.filter((b) => b.booking_date === key)
          .sort((a, b) => (a.start_time || '').localeCompare(b.start_time || ''));

        const rows = dayItems.map((b) => {
          const empIds = bookingEmployeeIds(b);
          const emp = empIds.length ? employeeById[empIds[0]] : null;
          const employeeColor = (emp && emp.color) || '#00BFA5';
          const accent = statusColor(b);
          const mainColor = accent || employeeColor;
          const start = (b.start_time || '').substring(0, 5);
          const topBorder = accent ? ('border-top:3px solid ' + accent + ';') : '';
          return '<div class="week-chip" data-booking-id="' + b.id + '" style="border-left-color:' + employeeColor + ';' + topBorder + ' background:' + mainColor + '10">' +
            '<span class="wc-time" style="color:' + mainColor + '">' + escapeHtml(start) + '</span>' +
            '<span class="wc-name">' + escapeHtml(clientName(b)) + '</span>' +
            '</div>';
        }).join('');

        return '<div class="week-day-col">' + (rows || '<div class="week-empty">-</div>') + '</div>';
      }).join('');

      bodyEl.querySelectorAll('.week-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
          const b = weekBookings.find((x) => x.id === chip.dataset.bookingId);
          if (!b) return;
          const empIds = bookingEmployeeIds(b);
          const emp = empIds.length ? employeeById[empIds[0]] : null;
          openPanel(b, (emp && emp.color) || '#00BFA5');
        });
      });
    }

    // -----------------------------------------------------
    // PANNEAU D'ACTIONS SUR UN RDV
    // -----------------------------------------------------
    function openPanel(booking, employeeColor) {
      currentBooking = booking;
      const color = statusColor(booking) || employeeColor;
      const svc = booking.services;
      const price = svc ? (svc.price || 0) : 0;
      const currencySymbols = { EUR: '\u20ac', MAD: 'DH', CHF: 'CHF', XOF: 'FCFA' };
      const currencySymbol = currencySymbols[(business && business.currency_code) || 'EUR'] || '\u20ac';

      document.getElementById('panel-icon').style.background = color + '1A';
      document.getElementById('panel-icon').style.color = color;
      document.getElementById('panel-icon').textContent = '\u{1F4C5}';
      document.getElementById('panel-client').textContent = clientName(booking);
      document.getElementById('panel-service').textContent = svc ? svc.name : 'RDV';
      document.getElementById('panel-price').textContent = Math.round(price) + ' ' + currencySymbol;
      document.getElementById('panel-price').style.color = color;

      const phone = bookingPhone(booking);
      const phoneEl = document.getElementById('panel-phone');
      if (phone) {
        phoneEl.style.display = 'inline-flex';
        phoneEl.href = 'tel:' + phone;
        phoneEl.textContent = phone;
      } else {
        phoneEl.style.display = 'none';
      }

      document.getElementById('confirm-cancel-box').classList.remove('visible');
      renderPanelActions(booking);
      document.getElementById('panel-overlay').classList.add('visible');
    }

    function closePanel() {
      document.getElementById('panel-overlay').classList.remove('visible');
      currentBooking = null;
    }

    function renderPanelActions(booking) {
      const container = document.getElementById('panel-actions');
      container.innerHTML = '';

      if (booking.status === 'no_show') {
        container.appendChild(makeAction('\u21A9', 'Annuler le no-show (restaurer le RDV)', async () => {
          await restoreNoShow(booking);
          showToast('No-show annule, RDV restaure.');
          closePanel();
          await reloadCurrentView();
        }));
        return;
      }

      if (booking.status === 'pending') {
        container.appendChild(makeAction('\u2705', 'Confirmer le rendez-vous', async () => {
          await confirmBooking(booking.id);
          showToast('Rendez-vous confirme.');
          closePanel();
          await reloadCurrentView();
        }));
        container.appendChild(divider());
      }

      container.appendChild(makeAction('\u{1F4DD}', 'Modifier le rendez-vous', () => {
        closePanel();
        openNewBookingModal(booking);
      }));
      container.appendChild(divider());

      if (booking.status === 'confirmed' && !booking.is_paid) {
        container.appendChild(makeAction('\u{1F6AB}', 'Client absent (no-show)', async () => {
          const businessName = (business && business.name) || 'le professionnel';
          const result = await markNoShow(booking, business.id, businessName);
          showToast(result.blocked ? 'Client bloque apres 3 no-shows.' : 'No-show enregistre (' + result.count + '/3).');
          closePanel();
          await reloadCurrentView();
        }));
        container.appendChild(divider());
      }

      container.appendChild(makeAction('\u2716', 'Annuler le rendez-vous', () => {
        document.getElementById('confirm-cancel-box').classList.add('visible');
      }, 'var(--error)'));
    }

    function makeAction(icon, label, onClick, color) {
      const btn = document.createElement('button');
      btn.className = 'action-tile';
      if (color) btn.style.color = color;
      btn.innerHTML = '<span class="a-icon">' + icon + '</span><span>' + escapeHtml(label) + '</span>';
      btn.addEventListener('click', onClick);
      return btn;
    }
    function divider() {
      const d = document.createElement('div');
      d.className = 'panel-divider';
      return d;
    }

    document.getElementById('panel-overlay').addEventListener('click', (e) => {
      if (e.target.id === 'panel-overlay') closePanel();
    });
    document.getElementById('confirm-cancel-no').addEventListener('click', () => {
      document.getElementById('confirm-cancel-box').classList.remove('visible');
    });
    document.getElementById('confirm-cancel-yes').addEventListener('click', async () => {
      if (!currentBooking) return;
      await cancelBooking(currentBooking.id);
      showToast('Rendez-vous annule.');
      closePanel();
      await reloadCurrentView();
    });

    // -----------------------------------------------------
    // NAVIGATION
    // -----------------------------------------------------
    async function reloadCurrentView() {
      if (viewMode === 'day') await renderDayView();
      else await renderWeekView();
    }

    document.getElementById('prev-btn').addEventListener('click', () => {
      selectedDate.setDate(selectedDate.getDate() - (viewMode === 'week' ? 7 : 1));
      reloadCurrentView();
    });
    document.getElementById('next-btn').addEventListener('click', () => {
      selectedDate.setDate(selectedDate.getDate() + (viewMode === 'week' ? 7 : 1));
      reloadCurrentView();
    });
    document.getElementById('today-btn').addEventListener('click', () => {
      selectedDate = new Date();
      reloadCurrentView();
    });
    document.getElementById('view-day').addEventListener('click', () => {
      viewMode = 'day';
      document.getElementById('view-day').classList.add('active');
      document.getElementById('view-week').classList.remove('active');
      document.getElementById('new-booking-btn').style.display = 'inline-block';
      reloadCurrentView();
    });
    document.getElementById('view-week').addEventListener('click', () => {
      viewMode = 'week';
      document.getElementById('view-week').classList.add('active');
      document.getElementById('view-day').classList.remove('active');
      document.getElementById('new-booking-btn').style.display = 'none';
      reloadCurrentView();
    });

    // -----------------------------------------------------
    // NOUVELLE RESERVATION
    // -----------------------------------------------------
    let services = [];
    let selectedNewEmployeeId = null;
    let selectedClient = null; // {id, type:'dambou'|'manual', name, phone} ou null
    let editingBookingId = null;

    function updateClientBoxDisplay() {
      const placeholder = document.getElementById('client-box-placeholder');
      const selectedBox = document.getElementById('client-box-selected');
      if (!selectedClient) {
        placeholder.style.display = 'block';
        selectedBox.style.display = 'none';
        return;
      }
      placeholder.style.display = 'none';
      selectedBox.style.display = 'flex';
      document.getElementById('csb-avatar').textContent = (selectedClient.name || '?').charAt(0).toUpperCase();
      document.getElementById('csb-name').textContent = selectedClient.name || 'Client';
      document.getElementById('csb-phone').textContent = selectedClient.phone || '';
      document.getElementById('csb-badge').style.display = selectedClient.type === 'dambou' ? 'inline-block' : 'none';
    }

    function openNewBookingModal(existingBooking) {
      if (viewMode !== 'day') return;
      document.getElementById('new-date-label').textContent = formatDateLong(selectedDate);
      document.getElementById('new-booking-form').reset();
      document.getElementById('new-error').style.display = 'none';

      if (existingBooking) {
        editingBookingId = existingBooking.id;
        document.getElementById('new-modal-title').textContent = 'Modifier le rendez-vous';
        document.getElementById('new-submit-btn').textContent = 'Enregistrer les modifications';

        document.getElementById('new-date').value = existingBooking.booking_date;
        document.getElementById('new-time').value = (existingBooking.start_time || '').substring(0, 5);
        document.getElementById('new-service').value = (existingBooking.services && existingBooking.services.id) || (existingBooking.service_id || '');

        if (existingBooking.customer_id) {
          selectedClient = { id: existingBooking.customer_id, type: 'dambou', name: clientName(existingBooking), phone: bookingPhone(existingBooking) };
        } else if (existingBooking.manual_client_id || existingBooking.manual_customer_name) {
          selectedClient = { id: existingBooking.manual_client_id || null, type: 'manual', name: clientName(existingBooking), phone: bookingPhone(existingBooking) };
        } else {
          selectedClient = null;
        }

        const empIds = bookingEmployeeIds(existingBooking);
        selectedNewEmployeeId = empIds.length ? empIds[0] : null;
      } else {
        editingBookingId = null;
        document.getElementById('new-modal-title').textContent = 'Nouvelle reservation';
        document.getElementById('new-submit-btn').textContent = 'Creer la reservation';
        document.getElementById('new-date').value = toDateKey(selectedDate);
        selectedNewEmployeeId = employees.length === 1 ? employees[0].id : null;
        selectedClient = null;
      }

      updateClientBoxDisplay();
      renderEmployeeChips();
      document.getElementById('new-overlay').classList.add('visible');
    }
    function closeNewBookingModal() {
      document.getElementById('new-overlay').classList.remove('visible');
    }

    function renderEmployeeChips() {
      const wrap = document.getElementById('new-employees-chips');
      const wrapContainer = document.getElementById('new-employees-wrap');
      if (employees.length === 0) { wrapContainer.style.display = 'none'; return; }
      wrapContainer.style.display = 'block';
      wrap.innerHTML = '';
      employees.forEach((emp) => {
        const isSelected = selectedNewEmployeeId === emp.id;
        const color = emp.color || '#00BFA5';
        const chip = document.createElement('div');
        chip.style.cssText = 'display:flex; align-items:center; gap:6px; padding:6px 10px; border-radius:20px; cursor:pointer; font-size:12px; font-weight:700; border:1.5px solid ' +
          (isSelected ? color : 'var(--card-border)') + '; background:' + (isSelected ? color + '26' : 'white') + '; color:' + (isSelected ? color : 'var(--text-dark)');
        chip.innerHTML = '<span style="width:18px;height:18px;border-radius:50%;background:' + color + '33;border:1px solid ' + color + ';display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;color:' + color + '">' +
          escapeHtml((emp.first_name || '?').charAt(0).toUpperCase()) + '</span>' + escapeHtml(emp.first_name || '');
        chip.addEventListener('click', () => {
          selectedNewEmployeeId = isSelected ? null : emp.id;
          renderEmployeeChips();
        });
        wrap.appendChild(chip);
      });
    }

    document.getElementById('new-booking-btn').addEventListener('click', () => openNewBookingModal());
    document.getElementById('new-close-btn').addEventListener('click', closeNewBookingModal);
    document.getElementById('new-overlay').addEventListener('click', (e) => {
      if (e.target.id === 'new-overlay') closeNewBookingModal();
    });

    // -----------------------------------------------------
    // SELECTEUR CLIENT (recherche Dambou + manuels, ou creation)
    // -----------------------------------------------------
    document.getElementById('client-selector-box').addEventListener('click', openClientSearch);
    document.getElementById('csb-clear').addEventListener('click', (e) => {
      e.stopPropagation();
      selectedClient = null;
      updateClientBoxDisplay();
    });
    document.getElementById('client-search-close').addEventListener('click', closeClientSearch);
    document.getElementById('client-search-overlay').addEventListener('click', (e) => {
      if (e.target.id === 'client-search-overlay') closeClientSearch();
    });

    function openClientSearch() {
      document.getElementById('client-search-input').value = '';
      document.getElementById('client-results').innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-light); font-size:13px">Tapez un nom ou un telephone</div>';
      document.getElementById('client-create-form').style.display = 'none';
      document.getElementById('client-search-overlay').classList.add('visible');
      document.getElementById('client-search-input').focus();
    }
    function closeClientSearch() {
      document.getElementById('client-search-overlay').classList.remove('visible');
    }

    function clientTile(name, phone, badge, onClick) {
      const tile = document.createElement('div');
      tile.style.cssText = 'display:flex; align-items:center; gap:10px; padding:10px 6px; cursor:pointer; border-bottom:1px solid var(--card-border)';
      tile.innerHTML =
        '<div style="width:30px;height:30px;border-radius:50%;background:rgba(0,191,165,0.12);color:var(--primary-dark);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0">' +
        escapeHtml((name || '?').charAt(0).toUpperCase()) + '</div>' +
        '<div style="flex:1;min-width:0"><div style="display:flex;align-items:center;gap:6px"><span style="font-size:13px;font-weight:700">' + escapeHtml(name) + '</span>' +
        (badge ? '<span style="font-size:9px;font-weight:800;color:var(--primary-dark);background:rgba(0,191,165,0.1);padding:2px 5px;border-radius:4px">Dambou</span>' : '') + '</div>' +
        (phone ? '<div style="font-size:11px;color:var(--text-medium)">' + escapeHtml(phone) + '</div>' : '') + '</div>';
      tile.addEventListener('click', onClick);
      return tile;
    }

    const clientSearchInput = document.getElementById('client-search-input');
    let clientSearchDebounce = null;
    clientSearchInput.addEventListener('input', () => {
      clearTimeout(clientSearchDebounce);
      const query = clientSearchInput.value;
      clientSearchDebounce = setTimeout(async () => {
        const resultsBox = document.getElementById('client-results');
        if (query.trim().length < 2) {
          resultsBox.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-light); font-size:13px">Tapez un nom ou un telephone</div>';
          return;
        }
        resultsBox.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-light); font-size:13px">Recherche...</div>';
        const results = await searchClients(business.id, query);
        resultsBox.innerHTML = '';

        if (!results.dambou.length && !results.manual.length) {
          resultsBox.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-light); font-size:13px">Aucun resultat</div>';
          return;
        }
        results.dambou.forEach((u) => {
          const name = ((u.first_name || '') + ' ' + (u.last_name || '')).trim() || 'Client Dambou';
          resultsBox.appendChild(clientTile(name, u.phone || '', true, () => {
            selectedClient = { id: u.id, type: 'dambou', name: name, phone: u.phone || '' };
            updateClientBoxDisplay();
            closeClientSearch();
          }));
        });
        results.manual.forEach((cl) => {
          const name = ((cl.first_name || '') + ' ' + (cl.last_name || '')).trim() || 'Client';
          resultsBox.appendChild(clientTile(name, cl.phone || '', false, () => {
            selectedClient = { id: cl.id, type: 'manual', name: name, phone: cl.phone || '' };
            updateClientBoxDisplay();
            closeClientSearch();
          }));
        });
      }, 350);
    });

    document.getElementById('client-create-toggle').addEventListener('click', () => {
      const form = document.getElementById('client-create-form');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
      if (form.style.display === 'block') document.getElementById('cc-first-name').focus();
    });

    document.getElementById('cc-save-btn').addEventListener('click', async () => {
      const firstName = document.getElementById('cc-first-name').value.trim();
      if (!firstName) { document.getElementById('cc-first-name').focus(); return; }
      const lastName = document.getElementById('cc-last-name').value.trim();
      const phone = document.getElementById('cc-phone').value.trim();
      const email = document.getElementById('cc-email').value.trim();

      const btn = document.getElementById('cc-save-btn');
      btn.disabled = true;
      btn.textContent = 'Creation...';
      try {
        const created = await createManualClient(business.id, { firstName, lastName, phone, email });
        const name = ((created.first_name || '') + ' ' + (created.last_name || '')).trim();
        selectedClient = { id: created.id, type: 'manual', name: name, phone: created.phone || '' };
        updateClientBoxDisplay();
        closeClientSearch();
        showToast('Fiche client creee.');
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de la creation du client.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Creer et selectionner';
      }
    });

    // -----------------------------------------------------
    // SOUMISSION (creation ou mise a jour)
    // -----------------------------------------------------
    document.getElementById('new-booking-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const errorEl = document.getElementById('new-error');
      errorEl.style.display = 'none';

      const dateKey = document.getElementById('new-date').value;
      const time = document.getElementById('new-time').value; // 'HH:MM'
      const serviceId = document.getElementById('new-service').value;
      const service = services.find((s) => s.id === serviceId);

      if (!dateKey || !time || !service || !selectedClient) {
        errorEl.textContent = 'Merci de remplir la date, l\'heure, le service et le client.';
        errorEl.style.display = 'block';
        return;
      }

      const startMin = timeToMinutes(time);
      const duration = service.duration || 30;
      const endMin = startMin + duration;
      const startTime = time + ':00';
      const endTime = String(Math.floor(endMin / 60)).padStart(2, '0') + ':' + String(endMin % 60).padStart(2, '0') + ':00';

      let employeeId = selectedNewEmployeeId;
      if (!employeeId && employees.length === 1) employeeId = employees[0].id;

      // Conflit : si la date choisie est celle deja affichee, on reutilise dayBookings (deja charge).
      // Sinon on va chercher les reservations de la date cible avant de verifier.
      const conflictSource = (dateKey === toDateKey(selectedDate))
        ? dayBookings
        : await loadBookingsForDay(business.id, dateKey);

      if (employeeId && hasConflict(conflictSource, employeeId, startMin, endMin, editingBookingId)) {
        const proceed = confirm('Cet employe a deja un rendez-vous sur ce creneau. Continuer quand meme ?');
        if (!proceed) return;
      }

      const submitBtn = document.getElementById('new-submit-btn');
      submitBtn.disabled = true;
      submitBtn.textContent = editingBookingId ? 'Enregistrement...' : 'Creation...';

      const clientParams = selectedClient.type === 'dambou'
        ? { customerId: selectedClient.id }
        : { customerName: selectedClient.name, customerPhone: selectedClient.phone, manualClientId: selectedClient.id };

      try {
        if (editingBookingId) {
          await updateBooking(editingBookingId, Object.assign({
            serviceId: service.id,
            dateKey: dateKey,
            startTime: startTime,
            endTime: endTime,
            price: service.price,
            employeeId: employeeId,
          }, clientParams));
          showToast('Rendez-vous modifie.');
        } else {
          await createBooking(Object.assign({
            businessId: business.id,
            serviceId: service.id,
            dateKey: dateKey,
            startTime: startTime,
            endTime: endTime,
            price: service.price,
            employeeId: employeeId,
          }, clientParams));
          showToast('Reservation creee.');
        }
        closeNewBookingModal();
        selectedDate = new Date(dateKey + 'T00:00:00');
        await renderDayView();
      } catch (err) {
        console.error(err);
        errorEl.textContent = 'Erreur lors de l\'enregistrement de la reservation.';
        errorEl.style.display = 'block';
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = editingBookingId ? 'Enregistrer les modifications' : 'Creer la reservation';
      }
    });

    // -----------------------------------------------------
    // INIT
    // -----------------------------------------------------
    (async () => {
      const session = await requireAuth();
      if (!session) return;

      business = await getBusinessForUser(session.user.id);
      if (!business) {
        loadingEl.textContent = 'Aucun etablissement associe a ce compte.';
        return;
      }
      employees = await loadEmployees(business.id);
      services = await loadServices(business.id);
      const serviceSelect = document.getElementById('new-service');
      services.forEach((s) => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.name + ' - ' + Math.round(s.price || 0) + ' ' +
          ({ EUR: '\u20ac', MAD: 'DH', CHF: 'CHF', XOF: 'FCFA' }[business.currency_code] || '\u20ac');
        serviceSelect.appendChild(opt);
      });
      await renderDayView();
    })();
  </script>
</body>
</html>
