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
<title>Commandes - Dambou Pro</title>
<meta name="theme-color" content="#00BFA5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #00BFA5; --primary-dark: #00897B; --text-dark: #2D3748; --text-medium: #718096;
    --text-light: #A0AEC0; --background: #F7F8FA; --card-border: #E2E8F0;
    --success: #38A169; --warning: #DD6B20; --error: #E53E3E;
    --c-confirm: #DD6B20; --c-later: #805AD5; --c-soon: #E53E3E; --c-progress: #3182CE;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .top-actions { display: flex; align-items: center; gap: 14px; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }
  .btn-new-order { display: flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 12px; border: none; background: var(--primary); color: white; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }

  .container { max-width: 1500px; margin: 0 auto; padding: 20px 24px 50px; }

  .tabs { display: flex; gap: 4px; background: white; border: 1px solid var(--card-border); border-radius: 12px; padding: 4px; margin-bottom: 20px; overflow-x: auto; min-width: 0; }
  .tab-btn { flex-shrink: 0; display: flex; align-items: center; gap: 6px; padding: 10px 14px; border: none; background: none; font-family: inherit; font-size: 12px; font-weight: 700; color: var(--text-medium); border-radius: 8px; cursor: pointer; white-space: nowrap; }
  .tab-btn.active { background: var(--background); color: var(--text-dark); }
  .tab-count { color: white; font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 10px; min-width: 16px; text-align: center; }

  #loading, #empty-state { text-align: center; padding: 60px 20px; color: var(--text-medium); }

  .kanban { display: flex; gap: 14px; align-items: flex-start; }
  .kanban-col { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 10px; }

  .order-card { background: white; border: 1px solid var(--card-border); border-left: 4px solid var(--primary); border-radius: 14px; padding: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); }
  .oc-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
  .oc-number { font-size: 14px; font-weight: 800; }
  .oc-time { font-size: 11px; color: var(--text-light); }
  .oc-pickup { font-size: 11px; font-weight: 700; color: var(--primary-dark); background: rgba(0,191,165,0.1); padding: 4px 9px; border-radius: 8px; cursor: pointer; white-space: nowrap; }
  .oc-client { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
  .oc-client-name { font-size: 13px; font-weight: 700; }
  .oc-phone { font-size: 11px; color: var(--primary); text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 3px; }
  .oc-badges { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
  .oc-badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px; }
  .oc-items { border-top: 1px solid var(--card-border); padding-top: 8px; margin-top: 4px; }
  .oc-item-row { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 6px; }
  .oc-item-qty { width: 22px; height: 22px; border-radius: 6px; background: rgba(0,191,165,0.1); color: var(--primary-dark); font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
  .oc-item-name { flex: 1; font-size: 12px; font-weight: 700; }
  .oc-item-price { font-size: 11px; font-weight: 700; }
  .oc-item-note { font-size: 10px; color: var(--text-medium); font-style: italic; margin-left: 30px; }
  .oc-item-ing { font-size: 10px; color: var(--text-medium); margin-left: 30px; }
  .oc-total { display: flex; justify-content: space-between; border-top: 1px solid var(--card-border); padding-top: 8px; margin-top: 8px; font-size: 13px; font-weight: 800; }
  .oc-actions { display: flex; gap: 8px; margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--card-border); }
  .oc-actions button, .oc-actions a { flex: 1; padding: 10px; border-radius: 10px; border: none; font-family: inherit; font-size: 12px; font-weight: 700; cursor: pointer; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 4px; }
  .oc-btn-primary { background: var(--primary); color: white; }
  .oc-btn-danger { background: none; color: var(--error); border: 1px solid rgba(229,62,62,0.4) !important; flex: 0 0 auto !important; padding: 10px 12px !important; }
  .oc-btn-outline { background: none; border: 1px solid var(--card-border) !important; color: var(--text-dark); }
  .oc-btn-success { background: var(--success); color: white; }
  .oc-btn-info { background: #3182CE; color: white; }
  .toast.visible { display: block !important; }
  .overlay.visible { display: flex !important; }
  .ing-chip { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 9px 11px; border-radius: 10px; border: 1px solid var(--card-border); }
  .ing-chip-name { flex: 1; font-size: 13px; font-weight: 600; }
  .ing-mode-btn { padding: 5px 10px; border-radius: 8px; border: 1px solid var(--card-border); background: white; font-family: inherit; font-size: 11px; font-weight: 700; cursor: pointer; color: var(--text-medium); }
  .ing-mode-btn.active-free { background: rgba(0,191,165,0.12); border-color: var(--primary); color: var(--primary-dark); }
  .ing-mode-btn.active-paid { background: rgba(221,107,32,0.12); border-color: var(--warning); color: var(--warning); }
  .no-product-tile { border: 1px solid var(--card-border); border-radius: 10px; padding: 8px; cursor: pointer; text-align: center; background: white; }
  .no-product-tile:hover { border-color: var(--primary); }
  .no-product-name { font-size: 11px; font-weight: 700; display: block; margin-bottom: 2px; }
  .no-product-price { font-size: 11px; color: var(--primary-dark); font-weight: 700; }
  .no-cart-line { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--card-border); }
  .no-cart-line-name { flex: 1; font-size: 12px; font-weight: 700; }
  .no-cart-line-note { font-size: 10px; color: var(--text-medium); display: block; margin-top: 2px; font-weight: 400; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <div class="top-actions">
      <a class="back-link" href="/pro">Tableau de bord</a>
      <button class="btn-new-order" id="new-order-btn">+ Nouvelle commande</button>
    </div>
  </div>

  <div class="container">
    <div class="tabs" id="tabs"></div>
    <div id="loading">Chargement des commandes...</div>
    <div id="empty-state" style="display:none">Aucune commande dans cet onglet.</div>
    <div class="kanban" id="kanban" style="display:none"></div>
  </div>

  <div class="toast" id="toast" style="position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:var(--text-dark); color:white; padding:12px 20px; border-radius:12px; font-size:13px; font-weight:600; z-index:60; display:none"></div>

  <!-- Nouvelle commande -->
  <div class="overlay" id="new-order-overlay" style="position:fixed; inset:0; background:rgba(45,55,72,0.4); display:none; align-items:center; justify-content:center; z-index:50; padding:16px">
    <div class="panel" id="new-order-panel" style="background:white; border-radius:18px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; padding:20px">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px">
        <span style="font-size:16px; font-weight:800">Nouvelle commande</span>
        <button id="new-order-close" style="border:none; background:none; font-size:22px; color:var(--text-light); cursor:pointer">&times;</button>
      </div>

      <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:5px">Client (optionnel)</label>
      <input type="text" id="no-client-search" placeholder="Nom, t&eacute;l&eacute;phone..." autocomplete="off"
        style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:6px">
      <div id="no-client-results" style="margin-bottom:8px"></div>
      <input type="text" id="no-client-name" placeholder="Nom du client" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:8px">
      <input type="tel" id="no-client-phone" placeholder="T&eacute;l&eacute;phone (optionnel)" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:14px">

      <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:5px">Heure de retrait (optionnel)</label>
      <input type="time" id="no-pickup-time" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:14px">

      <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:6px">Ajouter un produit</label>
      <div id="no-products-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(110px, 1fr)); gap:8px; max-height:180px; overflow-y:auto; margin-bottom:14px"></div>

      <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:6px">Panier</label>
      <div id="no-cart-lines" style="margin-bottom:10px"></div>
      <div id="no-cart-empty" style="text-align:center; padding:14px; color:var(--text-light); font-size:12px">Aucun article ajout&eacute;.</div>

      <div style="display:flex; justify-content:space-between; align-items:center; padding-top:12px; border-top:1px solid var(--card-border); margin-bottom:14px">
        <span style="font-size:14px; font-weight:700">Total</span>
        <span id="no-total" style="font-size:18px; font-weight:800; color:var(--primary-dark)">0</span>
      </div>

      <div id="no-error" style="display:none; background:rgba(229,62,62,0.08); color:var(--error); font-size:12px; padding:9px 11px; border-radius:8px; margin-bottom:10px"></div>
      <button id="no-submit-btn" style="width:100%; padding:13px; background:var(--primary); color:white; border:none; border-radius:14px; font-size:14px; font-weight:700; font-family:inherit; cursor:pointer">Cr&eacute;er la commande</button>
    </div>
  </div>

  <!-- Personnalisation ingredients d'une ligne -->
  <div class="overlay" id="ing-overlay" style="position:fixed; inset:0; background:rgba(45,55,72,0.4); display:none; align-items:center; justify-content:center; z-index:55; padding:16px">
    <div class="panel" style="background:white; border-radius:18px; width:100%; max-width:420px; max-height:85vh; overflow-y:auto; padding:20px">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px">
        <span id="ing-title" style="font-size:15px; font-weight:800"></span>
        <button id="ing-close" style="border:none; background:none; font-size:22px; color:var(--text-light); cursor:pointer">&times;</button>
      </div>
      <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:8px">Ingr&eacute;dients inclus</label>
      <div id="ing-default-list" style="margin-bottom:16px"></div>
      <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:8px">Ajouter</label>
      <div id="ing-extra-list" style="display:flex; flex-direction:column; gap:6px; margin-bottom:16px"></div>
      <button id="ing-done-btn" style="width:100%; padding:12px; background:var(--primary); color:white; border:none; border-radius:12px; font-size:13px; font-weight:700; font-family:inherit; cursor:pointer">OK</button>
    </div>
  </div>

  <script type="module">
    import { requireAuth, getBusinessForUser, fr } from '/pro/js/auth.js';
    import { clientName, bookingPhone, searchClients, createManualClient } from '/pro/js/planning.js';
    import {
      loadOrders, loadCompletedOrders, categorizeOrders, updateOrderStatus, editPickupTime,
      cancelOrderWithRefund, isPaidOnline, orderNeedsPrep, loadProductsForNewOrder, createManualOrder,
    } from '/pro/js/orders.js';

    let business = null;
    let currentTab = 'toConfirm';
    let categorized = { toConfirm: [], soon: [], later: [], inProgress: [], ready: [], done: [], cancelled: [] };
    let refreshTimer = null;

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
    function currencySymbol() {
      return { EUR: '\u20ac', MAD: 'DH', CHF: 'CHF', XOF: 'FCFA' }[(business && business.currency_code) || 'EUR'] || '\u20ac';
    }
    function fmt(n) { return Math.round(n || 0) + ' ' + currencySymbol(); }
    function timeAgo(iso) {
      if (!iso) return '';
      const diffMin = Math.floor((Date.now() - new Date(iso).getTime()) / 60000);
      if (diffMin < 1) return fr("&Agrave; l'instant");
      if (diffMin < 60) return 'Il y a ' + diffMin + ' min';
      const diffH = Math.floor(diffMin / 60);
      if (diffH < 24) return 'Il y a ' + diffH + 'h';
      const d = new Date(iso);
      return String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + ' ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }

    const TABS = [
      { key: 'toConfirm', label: 'A confirmer', color: 'var(--c-confirm)' },
      { key: 'later', label: 'A venir', color: 'var(--c-later)' },
      { key: 'soon', label: 'A faire', color: 'var(--c-soon)' },
      { key: 'inProgress', label: 'En cours', color: 'var(--c-progress)' },
      { key: 'ready', label: 'Pretes', color: 'var(--success)' },
      { key: 'done', label: 'Terminees', color: 'var(--text-light)' },
      { key: 'cancelled', label: 'Annulees', color: 'var(--error)' },
    ];

    function renderTabs() {
      const wrap = document.getElementById('tabs');
      wrap.innerHTML = '';
      TABS.forEach((t) => {
        const count = categorized[t.key].length;
        const btn = document.createElement('button');
        btn.className = 'tab-btn' + (currentTab === t.key ? ' active' : '');
        btn.innerHTML = escapeHtml(t.label) + (count > 0 ? ' <span class="tab-count" style="background:' + t.color + '">' + count + '</span>' : '');
        btn.addEventListener('click', () => { currentTab = t.key; renderTabs(); renderBoard(); });
        wrap.appendChild(btn);
      });
    }

    async function loadAndRender() {
      const [active, done] = await Promise.all([
        loadOrders(business.id),
        loadCompletedOrders(business.id, 50),
      ]);
      categorized = categorizeOrders(active, done);
      renderTabs();
      renderBoard();
      document.getElementById('loading').style.display = 'none';
    }

    function renderBoard() {
      const list = categorized[currentTab] || [];
      const kanban = document.getElementById('kanban');
      const empty = document.getElementById('empty-state');
      if (list.length === 0) {
        kanban.style.display = 'none';
        empty.style.display = 'block';
        return;
      }
      empty.style.display = 'none';
      kanban.style.display = 'flex';

      const cardMaxWidth = 380;
      const availWidth = kanban.clientWidth || window.innerWidth - 48;
      const cols = Math.max(2, Math.min(5, Math.floor(availWidth / cardMaxWidth)));

      const columns = Array.from({ length: cols }, () => []);
      list.forEach((o, i) => columns[i % cols].push(o));

      kanban.innerHTML = '';
      columns.forEach((colOrders) => {
        const col = document.createElement('div');
        col.className = 'kanban-col';
        colOrders.forEach((o) => col.appendChild(renderOrderCard(o)));
        kanban.appendChild(col);
      });
    }

    function statusColorFor(status) {
      const map = { pending: 'var(--c-confirm)', confirmed: 'var(--c-later)', preparing: 'var(--c-progress)', ready: 'var(--success)', completed: 'var(--text-light)', cancelled: 'var(--error)', no_show: 'var(--error)' };
      return map[status] || 'var(--primary)';
    }

    function renderOrderCard(order) {
      const items = order.order_items || [];
      const user = order.users;
      const cName = user ? ((user.first_name || '') + ' ' + (user.last_name || '')).trim() : (order.notes || '').replace('Client : ', '').split(' | ')[0];
      const phone = user ? user.phone : ((order.notes || '').includes('Tel : ') ? order.notes.split('Tel : ')[1].split(' | ')[0] : '');
      const pickupLabel = order.pickup_time ? order.pickup_time.substring(0, 5) : '--:--';

      const card = document.createElement('div');
      card.className = 'order-card';
      card.style.borderLeftColor = statusColorFor(order.status);

      let itemsHtml = '';
      items.forEach((item) => {
        const pname = (item.products && item.products.name) || '';
        itemsHtml += '<div class="oc-item-row">' +
          '<div class="oc-item-qty">' + (item.quantity || 1) + '</div>' +
          '<div class="oc-item-name">' + escapeHtml(pname) + '</div>' +
          '<div class="oc-item-price">' + fmt((item.unit_price || 0) * (item.quantity || 1)) + '</div>' +
          '</div>';
        if (item.customization_note) itemsHtml += '<div class="oc-item-note">' + escapeHtml(item.customization_note) + '</div>';
        if (item.selected_ingredients) itemsHtml += '<div class="oc-item-ing">&#129468; ' + escapeHtml(item.selected_ingredients) + '</div>';
      });

      const paidBadge = isPaidOnline(order) ? '<span class="oc-badge" style="background:rgba(56,161,105,0.12); color:var(--success)">Paye en ligne</span>' : '';

      card.innerHTML =
        '<div class="oc-head">' +
        '<div><div class="oc-number">' + escapeHtml(order.order_number || '#') + '</div>' +
        '<div class="oc-time">' + timeAgo(order.created_at) + '</div></div>' +
        '<div class="oc-pickup" data-id="' + order.id + '" data-time="' + (order.pickup_time || '') + '">&#128337; ' + pickupLabel + '</div>' +
        '</div>' +
        '<div class="oc-client"><span class="oc-client-name">' + escapeHtml(cName || 'Client') + '</span>' +
        (phone ? '<a class="oc-phone" href="tel:' + escapeHtml(phone) + '">&#128222; ' + escapeHtml(phone) + '</a>' : '') + '</div>' +
        (paidBadge ? '<div class="oc-badges">' + paidBadge + '</div>' : '') +
        '<div class="oc-items">' + itemsHtml + '</div>' +
        '<div class="oc-total"><span>Total</span><span>' + fmt(order.total) + '</span></div>' +
        '<div class="oc-actions" id="actions-' + order.id + '"></div>';

      card.querySelector('.oc-pickup').addEventListener('click', () => openEditPickupTime(order));
      renderCardActions(card.querySelector('#actions-' + order.id), order);
      return card;
    }

    function renderCardActions(container, order) {
      const status = order.status;
      const needsPrep = orderNeedsPrep(order);

      if (status === 'pending') {
        container.innerHTML =
          '<button class="oc-btn-danger" data-action="refuse">Refuser</button>' +
          '<button class="oc-btn-info" data-action="confirm" style="flex:2">Confirmer</button>';
      } else if (status === 'confirmed') {
        if (!needsPrep) {
          container.innerHTML = '<button class="oc-btn-success" data-action="ready" style="flex:2">&#128276; Pr&ecirc;t - Avertir</button>' +
            '<button class="oc-btn-danger" data-action="cancel">Annuler</button>';
        } else {
          container.innerHTML = '<button class="oc-btn-info" data-action="preparing" style="flex:2">&#9654; Commencer</button>' +
            '<button class="oc-btn-danger" data-action="cancel">Annuler</button>';
        }
      } else if (status === 'preparing') {
        container.innerHTML = '<button class="oc-btn-success" data-action="ready" style="flex:2">&#128276; Pr&ecirc;t - Avertir</button>' +
          '<button class="oc-btn-danger" data-action="cancel">Annuler</button>';
      } else if (status === 'ready') {
        if (isPaidOnline(order)) {
          container.innerHTML =
            '<div style="flex-basis:100%; text-align:center; background:rgba(56,161,105,0.1); color:var(--success); font-weight:700; font-size:11px; padding:8px; border-radius:8px; margin-bottom:6px">&#128179; D&eacute;j&agrave; encaiss&eacute;e en ligne</div>' +
            '<button class="oc-btn-primary" data-action="complete" style="flex:2">Confirmer la r&eacute;cup&eacute;ration</button>' +
            '<button class="oc-btn-danger" data-action="cancel">Annuler</button>';
        } else if (order.is_paid) {
          container.innerHTML =
            '<div style="flex-basis:100%; text-align:center; background:rgba(56,161,105,0.1); color:var(--success); font-weight:700; font-size:11px; padding:8px; border-radius:8px; margin-bottom:6px">&#9989; D&eacute;j&agrave; encaiss&eacute;e</div>' +
            '<button class="oc-btn-primary" data-action="complete" style="flex:2">Confirmer la r&eacute;cup&eacute;ration</button>' +
            '<button class="oc-btn-danger" data-action="cancel">Annuler</button>';
        } else {
          container.innerHTML =
            '<button class="oc-btn-outline" data-action="complete" style="flex-basis:100%; margin-bottom:6px">R&eacute;cup&eacute;r&eacute;e sans paiement</button>' +
            '<button class="oc-btn-primary" data-action="checkout" style="flex-basis:100%; margin-bottom:6px">&#128179; Encaisser maintenant</button>' +
            '<button class="oc-btn-danger" data-action="cancel" style="flex-basis:100%">Annuler</button>';
        }
      } else {
        container.style.display = 'none';
        return;
      }

      container.querySelectorAll('[data-action]').forEach((btn) => {
        btn.addEventListener('click', () => handleCardAction(btn.dataset.action, order));
      });
    }

    async function handleCardAction(action, order) {
      try {
        if (action === 'confirm') {
          await updateOrderStatus(order.id, 'confirmed');
          showToast('Commande confirm&eacute;e.');
        } else if (action === 'preparing') {
          await updateOrderStatus(order.id, 'preparing');
          showToast('Pr&eacute;paration commenc&eacute;e.');
        } else if (action === 'ready') {
          await updateOrderStatus(order.id, 'ready');
          showToast('Client averti.');
        } else if (action === 'complete') {
          await updateOrderStatus(order.id, 'completed');
          showToast('Commande termin&eacute;e.');
        } else if (action === 'checkout') {
          const items = (order.order_items || []).map((item) => ({
            id: item.product_id, name: (item.products && item.products.name) || '',
            price: item.unit_price || 0, qty: item.quantity || 1,
          }));
          const params = new URLSearchParams();
          params.set('source_order_id', order.id);
          if (order.customer_id) params.set('customer_id', order.customer_id);
          params.set('items', JSON.stringify(items));
          window.location.href = '/pro/caisse?' + params.toString();
          return;
        } else if (action === 'refuse' || action === 'cancel') {
          const paidOnline = isPaidOnline(order);
          if (!confirm(fr(paidOnline ? 'Annuler et rembourser ? Le client sera rembours&eacute; automatiquement.' : 'Annuler cette commande ?'))) return;
          await cancelOrderWithRefund(order, business.stripe_account_id);
          showToast(paidOnline ? 'Commande annul&eacute;e et rembours&eacute;e.' : 'Commande annul&eacute;e.');
        }
        await loadAndRender();
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de la mise &agrave; jour.');
      }
    }

    function openEditPickupTime(order) {
      const current = order.pickup_time ? order.pickup_time.substring(0, 5) : '';
      const newTime = prompt(fr("Nouvelle heure de retrait (format HH:MM) :"), current);
      if (!newTime || !/^\d{1,2}:\d{2}$/.test(newTime)) return;
      const parts = newTime.split(':');
      const padded = String(parts[0]).padStart(2, '0') + ':' + String(parts[1]).padStart(2, '0');
      editPickupTime(order.id, padded).then(() => {
        showToast('Heure de retrait modifi&eacute;e.');
        loadAndRender();
      }).catch((err) => {
        console.error(err);
        showToast('Erreur lors de la modification.');
      });
    }

    (async () => {
      const session = await requireAuth();
      if (!session) return;
      business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('loading').textContent = fr('Aucun &eacute;tablissement associ&eacute; &agrave; ce compte.');
        return;
      }
      await loadAndRender();

      window.addEventListener('resize', () => renderBoard());
      refreshTimer = setInterval(loadAndRender, 60000);
    })();

    // ------------------------------------------------------------
    // NOUVELLE COMMANDE
    // ------------------------------------------------------------
    let noProducts = [];
    let noIngByProduct = {};
    let noAllIngredients = [];
    let noCartLines = []; // [{lineId, productId, productName, basePrice, presentIngs:Set, addedFree:Set, addedPaid:Set}]
    let noSelectedClient = null; // {id, type} ou null
    let noLineCounter = 0;
    let currentIngLineId = null;
    let noDataLoaded = false;

    function extraIngPrice() {
      return business.extra_price_per_ingredient != null ? business.extra_price_per_ingredient : 0.5;
    }
    function lineUnitPrice(line) {
      return line.basePrice + line.addedPaid.size * extraIngPrice();
    }
    function lineNote(line) {
      const parts = [];
      const removed = (noIngByProduct[line.productId] || []).filter((n) => !line.presentIngs.has(n));
      if (removed.length) parts.push('sans ' + removed.join(', '));
      if (line.addedFree.size) parts.push('+' + Array.from(line.addedFree).join(', '));
      if (line.addedPaid.size) parts.push('+' + Array.from(line.addedPaid).join(', ') + ' (sup.)');
      return parts.join(' \u00b7 ');
    }

    async function openNewOrderModal() {
      if (!noDataLoaded) {
        const res = await loadProductsForNewOrder(business.id);
        noProducts = res.products;
        noIngByProduct = res.ingByProduct;
        noAllIngredients = res.allIngredients;
        noDataLoaded = true;
      }
      noCartLines = [];
      noSelectedClient = null;
      document.getElementById('no-client-search').value = '';
      document.getElementById('no-client-name').value = '';
      document.getElementById('no-client-phone').value = '';
      document.getElementById('no-client-results').innerHTML = '';
      document.getElementById('no-pickup-time').value = '';
      document.getElementById('no-error').style.display = 'none';
      renderProductGrid();
      renderCartLines();
      document.getElementById('new-order-overlay').classList.add('visible');
    }
    function closeNewOrderModal() {
      document.getElementById('new-order-overlay').classList.remove('visible');
    }

    function renderProductGrid() {
      const grid = document.getElementById('no-products-grid');
      grid.innerHTML = '';
      noProducts.forEach((p) => {
        const tile = document.createElement('div');
        tile.className = 'no-product-tile';
        tile.innerHTML = '<span class="no-product-name">' + escapeHtml(p.name) + '</span><span class="no-product-price">' + fmt(p.price) + '</span>';
        tile.addEventListener('click', () => {
          noLineCounter++;
          const defaults = noIngByProduct[p.id] || [];
          noCartLines.push({
            lineId: 'l' + noLineCounter, productId: p.id, productName: p.name, basePrice: p.price || 0,
            presentIngs: new Set(defaults), addedFree: new Set(), addedPaid: new Set(),
          });
          renderCartLines();
        });
        grid.appendChild(tile);
      });
    }

    function renderCartLines() {
      const wrap = document.getElementById('no-cart-lines');
      const empty = document.getElementById('no-cart-empty');
      if (noCartLines.length === 0) {
        wrap.innerHTML = '';
        empty.style.display = 'block';
        document.getElementById('no-total').textContent = fmt(0);
        return;
      }
      empty.style.display = 'none';
      wrap.innerHTML = '';
      let total = 0;
      noCartLines.forEach((line) => {
        const price = lineUnitPrice(line);
        total += price;
        const hasIngredients = (noIngByProduct[line.productId] || []).length > 0 || noAllIngredients.length > 0;
        const note = lineNote(line);
        const row = document.createElement('div');
        row.className = 'no-cart-line';
        row.innerHTML =
          '<span class="no-cart-line-name">' + escapeHtml(line.productName) + (note ? '<span class="no-cart-line-note">' + escapeHtml(note) + '</span>' : '') + '</span>' +
          '<span style="font-size:12px; font-weight:700">' + fmt(price) + '</span>' +
          (hasIngredients ? '<button type="button" data-act="custom" data-id="' + line.lineId + '" style="border:none; background:none; color:var(--primary-dark); font-size:11px; font-weight:700; cursor:pointer">Personnaliser</button>' : '') +
          '<button type="button" data-act="remove" data-id="' + line.lineId + '" style="border:none; background:none; color:var(--error); font-size:16px; cursor:pointer">&times;</button>';
        wrap.appendChild(row);
      });
      document.getElementById('no-total').textContent = fmt(total);
      wrap.querySelectorAll('[data-act="remove"]').forEach((btn) => {
        btn.addEventListener('click', () => {
          noCartLines = noCartLines.filter((l) => l.lineId !== btn.dataset.id);
          renderCartLines();
        });
      });
      wrap.querySelectorAll('[data-act="custom"]').forEach((btn) => {
        btn.addEventListener('click', () => openIngredientEditor(btn.dataset.id));
      });
    }

    function openIngredientEditor(lineId) {
      const line = noCartLines.find((l) => l.lineId === lineId);
      if (!line) return;
      currentIngLineId = lineId;
      document.getElementById('ing-title').textContent = line.productName;

      const defaultList = document.getElementById('ing-default-list');
      const defaults = noIngByProduct[line.productId] || [];
      if (defaults.length === 0) {
        defaultList.innerHTML = '<div style="font-size:12px; color:var(--text-light)">Aucun ingr&eacute;dient de base d&eacute;fini.</div>';
      } else {
        defaultList.innerHTML = '';
        defaults.forEach((name) => {
          const present = line.presentIngs.has(name);
          const row = document.createElement('label');
          row.style.cssText = 'display:flex; align-items:center; gap:8px; padding:6px 0; cursor:pointer; font-size:13px; font-weight:600;' + (present ? '' : ' color:var(--text-light); text-decoration:line-through');
          row.innerHTML = '<input type="checkbox" ' + (present ? 'checked' : '') + '> ' + escapeHtml(name);
          row.querySelector('input').addEventListener('change', (e) => {
            if (e.target.checked) line.presentIngs.add(name); else line.presentIngs.delete(name);
            openIngredientEditor(lineId);
          });
          defaultList.appendChild(row);
        });
      }

      const extraList = document.getElementById('ing-extra-list');
      const extras = noAllIngredients.filter((n) => !defaults.includes(n));
      if (extras.length === 0) {
        extraList.innerHTML = '<div style="font-size:12px; color:var(--text-light)">Aucun autre ingr&eacute;dient disponible.</div>';
      } else {
        extraList.innerHTML = '';
        extras.forEach((name) => {
          const isFree = line.addedFree.has(name);
          const isPaid = line.addedPaid.has(name);
          const row = document.createElement('div');
          row.className = 'ing-chip';
          row.innerHTML =
            '<span class="ing-chip-name">' + escapeHtml(name) + '</span>' +
            '<button type="button" class="ing-mode-btn' + (isFree ? ' active-free' : '') + '" data-mode="free">Gratuit</button>' +
            '<button type="button" class="ing-mode-btn' + (isPaid ? ' active-paid' : '') + '" data-mode="paid">+' + extraIngPrice().toFixed(2) + '</button>';
          row.querySelector('[data-mode="free"]').addEventListener('click', () => {
            if (line.addedFree.has(name)) line.addedFree.delete(name);
            else { line.addedFree.add(name); line.addedPaid.delete(name); }
            openIngredientEditor(lineId);
          });
          row.querySelector('[data-mode="paid"]').addEventListener('click', () => {
            if (line.addedPaid.has(name)) line.addedPaid.delete(name);
            else { line.addedPaid.add(name); line.addedFree.delete(name); }
            openIngredientEditor(lineId);
          });
          extraList.appendChild(row);
        });
      }

      document.getElementById('ing-overlay').classList.add('visible');
    }
    document.getElementById('ing-close').addEventListener('click', () => {
      document.getElementById('ing-overlay').classList.remove('visible');
      renderCartLines();
    });
    document.getElementById('ing-done-btn').addEventListener('click', () => {
      document.getElementById('ing-overlay').classList.remove('visible');
      renderCartLines();
    });

    let noSearchDebounce = null;
    document.getElementById('no-client-search').addEventListener('input', (e) => {
      clearTimeout(noSearchDebounce);
      const q = e.target.value.trim();
      const resultsBox = document.getElementById('no-client-results');
      if (q.length < 2) { resultsBox.innerHTML = ''; return; }
      noSearchDebounce = setTimeout(async () => {
        const { dambou, manual } = await searchClients(business.id, q);
        resultsBox.innerHTML = '';
        [...dambou.map((u) => ({ ...u, _type: 'dambou' })), ...manual.map((c) => ({ ...c, _type: 'manual' }))].slice(0, 6).forEach((c) => {
          const name = ((c.first_name || '') + ' ' + (c.last_name || '')).trim() || 'Client';
          const tile = document.createElement('div');
          tile.style.cssText = 'padding:8px 10px; border:1px solid var(--card-border); border-radius:8px; margin-bottom:4px; cursor:pointer; font-size:12px; font-weight:600';
          tile.textContent = name + (c.phone ? ' \u2014 ' + c.phone : '');
          tile.addEventListener('click', () => {
            noSelectedClient = { id: c.id, type: c._type };
            document.getElementById('no-client-name').value = name;
            document.getElementById('no-client-phone').value = c.phone || '';
            resultsBox.innerHTML = '';
            document.getElementById('no-client-search').value = '';
          });
          resultsBox.appendChild(tile);
        });
      }, 300);
    });
    document.getElementById('no-client-name').addEventListener('input', () => { noSelectedClient = null; });

    document.getElementById('new-order-btn').addEventListener('click', openNewOrderModal);
    document.getElementById('new-order-close').addEventListener('click', closeNewOrderModal);
    document.getElementById('new-order-overlay').addEventListener('click', (e) => {
      if (e.target.id === 'new-order-overlay') closeNewOrderModal();
    });

    document.getElementById('no-submit-btn').addEventListener('click', async () => {
      const errorEl = document.getElementById('no-error');
      errorEl.style.display = 'none';
      if (noCartLines.length === 0) {
        errorEl.textContent = fr('Ajoutez au moins un article.');
        errorEl.style.display = 'block';
        return;
      }
      const btn = document.getElementById('no-submit-btn');
      btn.disabled = true;
      btn.textContent = fr('Cr&eacute;ation...');
      try {
        const lines = noCartLines.map((l) => ({
          productId: l.productId, unitPrice: lineUnitPrice(l), note: lineNote(l),
          selectedIngredients: Array.from(l.presentIngs).concat(Array.from(l.addedFree)).concat(Array.from(l.addedPaid)),
        }));
        const total = lines.reduce((s, l) => s + l.unitPrice, 0);
        await createManualOrder({
          businessId: business.id,
          customerId: (noSelectedClient && noSelectedClient.type === 'dambou') ? noSelectedClient.id : null,
          customerName: document.getElementById('no-client-name').value.trim(),
          customerPhone: document.getElementById('no-client-phone').value.trim(),
          pickupTime: document.getElementById('no-pickup-time').value || null,
          lines: lines, total: total,
        });
        showToast('Commande cr&eacute;&eacute;e.');
        closeNewOrderModal();
        await loadAndRender();
      } catch (err) {
        console.error(err);
        errorEl.textContent = fr("Erreur lors de la cr&eacute;ation de la commande.");
        errorEl.style.display = 'block';
      } finally {
        btn.disabled = false;
        btn.textContent = 'Cr&eacute;er la commande';
      }
    });

  </script>
</body>
</html>
