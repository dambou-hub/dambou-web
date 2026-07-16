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

  .appt { position: absolute; left: 3px; right: 3px; border-radius: 8px; border-left: 3px solid; padding: 3px 7px; overflow: hidden; cursor: pointer; z-index: 2; }
  .appt:hover { z-index: 3; box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
  .appt.dragging { opacity: 0.4; }
  .appt .t { font-size: 10px; font-weight: 800; }
  .appt .c { font-size: 11px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .appt .s { font-size: 10px; color: var(--text-medium); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .appt .status-dot { position: absolute; top: 5px; right: 5px; width: 6px; height: 6px; border-radius: 50%; }

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

  <script type="module">
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import {
      toDateKey, formatDateLong, loadEmployees, loadBookingsForDay, loadBookingsForRange,
      clientName, bookingEmployeeIds, bookingPhone, getDayHours, timeToMinutes, layoutOverlaps,
      hasConflict, reassignEmployee, confirmBooking, cancelBooking, restoreNoShow, markNoShow,
    } from '/pro/js/planning.js';

    let business = null;
    let employees = [];
    let selectedDate = new Date();
    let viewMode = 'day'; // 'day' | 'week'
    let dayBookings = []; // donnees du jour affiche (utilisees pour la verif de conflits)
    let currentBooking = null;

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
      const hours = getDayHours(business, selectedDate);
      const startMin = timeToMinutes(hours.start);
      const endMin = timeToMinutes(hours.end);
      const totalMin = Math.max(endMin - startMin, 60);
      const gridHeight = totalMin * PX_PER_MIN;

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
      const color = statusColor(booking) || employeeColor;

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
      el.style.background = color + '17';
      el.style.borderLeftColor = color;
      el.draggable = true;
      el.dataset.bookingId = booking.id;

      el.innerHTML =
        '<div class="t" style="color:' + color + '">' + (booking.start_time || '').substring(0, 5) + '</div>' +
        (height > 34 ? '<div class="c">' + escapeHtml(clientName(booking)) + '</div>' : '') +
        (height > 48 && svc ? '<div class="s">' + escapeHtml(svc.name) + '</div>' : '') +
        '<div class="status-dot" style="background:' + color + '"></div>';

      el.addEventListener('click', () => openPanel(booking, employeeColor));
      el.addEventListener('dragstart', (e) => {
        el.classList.add('dragging');
        e.dataTransfer.setData('text/plain', booking.id);
      });
      el.addEventListener('dragend', () => el.classList.remove('dragging'));

      return el;
    }

    function attachDropZone(col, employeeId) {
      col.addEventListener('dragover', (e) => {
        e.preventDefault();
        col.classList.add('drop-hover');
      });
      col.addEventListener('dragleave', () => col.classList.remove('drop-hover'));
      col.addEventListener('drop', async (e) => {
        e.preventDefault();
        col.classList.remove('drop-hover');
        const bookingId = e.dataTransfer.getData('text/plain');
        const booking = dayBookings.find((b) => b.id === bookingId);
        if (!booking) return;
        if (bookingEmployeeIds(booking).includes(employeeId)) return; // deja sur cette colonne

        const start = timeToMinutes((booking.start_time || '').substring(0, 5));
        const end = timeToMinutes((booking.end_time || '').substring(0, 5));
        const conflictCheckEnabled = business.check_employee_conflicts !== false;

        if (conflictCheckEnabled && hasConflict(dayBookings, employeeId, start, end, bookingId)) {
          showToast('Conflit : cet employe a deja un RDV sur ce creneau.');
          return;
        }

        try {
          await reassignEmployee(bookingId, employeeId);
          showToast('Rendez-vous reassigne.');
          await renderDayView();
        } catch (err) {
          console.error(err);
          showToast('Erreur lors de la reassignation.');
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

      // Amplitude horaire = la plus large parmi les 7 jours de la semaine
      let startMin = 24 * 60, endMin = 0;
      const days = [];
      for (let i = 0; i < 7; i++) {
        const d = new Date(monday);
        d.setDate(d.getDate() + i);
        const h = getDayHours(business, d);
        startMin = Math.min(startMin, timeToMinutes(h.start));
        endMin = Math.max(endMin, timeToMinutes(h.end));
        days.push(d);
      }
      const totalMin = Math.max(endMin - startMin, 60);
      const gridHeight = totalMin * PX_PER_MIN;

      headEl.style.gridTemplateColumns = '56px repeat(7, 1fr)';
      bodyEl.style.gridTemplateColumns = '56px repeat(7, 1fr)';

      const dayLabels = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
      headEl.innerHTML = '<div class="corner"></div>' + days.map((d, i) => {
        const key = toDateKey(d);
        const count = weekBookings.filter((b) => b.booking_date === key).length;
        return '<div class="col-head"><div><div class="col-name">' + dayLabels[i] + ' ' + d.getDate() + '</div>' +
          '<div class="col-count">' + count + ' RDV</div></div></div>';
      }).join('');

      bodyEl.innerHTML = '';
      bodyEl.style.height = gridHeight + 'px';

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

      days.forEach((d) => {
        const key = toDateKey(d);
        const col = document.createElement('div');
        col.className = 'day-col';
        const dayItems = weekBookings.filter((b) => b.booking_date === key);
        const layouts = layoutOverlaps(dayItems.map((b) => ({
          start: timeToMinutes((b.start_time || '').substring(0, 5)),
          end: timeToMinutes((b.end_time || '').substring(0, 5)) || 0,
        })));
        dayItems.forEach((b, i) => {
          const empIds = bookingEmployeeIds(b);
          const emp = empIds.length ? employeeById[empIds[0]] : null;
          const color = (emp && emp.color) || '#00BFA5';
          const el = renderApptBlock(b, color, startMin, layouts[i]);
          el.draggable = false; // reassignation inter-jours non geree dans cette vue
          col.appendChild(el);
        });
        bodyEl.appendChild(col);
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

      container.appendChild(makeAction('\u{1F4DD}', 'Gerer dans les reservations', () => {
        window.location.href = '/pro/reservations';
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
      reloadCurrentView();
    });
    document.getElementById('view-week').addEventListener('click', () => {
      viewMode = 'week';
      document.getElementById('view-week').classList.add('active');
      document.getElementById('view-day').classList.remove('active');
      reloadCurrentView();
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
      await renderDayView();
    })();
  </script>
</body>
</html>
