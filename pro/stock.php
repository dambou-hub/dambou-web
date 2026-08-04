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
<title>Stock - Dambou Pro</title>
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
    --warning: #DD6B20;
    --success: #38A169;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }

  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .top-links { display: flex; align-items: center; gap: 16px; }
  .top-links a { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .top-links a:hover { color: var(--primary); }

  .container { max-width: 900px; margin: 0 auto; padding: 24px 24px 60px; }
  .page-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
  .page-head h1 { font-size: 22px; font-weight: 800; }
  .filter-toggle { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: var(--text-medium); cursor: pointer; background: white; border: 1px solid var(--card-border); border-radius: 10px; padding: 8px 14px; }
  .filter-toggle.active { border-color: var(--warning); color: var(--warning); background: rgba(221,107,32,0.06); }

  #loading, #empty-state { text-align: center; padding: 60px 20px; color: var(--text-medium); }

  .stock-row { background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 14px 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 10px; }
  .stock-row.low { border-color: rgba(221,107,32,0.4); background: rgba(221,107,32,0.03); }
  .stock-info { flex: 1; min-width: 0; }
  .stock-name { font-size: 14px; font-weight: 700; }
  .stock-cat { font-size: 12px; color: var(--text-medium); }
  .stock-qty-wrap { display: flex; align-items: center; gap: 10px; }
  .stock-qty { font-size: 20px; font-weight: 900; min-width: 40px; text-align: center; }
  .stock-qty.low { color: var(--warning); }
  .low-badge { font-size: 10px; font-weight: 800; color: var(--warning); background: rgba(221,107,32,0.12); padding: 3px 8px; border-radius: 6px; }
  .track-toggle { font-size: 11px; color: var(--text-light); display: flex; align-items: center; gap: 6px; cursor: pointer; }
  .stock-btn { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--card-border); background: white; cursor: pointer; font-size: 14px; font-weight: 700; color: var(--text-dark); }
  .stock-btn:hover { border-color: var(--primary); color: var(--primary); }
  .reappro-btn { padding: 8px 12px; border-radius: 10px; border: none; background: rgba(0,191,165,0.1); color: var(--primary-dark); font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; }

  .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: var(--text-dark); color: white; padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: 600; z-index: 60; display: none; }
  .toast.visible { display: block; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <div class="top-links">
      <a href="/pro/catalogue">Catalogue</a>
      <a href="/pro">Tableau de bord</a>
    </div>
  </div>

  <div class="container">
    <div class="page-head">
      <h1>Stock</h1>
      <div class="filter-toggle" id="low-filter-toggle">Stock bas uniquement</div>
    </div>

    <div id="loading">Chargement du stock...</div>
    <div id="stock-list" style="display:none"></div>
    <div id="empty-state" style="display:none">Aucun produit avec suivi de stock actif.</div>
  </div>

  <div class="toast" id="toast"></div>

  <script type="module">
    import { requireAuth, getBusinessForUser, fr } from '/pro/js/auth.js';
    import { loadStockProducts, updateStockQty, toggleTrackStock } from '/pro/js/catalogue.js';

    let business = null;
    let allProducts = [];
    let showLowOnly = false;

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
    function isLow(p) {
      return p.track_stock && (p.stock_qty || 0) <= (p.stock_alert != null ? p.stock_alert : 5);
    }

    async function loadAll() {
      allProducts = await loadStockProducts(business.id);
      render();
    }

    function render() {
      document.getElementById('loading').style.display = 'none';
      const list = showLowOnly ? allProducts.filter(isLow) : allProducts;
      const container = document.getElementById('stock-list');
      const empty = document.getElementById('empty-state');

      if (list.length === 0) {
        container.style.display = 'none';
        empty.style.display = 'block';
        empty.textContent = showLowOnly ? 'Aucun produit en stock bas.' : 'Aucun produit avec suivi de stock actif.';
        return;
      }
      empty.style.display = 'none';
      container.style.display = 'block';
      container.innerHTML = '';
      list.forEach((p) => container.appendChild(renderRow(p)));
    }

    function renderRow(p) {
      const low = isLow(p);
      const row = document.createElement('div');
      row.className = 'stock-row' + (low ? ' low' : '');
      row.innerHTML =
        '<div class="stock-info">' +
        '<div class="stock-name">' + escapeHtml(p.name) + (low ? ' <span class="low-badge">Stock bas</span>' : '') + '</div>' +
        '<div class="stock-cat">' + escapeHtml((p.categories && p.categories.name) || 'Sans cat&eacute;gorie') + '</div>' +
        '<label class="track-toggle"><input type="checkbox" ' + (p.track_stock ? 'checked' : '') + ' data-id="' + p.id + '" class="track-cb"> Suivre le stock</label>' +
        '</div>';

      if (p.track_stock) {
        const qtyWrap = document.createElement('div');
        qtyWrap.className = 'stock-qty-wrap';
        qtyWrap.innerHTML =
          '<button class="stock-btn" data-action="minus" data-id="' + p.id + '">-</button>' +
          '<span class="stock-qty' + (low ? ' low' : '') + '">' + (p.stock_qty || 0) + '</span>' +
          '<button class="stock-btn" data-action="plus" data-id="' + p.id + '">+</button>' +
          '<button class="reappro-btn" data-action="reappro" data-id="' + p.id + '">R&eacute;appro</button>';
        row.appendChild(qtyWrap);
      }
      return row;
    }

    document.getElementById('low-filter-toggle').addEventListener('click', () => {
      showLowOnly = !showLowOnly;
      document.getElementById('low-filter-toggle').classList.toggle('active', showLowOnly);
      render();
    });

    document.getElementById('stock-list').addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;
      const id = btn.dataset.id;
      const product = allProducts.find((p) => p.id === id);
      if (!product) return;

      const action = btn.dataset.action;
      if (action === 'minus') {
        await updateStockQty(id, (product.stock_qty || 0) - 1);
        product.stock_qty = Math.max((product.stock_qty || 0) - 1, 0);
        render();
      } else if (action === 'plus') {
        await updateStockQty(id, (product.stock_qty || 0) + 1);
        product.stock_qty = (product.stock_qty || 0) + 1;
        render();
      } else if (action === 'reappro') {
        const qty = prompt(fr('Quantit&eacute; re&ccedil;ue pour "' + product.name + '" :'));
        const n = parseInt(qty, 10);
        if (!n || n <= 0) return;
        const newQty = (product.stock_qty || 0) + n;
        await updateStockQty(id, newQty);
        product.stock_qty = newQty;
        showToast('+' + n + ' unit&eacute;s ajout&eacute;es.');
        render();
      }
    });

    document.getElementById('stock-list').addEventListener('change', async (e) => {
      if (!e.target.classList.contains('track-cb')) return;
      const id = e.target.dataset.id;
      const product = allProducts.find((p) => p.id === id);
      if (!product) return;
      try {
        await toggleTrackStock(id, product.track_stock);
        product.track_stock = !product.track_stock;
        render();
      } catch (err) {
        console.error(err);
        showToast('Erreur.');
      }
    });

    (async () => {
      const session = await requireAuth();
      if (!session) return;
      business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('loading').textContent = fr('Aucun &eacute;tablissement associ&eacute; &agrave; ce compte.');
        return;
      }
      await loadAll();
    })();
  </script>
</body>
</html>
