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
<title>Caisse - Dambou Pro</title>
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
    --error: #E53E3E;
    --success: #38A169;
    --gold: #B7891E;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }

  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .top-links a { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .top-links a:hover { color: var(--primary); }

  .layout { display: grid; grid-template-columns: 1fr 380px; gap: 20px; max-width: 1300px; margin: 0 auto; padding: 20px 24px 40px; align-items: start; }
  @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } }

  .search-bar { margin-bottom: 14px; }
  .search-bar input { width: 100%; padding: 12px 14px; border: 1px solid var(--card-border); border-radius: 12px; font-size: 14px; font-family: inherit; }

  .category-chips { display: flex; gap: 8px; margin-bottom: 14px; overflow-x: auto; padding-bottom: 4px; }
  .cat-chip { flex-shrink: 0; padding: 8px 16px; border-radius: 20px; border: 1.5px solid var(--card-border); background: white; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--text-medium); cursor: pointer; white-space: nowrap; }
  .cat-chip.active { background: var(--primary); border-color: var(--primary); color: white; }

  #loading { text-align: center; padding: 60px 20px; color: var(--text-medium); }
  .item-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
  .catalogue-item { background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 12px; cursor: pointer; text-align: left; font-family: inherit; }
  .catalogue-item:hover { border-color: var(--primary); }
  .catalogue-item .ci-name { font-size: 13px; font-weight: 700; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .catalogue-item .ci-price { font-size: 13px; font-weight: 800; color: var(--primary-dark); }
  .catalogue-item .ci-badge { font-size: 9px; font-weight: 700; color: var(--text-light); text-transform: uppercase; }

  /* Panneau caisse (droite) */
  .cart-panel { background: white; border: 1px solid var(--card-border); border-radius: 16px; padding: 18px; position: sticky; top: 20px; }
  .cart-panel h2 { font-size: 15px; font-weight: 800; margin-bottom: 12px; }

  .client-box { display: flex; align-items: center; gap: 10px; padding: 10px; border: 1.5px solid var(--card-border); border-radius: 12px; cursor: pointer; margin-bottom: 14px; }
  .client-box .placeholder { color: var(--text-light); font-size: 13px; flex: 1; }
  .client-box .selected { display: none; align-items: center; gap: 8px; flex: 1; min-width: 0; }
  .client-avatar { width: 28px; height: 28px; border-radius: 50%; background: rgba(0,191,165,0.15); color: var(--primary-dark); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0; }
  .client-name-row { display: flex; align-items: center; gap: 5px; }
  .client-badge { font-size: 8px; font-weight: 800; color: var(--primary-dark); background: rgba(0,191,165,0.1); padding: 2px 4px; border-radius: 4px; }
  .loyalty-row { display: flex; align-items: center; justify-content: space-between; background: rgba(183,137,30,0.08); border-radius: 10px; padding: 8px 10px; margin-bottom: 14px; font-size: 12px; }
  .loyalty-row.disabled { opacity: 0.5; }

  #cart-items { max-height: 220px; overflow-y: auto; margin-bottom: 12px; }
  .cart-row { display: flex; align-items: center; gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--card-border); }
  .cart-row-name { flex: 1; font-size: 12px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .cart-qty-ctrl { display: flex; align-items: center; gap: 6px; }
  .qty-btn { width: 22px; height: 22px; border-radius: 6px; border: 1px solid var(--card-border); background: white; font-size: 12px; font-weight: 700; cursor: pointer; }
  .cart-row-total { font-size: 12px; font-weight: 700; min-width: 50px; text-align: right; }
  #cart-empty { text-align: center; padding: 20px; color: var(--text-light); font-size: 12px; }

  .discount-row { display: flex; gap: 6px; margin-bottom: 10px; }
  .discount-row select { border: 1px solid var(--card-border); border-radius: 8px; font-size: 11px; padding: 6px; font-family: inherit; }
  .discount-row input { flex: 1; border: 1px solid var(--card-border); border-radius: 8px; font-size: 12px; padding: 6px 8px; font-family: inherit; }

  .totals { border-top: 1px solid var(--card-border); padding-top: 10px; margin-top: 6px; }
  .totals-row { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-medium); padding: 2px 0; }
  .totals-row.grand { font-size: 20px; font-weight: 900; color: var(--text-dark); padding-top: 6px; }

  .pay-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 14px; }
  .pay-btn { padding: 12px; border-radius: 12px; border: 1.5px solid var(--card-border); background: white; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
  .pay-btn:hover { border-color: var(--primary); background: rgba(0,191,165,0.06); color: var(--primary-dark); }
  .pay-btn.free { grid-column: 1 / -1; }
  .pay-btn:disabled { opacity: 0.4; cursor: not-allowed; }

  /* Overlays reutilises (client selector + montant recu), memes classes que planning.php */
  .overlay { position: fixed; inset: 0; background: rgba(45,55,72,0.35); display: none; align-items: center; justify-content: center; z-index: 50; padding: 20px; }
  .overlay.visible { display: flex; }
  .panel { background: white; border-radius: 18px; width: 100%; max-width: 400px; padding: 20px; max-height: 85vh; overflow-y: auto; }
  .panel-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px; }
  .panel-title { font-size: 16px; font-weight: 800; }
  .nav-btn { width: 32px; height: 32px; border-radius: 10px; border: none; background: none; font-size: 18px; color: var(--text-light); cursor: pointer; }
  .action-tile { display: flex; align-items: center; gap: 12px; padding: 12px 4px; cursor: pointer; border: none; background: none; width: 100%; text-align: left; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--primary-dark); border-top: 1px solid var(--card-border); margin-top: 6px; }

  #cash-received { width: 100%; padding: 12px; border: 1.5px solid var(--card-border); border-radius: 12px; font-size: 16px; font-weight: 700; font-family: inherit; margin-bottom: 10px; }
  #cash-change-box { display: none; padding: 12px; border-radius: 10px; justify-content: space-between; align-items: center; margin-bottom: 10px; }
  .confirm-actions { display: flex; gap: 8px; margin-top: 10px; }
  .confirm-actions button { flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--card-border); background: white; font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer; }
  .confirm-actions button.primary { background: var(--primary); color: white; border-color: var(--primary); }

  .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: var(--text-dark); color: white; padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: 600; z-index: 60; display: none; }
  .toast.visible { display: block; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <div class="top-links"><a href="/pro">Tableau de bord</a></div>
  </div>

  <div class="layout">
    <div>
      <div class="search-bar"><input type="text" id="item-search" placeholder="Rechercher un produit ou un service..."></div>
      <div class="category-chips" id="category-chips"></div>
      <div id="loading">Chargement du catalogue...</div>
      <div class="item-grid" id="item-grid" style="display:none"></div>
    </div>

    <div class="cart-panel">
      <h2>Vente en cours</h2>

      <div class="client-box" id="client-box">
        <span class="placeholder" id="client-placeholder">Client de passage (optionnel)</span>
        <div class="selected" id="client-selected">
          <div class="client-avatar" id="cb-avatar"></div>
          <div style="flex:1; min-width:0">
            <div class="client-name-row"><span id="cb-name" style="font-size:13px; font-weight:700"></span><span class="client-badge" id="cb-badge" style="display:none">Dambou</span></div>
          </div>
          <button type="button" id="client-clear" style="border:none; background:none; font-size:16px; color:var(--text-light); cursor:pointer">&times;</button>
        </div>
      </div>

      <div class="loyalty-row disabled" id="loyalty-row" style="display:none">
        <span id="loyalty-points-label"></span>
        <label style="display:flex; align-items:center; gap:6px; cursor:pointer"><input type="checkbox" id="use-loyalty-cb"> Utiliser</label>
      </div>

      <div id="cart-items"><div id="cart-empty">Panier vide</div></div>

      <div class="discount-row">
        <select id="discount-type"><option value="amount">Montant</option><option value="percent">%</option></select>
        <input type="number" id="discount-value" placeholder="Remise" min="0" step="0.01">
      </div>

      <div class="totals">
        <div class="totals-row"><span>Sous-total</span><span id="t-subtotal">0</span></div>
        <div class="totals-row" id="t-discount-row" style="display:none"><span>Remise</span><span id="t-discount">0</span></div>
        <div class="totals-row" id="t-loyalty-row" style="display:none"><span>Fidelite</span><span id="t-loyalty">0</span></div>
        <div class="totals-row grand"><span>Total</span><span id="t-total">0</span></div>
      </div>

      <div class="pay-grid">
        <button class="pay-btn" data-method="cash">Especes</button>
        <button class="pay-btn" data-method="card">Carte</button>
        <button class="pay-btn" data-method="check">Cheque</button>
        <button class="pay-btn" data-method="ticket_restaurant">Ticket resto</button>
        <button class="pay-btn free" data-method="free">Offert</button>
      </div>
    </div>
  </div>

  <!-- Selection client (recherche Dambou + manuels + creation + scan) -->
  <div class="overlay" id="client-search-overlay">
    <div class="panel" style="display:flex; flex-direction:column">
      <div class="panel-head">
        <span class="panel-title">Choisir un client</span>
        <button class="nav-btn" id="client-search-close">&times;</button>
      </div>
      <input type="text" id="client-search-input" placeholder="Nom, telephone, ou scannez un QR client..." autocomplete="off"
        style="width:100%; padding:11px 12px; border:1px solid var(--card-border); border-radius:12px; font-size:14px; font-family:inherit; margin-bottom:6px">
      <div style="font-size:11px; color:var(--text-light); margin-bottom:10px">Une douchette USB/Bluetooth scanne directement dans ce champ.</div>
      <div id="client-results" style="overflow-y:auto; max-height:280px; min-height:60px"></div>
      <button type="button" class="action-tile" id="client-create-toggle"><span>+</span><span>Creer une fiche client</span></button>
      <div id="client-create-form" style="display:none">
        <input type="text" id="cc-first-name" placeholder="Prenom" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin:8px 0">
        <input type="text" id="cc-last-name" placeholder="Nom (optionnel)" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:8px">
        <input type="tel" id="cc-phone" placeholder="Telephone" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:8px">
        <input type="email" id="cc-email" placeholder="Email (optionnel)" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:10px">
        <button type="button" id="cc-save-btn" style="width:100%; padding:11px; background:var(--primary); color:white; border:none; border-radius:12px; font-size:13px; font-weight:700; font-family:inherit; cursor:pointer">Creer et selectionner</button>
      </div>
      <button type="button" class="action-tile" id="guest-client-toggle" style="color:var(--text-medium)"><span>&#128100;</span><span>Client de passage (sans fiche)</span></button>
      <div id="guest-form" style="display:none">
        <input type="text" id="guest-name" placeholder="Nom (optionnel)" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin:8px 0">
        <input type="tel" id="guest-phone" placeholder="Telephone (optionnel)" style="width:100%; padding:10px 12px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit; margin-bottom:10px">
        <button type="button" id="guest-save-btn" style="width:100%; padding:11px; background:var(--text-dark); color:white; border:none; border-radius:12px; font-size:13px; font-weight:700; font-family:inherit; cursor:pointer">Valider</button>
      </div>
    </div>
  </div>

  <!-- Montant recu (especes) -->
  <div class="overlay" id="cash-overlay">
    <div class="panel" style="max-width:340px">
      <div class="panel-title" id="cash-total-label" style="margin-bottom:10px">A encaisser</div>
      <input type="number" id="cash-received" step="0.01" placeholder="Montant recu">
      <div id="cash-change-box"><span id="cash-change-label" style="font-weight:700; font-size:13px"></span><span id="cash-change-value" style="font-weight:900; font-size:18px"></span></div>
      <div class="confirm-actions">
        <button id="cash-cancel-btn">Annuler</button>
        <button class="primary" id="cash-confirm-btn" disabled>Valider</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script type="module">
    import { requireAuth, getBusinessForUser, supabase } from '/pro/js/auth.js';
    import { searchClients, createManualClient } from '/pro/js/planning.js';
    import { loadCatalogueItems, loadLoyaltyCard, payCart, PAYMENT_METHOD_LABELS } from '/pro/js/caisse.js';
    import { loadCategories } from '/pro/js/catalogue.js';

    let business = null;
    let categories = [];
    let allItems = [];
    let selectedCategoryId = null;
    let cart = []; // {id, name, unitPrice, qty, isProduct}
    let selectedClient = null; // {type:'dambou'|'guest', id, name, phone}
    let loyaltyCard = null;
    let useLoyalty = false;

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
    function currencySymbol() {
      return { EUR: '\u20ac', MAD: 'DH', CHF: 'CHF', XOF: 'FCFA' }[(business && business.currency_code) || 'EUR'] || '\u20ac';
    }
    function fmt(n) { return Math.round(n) + ' ' + currencySymbol(); }

    // -----------------------------------------------------
    // CATALOGUE (grille de vente)
    // -----------------------------------------------------
    function renderCategoryChips() {
      const wrap = document.getElementById('category-chips');
      if (categories.length === 0) { wrap.style.display = 'none'; return; }
      wrap.innerHTML = '';
      const allChip = document.createElement('button');
      allChip.className = 'cat-chip' + (selectedCategoryId === null ? ' active' : '');
      allChip.textContent = 'Tout';
      allChip.addEventListener('click', () => { selectedCategoryId = null; renderCategoryChips(); renderGrid(document.getElementById('item-search').value); });
      wrap.appendChild(allChip);
      categories.forEach((cat) => {
        const chip = document.createElement('button');
        chip.className = 'cat-chip' + (selectedCategoryId === cat.id ? ' active' : '');
        chip.textContent = cat.name;
        chip.addEventListener('click', () => { selectedCategoryId = cat.id; renderCategoryChips(); renderGrid(document.getElementById('item-search').value); });
        wrap.appendChild(chip);
      });
    }

    function renderGrid(filter) {
      const grid = document.getElementById('item-grid');
      const q = (filter || '').toLowerCase().trim();
      let list = selectedCategoryId ? allItems.filter((i) => i.category_id === selectedCategoryId) : allItems;
      if (q) list = list.filter((i) => i.name.toLowerCase().includes(q));
      grid.innerHTML = '';
      list.forEach((item) => {
        const btn = document.createElement('button');
        btn.className = 'catalogue-item';
        btn.innerHTML = '<div class="ci-badge">' + (item._isProduct ? 'Produit' : 'Service') + '</div>' +
          '<div class="ci-name">' + escapeHtml(item.name) + '</div>' +
          '<div class="ci-price">' + fmt(item.price || 0) + '</div>';
        btn.addEventListener('click', () => addToCart(item));
        grid.appendChild(btn);
      });
    }
    document.getElementById('item-search').addEventListener('input', (e) => renderGrid(e.target.value));

    function addToCart(item) {
      const existing = cart.find((c) => c.id === item.id);
      if (existing) existing.qty += 1;
      else cart.push({ id: item.id, name: item.name, unitPrice: item.price || 0, qty: 1, isProduct: item._isProduct });
      renderCart();
    }
    function changeQty(id, delta) {
      const item = cart.find((c) => c.id === id);
      if (!item) return;
      item.qty += delta;
      if (item.qty <= 0) cart = cart.filter((c) => c.id !== id);
      renderCart();
    }

    // -----------------------------------------------------
    // PANIER + TOTAUX
    // -----------------------------------------------------
    function computeTotals() {
      const subtotal = cart.reduce((s, i) => s + i.unitPrice * i.qty, 0);
      const discountType = document.getElementById('discount-type').value;
      const discountVal = parseFloat(document.getElementById('discount-value').value) || 0;
      const discountAmount = discountType === 'percent' ? subtotal * discountVal / 100 : discountVal;

      let loyaltyDiscount = 0;
      if (useLoyalty && loyaltyCard) {
        const rewardVal = business.loyalty_reward_value || 5;
        const maxDiscount = ((loyaltyCard.points || 0) / 100) * rewardVal;
        const remaining = Math.max(subtotal - discountAmount, 0);
        loyaltyDiscount = Math.min(maxDiscount, remaining);
      }
      const total = Math.max(subtotal - discountAmount - loyaltyDiscount, 0);
      return { subtotal, discountAmount, discountType, loyaltyDiscount, total };
    }

    function renderCart() {
      const container = document.getElementById('cart-items');
      if (cart.length === 0) {
        container.innerHTML = '<div id="cart-empty">Panier vide</div>';
      } else {
        container.innerHTML = '';
        cart.forEach((item) => {
          const row = document.createElement('div');
          row.className = 'cart-row';
          row.innerHTML =
            '<span class="cart-row-name">' + escapeHtml(item.name) + '</span>' +
            '<div class="cart-qty-ctrl">' +
            '<button class="qty-btn" data-id="' + item.id + '" data-delta="-1">-</button>' +
            '<span style="font-size:12px; font-weight:700; min-width:16px; text-align:center">' + item.qty + '</span>' +
            '<button class="qty-btn" data-id="' + item.id + '" data-delta="1">+</button></div>' +
            '<span class="cart-row-total">' + fmt(item.unitPrice * item.qty) + '</span>';
          container.appendChild(row);
        });
      }
      renderTotals();
    }
    document.getElementById('cart-items').addEventListener('click', (e) => {
      const btn = e.target.closest('.qty-btn');
      if (!btn) return;
      changeQty(btn.dataset.id, parseInt(btn.dataset.delta, 10));
    });
    document.getElementById('discount-type').addEventListener('change', renderTotals);
    document.getElementById('discount-value').addEventListener('input', renderTotals);
    document.getElementById('use-loyalty-cb').addEventListener('change', (e) => {
      useLoyalty = e.target.checked;
      renderTotals();
    });

    function renderTotals() {
      const t = computeTotals();
      document.getElementById('t-subtotal').textContent = fmt(t.subtotal);
      document.getElementById('t-discount-row').style.display = t.discountAmount > 0 ? 'flex' : 'none';
      document.getElementById('t-discount').textContent = '-' + fmt(t.discountAmount);
      document.getElementById('t-loyalty-row').style.display = t.loyaltyDiscount > 0 ? 'flex' : 'none';
      document.getElementById('t-loyalty').textContent = '-' + fmt(t.loyaltyDiscount);
      document.getElementById('t-total').textContent = fmt(t.total);
    }

    // -----------------------------------------------------
    // CLIENT (recherche / creation / scan / passage)
    // -----------------------------------------------------
    function updateClientBox() {
      const placeholder = document.getElementById('client-placeholder');
      const sel = document.getElementById('client-selected');
      const loyaltyRow = document.getElementById('loyalty-row');
      if (!selectedClient) {
        placeholder.style.display = 'block';
        sel.style.display = 'none';
        loyaltyRow.style.display = 'none';
        useLoyalty = false;
        document.getElementById('use-loyalty-cb').checked = false;
        return;
      }
      placeholder.style.display = 'none';
      sel.style.display = 'flex';
      document.getElementById('cb-avatar').textContent = (selectedClient.name || '?').charAt(0).toUpperCase();
      document.getElementById('cb-name').textContent = selectedClient.name || 'Client';
      document.getElementById('cb-badge').style.display = selectedClient.type === 'dambou' ? 'inline-block' : 'none';

      if (selectedClient.type === 'dambou' && loyaltyCard && business.loyalty_enabled) {
        loyaltyRow.style.display = 'flex';
        document.getElementById('loyalty-points-label').textContent = (loyaltyCard.points || 0) + ' points disponibles';
      } else {
        loyaltyRow.style.display = 'none';
      }
    }

    document.getElementById('client-box').addEventListener('click', openClientSearch);
    document.getElementById('client-clear').addEventListener('click', (e) => {
      e.stopPropagation();
      selectedClient = null; loyaltyCard = null; useLoyalty = false;
      updateClientBox(); renderTotals();
    });
    document.getElementById('client-search-close').addEventListener('click', closeClientSearch);
    document.getElementById('client-search-overlay').addEventListener('click', (e) => {
      if (e.target.id === 'client-search-overlay') closeClientSearch();
    });

    function openClientSearch() {
      document.getElementById('client-search-input').value = '';
      document.getElementById('client-results').innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-light); font-size:13px">Tapez un nom, un telephone, ou scannez</div>';
      document.getElementById('client-create-form').style.display = 'none';
      document.getElementById('guest-form').style.display = 'none';
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

    async function selectDambouClient(id, name, phone) {
      selectedClient = { type: 'dambou', id: id, name: name, phone: phone || '' };
      loyaltyCard = await loadLoyaltyCard(business.id, id);
      updateClientBox(); renderTotals();
      closeClientSearch();
    }

    const clientSearchInput = document.getElementById('client-search-input');
    let clientSearchDebounce = null;

    // Une douchette USB/Bluetooth "tape" tres vite puis envoie Entree : on distingue
    // un scan d'une frappe humaine via ce delai, et on tente d'abord une correspondance
    // exacte par ID (le QR client Dambou encode directement l'UUID du compte).
    let lastKeyTime = 0;
    let typedFast = true;
    clientSearchInput.addEventListener('keydown', (e) => {
      const now = Date.now();
      if (now - lastKeyTime > 60) typedFast = false;
      lastKeyTime = now;
      if (e.key === 'Enter') {
        e.preventDefault();
        handleClientSubmit(clientSearchInput.value.trim(), typedFast);
        typedFast = true;
      }
    });

    async function handleClientSubmit(value, wasFastInput) {
      if (!value) return;
      // Tentative de scan : on cherche un compte Dambou dont l'id correspond exactement
      // (le QR client Dambou encode directement l'UUID du compte).
      const { data: exactUser } = await supabase.from('users').select('id, first_name, last_name, phone').eq('id', value).maybeSingle();
      if (exactUser) {
        const name = ((exactUser.first_name || '') + ' ' + (exactUser.last_name || '')).trim() || 'Client';
        await selectDambouClient(exactUser.id, name, exactUser.phone || '');
        showToast((wasFastInput ? 'Scan' : 'Client') + ' : ' + name);
        return;
      }
      // Sinon recherche classique (nom/telephone)
      triggerSearch(value);
    }

    clientSearchInput.addEventListener('input', () => {
      clearTimeout(clientSearchDebounce);
      const query = clientSearchInput.value;
      clientSearchDebounce = setTimeout(() => triggerSearch(query), 350);
    });

    async function triggerSearch(query) {
      const resultsBox = document.getElementById('client-results');
      if (query.trim().length < 2) {
        resultsBox.innerHTML = '<div style="padding:20px; text-align:center; color:var(--text-light); font-size:13px">Tapez un nom, un telephone, ou scannez</div>';
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
        resultsBox.appendChild(clientTile(name, u.phone || '', true, () => selectDambouClient(u.id, name, u.phone || '')));
      });
      results.manual.forEach((cl) => {
        const name = ((cl.first_name || '') + ' ' + (cl.last_name || '')).trim() || 'Client';
        resultsBox.appendChild(clientTile(name, cl.phone || '', false, () => {
          selectedClient = { type: 'guest', name: name, phone: cl.phone || '' };
          loyaltyCard = null;
          updateClientBox(); renderTotals();
          closeClientSearch();
        }));
      });
    }

    document.getElementById('client-create-toggle').addEventListener('click', () => {
      const form = document.getElementById('client-create-form');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
      document.getElementById('guest-form').style.display = 'none';
    });
    document.getElementById('cc-save-btn').addEventListener('click', async () => {
      const firstName = document.getElementById('cc-first-name').value.trim();
      if (!firstName) return;
      const lastName = document.getElementById('cc-last-name').value.trim();
      const phone = document.getElementById('cc-phone').value.trim();
      const email = document.getElementById('cc-email').value.trim();
      try {
        const created = await createManualClient(business.id, { firstName, lastName, phone, email });
        const name = ((created.first_name || '') + ' ' + (created.last_name || '')).trim();
        selectedClient = { type: 'guest', name: name, phone: created.phone || '' };
        loyaltyCard = null;
        updateClientBox(); renderTotals();
        closeClientSearch();
        showToast('Fiche client creee.');
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de la creation.');
      }
    });

    document.getElementById('guest-client-toggle').addEventListener('click', () => {
      const form = document.getElementById('guest-form');
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
      document.getElementById('client-create-form').style.display = 'none';
    });
    document.getElementById('guest-save-btn').addEventListener('click', () => {
      const name = document.getElementById('guest-name').value.trim();
      const phone = document.getElementById('guest-phone').value.trim();
      selectedClient = { type: 'guest', name: name || 'Client de passage', phone: phone };
      loyaltyCard = null;
      updateClientBox(); renderTotals();
      closeClientSearch();
    });

    // -----------------------------------------------------
    // PAIEMENT
    // -----------------------------------------------------
    document.querySelectorAll('.pay-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (cart.length === 0) { showToast('Le panier est vide.'); return; }
        const method = btn.dataset.method;
        if (method === 'cash') {
          const t = computeTotals();
          document.getElementById('cash-total-label').textContent = 'A encaisser : ' + fmt(t.total);
          document.getElementById('cash-received').value = '';
          document.getElementById('cash-change-box').style.display = 'none';
          document.getElementById('cash-confirm-btn').disabled = true;
          document.getElementById('cash-overlay').classList.add('visible');
          document.getElementById('cash-received').focus();
          return;
        }
        finalizePayment(method);
      });
    });

    document.getElementById('cash-received').addEventListener('input', () => {
      const t = computeTotals();
      const received = parseFloat(document.getElementById('cash-received').value) || 0;
      const box = document.getElementById('cash-change-box');
      const confirmBtn = document.getElementById('cash-confirm-btn');
      if (!document.getElementById('cash-received').value) { box.style.display = 'none'; confirmBtn.disabled = true; return; }
      const change = received - t.total;
      box.style.display = 'flex';
      box.style.background = change >= 0 ? 'rgba(56,161,105,0.1)' : 'rgba(229,62,62,0.1)';
      document.getElementById('cash-change-label').textContent = change >= 0 ? 'Monnaie' : 'Insuffisant';
      document.getElementById('cash-change-label').style.color = change >= 0 ? 'var(--success)' : 'var(--error)';
      document.getElementById('cash-change-value').textContent = Math.abs(change).toFixed(2);
      document.getElementById('cash-change-value').style.color = change >= 0 ? 'var(--success)' : 'var(--error)';
      confirmBtn.disabled = change < 0;
    });
    document.getElementById('cash-cancel-btn').addEventListener('click', () => {
      document.getElementById('cash-overlay').classList.remove('visible');
    });
    document.getElementById('cash-confirm-btn').addEventListener('click', () => {
      document.getElementById('cash-overlay').classList.remove('visible');
      finalizePayment('cash');
    });

    async function finalizePayment(method) {
      const t = computeTotals();
      try {
        await payCart({
          cart: cart, business: business, client: selectedClient,
          discountAmount: t.discountAmount, discountType: t.discountType,
          loyaltyDiscount: t.loyaltyDiscount, useLoyalty: useLoyalty, loyaltyCard: loyaltyCard,
          method: method,
        });
        showToast('Vente encaissee (' + (PAYMENT_METHOD_LABELS[method] || method) + ').');
        cart = []; selectedClient = null; loyaltyCard = null; useLoyalty = false;
        document.getElementById('discount-value').value = '';
        renderCart();
        updateClientBox();
      } catch (err) {
        console.error(err);
        showToast("Erreur lors de l'encaissement.");
      }
    }

    // -----------------------------------------------------
    // INIT
    // -----------------------------------------------------
    (async () => {
      const session = await requireAuth();
      if (!session) return;
      business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('loading').textContent = 'Aucun etablissement associe a ce compte.';
        return;
      }
      allItems = await loadCatalogueItems(business.id);
      categories = await loadCategories(business.id);
      document.getElementById('loading').style.display = 'none';
      document.getElementById('item-grid').style.display = 'grid';
      renderCategoryChips();
      renderGrid('');
      renderCart();
    })();
  </script>
</body>
</html>
