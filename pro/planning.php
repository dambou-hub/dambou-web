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
    --grey-noshow: #A0AEC0;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }

  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 1180px; margin: 0 auto; padding: 24px 20px 60px; }

  .date-nav { display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 10px 12px; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
  .nav-btn { width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--card-border); background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; color: var(--text-dark); flex-shrink: 0; }
  .nav-btn:hover { border-color: var(--primary); color: var(--primary); }
  .date-label { font-size: 15px; font-weight: 700; text-transform: capitalize; text-align: center; }
  .nav-controls { display: flex; align-items: center; gap: 10px; }
  .today-btn { font-size: 12px; font-weight: 700; color: var(--primary); background: rgba(0,191,165,0.1); border: none; border-radius: 8px; padding: 8px 14px; cursor: pointer; font-family: inherit; }
  .view-toggle { display: flex; gap: 4px; background: var(--background); border-radius: 10px; padding: 3px; }
  .view-toggle button { border: none; background: transparent; padding: 7px 14px; font-size: 12px; font-weight: 700; border-radius: 8px; color: var(--text-medium); font-family: inherit; cursor: pointer; }
  .view-toggle button.active { background: white; color: var(--primary-dark); }

  #loading, #empty-employees { text-align: center; padding: 60px 20px; color: var(--text-medium); font-size: 14px; }

  /* ---- Vue mobile : cards empilees par employe ---- */
  .employee-card { background: white; border: 1px solid var(--card-border); border-radius: 16px; margin-bottom: 14px; overflow: hidden; }
  .employee-head { display: flex; align-items: center; gap: 10px; padding: 14px 16px; }
  .employee-avatar { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; }
  .employee-name { font-weight: 700; font-size: 14px; flex: 1; }
  .badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
  .badge-pending { background: rgba(221,107,32,0.12); color: var(--warning); margin-right: 6px; }
  .bookings-list-mobile { padding: 0 12px 12px; }
  .empty-slot { display: flex; align-items: center; gap: 8px; padding: 10px 4px; color: var(--text-light); font-size: 13px; }
  .mcard { display: flex; gap: 10px; padding: 10px; border-radius: 10px; margin-bottom: 6px; border-left: 3px solid; cursor: pointer; }
  .mcard .t { font-size: 13px; font-weight: 800; }
  .mcard .c { font-size: 13px; font-weight: 700; }
  .mcard .s { font-size: 11px; color: var(--text-medium); }

  /* ---- Vue desktop : grille ---- */
  .agenda { background: white; border: 1px solid var(--card-border); border-radius: 16px; overflow: hidden; }
  .agenda-head { display: grid; border-bottom: 1px solid var(--card-border); }
  .agenda-head .corner { border-right: 1px solid var(--card-border); }
  .col-head { display: flex; align-items: center; gap: 8px; padding: 12px 10px; border-right: 1px solid var(--card-border); min-width: 0; }
  .col-head:last-child { border-right: none; }
  .col-head .emp-avatar { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 800; flex-shrink: 0; }
  .col-head-text { min-width: 0; }
  .col-head-title { font-size: 12px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .col-head-sub { font-size: 10px; color: var(--text-medium); font-weight: 600; }

  .agenda-body { display: grid; position: relative; }
  .time-col { border-right: 1px solid var(--card-border); position: relative; }
  .time-mark { position: absolute; right: 8px; font-size: 10px; color: var(--text-light); font-weight: 600; background: white; padding: 0 2px; transform: translateY(-50%); }

  .day-col { position: relative; border-right: 1px solid var(--card-border); }
  .day-col:last-child { border-right: none; }
  .day-col.drop-target { background: rgba(0,191,165,0.06); }

  .appt { position: absolute; left: 3px; right: 3px; border-radius: 8px; border-left: 3px solid; padding: 3px 7px; overflow: hidden; cursor: pointer; }
  .appt.dragging { opacity: 0.4; }
  .appt .t { font-size: 10px; font-weight: 800; }
  .appt .c { font-size: 11px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .appt .s { font-size: 10px; color: var(--text-medium); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .appt .status-dot { position: absolute; top: 5px; right: 5px; width: 6px; height: 6px; border-radius: 50%; }

  .closed-note { padding: 8px 12px; font-size: 12px; color: var(--warning); background: rgba(221,107,32,0.08); }

  /* ---- Panneau d'actions (modal) ---- */
  .overlay { position: fixed; inset: 0; background: rgba(45,55,72,0.4); display: none; align-items: flex-end; justify-content: center; z-index: 200; }
  .overlay.visible { display: flex; }
  @media (min-width: 640px) { .overlay { align-items: center; } }
  .sheet { background: white; border-radius: 20px 20px 0 0; padding: 20px; width: 100%; max-width: 420px; max-height: 85vh; overflow-y: auto; }
  @media (min-width: 640px) { .sheet { border-radius: 20px; } }
  .sheet-head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
  .sheet-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
  .sheet-client { font-size: 15px; font-weight: 800; }
  .sheet-service { font-size: 12px; color: var(--text-medium); }
  .sheet-phone { display: flex; align-items: center; gap: 4px; font-size: 12px; color: #25D366; font-weight: 700; text-decoration: none; margin-top: 2px; }
  .sheet-price { font-size: 17px; font-weight: 900; margin-left: auto; white-space: nowrap; }
  .sheet-divider { border-top: 1px solid var(--card-border); margin: 12px 0; }
  .action-tile { display: flex; align-items: center; gap: 12px; padding: 13px 4px; cursor: pointer; border: none; background: none; width: 100%; text-align: left; font-family: inherit; font-size: 14px; font-weight: 600; color: var(--text-dark); border-bottom: 1px solid var(--card-border); }
  .action-tile:last-child { border-bottom: none; }
  .action-tile .a-icon { width: 20px; text-align: center; flex-shrink: 0; }
  .sheet-close { display: block; text-align: center; font-size: 13px; color: var(--text-medium); margin-top: 14px; cursor: pointer; background: none; border: none; font-family: inherit; width: 100%; padding: 8px; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <a class="back-link" href="/pro">Retour au tableau de bord</a>
  </div>

  <div class="container">
    <div class="date-nav">
      <button class="nav-btn" id="prev-btn">&larr;</button>
      <div class="date-label" id="date-label">-</div>
      <div class="nav-controls">
        <div class="view-toggle">
          <button class="active" id="view-day">Jour</button>
          <button id="view-week">Semaine</button>
        </div>
        <button class="today-btn" id="today-btn">Aujourd'hui</button>
        <button class="nav-btn" id="next-btn">&rarr;</button>
      </div>
    </div>

    <div id="loading">Chargement du planning...</div>
    <div id="planning-root"></div>
  </div>

  <div class="overlay" id="action-overlay">
    <div class="sheet" id="action-sheet"></div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import {
      toDateKey, formatDateLong, loadEmployees, loadBookingsForDay, loadBookingsForRange,
      clientName, clientPhone, bookingEmployeeIds, getDayRange, parseTimeToMinutes,
      confirmBooking, cancelBooking, markNoShow, undoNoShow,
      hasEmployeeConflict, reassignBookingEmployee,
    } from '/pro/js/planning.js';

    const PX_PER_MIN = 1.2;
    const DAY_LABELS = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

    let business = null;
    let employees = [];
    let selectedDate = new Date();
    let viewMode = 'day'; // 'day' | 'week'
    let isDesktop = window.matchMedia('(min-width: 860px)').matches;
    window.matchMedia('(min-width: 860px)').addEventListener('change', (e) => { isDesktop = e.matches; render(); });

    const loadingEl = document.getElementById('loading');
    const rootEl = document.getElementById('planning-root');
    const dateLabelEl = document.getElementById('date-label');

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    }
    function initial(emp) { return (emp.first_name || '?').charAt(0).toUpperCase(); }
    function statusColor(b) {
      if (b.status === 'no_show') return 'var(--grey-noshow)';
      if (b.status === 'pending') return 'var(--warning)';
      if (b.is_paid) return 'var(--success)';
      return null; // utiliser la couleur employe
    }

    // -----------------------------------------------------
    // Navigation date / vue
    // -----------------------------------------------------
    function updateDateLabel() {
      if (viewMode === 'day') {
        dateLabelEl.textContent = formatDateLong(selectedDate);
      } else {
        const start = startOfWeek(selectedDate);
        const end = new Date(start); end.setDate(end.getDate() + 6);
        dateLabelEl.textContent = start.getDate() + ' - ' + end.getDate() + ' ' +
          end.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });
      }
    }
    function startOfWeek(date) {
      const d = new Date(date);
      const day = d.getDay(); // 0 = dimanche
      const diff = day === 0 ? -6 : 1 - day; // lundi comme premier jour
      d.setDate(d.getDate() + diff);
      d.setHours(0, 0, 0, 0);
      return d;
    }

    document.getElementById('prev-btn').addEventListener('click', () => {
      selectedDate.setDate(selectedDate.getDate() - (viewMode === 'week' ? 7 : 1));
      render();
    });
    document.getElementById('next-btn').addEventListener('click', () => {
      selectedDate.setDate(selectedDate.getDate() + (viewMode === 'week' ? 7 : 1));
      render();
    });
    document.getElementById('today-btn').addEventListener('click', () => {
      selectedDate = new Date();
      render();
    });
    document.getElementById('view-day').addEventListener('click', () => { viewMode = 'day'; syncViewButtons(); render(); });
    document.getElementById('view-week').addEventListener('click', () => { viewMode = 'week'; syncViewButtons(); render(); });
    function syncViewButtons() {
      document.getElementById('view-day').classList.toggle('active', viewMode === 'day');
      document.getElementById('view-week').classList.toggle('active', viewMode === 'week');
    }

    // -----------------------------------------------------
    // Rendu principal
    // -----------------------------------------------------
    async function render() {
      updateDateLabel();
      loadingEl.style.display = 'block';
      rootEl.innerHTML = '';

      if (viewMode === 'day') {
        const dateKey = toDateKey(selectedDate);
        const bookings = await loadBookingsForDay(business.id, dateKey);
        loadingEl.style.display = 'none';
        if (employees.length === 0) {
          rootEl.innerHTML = '<div id="empty-employees">Aucun employe actif. Ajoutez votre equipe depuis l\'application mobile.</div>';
          return;
        }
        if (isDesktop) renderDayGrid(bookings); else renderDayMobile(bookings);
      } else {
        const weekStart = startOfWeek(selectedDate);
        const weekEndExclusive = new Date(weekStart); weekEndExclusive.setDate(weekEndExclusive.getDate() + 7);
        const bookings = await loadBookingsForRange(business.id, toDateKey(weekStart), toDateKey(weekEndExclusive));
        loadingEl.style.display = 'none';
        renderWeekGrid(weekStart, bookings);
      }
    }

    // -----------------------------------------------------
    // Vue mobile : cards empilees (identique a la V1)
    // -----------------------------------------------------
    function renderDayMobile(bookings) {
      employees.forEach((emp) => {
        const dayBookings = bookings.filter((b) => bookingEmployeeIds(b).includes(emp.id));
        rootEl.appendChild(buildEmployeeCardMobile(emp, dayBookings));
      });
    }

    function buildEmployeeCardMobile(emp, dayBookings) {
      const color = emp.color || '#00BFA5';
      const pendingCount = dayBookings.filter((b) => b.status === 'pending').length;
      const card = document.createElement('div');
      card.className = 'employee-card';
      card.innerHTML =
        '<div class="employee-head">' +
        '<div class="employee-avatar" style="background:' + color + '">' + initial(emp) + '</div>' +
        '<div class="employee-name">' + escapeHtml(emp.first_name || '') + '</div>' +
        (pendingCount > 0 ? '<span class="badge badge-pending">' + pendingCount + ' en attente</span>' : '') +
        '<span class="badge" style="background:' + color + '26;color:' + color + '">' + dayBookings.length + ' RDV</span>' +
        '</div>';
      const list = document.createElement('div');
      list.className = 'bookings-list-mobile';
      if (dayBookings.length === 0) {
        list.innerHTML = '<div class="empty-slot">Disponible</div>';
      } else {
        dayBookings.forEach((b) => {
          const rawColor = statusColor(b);
          const sc = rawColor ? resolveCssVar(rawColor) : color;
          const svc = b.services;
          const el = document.createElement('div');
          el.className = 'mcard';
          el.style.background = sc + '0F';
          el.style.borderLeftColor = sc;
          el.innerHTML =
            '<div><div class="t" style="color:' + sc + '">' + escapeHtml((b.start_time || '').substring(0, 5)) + '</div></div>' +
            '<div><div class="c">' + escapeHtml(clientName(b)) + '</div>' +
            '<div class="s">' + escapeHtml(svc ? svc.name : 'RDV') + '</div></div>';
          el.addEventListener('click', () => openActionSheet(b, color));
          list.appendChild(el);
        });
      }
      card.appendChild(list);
      return card;
    }

    // -----------------------------------------------------
    // Vue desktop : grille jour (colonnes = employes)
    // -----------------------------------------------------
    function renderDayGrid(bookings) {
      const range = getDayRange(business, selectedDate);
      const totalMin = Math.max(range.endMin - range.startMin, 60);
      const gridHeight = totalMin * PX_PER_MIN;

      const agenda = document.createElement('div');
      agenda.className = 'agenda';

      if (!range.isOpen) {
        const note = document.createElement('div');
        note.className = 'closed-note';
        note.textContent = 'Ferme ce jour selon vos horaires configures -- les RDV existants restent visibles.';
        agenda.appendChild(note);
      }

      const head = document.createElement('div');
      head.className = 'agenda-head';
      head.style.gridTemplateColumns = '56px repeat(' + employees.length + ', 1fr)';
      head.innerHTML = '<div class="corner"></div>';
      employees.forEach((emp) => {
        const color = emp.color || '#00BFA5';
        const count = bookings.filter((b) => bookingEmployeeIds(b).includes(emp.id)).length;
        const h = document.createElement('div');
        h.className = 'col-head';
        h.innerHTML =
          '<div class="emp-avatar" style="background:' + color + '">' + initial(emp) + '</div>' +
          '<div class="col-head-text"><div class="col-head-title">' + escapeHtml(emp.first_name || '') + '</div>' +
          '<div class="col-head-sub">' + count + ' RDV</div></div>';
        head.appendChild(h);
      });
      agenda.appendChild(head);

      const body = document.createElement('div');
      body.className = 'agenda-body';
      body.style.gridTemplateColumns = '56px repeat(' + employees.length + ', 1fr)';
      body.style.height = gridHeight + 'px';

      const timeCol = document.createElement('div');
      timeCol.className = 'time-col';
      for (let m = range.startMin; m <= range.endMin; m += 60) {
        const mark = document.createElement('div');
        mark.className = 'time-mark';
        mark.style.top = ((m - range.startMin) * PX_PER_MIN) + 'px';
        const hh = String(Math.floor(m / 60)).padStart(2, '0');
        mark.textContent = hh + ':00';
        timeCol.appendChild(mark);
      }
      body.appendChild(timeCol);

      employees.forEach((emp) => {
        const color = emp.color || '#00BFA5';
        const col = document.createElement('div');
        col.className = 'day-col';
        col.dataset.employeeId = emp.id;

        col.addEventListener('dragover', (e) => { e.preventDefault(); col.classList.add('drop-target'); });
        col.addEventListener('dragleave', () => col.classList.remove('drop-target'));
        col.addEventListener('drop', async (e) => {
          e.preventDefault();
          col.classList.remove('drop-target');
          const bookingId = e.dataTransfer.getData('text/booking-id');
          if (!bookingId) return;
          await handleDrop(bookingId, emp.id, bookings);
        });

        const dayBookings = bookings.filter((b) => bookingEmployeeIds(b).includes(emp.id));
        dayBookings.forEach((b) => col.appendChild(buildApptBlock(b, color, range)));

        body.appendChild(col);
      });

      agenda.appendChild(body);
      rootEl.appendChild(agenda);
    }

    function buildApptBlock(b, empColor, range) {
      const rawColor = statusColor(b);
      const color = rawColor ? resolveCssVar(rawColor) : empColor;
      const start = parseTimeToMinutes(b.start_time);
      const end = parseTimeToMinutes(b.end_time) || (start + 30);
      const top = (start - range.startMin) * PX_PER_MIN;
      const height = Math.max((end - start) * PX_PER_MIN, 24);
      const svc = b.services;

      const el = document.createElement('div');
      el.className = 'appt';
      el.style.top = top + 'px';
      el.style.height = height + 'px';
      el.style.background = color + '14';
      el.style.borderLeftColor = color;
      el.draggable = true;
      el.innerHTML =
        '<div class="t" style="color:' + color + '">' + escapeHtml((b.start_time || '').substring(0, 5)) + '</div>' +
        '<div class="c">' + escapeHtml(clientName(b)) + '</div>' +
        (height > 40 ? '<div class="s">' + escapeHtml(svc ? svc.name : 'RDV') + '</div>' : '') +
        '<div class="status-dot" style="background:' + color + '"></div>';

      el.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/booking-id', b.id);
        el.classList.add('dragging');
      });
      el.addEventListener('dragend', () => el.classList.remove('dragging'));
      el.addEventListener('click', () => {
        if (el.classList.contains('dragging')) return;
        openActionSheet(b, empColor);
      });

      return el;
    }

    function resolveCssVar(v) {
      if (v.indexOf('var(') !== 0) return v;
      const name = v.slice(4, -1);
      return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    }

    async function handleDrop(bookingId, newEmployeeId, bookings) {
      const booking = bookings.find((b) => b.id === bookingId);
      if (!booking) return;
      const currentIds = bookingEmployeeIds(booking);
      if (currentIds.includes(newEmployeeId)) return; // deja sur cet employe

      if (business.check_employee_conflicts !== false) {
        const conflict = await hasEmployeeConflict(
          business.id, newEmployeeId, booking.booking_date, booking.start_time, booking.end_time, booking.id
        );
        if (conflict) {
          alert("Cet employe a deja un rendez-vous sur ce creneau. Deplacement annule.");
          return;
        }
      }

      try {
        await reassignBookingEmployee(bookingId, newEmployeeId);
        render();
      } catch (e) {
        console.error(e);
        alert('Erreur lors du deplacement du rendez-vous.');
      }
    }

    // -----------------------------------------------------
    // Vue semaine : colonnes = jours, employes melanges (couleur = employe)
    // -----------------------------------------------------
    function renderWeekGrid(weekStart, bookings) {
      let minStart = 24 * 60, maxEnd = 0;
      for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart); d.setDate(d.getDate() + i);
        const r = getDayRange(business, d);
        minStart = Math.min(minStart, r.startMin);
        maxEnd = Math.max(maxEnd, r.endMin);
      }
      if (maxEnd <= minStart) { minStart = 8 * 60; maxEnd = 20 * 60; }
      const totalMin = maxEnd - minStart;
      const gridHeight = totalMin * PX_PER_MIN;

      const agenda = document.createElement('div');
      agenda.className = 'agenda';

      const head = document.createElement('div');
      head.className = 'agenda-head';
      head.style.gridTemplateColumns = '56px repeat(7, 1fr)';
      head.innerHTML = '<div class="corner"></div>';
      const days = [];
      for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart); d.setDate(d.getDate() + i);
        days.push(d);
        const isToday = toDateKey(d) === toDateKey(new Date());
        const h = document.createElement('div');
        h.className = 'col-head';
        h.innerHTML =
          '<div class="col-head-text"><div class="col-head-title"' + (isToday ? ' style="color:var(--primary)"' : '') + '>' +
          DAY_LABELS[d.getDay()] + ' ' + d.getDate() + '</div></div>';
        head.appendChild(h);
      }
      agenda.appendChild(head);

      const body = document.createElement('div');
      body.className = 'agenda-body';
      body.style.gridTemplateColumns = '56px repeat(7, 1fr)';
      body.style.height = gridHeight + 'px';

      const timeCol = document.createElement('div');
      timeCol.className = 'time-col';
      for (let m = minStart; m <= maxEnd; m += 60) {
        const mark = document.createElement('div');
        mark.className = 'time-mark';
        mark.style.top = ((m - minStart) * PX_PER_MIN) + 'px';
        mark.textContent = String(Math.floor(m / 60)).padStart(2, '0') + ':00';
        timeCol.appendChild(mark);
      }
      body.appendChild(timeCol);

      days.forEach((d) => {
        const dateKey = toDateKey(d);
        const dayBookings = bookings.filter((b) => b.booking_date === dateKey);
        const col = document.createElement('div');
        col.className = 'day-col';
        dayBookings.forEach((b) => {
          const empIds = bookingEmployeeIds(b);
          const emp = employees.find((e) => empIds.includes(e.id));
          const color = emp ? (emp.color || '#00BFA5') : '#A0AEC0';
          const block = buildApptBlock(b, color, { startMin: minStart });
          block.draggable = false;
          col.appendChild(block);
        });
        body.appendChild(col);
      });

      agenda.appendChild(body);
      rootEl.appendChild(agenda);
    }

    // -----------------------------------------------------
    // Panneau d'actions (reproduit _showBookingActions)
    // -----------------------------------------------------
    const overlay = document.getElementById('action-overlay');
    const sheet = document.getElementById('action-sheet');

    function closeSheet() { overlay.classList.remove('visible'); sheet.innerHTML = ''; }
    overlay.addEventListener('click', (e) => { if (e.target === overlay) closeSheet(); });

    function openActionSheet(b, empColor) {
      const svc = b.services;
      const svcName = svc ? svc.name : 'RDV';
      const price = svc ? (svc.price || 0) : 0;
      const phone = clientPhone(b);
      const status = b.status;
      const isPaid = b.is_paid === true;
      const currency = (business && business.currency_code === 'MAD') ? 'DH' : (business && (business.currency_code === 'XOF')) ? 'FCFA' : '\u20ac';

      let html = '<div class="sheet-head">' +
        '<div class="sheet-icon" style="background:' + empColor + '1A;color:' + empColor + '">&#128197;</div>' +
        '<div style="min-width:0;flex:1">' +
        '<div class="sheet-client">' + escapeHtml(clientName(b)) + '</div>' +
        '<div class="sheet-service">' + escapeHtml(svcName) + '</div>' +
        (phone ? '<a class="sheet-phone" href="tel:' + escapeHtml(phone) + '">&#9742; ' + escapeHtml(phone) + '</a>' : '') +
        '</div>' +
        '<div class="sheet-price" style="color:' + empColor + '">' + Math.round(price) + ' ' + currency + '</div>' +
        '</div><div class="sheet-divider"></div>';

      sheet.innerHTML = html;

      if (status === 'no_show') {
        const p = document.createElement('p');
        p.style.cssText = 'font-size:13px;color:var(--text-medium);margin-bottom:14px;line-height:1.5';
        p.textContent = 'Ce rendez-vous a ete marque comme absence. Vous pouvez annuler ce signalement si le client avait une bonne raison.';
        sheet.appendChild(p);
        addAction('Annuler le no-show', '\u2705', 'var(--warning)', async () => {
          await undoNoShow(business.id, b);
          closeSheet(); render();
        });
        addCloseButton();
        overlay.classList.add('visible');
        return;
      }

      if (status === 'pending') {
        addAction('Confirmer le rendez-vous', '\u2705', 'var(--success)', async () => {
          await confirmBooking(b.id); closeSheet(); render();
        });
      }

      if (isPaid) {
        addAction('Paye', '\u2713', 'var(--success)', () => closeSheet());
      } else {
        addAction("Encaissement disponible dans l'app mobile", '\u{1F4B3}', 'var(--text-medium)', () => closeSheet());
      }

      addAction('Gerer dans les reservations', '\u270E', 'var(--text-medium)', () => {
        window.location.href = '/pro/reservations';
      });

      if (status === 'confirmed' && !isPaid) {
        addAction('Client absent (no-show)', '\u{1F6AB}', '#DD6B20', async () => {
          const res = await markNoShow(business.id, b);
          closeSheet();
          if (res.blocked) alert('Client bloque apres 3 no-shows.');
          render();
        });
      }

      addAction('Annuler le rendez-vous', '\u2716', 'var(--error)', async () => {
        if (!confirm('Annuler ce rendez-vous ?')) return;
        await cancelBooking(b.id); closeSheet(); render();
      });

      addCloseButton();
      overlay.classList.add('visible');
    }

    function addAction(label, icon, color, onClick) {
      const btn = document.createElement('button');
      btn.className = 'action-tile';
      btn.innerHTML = '<span class="a-icon" style="color:' + color + '">' + icon + '</span><span>' + escapeHtml(label) + '</span>';
      btn.addEventListener('click', onClick);
      sheet.appendChild(btn);
    }
    function addCloseButton() {
      const btn = document.createElement('button');
      btn.className = 'sheet-close';
      btn.textContent = 'Fermer';
      btn.addEventListener('click', closeSheet);
      sheet.appendChild(btn);
    }

    // -----------------------------------------------------
    // Initialisation
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
      await render();
    })();
  </script>
</body>
</html>
