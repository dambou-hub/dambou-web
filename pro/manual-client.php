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
<title>Fiche client - Dambou Pro</title>
<meta name="theme-color" content="#00BFA5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #00BFA5; --primary-dark: #00897B; --text-dark: #2D3748; --text-medium: #718096;
    --text-light: #A0AEC0; --background: #F7F8FA; --card-border: #E2E8F0; --error: #E53E3E;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 560px; margin: 0 auto; padding: 24px 24px 60px; }
  #loading { text-align: center; padding: 60px 20px; color: var(--text-medium); }

  .card { background: white; border: 1px solid var(--card-border); border-radius: 16px; padding: 20px; margin-bottom: 20px; }
  .card h2 { font-size: 15px; font-weight: 800; margin-bottom: 14px; }
  .field { margin-bottom: 12px; }
  .field label { display: block; font-size: 12px; font-weight: 700; color: var(--text-medium); margin-bottom: 5px; }
  .field input { width: 100%; padding: 11px 12px; border: 1px solid var(--card-border); border-radius: 12px; font-size: 14px; font-family: inherit; }
  .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .btn { padding: 11px 18px; border-radius: 12px; border: none; font-size: 13px; font-weight: 700; font-family: inherit; cursor: pointer; }
  .btn-primary { background: var(--primary); color: white; }
  .btn-danger { background: rgba(229,62,62,0.1); color: var(--error); }
  .actions-row { display: flex; gap: 10px; margin-top: 16px; }

  .item-card { background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; }
  .item-row { display: flex; justify-content: space-between; align-items: center; }
  .item-title { font-size: 13px; font-weight: 700; }
  .item-sub { font-size: 12px; color: var(--text-medium); margin-top: 2px; }
  .badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px; }
  #empty-bookings { text-align: center; padding: 24px; color: var(--text-light); font-size: 13px; }

  .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: var(--text-dark); color: white; padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: 600; z-index: 60; display: none; }
  .toast.visible { display: block; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <a class="back-link" href="/pro/clients">Retour aux clients</a>
  </div>

  <div class="container">
    <div id="loading">Chargement de la fiche client...</div>
    <div id="content" style="display:none">
      <div class="card">
        <h2>Informations</h2>
        <div class="row2">
          <div class="field"><label>Prenom</label><input type="text" id="f-first-name"></div>
          <div class="field"><label>Nom</label><input type="text" id="f-last-name"></div>
        </div>
        <div class="field"><label>Telephone</label><input type="tel" id="f-phone"></div>
        <div class="field"><label>Email</label><input type="email" id="f-email"></div>
        <div class="actions-row">
          <button class="btn btn-primary" id="save-btn" style="flex:1">Enregistrer</button>
          <button class="btn btn-danger" id="delete-btn">Supprimer</button>
        </div>
      </div>

      <h2 style="font-size:15px; font-weight:800; margin-bottom:10px">Rendez-vous</h2>
      <div id="bookings-list"></div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script type="module">
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import { loadManualClientDetail, updateManualClient, deleteManualClient } from '/pro/js/clients.js';

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }
    function showToast(msg) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.classList.add('visible');
      setTimeout(() => t.classList.remove('visible'), 3000);
    }

    const STATUS_LABELS = { pending: 'En attente', confirmed: 'Confirme', completed: 'Termine', cancelled: 'Annule', no_show: 'No-show' };
    const STATUS_COLORS = { pending: 'var(--text-medium)', confirmed: '#38A169', completed: 'var(--text-medium)', cancelled: '#E53E3E', no_show: 'var(--text-light)' };

    let clientId = null;

    (async () => {
      const session = await requireAuth();
      if (!session) return;
      const business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('loading').textContent = 'Aucun etablissement associe a ce compte.';
        return;
      }

      const params = new URLSearchParams(window.location.search);
      clientId = params.get('id');
      if (!clientId) {
        document.getElementById('loading').textContent = 'Client introuvable.';
        return;
      }

      const detail = await loadManualClientDetail(business.id, clientId);
      if (!detail.client) {
        document.getElementById('loading').textContent = 'Client introuvable.';
        return;
      }

      document.getElementById('f-first-name').value = detail.client.first_name || '';
      document.getElementById('f-last-name').value = detail.client.last_name || '';
      document.getElementById('f-phone').value = detail.client.phone || '';
      document.getElementById('f-email').value = detail.client.email || '';

      const bookingsList = document.getElementById('bookings-list');
      if (detail.bookings.length === 0) {
        bookingsList.innerHTML = '<div id="empty-bookings">Aucun rendez-vous enregistre pour ce client.</div>';
      } else {
        bookingsList.innerHTML = detail.bookings.map((b) => {
          const svc = b.services;
          const color = STATUS_COLORS[b.status] || 'var(--text-medium)';
          const label = STATUS_LABELS[b.status] || b.status;
          return '<div class="item-card"><div class="item-row"><div>' +
            '<div class="item-title">' + escapeHtml(svc ? svc.name : 'Rendez-vous') + '</div>' +
            '<div class="item-sub">' + escapeHtml(b.booking_date) + ' a ' + escapeHtml((b.start_time || '').substring(0, 5)) + '</div></div>' +
            '<span class="badge" style="background:' + color + '1A; color:' + color + '">' + escapeHtml(label) + '</span></div></div>';
        }).join('');
      }

      document.getElementById('loading').style.display = 'none';
      document.getElementById('content').style.display = 'block';
    })();

    document.getElementById('save-btn').addEventListener('click', async () => {
      const firstName = document.getElementById('f-first-name').value.trim();
      if (!firstName) { showToast('Le prenom est requis.'); return; }
      const btn = document.getElementById('save-btn');
      btn.disabled = true;
      btn.textContent = 'Enregistrement...';
      try {
        await updateManualClient(clientId, {
          firstName: firstName,
          lastName: document.getElementById('f-last-name').value.trim(),
          phone: document.getElementById('f-phone').value.trim(),
          email: document.getElementById('f-email').value.trim(),
        });
        showToast('Fiche enregistree.');
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de l\'enregistrement.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Enregistrer';
      }
    });

    document.getElementById('delete-btn').addEventListener('click', async () => {
      if (!confirm('Supprimer ce client ? Toutes les notes associees seront supprimees.')) return;
      try {
        await deleteManualClient(clientId);
        window.location.href = '/pro/clients';
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de la suppression.');
      }
    });
  </script>
</body>
</html>
