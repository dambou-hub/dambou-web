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
<title>Employ&eacute;s - Dambou Pro</title>
<meta name="theme-color" content="#00BFA5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #00BFA5; --primary-dark: #00897B; --text-dark: #2D3748; --text-medium: #718096;
    --text-light: #A0AEC0; --background: #F7F8FA; --card-border: #E2E8F0;
    --success: #38A169; --warning: #DD6B20; --error: #E53E3E;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 640px; margin: 0 auto; padding: 24px 24px 60px; }
  .page-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
  h1 { font-size: 22px; font-weight: 800; }
  .btn { padding: 11px 18px; border-radius: 12px; border: none; font-size: 13px; font-weight: 700; font-family: inherit; cursor: pointer; }
  .btn-primary { background: var(--primary); color: white; }

  #loading, #empty-state { text-align: center; padding: 60px 20px; color: var(--text-medium); }

  .emp-card { background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 12px 14px; margin-bottom: 10px; display: flex; align-items: center; gap: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); }
  .emp-card.inactive { border-color: var(--card-border); opacity: 0.6; }
  .emp-avatar-wrap { position: relative; flex-shrink: 0; }
  .emp-avatar { width: 46px; height: 46px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; border-width: 2px; border-style: solid; }
  .emp-status-dot { position: absolute; bottom: 0; right: 0; width: 13px; height: 13px; border-radius: 50%; border: 2px solid white; }
  .emp-info { flex: 1; min-width: 0; }
  .emp-name-row { display: flex; align-items: center; gap: 8px; }
  .emp-name { font-size: 14px; font-weight: 800; }
  .emp-inactive-badge { font-size: 10px; font-weight: 700; color: var(--text-light); background: rgba(160,174,192,0.2); padding: 2px 7px; border-radius: 6px; }
  .emp-contact { font-size: 12px; color: var(--text-medium); display: flex; align-items: center; gap: 5px; margin-top: 2px; }
  .emp-menu-btn { border: none; background: none; color: var(--text-medium); font-size: 18px; cursor: pointer; padding: 6px; flex-shrink: 0; }
  .emp-menu { position: relative; }
  .emp-dropdown { position: absolute; right: 0; top: 100%; background: white; border: 1px solid var(--card-border); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); min-width: 160px; z-index: 20; display: none; overflow: hidden; }
  .emp-dropdown.visible { display: block; }
  .emp-dropdown button { width: 100%; text-align: left; padding: 11px 14px; border: none; background: none; font-family: inherit; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; }
  .emp-dropdown button:hover { background: var(--background); }
  .emp-dropdown button.danger { color: var(--error); }

  /* Modal */
  .overlay { position: fixed; inset: 0; background: rgba(45,55,72,0.35); display: none; align-items: center; justify-content: center; z-index: 50; padding: 20px; }
  .overlay.visible { display: flex; }
  .panel { background: white; border-radius: 18px; width: 100%; max-width: 420px; padding: 22px; max-height: 88vh; overflow-y: auto; }
  .panel-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
  .panel-title { font-size: 16px; font-weight: 800; }
  .nav-btn { width: 32px; height: 32px; border-radius: 10px; border: none; background: none; font-size: 20px; color: var(--text-light); cursor: pointer; }
  .field { margin-bottom: 14px; }
  .field label { display: block; font-size: 12px; font-weight: 700; color: var(--text-medium); margin-bottom: 5px; }
  .field input { width: 100%; padding: 11px 12px; border: 1px solid var(--card-border); border-radius: 12px; font-size: 14px; font-family: inherit; }
  .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

  .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 4px 0 14px; }
  .toggle-row-label { font-size: 13px; font-weight: 700; }
  .toggle-row-sub { font-size: 11px; color: var(--text-light); margin-top: 2px; max-width: 260px; }
  .toggle { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
  .toggle input { opacity: 0; width: 0; height: 0; }
  .toggle-slider { position: absolute; inset: 0; background: var(--card-border); border-radius: 24px; cursor: pointer; transition: background 0.15s; }
  .toggle-slider::before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: white; border-radius: 50%; transition: transform 0.15s; }
  .toggle input:checked + .toggle-slider { background: var(--primary); }
  .toggle input:checked + .toggle-slider::before { transform: translateX(20px); }

  .section-label { font-size: 12px; font-weight: 700; color: var(--text-medium); margin-bottom: 8px; }
  .color-picker { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; }
  .color-dot { width: 34px; height: 34px; border-radius: 50%; border: 3px solid transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; color: white; font-size: 14px; }
  .color-dot.selected { border-color: var(--text-dark); }

  .invite-box { border-radius: 12px; padding: 12px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 8px; }
  .invite-box.accepted { background: rgba(56,161,105,0.1); }
  .invite-box.pending { background: rgba(221,107,32,0.1); }
  .invite-box-title { font-size: 12px; font-weight: 700; }
  .invite-box-email { font-size: 11px; color: var(--text-medium); margin-top: 2px; }
  .invite-box button { border: none; background: none; font-size: 11px; font-weight: 700; color: var(--text-medium); cursor: pointer; text-decoration: underline; }
  .invite-row { display: flex; gap: 8px; margin-bottom: 16px; }
  .invite-row input { flex: 1; padding: 11px 12px; border: 1px solid var(--card-border); border-radius: 12px; font-size: 13px; font-family: inherit; }
  .invite-row button { padding: 0 16px; border-radius: 12px; border: none; background: var(--primary); color: white; font-size: 13px; font-weight: 700; font-family: inherit; cursor: pointer; white-space: nowrap; }

  .error-msg { display: none; background: rgba(229,62,62,0.08); color: var(--error); font-size: 13px; padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; }
  .error-msg.visible { display: block; }
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
    <div class="page-head">
      <h1>Employ&eacute;s</h1>
      <button class="btn btn-primary" id="add-employee-btn">+ Ajouter</button>
    </div>

    <div id="loading">Chargement...</div>
    <div id="list-container" style="display:none"></div>
  </div>

  <!-- Formulaire employe -->
  <div class="overlay" id="employee-overlay">
    <div class="panel">
      <div class="panel-head">
        <span class="panel-title" id="employee-modal-title">Nouvel employ&eacute;</span>
        <button class="nav-btn" id="employee-close">&times;</button>
      </div>

      <div class="error-msg" id="employee-error"></div>

      <div class="row2">
        <div class="field"><label>Pr&eacute;nom *</label><input type="text" id="f-first-name"></div>
        <div class="field"><label>Nom</label><input type="text" id="f-last-name"></div>
      </div>
      <div class="field"><label>T&eacute;l&eacute;phone</label><input type="tel" id="f-phone"></div>
      <div class="field"><label>Email</label><input type="email" id="f-email"></div>

      <div class="toggle-row">
        <div><div class="toggle-row-label">Voit tout le planning</div>
          <div class="toggle-row-sub">Si d&eacute;sactiv&eacute;, l'employ&eacute; ne verra que ses propres rendez-vous une fois son compte li&eacute;.</div></div>
        <label class="toggle"><input type="checkbox" id="f-see-all"><span class="toggle-slider"></span></label>
      </div>

      <div id="invite-section" style="display:none">
        <div class="section-label">Lier un compte Dambou</div>
        <div id="invite-status-accepted" class="invite-box accepted" style="display:none">
          <span>&#9989;</span>
          <div style="flex:1">
            <div class="invite-box-title" style="color:var(--success)">Compte Dambou li&eacute;</div>
            <div class="invite-box-email" id="invite-email-accepted"></div>
          </div>
          <button type="button" id="invite-reset-accepted">Modifier</button>
        </div>
        <div id="invite-status-pending" class="invite-box pending" style="display:none">
          <span>&#9203;</span>
          <div style="flex:1">
            <div class="invite-box-title" style="color:var(--warning)">Invitation envoy&eacute;e</div>
            <div class="invite-box-email" id="invite-email-pending"></div>
          </div>
          <button type="button" id="invite-reset-pending">Modifier</button>
        </div>
        <div class="invite-row" id="invite-row">
          <input type="email" id="invite-email-input" placeholder="Email Dambou de l'employ&eacute;(e)">
          <button type="button" id="invite-send-btn">Inviter</button>
        </div>
      </div>

      <div class="section-label">Couleur dans l'agenda</div>
      <div class="color-picker" id="color-picker"></div>

      <button class="btn btn-primary" id="employee-save-btn" style="width:100%">Enregistrer</button>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script type="module">
    import { requireAuth, getBusinessForUser, fr } from '/pro/js/auth.js';
    import { EMPLOYEE_COLORS, loadEmployees, saveEmployee, toggleEmployeeActive, deleteEmployee, inviteEmployee } from '/pro/js/employees.js';

    let business = null;
    let employees = [];
    let editingEmployeeId = null;
    let selectedColor = EMPLOYEE_COLORS[0];
    let invitationStatus = 'none'; // 'none' | 'pending' | 'accepted'

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }
    function showToast(msg) {
      const t = document.getElementById('toast');
      t.textContent = fr(msg);
      t.classList.add('visible');
      setTimeout(() => t.classList.remove('visible'), 3000);
    }

    function renderList() {
      const container = document.getElementById('list-container');
      if (employees.length === 0) {
        container.innerHTML = '<div id="empty-state">Aucun employ&eacute; pour le moment.</div>';
        return;
      }
      container.innerHTML = '';
      employees.forEach((emp) => {
        const name = ((emp.first_name || '') + ' ' + (emp.last_name || '')).trim() || 'Sans nom';
        const color = emp.color || '#00BFA5';
        const isActive = emp.is_active !== false;
        const card = document.createElement('div');
        card.className = 'emp-card' + (isActive ? '' : ' inactive');
        card.innerHTML =
          '<div class="emp-avatar-wrap">' +
          '<div class="emp-avatar" style="background:' + color + '1F; border-color:' + color + '; color:' + color + '">' +
          escapeHtml((emp.first_name || '?').charAt(0).toUpperCase()) + '</div>' +
          '<div class="emp-status-dot" style="background:' + (isActive ? 'var(--success)' : 'var(--text-light)') + '"></div>' +
          '</div>' +
          '<div class="emp-info">' +
          '<div class="emp-name-row"><span class="emp-name">' + escapeHtml(name) + '</span>' +
          (isActive ? '' : '<span class="emp-inactive-badge">Inactif</span>') + '</div>' +
          (emp.phone ? '<div class="emp-contact">&#128222; ' + escapeHtml(emp.phone) + '</div>' : '') +
          (emp.email ? '<div class="emp-contact">&#9993; ' + escapeHtml(emp.email) + '</div>' : '') +
          '</div>' +
          '<div class="emp-menu">' +
          '<button class="emp-menu-btn" data-menu-toggle="' + emp.id + '">&#8942;</button>' +
          '<div class="emp-dropdown" id="menu-' + emp.id + '">' +
          '<button data-action="edit" data-id="' + emp.id + '">&#9998; Modifier</button>' +
          '<button data-action="toggle" data-id="' + emp.id + '">' + (isActive ? '&#9208; D&eacute;sactiver' : '&#9654; Activer') + '</button>' +
          '<button class="danger" data-action="delete" data-id="' + emp.id + '">&#128465; Supprimer</button>' +
          '</div></div>';
        container.appendChild(card);
      });

      container.querySelectorAll('[data-menu-toggle]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const id = btn.dataset.menuToggle;
          document.querySelectorAll('.emp-dropdown').forEach((d) => {
            if (d.id !== 'menu-' + id) d.classList.remove('visible');
          });
          document.getElementById('menu-' + id).classList.toggle('visible');
        });
      });
      container.querySelectorAll('[data-action]').forEach((btn) => {
        btn.addEventListener('click', async (e) => {
          e.stopPropagation();
          document.querySelectorAll('.emp-dropdown').forEach((d) => d.classList.remove('visible'));
          const id = btn.dataset.id;
          const emp = employees.find((x) => x.id === id);
          if (btn.dataset.action === 'edit') {
            openEmployeeModal(emp);
          } else if (btn.dataset.action === 'toggle') {
            try {
              await toggleEmployeeActive(id, emp.is_active !== false);
              showToast(emp.is_active !== false ? 'Employ&eacute; d&eacute;sactiv&eacute;.' : 'Employ&eacute; activ&eacute;.');
              await refresh();
            } catch (err) { console.error(err); showToast('Erreur.'); }
          } else if (btn.dataset.action === 'delete') {
            const name = ((emp.first_name || '') + ' ' + (emp.last_name || '')).trim();
            if (!confirm(fr('Supprimer ' + name + ' ? Cette action est irr&eacute;versible.'))) return;
            try {
              await deleteEmployee(id);
              showToast('Employ&eacute; supprim&eacute;.');
              await refresh();
            } catch (err) { console.error(err); showToast('Erreur lors de la suppression.'); }
          }
        });
      });
    }
    document.addEventListener('click', () => {
      document.querySelectorAll('.emp-dropdown').forEach((d) => d.classList.remove('visible'));
    });

    function renderColorPicker() {
      const wrap = document.getElementById('color-picker');
      wrap.innerHTML = '';
      EMPLOYEE_COLORS.forEach((hex) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'color-dot' + (selectedColor === hex ? ' selected' : '');
        dot.style.background = hex;
        dot.innerHTML = selectedColor === hex ? '&#10003;' : '';
        dot.addEventListener('click', () => { selectedColor = hex; renderColorPicker(); });
        wrap.appendChild(dot);
      });
    }

    function renderInviteStatus() {
      document.getElementById('invite-status-accepted').style.display = invitationStatus === 'accepted' ? 'flex' : 'none';
      document.getElementById('invite-status-pending').style.display = invitationStatus === 'pending' ? 'flex' : 'none';
      document.getElementById('invite-row').style.display = invitationStatus === 'none' ? 'flex' : 'none';
    }

    function openEmployeeModal(emp) {
      editingEmployeeId = emp ? emp.id : null;
      document.getElementById('employee-error').classList.remove('visible');
      document.getElementById('employee-modal-title').textContent = fr(emp ? "Modifier l'employ&eacute;" : 'Nouvel employ&eacute;');
      document.getElementById('f-first-name').value = emp ? (emp.first_name || '') : '';
      document.getElementById('f-last-name').value = emp ? (emp.last_name || '') : '';
      document.getElementById('f-phone').value = emp ? (emp.phone || '') : '';
      document.getElementById('f-email').value = emp ? (emp.email || '') : '';
      document.getElementById('f-see-all').checked = emp ? !!emp.can_see_all_planning : false;
      selectedColor = emp ? (emp.color || EMPLOYEE_COLORS[0]) : EMPLOYEE_COLORS[employees.length % EMPLOYEE_COLORS.length];
      renderColorPicker();

      document.getElementById('invite-section').style.display = emp ? 'block' : 'none';
      invitationStatus = emp ? (emp.invitation_status || 'none') : 'none';
      document.getElementById('invite-email-input').value = emp ? (emp.invited_email || '') : '';
      document.getElementById('invite-email-accepted').textContent = (emp && (emp.invited_email || emp.email)) || '';
      document.getElementById('invite-email-pending').textContent = (emp && emp.invited_email) || '';
      renderInviteStatus();

      document.getElementById('employee-overlay').classList.add('visible');
    }
    function closeEmployeeModal() {
      document.getElementById('employee-overlay').classList.remove('visible');
    }

    document.getElementById('add-employee-btn').addEventListener('click', () => openEmployeeModal(null));
    document.getElementById('employee-close').addEventListener('click', closeEmployeeModal);
    document.getElementById('employee-overlay').addEventListener('click', (e) => {
      if (e.target.id === 'employee-overlay') closeEmployeeModal();
    });
    document.getElementById('invite-reset-accepted').addEventListener('click', () => { invitationStatus = 'none'; renderInviteStatus(); });
    document.getElementById('invite-reset-pending').addEventListener('click', () => { invitationStatus = 'none'; renderInviteStatus(); });

    document.getElementById('invite-send-btn').addEventListener('click', async () => {
      const email = document.getElementById('invite-email-input').value.trim();
      if (!email || !editingEmployeeId) return;
      const btn = document.getElementById('invite-send-btn');
      btn.disabled = true;
      try {
        const status = await inviteEmployee(editingEmployeeId, email, business.name || 'Dambou');
        invitationStatus = status === 'linked' ? 'accepted' : 'pending';
        document.getElementById('invite-email-accepted').textContent = email;
        document.getElementById('invite-email-pending').textContent = email;
        renderInviteStatus();
        showToast(status === 'linked' ? 'Compte Dambou trouv&eacute; et li&eacute; !' : 'Invitation envoy&eacute;e par email !');
        await refresh();
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de l\'invitation.');
      } finally {
        btn.disabled = false;
      }
    });

    document.getElementById('employee-save-btn').addEventListener('click', async () => {
      const errorEl = document.getElementById('employee-error');
      const firstName = document.getElementById('f-first-name').value.trim();
      if (!firstName) {
        errorEl.textContent = fr('Le pr&eacute;nom est obligatoire.');
        errorEl.classList.add('visible');
        return;
      }
      errorEl.classList.remove('visible');
      const btn = document.getElementById('employee-save-btn');
      btn.disabled = true;
      btn.textContent = fr('Enregistrement...');
      try {
        await saveEmployee(business.id, editingEmployeeId, {
          firstName: firstName,
          lastName: document.getElementById('f-last-name').value.trim(),
          phone: document.getElementById('f-phone').value.trim(),
          email: document.getElementById('f-email').value.trim(),
          color: selectedColor,
          canSeeAllPlanning: document.getElementById('f-see-all').checked,
        });
        showToast(editingEmployeeId ? 'Employ&eacute; modifi&eacute; !' : 'Employ&eacute; ajout&eacute; !');
        closeEmployeeModal();
        await refresh();
      } catch (err) {
        console.error(err);
        errorEl.textContent = fr('Erreur lors de l\'enregistrement.');
        errorEl.classList.add('visible');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Enregistrer';
      }
    });

    async function refresh() {
      employees = await loadEmployees(business.id);
      renderList();
    }

    (async () => {
      const session = await requireAuth();
      if (!session) return;
      business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('loading').textContent = fr('Aucun &eacute;tablissement associ&eacute; &agrave; ce compte.');
        return;
      }
      await refresh();
      document.getElementById('loading').style.display = 'none';
      document.getElementById('list-container').style.display = 'block';
    })();
  </script>
</body>
</html>
