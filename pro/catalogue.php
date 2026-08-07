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
<title>Catalogue - Dambou Pro</title>
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
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }

  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .top-links { display: flex; align-items: center; gap: 16px; }
  .top-links a { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .top-links a:hover { color: var(--primary); }

  .container { max-width: 1100px; margin: 0 auto; padding: 24px 24px 60px; }
  .page-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
  .page-head h1 { font-size: 22px; font-weight: 800; }
  .btn { padding: 10px 18px; border-radius: 12px; border: none; font-size: 13px; font-weight: 700; font-family: inherit; cursor: pointer; }
  .btn-primary { background: var(--primary); color: white; }
  .btn-outline { background: white; border: 1px solid var(--card-border); color: var(--text-dark); }

  #loading { text-align: center; padding: 60px 20px; color: var(--text-medium); }

  .category-section { margin-bottom: 28px; }
  .category-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
  .category-head h2 { font-size: 15px; font-weight: 800; }
  .category-actions { display: flex; gap: 8px; }
  .icon-btn { width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--card-border); background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; color: var(--text-medium); }
  .icon-btn:hover { border-color: var(--primary); color: var(--primary); }

  .item-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
  .item-card { background: white; border: 1px solid var(--card-border); border-radius: 14px; overflow: hidden; cursor: pointer; position: relative; transition: opacity 0.15s, box-shadow 0.15s; }
  .item-card:hover { border-color: var(--primary); }
  .item-card.inactive { opacity: 0.5; }
  .item-card.dragging { opacity: 0.4; }
  .item-card.drag-over { box-shadow: inset 0 0 0 2px var(--primary); }
  .drag-handle { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 6px; background: rgba(255,255,255,0.9); color: var(--text-light); display: flex; align-items: center; justify-content: center; font-size: 11px; letter-spacing: -3px; cursor: grab; z-index: 2; }
  .drag-handle:active { cursor: grabbing; }
  .item-thumb { width: 100%; height: 110px; background: var(--background); display: flex; align-items: center; justify-content: center; font-size: 26px; color: var(--text-light); overflow: hidden; }
  .item-thumb img { width: 100%; height: 100%; object-fit: cover; }
  .item-info { padding: 10px 12px; }
  .item-name { font-size: 13px; font-weight: 700; margin-bottom: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .item-meta { font-size: 12px; color: var(--text-medium); display: flex; justify-content: space-between; align-items: center; }
  .stock-badge { font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 6px; }

  .add-tile { border: 1.5px dashed var(--card-border); border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; min-height: 168px; cursor: pointer; color: var(--text-light); font-size: 12px; font-weight: 700; background: none; font-family: inherit; }
  .add-tile:hover { border-color: var(--primary); color: var(--primary); }

  .add-category-card { border: 1.5px dashed var(--card-border); border-radius: 14px; padding: 20px; text-align: center; cursor: pointer; color: var(--text-medium); font-size: 13px; font-weight: 700; background: none; font-family: inherit; width: 100%; }
  .add-category-card:hover { border-color: var(--primary); color: var(--primary); }

  /* Modal edition item */
  .overlay { position: fixed; inset: 0; background: rgba(45,55,72,0.35); display: none; align-items: center; justify-content: center; z-index: 50; padding: 20px; }
  .overlay.visible { display: flex; }
  .panel { background: white; border-radius: 18px; width: 100%; max-width: 440px; padding: 24px; max-height: 90vh; overflow-y: auto; }
  .panel h3 { font-size: 17px; font-weight: 800; margin-bottom: 16px; }
  .field { margin-bottom: 12px; }
  .field label { display: block; font-size: 12px; font-weight: 700; color: var(--text-medium); margin-bottom: 5px; }
  .field input[type=text], .field input[type=number], .field select, .field textarea {
    width: 100%; padding: 11px 12px; border: 1px solid var(--card-border); border-radius: 12px; font-size: 14px; font-family: inherit;
  }
  .field textarea { resize: vertical; min-height: 60px; }
  .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 4px 0; }
  .toggle-row label { font-size: 13px; font-weight: 600; }
  .image-upload { width: 100%; height: 120px; border-radius: 12px; border: 1.5px dashed var(--card-border); display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; margin-bottom: 12px; background: var(--background); color: var(--text-light); font-size: 12px; font-weight: 700; }
  .image-upload img { width: 100%; height: 100%; object-fit: cover; }
  .modal-actions { display: flex; gap: 10px; margin-top: 18px; }
  .modal-actions .btn { flex: 1; }
  .btn-danger { background: rgba(229,62,62,0.1); color: var(--error); }

  .type-choice-btn {
    display: flex; align-items: center; gap: 14px; width: 100%; padding: 14px;
    border: 1.5px solid var(--card-border); border-radius: 14px; background: white;
    font-family: inherit; font-size: 14px; text-align: left; cursor: pointer; margin-bottom: 10px;
  }
  .type-choice-btn:hover { border-color: var(--primary); background: rgba(0,191,165,0.04); }
  .error-msg { display: none; background: rgba(229,62,62,0.08); color: var(--error); font-size: 13px; padding: 10px 12px; border-radius: 8px; margin-bottom: 12px; }
  .error-msg.visible { display: block; }

  .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: var(--text-dark); color: white; padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: 600; z-index: 60; display: none; }
  .toast.visible { display: block; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <div class="top-links">
      <a href="/pro/stock">Stock</a>
      <a href="/pro">Tableau de bord</a>
    </div>
  </div>

  <div class="container">
    <div class="page-head">
      <h1>Catalogue</h1>
      <button class="btn btn-primary" id="add-category-btn">+ Nouvelle cat&eacute;gorie</button>
    </div>

    <div id="loading">Chargement du catalogue...</div>
    <div id="catalogue-content" style="display:none"></div>
  </div>

  <!-- Choix service ou produit -->
  <div class="overlay" id="type-choice-overlay">
    <div class="panel" style="max-width:340px">
      <h3>Type d'element</h3>
      <button type="button" class="type-choice-btn" id="choice-service">
        <span style="font-size:22px">&#128467;</span>
        <span><strong>Prestation / Service</strong><br><span style="font-weight:400; color:var(--text-medium)">Massage, coaching, cours...</span></span>
      </button>
      <button type="button" class="type-choice-btn" id="choice-product">
        <span style="font-size:22px">&#128230;</span>
        <span><strong>Produit &agrave; vendre</strong><br><span style="font-weight:400; color:var(--text-medium)">Whey, huile, cr&egrave;me...</span></span>
      </button>
      <button class="btn btn-outline" id="type-choice-cancel" style="width:100%; margin-top:8px">Annuler</button>
    </div>
  </div>

  <!-- Modal edition produit/service -->
  <div class="overlay" id="item-overlay">
    <div class="panel">
      <h3 id="item-modal-title">Nouveau produit</h3>
      <div class="error-msg" id="item-error"></div>

      <div class="image-upload" id="image-upload-box">
        <span id="image-upload-placeholder">Ajouter une photo</span>
        <img id="image-preview" style="display:none">
      </div>
      <input type="file" id="image-input" accept="image/*" style="display:none">

      <div class="field"><label>Nom</label><input type="text" id="item-name"></div>
      <div class="field"><label>Description (optionnel)</label><textarea id="item-description"></textarea></div>
      <div class="row2">
        <div class="field"><label>Prix</label><input type="number" id="item-price" step="0.01" min="0"></div>
        <div class="field" id="duration-field"><label>Dur&eacute;e (min)</label><input type="number" id="item-duration" min="0"></div>
      </div>
      <div class="field" id="participants-field" style="display:none">
        <label>Places (laisser 1 pour un rendez-vous individuel classique)</label>
        <input type="number" id="item-max-participants" min="1" value="1">
        <div style="font-size:11px; color:var(--text-light); margin-top:4px">Au-dessus de 1, ce service devient un atelier : plusieurs clients peuvent s'inscrire sur le meme creneau.</div>
      </div>
      <div class="field"><label>Cat&eacute;gorie</label><select id="item-category"></select></div>

      <div id="product-fields">
        <div class="toggle-row"><label>Suivre le stock</label><input type="checkbox" id="item-track-stock"></div>
        <div class="row2" id="stock-fields" style="display:none; margin-top:8px">
          <div class="field"><label>Stock actuel</label><input type="number" id="item-stock-qty" min="0"></div>
          <div class="field"><label>Seuil d'alerte</label><input type="number" id="item-stock-alert" min="0" value="5"></div>
        </div>

        <div id="ingredients-section" style="display:none; margin-top:14px; padding-top:14px; border-top:1px solid var(--card-border)">
          <div style="background:rgba(0,191,165,0.06); border:1px solid rgba(0,191,165,0.15); border-radius:12px; padding:12px; margin-bottom:12px; font-size:12px; font-weight:600; display:flex; gap:8px; align-items:flex-start">
            <span style="font-size:18px">&#127869;</span>
            <span>Le client pourra personnaliser sa commande en ajoutant ou retirant des ingr&eacute;dients.</span>
          </div>
          <div id="existing-ingredients-chips" style="display:none; margin-bottom:12px">
            <label style="display:block; font-size:11px; font-weight:700; color:var(--primary-dark); margin-bottom:6px">&#8635; D&eacute;j&agrave; utilis&eacute;s dans votre catalogue</label>
            <div id="existing-ingredients-list" style="display:flex; flex-wrap:wrap; gap:6px"></div>
          </div>
          <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:6px">Ingr&eacute;dients de ce produit</label>
          <div id="product-ingredients-list" style="margin-bottom:8px"></div>
          <div style="display:flex; gap:6px">
            <input type="text" id="new-ingredient-input" placeholder="Nom de l'ingr&eacute;dient" style="flex:1; padding:9px 11px; border:1px solid var(--card-border); border-radius:10px; font-size:13px; font-family:inherit">
            <button type="button" id="add-ingredient-btn" style="padding:0 14px; border:none; border-radius:10px; background:var(--primary); color:white; font-family:inherit; font-size:13px; font-weight:700; cursor:pointer">Ajouter</button>
          </div>
        </div>
      </div>

      <div class="field" id="tva-field" style="margin-top:8px"><label>TVA</label>
        <select id="item-tva"></select>
      </div>

      <div class="toggle-row"><label>Actif (visible pour les clients)</label><input type="checkbox" id="item-active" checked></div>

      <div class="modal-actions">
        <button class="btn btn-outline" id="item-cancel-btn">Annuler</button>
        <button class="btn btn-danger" id="item-delete-btn" style="display:none">Supprimer</button>
        <button class="btn btn-primary" id="item-save-btn">Enregistrer</button>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script type="module">
    import { requireAuth, getBusinessForUser, fr } from '/pro/js/auth.js';
    import {
      TVA_RATES, loadCategories, loadProducts, loadServices,
      createCategory, deleteCategory, uploadItemImage, saveItem, deleteItem, updateItemImageUrl,
      loadIngredientsForProduct, loadAllBusinessIngredientNames, saveIngredients,
      saveCategoriesOrder, saveItemsOrder,
    } from '/pro/js/catalogue.js';

    let business = null;
    let categories = [];
    let businessManageIngredients = false;
    let allBusinessIngredientNames = [];
    let currentIngredients = []; // [{name, extra_price, is_default, is_removable}]
    let products = [];
    let services = [];
    let editingItem = null; // {isProduct, id} ou null si creation
    let pendingImageFile = null;

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

    async function loadAll() {
      const [cats, prods, svcs] = await Promise.all([
        loadCategories(business.id), loadProducts(business.id), loadServices(business.id),
      ]);
      categories = cats; products = prods; services = svcs;
      render();
    }

    function render() {
      const container = document.getElementById('catalogue-content');
      document.getElementById('loading').style.display = 'none';
      container.style.display = 'block';
      container.innerHTML = '';

      const byOrder = (a, b) => (a.sort_order || 0) - (b.sort_order || 0);
      const groups = categories.map((cat) => ({
        category: cat,
        items: [
          ...services.filter((s) => s.category_id === cat.id).map((s) => Object.assign({ _type: 'service' }, s)),
          ...products.filter((p) => p.category_id === cat.id).map((p) => Object.assign({ _type: 'product' }, p)),
        ].sort(byOrder),
      }));
      const uncategorized = [
        ...services.filter((s) => !s.category_id).map((s) => Object.assign({ _type: 'service' }, s)),
        ...products.filter((p) => !p.category_id).map((p) => Object.assign({ _type: 'product' }, p)),
      ].sort(byOrder);
      if (uncategorized.length) groups.push({ category: null, items: uncategorized });

      if (groups.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-medium)">Aucune cat&eacute;gorie. Commencez par en cr&eacute;er une.</div>';
        return;
      }

      groups.forEach((g, idx) => container.appendChild(renderCategorySection(g.category, g.items, idx)));
    }

    // idx = position de cette categorie parmi les VRAIES categories (hors
    // "Sans categorie", toujours en dernier et sans fleches -- comme cote
    // mobile ou seul _categories, jamais les items sans categorie, est
    // reordonnable).
    function renderCategorySection(category, items, idx) {
      const section = document.createElement('div');
      section.className = 'category-section';

      const head = document.createElement('div');
      head.className = 'category-head';
      head.innerHTML = '<h2>' + escapeHtml(category ? category.name : 'Sans cat&eacute;gorie') + '</h2>';
      if (category) {
        const actions = document.createElement('div');
        actions.className = 'category-actions';

        const upBtn = document.createElement('button');
        upBtn.className = 'icon-btn';
        upBtn.textContent = '\u2191';
        upBtn.title = fr('Monter');
        upBtn.disabled = idx === 0;
        upBtn.style.opacity = idx === 0 ? '0.3' : '1';
        upBtn.addEventListener('click', () => moveCategory(idx, idx - 1));
        actions.appendChild(upBtn);

        const downBtn = document.createElement('button');
        downBtn.className = 'icon-btn';
        downBtn.textContent = '\u2193';
        downBtn.title = fr('Descendre');
        downBtn.disabled = idx === categories.length - 1;
        downBtn.style.opacity = idx === categories.length - 1 ? '0.3' : '1';
        downBtn.addEventListener('click', () => moveCategory(idx, idx + 1));
        actions.appendChild(downBtn);

        const delBtn = document.createElement('button');
        delBtn.className = 'icon-btn';
        delBtn.textContent = '\u{1F5D1}';
        delBtn.title = fr('Supprimer la cat&eacute;gorie');
        delBtn.addEventListener('click', () => onDeleteCategory(category));
        actions.appendChild(delBtn);

        head.appendChild(actions);
      }
      section.appendChild(head);

      const grid = document.createElement('div');
      grid.className = 'item-grid';
      items.forEach((item, itemIdx) => grid.appendChild(renderItemCard(item, items, category ? category.id : null)));
      attachDragReorder(grid, items);

      const addTile = document.createElement('button');
      addTile.className = 'add-tile';
      addTile.innerHTML = '<span style="font-size:20px">+</span><span>Ajouter</span>';
      addTile.addEventListener('click', () => openTypeChoice(category ? category.id : null));
      grid.appendChild(addTile);

      section.appendChild(grid);
      return section;
    }

    async function moveCategory(oldIdx, newIdx) {
      if (newIdx < 0 || newIdx >= categories.length) return;
      const reordered = categories.slice();
      const [moved] = reordered.splice(oldIdx, 1);
      reordered.splice(newIdx, 0, moved);
      categories = reordered;
      render();
      try {
        await saveCategoriesOrder(reordered);
      } catch (err) {
        console.error(err);
        showToast('Erreur lors du tri.');
        await loadAll();
      }
    }

    // Glisser-deposer natif HTML5 sur les cartes d'une grille d'items
    // (produits/services melanges). itemsList = liste actuelle de CETTE
    // categorie uniquement (le tri est toujours relatif a la categorie,
    // comme cote mobile).
    function attachDragReorder(grid, itemsList) {
      let dragFromIdx = null;
      grid.querySelectorAll('.item-card').forEach((card, i) => {
        card.draggable = true;
        card.addEventListener('dragstart', (e) => {
          dragFromIdx = i;
          card.classList.add('dragging');
          e.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', () => card.classList.remove('dragging'));
        card.addEventListener('dragover', (e) => {
          e.preventDefault();
          card.classList.add('drag-over');
        });
        card.addEventListener('dragleave', () => card.classList.remove('drag-over'));
        card.addEventListener('drop', async (e) => {
          e.preventDefault();
          card.classList.remove('drag-over');
          if (dragFromIdx === null || dragFromIdx === i) return;
          const reordered = itemsList.slice();
          const [moved] = reordered.splice(dragFromIdx, 1);
          reordered.splice(i, 0, moved);
          try {
            await saveItemsOrder(reordered);
            await loadAll();
          } catch (err) {
            console.error(err);
            showToast('Erreur lors du tri.');
          }
        });
      });
    }

    function renderItemCard(item, itemsList, categoryId) {
      const card = document.createElement('div');
      card.className = 'item-card' + (item.is_active === false ? ' inactive' : '');
      const price = Math.round(item.price || 0) + ' ' + currencySymbol();
      const meta = item._type === 'service'
        ? (item.duration ? item.duration + ' min' : '')
        : (item.track_stock ? 'Stock: ' + (item.stock_qty || 0) : '');
      const lowStock = item._type === 'product' && item.track_stock && (item.stock_qty || 0) <= (item.stock_alert != null ? item.stock_alert : 5);

      card.innerHTML =
        '<div class="drag-handle" title="Glisser pour r&eacute;ordonner">\u{22EE}\u{22EE}</div>' +
        '<div class="item-thumb">' + (item.image_url ? '<img src="' + escapeHtml(item.image_url) + '">' : (item._type === 'service' ? '\u2702' : '\u{1F4E6}')) + '</div>' +
        '<div class="item-info"><div class="item-name">' + escapeHtml(item.name) + '</div>' +
        '<div class="item-meta"><span>' + price + '</span>' +
        (meta ? '<span' + (lowStock ? ' class="stock-badge" style="background:rgba(221,107,32,0.12);color:var(--warning)"' : '') + '>' + escapeHtml(meta) + '</span>' : '') +
        '</div></div>';
      card.addEventListener('click', (e) => {
        if (e.target.closest('.drag-handle')) return;
        openItemModal(item, item.category_id, item._type === 'product');
      });
      return card;
    }

    let pendingAddCategoryId = null;

    function openTypeChoice(categoryId) {
      pendingAddCategoryId = categoryId;
      document.getElementById('type-choice-overlay').classList.add('visible');
    }
    document.getElementById('type-choice-cancel').addEventListener('click', () => {
      document.getElementById('type-choice-overlay').classList.remove('visible');
    });
    document.getElementById('type-choice-overlay').addEventListener('click', (e) => {
      if (e.target.id === 'type-choice-overlay') document.getElementById('type-choice-overlay').classList.remove('visible');
    });
    document.getElementById('choice-service').addEventListener('click', () => {
      document.getElementById('type-choice-overlay').classList.remove('visible');
      openItemModal(null, pendingAddCategoryId, false);
    });
    document.getElementById('choice-product').addEventListener('click', () => {
      document.getElementById('type-choice-overlay').classList.remove('visible');
      openItemModal(null, pendingAddCategoryId, true);
    });

    async function onDeleteCategory(category) {
      if (!confirm(fr('La cat&eacute;gorie "' + category.name + '" et tous ses &eacute;l&eacute;ments seront supprim&eacute;s. Continuer ?'))) return;
      try {
        await deleteCategory(category.id);
        showToast('Cat&eacute;gorie supprim&eacute;e.');
        await loadAll();
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de la suppression.');
      }
    }

    document.getElementById('add-category-btn').addEventListener('click', async () => {
      const name = prompt(fr('Nom de la nouvelle cat&eacute;gorie :'));
      if (!name || !name.trim()) return;
      try {
        await createCategory(business.id, name.trim(), categories.length);
        showToast('Cat&eacute;gorie cr&eacute;&eacute;e.');
        await loadAll();
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de la cr&eacute;ation.');
      }
    });

    // -----------------------------------------------------
    // MODAL PRODUIT / SERVICE
    // -----------------------------------------------------
    function populateTvaSelect() {
      const sel = document.getElementById('item-tva');
      sel.innerHTML = '';
      TVA_RATES.forEach((t) => {
        const opt = document.createElement('option');
        opt.value = t.value;
        opt.textContent = t.label;
        sel.appendChild(opt);
      });
    }
    function populateCategorySelect() {
      const sel = document.getElementById('item-category');
      sel.innerHTML = '<option value="">Sans cat&eacute;gorie</option>';
      categories.forEach((c) => {
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name;
        sel.appendChild(opt);
      });
    }

    function openItemModal(item, categoryId, isProduct) {
      pendingImageFile = null;
      document.getElementById('item-error').classList.remove('visible');
      populateCategorySelect();
      populateTvaSelect();

      document.getElementById('duration-field').style.display = isProduct ? 'none' : 'block';
      document.getElementById('participants-field').style.display = isProduct ? 'none' : 'block';
      document.getElementById('product-fields').style.display = isProduct ? 'block' : 'none';
      document.getElementById('ingredients-section').style.display = (isProduct && businessManageIngredients) ? 'block' : 'none';
      document.getElementById('existing-ingredients-chips').style.display = allBusinessIngredientNames.length ? 'block' : 'none';
      // TVA : produits ET services desormais (colonne ajoutee sur services), uniquement
      // si le pro est assujetti a la TVA (businesses.is_tva_assujetti).
      const tvaAssujetti = !!(business && business.is_tva_assujetti);
      document.getElementById('tva-field').style.display = tvaAssujetti ? 'block' : 'none';

      const imgPreview = document.getElementById('image-preview');
      const imgPlaceholder = document.getElementById('image-upload-placeholder');

      if (item) {
        editingItem = { isProduct: isProduct, id: item.id };
        document.getElementById('item-modal-title').textContent = 'Modifier ' + (isProduct ? 'le produit' : 'le service');
        document.getElementById('item-name').value = item.name || '';
        document.getElementById('item-description').value = item.description || '';
        document.getElementById('item-price').value = item.price != null ? item.price : '';
        document.getElementById('item-duration').value = item.duration || '';
        document.getElementById('item-max-participants').value = item.max_participants || 1;
        document.getElementById('item-category').value = item.category_id || '';
        document.getElementById('item-active').checked = item.is_active !== false;
        if (isProduct) {
          document.getElementById('item-track-stock').checked = !!item.track_stock;
          document.getElementById('item-stock-qty').value = item.stock_qty || 0;
          document.getElementById('item-stock-alert').value = item.stock_alert != null ? item.stock_alert : 5;
          document.getElementById('stock-fields').style.display = item.track_stock ? 'grid' : 'none';
        }
        document.getElementById('item-tva').value = item.tva_rate != null ? item.tva_rate : 20;
        if (item.image_url) {
          imgPreview.src = item.image_url; imgPreview.style.display = 'block'; imgPlaceholder.style.display = 'none';
        } else {
          imgPreview.style.display = 'none'; imgPlaceholder.style.display = 'block';
        }
        document.getElementById('item-delete-btn').style.display = 'block';
        if (isProduct && businessManageIngredients) {
          loadIngredientsForProduct(item.id).then((ings) => {
            currentIngredients = ings.map((i) => ({ name: i.name, extra_price: i.extra_price || 0, is_default: i.is_default !== false, is_removable: i.is_removable !== false }));
            renderProductIngredientsList();
            renderExistingIngredientChips();
          });
        } else {
          currentIngredients = [];
        }
      } else {
        editingItem = { isProduct: isProduct, id: null };
        document.getElementById('item-modal-title').textContent = 'Nouveau ' + (isProduct ? 'produit' : 'service');
        document.getElementById('item-name').value = '';
        document.getElementById('item-description').value = '';
        document.getElementById('item-price').value = '';
        document.getElementById('item-duration').value = '';
        document.getElementById('item-max-participants').value = 1;
        document.getElementById('item-category').value = categoryId || '';
        document.getElementById('item-active').checked = true;
        document.getElementById('item-track-stock').checked = false;
        document.getElementById('item-stock-qty').value = 0;
        document.getElementById('item-stock-alert').value = 5;
        document.getElementById('item-tva').value = 20;
        document.getElementById('stock-fields').style.display = 'none';
        imgPreview.style.display = 'none'; imgPlaceholder.style.display = 'block';
        document.getElementById('item-delete-btn').style.display = 'none';
        currentIngredients = [];
        renderProductIngredientsList();
        renderExistingIngredientChips();
      }

      document.getElementById('item-overlay').classList.add('visible');
    }
    function closeItemModal() {
      document.getElementById('item-overlay').classList.remove('visible');
      editingItem = null;
    }

    function renderProductIngredientsList() {
      const list = document.getElementById('product-ingredients-list');
      if (currentIngredients.length === 0) {
        list.innerHTML = '<div style="text-align:center; padding:14px; color:var(--text-light); font-size:12px">Aucun ingr&eacute;dient pour ce produit.</div>';
        return;
      }
      list.innerHTML = '';
      currentIngredients.forEach((ing, idx) => {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex; align-items:center; gap:8px; padding:7px 0; border-bottom:1px solid var(--card-border)';
        row.innerHTML =
          '<span style="flex:1; font-size:13px; font-weight:600">' + escapeHtml(ing.name) + '</span>' +
          '<button type="button" class="remove-ing-btn" data-idx="' + idx + '" style="border:none; background:none; color:var(--error); font-size:16px; cursor:pointer">&times;</button>';
        list.appendChild(row);
      });
      list.querySelectorAll('.remove-ing-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
          currentIngredients.splice(parseInt(btn.dataset.idx, 10), 1);
          renderProductIngredientsList();
          renderExistingIngredientChips();
        });
      });
    }

    function renderExistingIngredientChips() {
      const wrap = document.getElementById('existing-ingredients-list');
      wrap.innerHTML = '';
      allBusinessIngredientNames.forEach((name) => {
        const alreadyAdded = currentIngredients.some((i) => i.name === name);
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.style.cssText = 'display:flex; align-items:center; gap:4px; padding:6px 11px; border-radius:16px; font-family:inherit; font-size:12px; font-weight:600; cursor:' + (alreadyAdded ? 'default' : 'pointer') + '; border:1px solid ' + (alreadyAdded ? 'var(--success)' : 'rgba(0,191,165,0.35)') + '; background:' + (alreadyAdded ? 'rgba(56,161,105,0.1)' : 'rgba(0,191,165,0.08)') + '; color:' + (alreadyAdded ? 'var(--success)' : 'var(--primary-dark)') + ';';
        chip.innerHTML = (alreadyAdded ? '&#10003; ' : '+ ') + escapeHtml(name);
        if (!alreadyAdded) {
          chip.addEventListener('click', () => {
            currentIngredients.push({ name: name, extra_price: 0, is_default: true, is_removable: true });
            renderProductIngredientsList();
            renderExistingIngredientChips();
          });
        }
        wrap.appendChild(chip);
      });
    }

    document.getElementById('add-ingredient-btn').addEventListener('click', () => {
      const input = document.getElementById('new-ingredient-input');
      const name = input.value.trim();
      if (!name) return;
      if (currentIngredients.some((i) => i.name.toLowerCase() === name.toLowerCase())) {
        showToast('Cet ingr&eacute;dient est d&eacute;j&agrave; dans la liste.');
        return;
      }
      currentIngredients.push({ name: name, extra_price: 0, is_default: true, is_removable: true });
      input.value = '';
      renderProductIngredientsList();
      renderExistingIngredientChips();
    });
    document.getElementById('new-ingredient-input').addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); document.getElementById('add-ingredient-btn').click(); }
    });

    document.getElementById('item-cancel-btn').addEventListener('click', closeItemModal);
    document.getElementById('item-overlay').addEventListener('click', (e) => {
      if (e.target.id === 'item-overlay') closeItemModal();
    });
    document.getElementById('item-track-stock').addEventListener('change', (e) => {
      document.getElementById('stock-fields').style.display = e.target.checked ? 'grid' : 'none';
    });

    document.getElementById('image-upload-box').addEventListener('click', () => {
      document.getElementById('image-input').click();
    });
    document.getElementById('image-input').addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      pendingImageFile = file;
      const reader = new FileReader();
      reader.onload = (ev) => {
        document.getElementById('image-preview').src = ev.target.result;
        document.getElementById('image-preview').style.display = 'block';
        document.getElementById('image-upload-placeholder').style.display = 'none';
      };
      reader.readAsDataURL(file);
    });

    document.getElementById('item-delete-btn').addEventListener('click', async () => {
      if (!editingItem || !editingItem.id) return;
      if (!confirm(fr('Supprimer cet &eacute;l&eacute;ment ?'))) return;
      try {
        await deleteItem(editingItem.isProduct, editingItem.id);
        showToast('&Eacute;l&eacute;ment supprim&eacute;.');
        closeItemModal();
        await loadAll();
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de la suppression.');
      }
    });

    document.getElementById('item-save-btn').addEventListener('click', async () => {
      const errorEl = document.getElementById('item-error');
      errorEl.classList.remove('visible');

      const name = document.getElementById('item-name').value.trim();
      if (!name) {
        errorEl.textContent = 'Le nom est requis.';
        errorEl.classList.add('visible');
        return;
      }
      let price = parseFloat(document.getElementById('item-price').value);
      if (isNaN(price) || price < 0) price = 0;

      const isProduct = editingItem.isProduct;
      const saveBtn = document.getElementById('item-save-btn');
      saveBtn.disabled = true;
      saveBtn.textContent = 'Enregistrement...';

      try {
        const itemId = await saveItem(isProduct, editingItem.id, {
          businessId: business.id,
          categoryId: document.getElementById('item-category').value || null,
          name: name,
          description: document.getElementById('item-description').value.trim(),
          price: price,
          duration: parseInt(document.getElementById('item-duration').value, 10) || null,
          maxParticipants: parseInt(document.getElementById('item-max-participants').value, 10) || 1,
          isActive: document.getElementById('item-active').checked,
          trackStock: document.getElementById('item-track-stock').checked,
          stockQty: parseInt(document.getElementById('item-stock-qty').value, 10) || 0,
          stockAlert: parseInt(document.getElementById('item-stock-alert').value, 10),
          tvaRate: parseFloat(document.getElementById('item-tva').value),
        });

        if (pendingImageFile) {
          const url = await uploadItemImage(business.id, itemId, pendingImageFile);
          await updateItemImageUrl(isProduct, itemId, url);
        }

        if (isProduct && businessManageIngredients) {
          await saveIngredients(itemId, currentIngredients);
          if (currentIngredients.length) {
            currentIngredients.forEach((ing) => {
              if (!allBusinessIngredientNames.includes(ing.name)) allBusinessIngredientNames.push(ing.name);
            });
            allBusinessIngredientNames.sort();
          }
        }

        showToast('Enregistr&eacute;.');
        closeItemModal();
        await loadAll();
      } catch (err) {
        console.error(err);
        errorEl.textContent = "Erreur lors de l'enregistrement.";
        errorEl.classList.add('visible');
      } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Enregistrer';
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
        document.getElementById('loading').textContent = fr('Aucun &eacute;tablissement associ&eacute; &agrave; ce compte.');
        return;
      }
      const foodKeywords = ['restaurant', 'food', 'boulangerie', 'pizza', 'snack', 'traiteur', 'burger'];
      const catNorm = (business.category || '').toLowerCase();
      const guessedManageIng = foodKeywords.some((k) => catNorm.includes(k));
      businessManageIngredients = business.manage_ingredients != null ? !!business.manage_ingredients : guessedManageIng;
      if (businessManageIngredients) {
        allBusinessIngredientNames = await loadAllBusinessIngredientNames(business.id);
      }
      await loadAll();
    })();
  </script>
</body>
</html>
