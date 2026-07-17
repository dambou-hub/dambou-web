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
  .item-card { background: white; border: 1px solid var(--card-border); border-radius: 14px; overflow: hidden; cursor: pointer; }
  .item-card:hover { border-color: var(--primary); }
  .item-card.inactive { opacity: 0.5; }
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
      <button class="btn btn-primary" id="add-category-btn">+ Nouvelle categorie</button>
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
        <span><strong>Produit a vendre</strong><br><span style="font-weight:400; color:var(--text-medium)">Whey, huile, creme...</span></span>
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
        <div class="field" id="duration-field"><label>Duree (min)</label><input type="number" id="item-duration" min="0"></div>
      </div>
      <div class="field"><label>Categorie</label><select id="item-category"></select></div>

      <div id="product-fields">
        <div class="toggle-row"><label>Suivre le stock</label><input type="checkbox" id="item-track-stock"></div>
        <div class="row2" id="stock-fields" style="display:none; margin-top:8px">
          <div class="field"><label>Stock actuel</label><input type="number" id="item-stock-qty" min="0"></div>
          <div class="field"><label>Seuil d'alerte</label><input type="number" id="item-stock-alert" min="0" value="5"></div>
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
    import { requireAuth, getBusinessForUser } from '/pro/js/auth.js';
    import {
      TVA_RATES, loadCategories, loadProducts, loadServices,
      createCategory, deleteCategory, uploadItemImage, saveItem, deleteItem, updateItemImageUrl,
    } from '/pro/js/catalogue.js';

    let business = null;
    let categories = [];
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
      t.textContent = msg;
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

      const groups = categories.map((cat) => ({
        category: cat,
        items: [
          ...services.filter((s) => s.category_id === cat.id).map((s) => Object.assign({ _type: 'service' }, s)),
          ...products.filter((p) => p.category_id === cat.id).map((p) => Object.assign({ _type: 'product' }, p)),
        ],
      }));
      const uncategorized = [
        ...services.filter((s) => !s.category_id).map((s) => Object.assign({ _type: 'service' }, s)),
        ...products.filter((p) => !p.category_id).map((p) => Object.assign({ _type: 'product' }, p)),
      ];
      if (uncategorized.length) groups.push({ category: null, items: uncategorized });

      if (groups.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-medium)">Aucune categorie. Commencez par en creer une.</div>';
        return;
      }

      groups.forEach((g) => container.appendChild(renderCategorySection(g.category, g.items)));
    }

    function renderCategorySection(category, items) {
      const section = document.createElement('div');
      section.className = 'category-section';

      const head = document.createElement('div');
      head.className = 'category-head';
      head.innerHTML = '<h2>' + escapeHtml(category ? category.name : 'Sans categorie') + '</h2>';
      if (category) {
        const actions = document.createElement('div');
        actions.className = 'category-actions';
        const delBtn = document.createElement('button');
        delBtn.className = 'icon-btn';
        delBtn.textContent = '\u{1F5D1}';
        delBtn.title = 'Supprimer la categorie';
        delBtn.addEventListener('click', () => onDeleteCategory(category));
        actions.appendChild(delBtn);
        head.appendChild(actions);
      }
      section.appendChild(head);

      const grid = document.createElement('div');
      grid.className = 'item-grid';
      items.forEach((item) => grid.appendChild(renderItemCard(item)));

      const addTile = document.createElement('button');
      addTile.className = 'add-tile';
      addTile.innerHTML = '<span style="font-size:20px">+</span><span>Ajouter</span>';
      addTile.addEventListener('click', () => openTypeChoice(category ? category.id : null));
      grid.appendChild(addTile);

      section.appendChild(grid);
      return section;
    }

    function renderItemCard(item) {
      const card = document.createElement('div');
      card.className = 'item-card' + (item.is_active === false ? ' inactive' : '');
      const price = Math.round(item.price || 0) + ' ' + currencySymbol();
      const meta = item._type === 'service'
        ? (item.duration ? item.duration + ' min' : '')
        : (item.track_stock ? 'Stock: ' + (item.stock_qty || 0) : '');
      const lowStock = item._type === 'product' && item.track_stock && (item.stock_qty || 0) <= (item.stock_alert != null ? item.stock_alert : 5);

      card.innerHTML =
        '<div class="item-thumb">' + (item.image_url ? '<img src="' + escapeHtml(item.image_url) + '">' : (item._type === 'service' ? '\u2702' : '\u{1F4E6}')) + '</div>' +
        '<div class="item-info"><div class="item-name">' + escapeHtml(item.name) + '</div>' +
        '<div class="item-meta"><span>' + price + '</span>' +
        (meta ? '<span' + (lowStock ? ' class="stock-badge" style="background:rgba(221,107,32,0.12);color:var(--warning)"' : '') + '>' + escapeHtml(meta) + '</span>' : '') +
        '</div></div>';
      card.addEventListener('click', () => openItemModal(item, item.category_id, item._type === 'product'));
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
      if (!confirm('La categorie "' + category.name + '" et tous ses elements seront supprimes. Continuer ?')) return;
      try {
        await deleteCategory(category.id);
        showToast('Categorie supprimee.');
        await loadAll();
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de la suppression.');
      }
    }

    document.getElementById('add-category-btn').addEventListener('click', async () => {
      const name = prompt('Nom de la nouvelle categorie :');
      if (!name || !name.trim()) return;
      try {
        await createCategory(business.id, name.trim(), categories.length);
        showToast('Categorie creee.');
        await loadAll();
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de la creation.');
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
      sel.innerHTML = '<option value="">Sans categorie</option>';
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
      document.getElementById('product-fields').style.display = isProduct ? 'block' : 'none';
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
      } else {
        editingItem = { isProduct: isProduct, id: null };
        document.getElementById('item-modal-title').textContent = 'Nouveau ' + (isProduct ? 'produit' : 'service');
        document.getElementById('item-name').value = '';
        document.getElementById('item-description').value = '';
        document.getElementById('item-price').value = '';
        document.getElementById('item-duration').value = '';
        document.getElementById('item-category').value = categoryId || '';
        document.getElementById('item-active').checked = true;
        document.getElementById('item-track-stock').checked = false;
        document.getElementById('item-stock-qty').value = 0;
        document.getElementById('item-stock-alert').value = 5;
        document.getElementById('item-tva').value = 20;
        document.getElementById('stock-fields').style.display = 'none';
        imgPreview.style.display = 'none'; imgPlaceholder.style.display = 'block';
        document.getElementById('item-delete-btn').style.display = 'none';
      }

      document.getElementById('item-overlay').classList.add('visible');
    }
    function closeItemModal() {
      document.getElementById('item-overlay').classList.remove('visible');
      editingItem = null;
    }

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
      if (!confirm('Supprimer cet element ?')) return;
      try {
        await deleteItem(editingItem.isProduct, editingItem.id);
        showToast('Element supprime.');
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

        showToast('Enregistre.');
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
        document.getElementById('loading').textContent = 'Aucun etablissement associe a ce compte.';
        return;
      }
      await loadAll();
    })();
  </script>
</body>
</html>
