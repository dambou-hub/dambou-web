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
    --grey-noshow: #A0AEC0;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }

  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 900px; margin: 0 auto; padding: 28px 24px 60px; }

  .date-nav { display: flex; align-items: center; justify-content: space-between; background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 10px 12px; margin-bottom: 24px; }
  .nav-btn { width: 36px; height: 36px; border-radius: 10px; border: 1px solid var(--card-border); background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 16px; color: var(--text-dark); flex-shrink: 0; }
  .nav-btn:hover { border-color: var(--primary); color: var(--primary); }
  .date-label { font-size: 15px; font-weight: 700; text-transform: capitalize; }
  .today-btn { font-size: 12px; font-weight: 700; color: var(--primary); background: rgba(0,191,165,0.1); border: none; border-radius: 8px; padding: 8px 14px; cursor: pointer; font-family: inherit; }

  #loading, #empty-employees { text-align: center; padding: 60px 20px; color: var(--text-medium); font-size: 14px; }

  .employee-card { background: white; border: 1px solid var(--card-border); border-radius: 16px; margin-bottom: 14px; overflow: hidden; }
  .employee-head { display: flex; align-items: center; gap: 10px; padding: 14px 16px; }
  .employee-avatar { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px; flex-shrink: 0; }
  .employee-name { font-weight: 700; font-size: 14px; flex: 1; }
  .badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
  .badge-pending { background: rgba(221,107,32,0.12); color: var(--warning); margin-right: 6px; }

  .bookings-list { padding: 0 12px 12px; }
  .empty-slot { display: flex; align-items: center; gap: 8px; padding: 10px 4px; color: var(--text-light); font-size: 13px; }

  .booking-card { display: flex; gap: 10px; padding: 10px; border-radius: 10px; margin-bottom: 6px; border-left: 3px solid; }
  .booking-time { min-width: 42px; }
  .booking-time .start { font-size: 13px; font-weight: 800; }
  .booking-time .end { font-size: 10px; color: var(--text-light); }
  .booking-divider { width: 1px; background: var(--card-border); }
  .booking-info { flex: 1; min-width: 0; }
  .booking-client { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .booking-service { font-size: 11px; color: var(--text-medium); }
  .booking-duration { font-size: 10px; color: var(--text-light); }

  .unassigned-note { font-size: 12px; color: var(--text-medium); text-align: center; padding: 8px; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <a class="back-link" href="/pro">Retour au tableau de bord</a>
  </div>

  <div class="container">
    <div class="date-nav">
      <button class="nav-btn" id="prev-day">&larr;</button>
      <div style="text-align:center">
        <div class="date-label" id="date-label">-</div>
      </div>
      <div style="display:flex; align-items:center; gap:8px">
        <button class="today-btn" id="today-btn">Aujourd'hui</button>
        <button class="nav-btn" id="next-day">&rarr;</button>
      </div>
    </div>

    <div id="loading">Chargement du planning...</div>
    <div id="employees-container"></div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import { toDateKey, formatDateLong, loadEmployees, loadBookingsForDay, clientName, bookingEmployeeIds } from '/pro/js/planning.js';

    let businessId = null;
    let employees = [];
    let selectedDate = new Date();

    const loadingEl = document.getElementById('loading');
    const containerEl = document.getElementById('employees-container');
    const dateLabelEl = document.getElementById('date-label');

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    }

    function employeeInitial(emp) {
      return (emp.first_name || '?').charAt(0).toUpperCase();
    }

    function bookingColor(booking, employeeColor) {
      if (booking.status === 'no_show') return getComputedStyle(document.documentElement).getPropertyValue('--grey-noshow').trim();
      if (booking.status === 'pending') return getComputedStyle(document.documentElement).getPropertyValue('--warning').trim();
      if (booking.is_paid) return getComputedStyle(document.documentElement).getPropertyValue('--success').trim();
      return employeeColor || '#00BFA5';
    }

    function renderBookingCard(booking, employeeColor) {
      const color = bookingColor(booking, employeeColor);
      const start = (booking.start_time || '').substring(0, 5);
      const end = (booking.end_time || '').substring(0, 5);
      const svc = booking.services;
      const svcName = svc ? svc.name : 'RDV';
      const duration = svc ? svc.duration : 0;

      const div = document.createElement('div');
      div.className = 'booking-card';
      div.style.background = color + '0F';
      div.style.borderLeftColor = color;
      div.innerHTML =
        '<div class="booking-time"><div class="start" style="color:' + color + '">' + escapeHtml(start) + '</div>' +
        (end ? '<div class="end">' + escapeHtml(end) + '</div>' : '') + '</div>' +
        '<div class="booking-divider"></div>' +
        '<div class="booking-info">' +
        '<div class="booking-client">' + escapeHtml(clientName(booking)) + '</div>' +
        '<div class="booking-service">' + escapeHtml(svcName) + '</div>' +
        (duration ? '<div class="booking-duration">' + duration + ' min</div>' : '') +
        '</div>';
      return div;
    }

    function renderEmployeeCard(emp, dayBookings) {
      const color = emp.color || '#00BFA5';
      const pendingCount = dayBookings.filter((b) => b.status === 'pending').length;

      const card = document.createElement('div');
      card.className = 'employee-card';

      const head = document.createElement('div');
      head.className = 'employee-head';
      head.innerHTML =
        '<div class="employee-avatar" style="background:' + color + '">' + employeeInitial(emp) + '</div>' +
        '<div class="employee-name">' + escapeHtml(emp.first_name || '') + '</div>' +
        (pendingCount > 0 ? '<span class="badge badge-pending">' + pendingCount + ' en attente</span>' : '') +
        '<span class="badge" style="background:' + color + '26;color:' + color + '">' + dayBookings.length + ' RDV</span>';
      card.appendChild(head);

      const list = document.createElement('div');
      list.className = 'bookings-list';
      if (dayBookings.length === 0) {
        list.innerHTML = '<div class="empty-slot">Disponible</div>';
      } else {
        dayBookings.forEach((b) => list.appendChild(renderBookingCard(b, color)));
      }
      card.appendChild(list);

      return card;
    }

    async function loadDay() {
      loadingEl.style.display = 'block';
      containerEl.innerHTML = '';
      dateLabelEl.textContent = formatDateLong(selectedDate);

      const dateKey = toDateKey(selectedDate);
      const bookings = await loadBookingsForDay(businessId, dateKey);

      loadingEl.style.display = 'none';

      if (employees.length === 0) {
        containerEl.innerHTML = '<div id="empty-employees">Aucun employe actif. Ajoutez votre equipe depuis l\'application mobile.</div>';
        return;
      }

      const assignedIds = new Set();
      employees.forEach((emp) => {
        const dayBookings = bookings.filter((b) => bookingEmployeeIds(b).includes(emp.id));
        dayBookings.forEach((b) => assignedIds.add(b.id));
        containerEl.appendChild(renderEmployeeCard(emp, dayBookings));
      });

      const unassigned = bookings.filter((b) => !assignedIds.has(b.id));
      if (unassigned.length > 0) {
        const note = document.createElement('div');
        note.className = 'unassigned-note';
        note.textContent = unassigned.length + ' reservation(s) sans employe assigne';
        containerEl.appendChild(note);
      }
    }

    document.getElementById('prev-day').addEventListener('click', () => {
      selectedDate.setDate(selectedDate.getDate() - 1);
      loadDay();
    });
    document.getElementById('next-day').addEventListener('click', () => {
      selectedDate.setDate(selectedDate.getDate() + 1);
      loadDay();
    });
    document.getElementById('today-btn').addEventListener('click', () => {
      selectedDate = new Date();
      loadDay();
    });

    (async () => {
      const session = await requireAuth();
      if (!session) return;

      const business = await getBusinessForUser(session.user.id);
      if (!business) {
        loadingEl.textContent = 'Aucun etablissement associe a ce compte.';
        return;
      }
      businessId = business.id;
      employees = await loadEmployees(businessId);
      await loadDay();
    })();
  </script>
</body>
</html>
