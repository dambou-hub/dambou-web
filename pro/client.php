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
    --text-light: #A0AEC0; --background: #F7F8FA; --card-border: #E2E8F0;
    --success: #38A169; --warning: #DD6B20; --error: #E53E3E; --gold: #B7891E;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 700px; margin: 0 auto; padding: 24px 24px 60px; }
  #loading { text-align: center; padding: 60px 20px; color: var(--text-medium); }

  .client-head { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
  .client-avatar { width: 52px; height: 52px; border-radius: 50%; background: rgba(0,191,165,0.15); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; flex-shrink: 0; }
  .client-name { font-size: 19px; font-weight: 800; }
  .client-contact { font-size: 13px; color: var(--text-medium); }

  .tabs { display: flex; gap: 4px; background: white; border: 1px solid var(--card-border); border-radius: 12px; padding: 4px; margin-bottom: 18px; }
  .tab-btn { flex: 1; padding: 10px; border: none; background: none; font-family: inherit; font-size: 12px; font-weight: 700; color: var(--text-medium); border-radius: 8px; cursor: pointer; }
  .tab-btn.active { background: var(--primary); color: white; }
  .tab-panel { display: none; }
  .tab-panel.active { display: block; }

  .item-card { background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; }
  .item-row { display: flex; justify-content: space-between; align-items: center; }
  .item-title { font-size: 13px; font-weight: 700; }
  .item-sub { font-size: 12px; color: var(--text-medium); margin-top: 2px; }
  .badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px; }
  #empty-active, #empty-history { text-align: center; padding: 30px; color: var(--text-light); font-size: 13px; }

  .loyalty-card { background: linear-gradient(180deg, var(--primary), var(--primary-dark)); border-radius: 18px; padding: 22px; color: white; text-align: center; margin-bottom: 16px; }
  .loyalty-points { font-size: 38px; font-weight: 900; }
  .loyalty-sub { font-size: 12px; opacity: 0.85; }
  .loyalty-tx-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--card-border); font-size: 12px; }
  .loyalty-tx-row .pts { font-weight: 800; }
  .loyalty-tx-row .pts.earn { color: var(--success); }
  .loyalty-tx-row .pts.redeem { color: var(--error); }
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
      <div class="client-head">
        <div class="client-avatar" id="c-avatar"></div>
        <div style="flex:1">
          <div class="client-name" id="c-name"></div>
          <div class="client-contact" id="c-contact"></div>
        </div>
        <button id="notes-btn" style="display:none; align-items:center; gap:6px; background:rgba(0,191,165,0.1); color:var(--primary-dark); border:none; border-radius:20px; padding:8px 14px; font-size:12px; font-weight:700; font-family:inherit; cursor:pointer">&#128221; Notes</button>
      </div>

      <div class="tabs">
        <button class="tab-btn active" data-tab="active">En cours</button>
        <button class="tab-btn" data-tab="history">Historique</button>
        <button class="tab-btn" data-tab="loyalty">&#11088; Fidelite</button>
      </div>

      <div class="tab-panel active" id="tab-active"></div>
      <div class="tab-panel" id="tab-history"></div>
      <div class="tab-panel" id="tab-loyalty"></div>
    </div>
  </div>

  <!-- Notes de seance -->
  <div class="overlay" id="notes-overlay" style="position:fixed; inset:0; background:rgba(45,55,72,0.35); display:none; align-items:center; justify-content:center; z-index:50; padding:20px">
    <div style="background:white; border-radius:18px; width:100%; max-width:440px; padding:20px; max-height:85vh; overflow-y:auto">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px">
        <h2 style="font-size:16px; font-weight:800">Notes de seance</h2>
        <button id="notes-close" style="border:none; background:none; font-size:20px; color:var(--text-light); cursor:pointer">&times;</button>
      </div>
      <div id="notes-list"></div>
      <div id="note-form" style="display:none; margin-top:14px; border-top:1px solid var(--card-border); padding-top:14px">
        <input type="text" id="note-title" placeholder="Titre" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:8px">
        <textarea id="note-content" placeholder="Contenu" rows="4" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:8px; resize:vertical"></textarea>
        <div style="display:flex; gap:8px">
          <button id="note-cancel-btn" style="flex:1; padding:10px; border:1px solid var(--card-border); background:white; border-radius:10px; font-size:13px; font-weight:700; font-family:inherit; cursor:pointer">Annuler</button>
          <button id="note-save-btn" style="flex:1; padding:10px; border:none; background:var(--primary); color:white; border-radius:10px; font-size:13px; font-weight:700; font-family:inherit; cursor:pointer">Enregistrer</button>
        </div>
      </div>
      <button id="note-add-btn" style="width:100%; margin-top:12px; padding:11px; border:1.5px dashed var(--card-border); background:none; border-radius:12px; font-size:13px; font-weight:700; color:var(--text-medium); font-family:inherit; cursor:pointer">+ Nouvelle note</button>
    </div>
  </div>

  <div class="toast" id="toast" style="position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:var(--text-dark); color:white; padding:12px 20px; border-radius:12px; font-size:13px; font-weight:600; z-index:60; display:none"></div>

  <script type="module">
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import {
      loadDambouClientDetail, isModuleEnabled, loadSessionNotes, saveSessionNote, deleteSessionNote, useSubscriptionSession,
    } from '/pro/js/clients.js';

    function escapeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }
    function showToast(msg) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.style.display = 'block';
      setTimeout(() => { t.style.display = 'none'; }, 3000);
    }
    function currencySymbol(business) {
      return { EUR: '\u20ac', MAD: 'DH', CHF: 'CHF', XOF: 'FCFA' }[(business && business.currency_code) || 'EUR'] || '\u20ac';
    }

    const STATUS_LABELS = {
      pending: 'En attente', confirmed: 'Confirme', completed: 'Termine',
      cancelled: 'Annule', no_show: 'No-show', preparing: 'En preparation', ready: 'Pret',
    };
    const STATUS_COLORS = {
      pending: 'var(--warning)', confirmed: 'var(--success)', completed: 'var(--text-medium)',
      cancelled: 'var(--error)', no_show: 'var(--text-light)', preparing: 'var(--primary)', ready: 'var(--primary-dark)',
    };
    function statusBadge(status) {
      const label = STATUS_LABELS[status] || status;
      const color = STATUS_COLORS[status] || 'var(--text-medium)';
      return '<span class="badge" style="background:' + color + '1A; color:' + color + '">' + escapeHtml(label) + '</span>';
    }

    (async () => {
      const session = await requireAuth();
      if (!session) return;
      const business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('loading').textContent = 'Aucun etablissement associe a ce compte.';
        return;
      }

      const params = new URLSearchParams(window.location.search);
      const customerId = params.get('id');
      if (!customerId) {
        document.getElementById('loading').textContent = 'Client introuvable.';
        return;
      }

      const detail = await loadDambouClientDetail(business.id, customerId);
      if (!detail.client) {
        document.getElementById('loading').textContent = 'Client introuvable.';
        return;
      }

      const name = ((detail.client.first_name || '') + ' ' + (detail.client.last_name || '')).trim() || 'Client';
      document.getElementById('c-avatar').textContent = name.charAt(0).toUpperCase();
      document.getElementById('c-name').textContent = name;
      document.getElementById('c-contact').textContent = [detail.client.phone, detail.client.email].filter(Boolean).join(' - ');

      // ----- Onglet En cours -----
      const activeItems = [];
      detail.activeBookings.forEach((b) => {
        const svc = b.services;
        activeItems.push('<div class="item-card"><div class="item-row">' +
          '<div><div class="item-title">' + escapeHtml(svc ? svc.name : 'Rendez-vous') + '</div>' +
          '<div class="item-sub">' + escapeHtml(b.booking_date) + ' a ' + escapeHtml((b.start_time || '').substring(0, 5)) + '</div></div>' +
          statusBadge(b.status) + '</div></div>');
      });
      detail.activeOrders.forEach((o) => {
        const items = (o.order_items || []).map((it) => (it.quantity || 1) + 'x ' + ((it.products && it.products.name) || '')).join(', ');
        activeItems.push('<div class="item-card"><div class="item-row">' +
          '<div><div class="item-title">Commande - ' + Math.round(o.total || 0) + ' ' + currencySymbol(business) + '</div>' +
          '<div class="item-sub">' + escapeHtml(items) + '</div></div>' + statusBadge(o.status) + '</div></div>');
      });
      document.getElementById('tab-active').innerHTML = activeItems.length ? activeItems.join('') : '<div id="empty-active">Rien en cours actuellement.</div>';

      // ----- Forfaits actifs (au-dessus de la liste "en cours") -----
      if (detail.activeForfaits.length) {
        const forfaitsHtml = detail.activeForfaits.map((sub) => {
          const plan = sub.subscription_plans;
          const remaining = sub.sessions_remaining || 0;
          const total = sub.sessions_total || 0;
          const progress = total > 0 ? (remaining / total) * 100 : 0;
          const color = remaining <= 1 ? 'var(--warning)' : 'var(--success)';
          return '<div class="item-card">' +
            '<div class="item-row"><div style="display:flex; align-items:center; gap:8px; flex:1"><span>&#127905;</span>' +
            '<span class="item-title">' + escapeHtml((plan && plan.name) || 'Forfait') + '</span></div>' +
            '<span style="font-size:18px; font-weight:900; color:' + color + '">' + remaining + ' / ' + total + '</span></div>' +
            '<div style="height:6px; background:var(--background); border-radius:4px; margin:8px 0; overflow:hidden">' +
            '<div style="height:100%; width:' + progress + '%; background:' + color + '"></div></div>' +
            '<button class="use-session-btn" data-id="' + sub.id + '" ' + (remaining <= 0 ? 'disabled' : '') +
            ' style="width:100%; padding:9px; border:none; border-radius:10px; background:' + (remaining > 0 ? 'var(--primary)' : 'var(--card-border)') + '; color:white; font-size:12px; font-weight:700; font-family:inherit; cursor:' + (remaining > 0 ? 'pointer' : 'not-allowed') + '">' +
            (remaining > 0 ? 'Utiliser une seance' : 'Forfait epuise') + '</button></div>';
        }).join('');
        document.getElementById('tab-active').innerHTML = forfaitsHtml + document.getElementById('tab-active').innerHTML;

        document.querySelectorAll('.use-session-btn').forEach((btn) => {
          btn.addEventListener('click', async () => {
            const sub = detail.activeForfaits.find((s) => s.id === btn.dataset.id);
            if (!sub || !confirm('Confirmer l\'utilisation d\'une seance pour ce forfait ?')) return;
            try {
              const remaining = await useSubscriptionSession(sub);
              showToast(remaining <= 0 ? 'Derniere seance utilisee, forfait epuise.' : 'Seance utilisee, reste ' + remaining + '.');
              window.location.reload();
            } catch (err) {
              console.error(err);
              showToast('Erreur lors de la mise a jour du forfait.');
            }
          });
        });
      }

      // ----- Onglet Historique -----
      const historyItems = detail.history.map((h) => {
        if (h._type === 'booking') {
          const svc = h.services;
          const paid = h.is_paid ? '<span class="badge" style="background:rgba(56,161,105,0.1); color:var(--success)">Paye</span>' : '';
          return '<div class="item-card"><div class="item-row"><div><div class="item-title">' + escapeHtml(svc ? svc.name : 'Rendez-vous') + '</div>' +
            '<div class="item-sub">' + escapeHtml(h.booking_date) + '</div></div><div style="display:flex; gap:6px">' + statusBadge(h.status) + paid + '</div></div></div>';
        }
        if (h._type === 'order') {
          const items = (h.order_items || []).map((it) => (it.quantity || 1) + 'x ' + ((it.products && it.products.name) || '')).join(', ');
          return '<div class="item-card"><div class="item-row"><div><div class="item-title">Commande - ' + Math.round(h.total || 0) + ' ' + currencySymbol(business) + '</div>' +
            '<div class="item-sub">' + escapeHtml(items) + '</div></div>' + statusBadge(h.status) + '</div></div>';
        }
        // transaction (caisse)
        const itemNames = (h.items || []).map((it) => it.name).join(', ');
        return '<div class="item-card"><div class="item-row"><div><div class="item-title">Achat en caisse - ' + Math.round(h.total || 0) + ' ' + currencySymbol(business) + '</div>' +
          '<div class="item-sub">' + escapeHtml(itemNames) + '</div></div>' +
          '<span class="badge" style="background:rgba(0,191,165,0.1); color:var(--primary-dark)">' + escapeHtml(h.payment_method || '') + '</span></div></div>';
      });
      document.getElementById('tab-history').innerHTML = historyItems.length ? historyItems.join('') : '<div id="empty-history">Aucun historique pour ce client.</div>';

      // ----- Onglet Fidelite -----
      let loyaltyHtml = '';
      if (detail.loyaltyCard) {
        loyaltyHtml += '<div class="loyalty-card"><div class="loyalty-points">' + (detail.loyaltyCard.points || 0) + '</div><div class="loyalty-sub">points</div></div>';
        if (detail.loyaltyHistory.length) {
          loyaltyHtml += detail.loyaltyHistory.map((tx) => {
            const isEarn = tx.type === 'earn';
            return '<div class="loyalty-tx-row"><span>' + escapeHtml(tx.description || (isEarn ? 'Gain' : 'Utilisation')) + '</span>' +
              '<span class="pts ' + (isEarn ? 'earn' : 'redeem') + '">' + (isEarn ? '+' : '') + tx.points + ' pts</span></div>';
          }).join('');
        } else {
          loyaltyHtml += '<div id="empty-history">Aucun mouvement fidelite.</div>';
        }
      } else {
        loyaltyHtml = '<div id="empty-history">Ce client n\'a pas encore de carte fidelite chez vous.</div>';
      }
      document.getElementById('tab-loyalty').innerHTML = loyaltyHtml;

      document.querySelectorAll('.tab-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
          document.querySelectorAll('.tab-btn').forEach((b) => b.classList.remove('active'));
          document.querySelectorAll('.tab-panel').forEach((p) => p.classList.remove('active'));
          btn.classList.add('active');
          document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
      });

      document.getElementById('loading').style.display = 'none';
      document.getElementById('content').style.display = 'block';

      // ----- Notes de seance (module optionnel) -----
      const hasSessionNotes = await isModuleEnabled(business.id, 'session_notes');
      if (hasSessionNotes) {
        document.getElementById('notes-btn').style.display = 'flex';
        setupNotesModal(business.id, { customerId: customerId, manualClientId: null });
      }
    })();

    function setupNotesModal(businessId, ids) {
      let editingNoteId = null;

      async function refreshNotes() {
        const notes = await loadSessionNotes(businessId, ids);
        const list = document.getElementById('notes-list');
        if (notes.length === 0) {
          list.innerHTML = '<div style="text-align:center; padding:20px; color:var(--text-light); font-size:13px">Aucune note pour ce client.</div>';
        } else {
          list.innerHTML = notes.map((n) => {
            const booking = n.bookings;
            const bookingInfo = booking ? escapeHtml(booking.booking_date) + (booking.services ? ' - ' + escapeHtml(booking.services.name) : '') : '';
            return '<div class="item-card">' +
              '<div class="item-row"><span class="item-title">' + escapeHtml(n.title) + '</span>' +
              '<div><button class="note-edit-btn" data-id="' + n.id + '" style="border:none; background:none; cursor:pointer; font-size:13px">&#9998;</button>' +
              '<button class="note-delete-btn" data-id="' + n.id + '" style="border:none; background:none; cursor:pointer; font-size:13px; color:var(--error)">&#128465;</button></div></div>' +
              (n.content ? '<div class="item-sub" style="margin-top:6px; white-space:pre-wrap">' + escapeHtml(n.content) + '</div>' : '') +
              (bookingInfo ? '<div class="item-sub" style="margin-top:6px; color:var(--text-light)">' + bookingInfo + '</div>' : '') +
              '</div>';
          }).join('');

          list.querySelectorAll('.note-edit-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
              const note = notes.find((n) => n.id === btn.dataset.id);
              editingNoteId = note.id;
              document.getElementById('note-title').value = note.title || '';
              document.getElementById('note-content').value = note.content || '';
              document.getElementById('note-form').style.display = 'block';
            });
          });
          list.querySelectorAll('.note-delete-btn').forEach((btn) => {
            btn.addEventListener('click', async () => {
              if (!confirm('Supprimer cette note ?')) return;
              await deleteSessionNote(btn.dataset.id);
              await refreshNotes();
            });
          });
        }
      }

      document.getElementById('notes-btn').addEventListener('click', async () => {
        document.getElementById('note-form').style.display = 'none';
        document.getElementById('notes-overlay').style.display = 'flex';
        await refreshNotes();
      });
      document.getElementById('notes-close').addEventListener('click', () => {
        document.getElementById('notes-overlay').style.display = 'none';
      });
      document.getElementById('note-add-btn').addEventListener('click', () => {
        editingNoteId = null;
        document.getElementById('note-title').value = '';
        document.getElementById('note-content').value = '';
        document.getElementById('note-form').style.display = 'block';
      });
      document.getElementById('note-cancel-btn').addEventListener('click', () => {
        document.getElementById('note-form').style.display = 'none';
      });
      document.getElementById('note-save-btn').addEventListener('click', async () => {
        const title = document.getElementById('note-title').value.trim();
        if (!title) return;
        try {
          await saveSessionNote({
            businessId: businessId, customerId: ids.customerId, manualClientId: ids.manualClientId,
            noteId: editingNoteId, title: title, content: document.getElementById('note-content').value.trim(),
          });
          document.getElementById('note-form').style.display = 'none';
          showToast('Note enregistree.');
          await refreshNotes();
        } catch (err) {
          console.error(err);
          showToast('Erreur lors de l\'enregistrement.');
        }
      });
    }
  </script>
</body>
</html>
