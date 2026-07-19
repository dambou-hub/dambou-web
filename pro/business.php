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
<title>Mon business - Dambou Pro</title>
<meta name="theme-color" content="#00BFA5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #00BFA5; --primary-dark: #00897B; --text-dark: #2D3748; --text-medium: #718096;
    --text-light: #A0AEC0; --background: #F7F8FA; --card-border: #E2E8F0; --error: #E53E3E; --success: #38A169;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 720px; margin: 0 auto; padding: 24px 24px 60px; }
  #loading { text-align: center; padding: 60px 20px; color: var(--text-medium); }

  .profile-media { position: relative; margin-bottom: 48px; }
  .cover-wrap { position: relative; height: 160px; border-radius: 16px; overflow: hidden; background: var(--card-border); cursor: pointer; }
  .cover-wrap img { width: 100%; height: 100%; object-fit: cover; }
  .cover-placeholder { display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-light); font-size: 13px; font-weight: 700; }
  .logo-wrap { position: absolute; left: 20px; bottom: -32px; width: 80px; height: 80px; border-radius: 20px; background: white; border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); overflow: hidden; cursor: pointer; z-index: 2; }
  .logo-wrap img { width: 100%; height: 100%; object-fit: cover; }
  .logo-placeholder { display: flex; align-items: center; justify-content: center; height: 100%; background: rgba(0,191,165,0.1); color: var(--primary-dark); font-size: 22px; font-weight: 900; }
  .logo-upload-hint { position: absolute; bottom: 2px; right: 2px; width: 20px; height: 20px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 2px solid white; }
  .upload-hint { position: absolute; bottom: 8px; right: 12px; background: rgba(0,0,0,0.55); color: white; font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 6px; }

  .tabs { display: flex; gap: 4px; background: white; border: 1px solid var(--card-border); border-radius: 12px; padding: 4px; margin-bottom: 20px; }
  .tab-btn { flex: 1; padding: 10px; border: none; background: none; font-family: inherit; font-size: 13px; font-weight: 700; color: var(--text-medium); border-radius: 8px; cursor: pointer; }
  .tab-btn.active { background: var(--primary); color: white; }
  .tab-panel { display: none; }
  .tab-panel.active { display: block; }

  .card { background: white; border: 1px solid var(--card-border); border-radius: 16px; padding: 20px; margin-bottom: 16px; }
  .field { margin-bottom: 14px; }
  .field label { display: block; font-size: 12px; font-weight: 700; color: var(--text-medium); margin-bottom: 5px; }
  .field input, .field textarea { width: 100%; padding: 11px 12px; border: 1px solid var(--card-border); border-radius: 12px; font-size: 14px; font-family: inherit; }
  .field textarea { resize: vertical; min-height: 60px; }
  .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .slug-row { display: flex; align-items: center; gap: 8px; background: var(--background); border: 1px solid var(--card-border); border-radius: 12px; padding: 11px 12px; }
  .slug-row span { flex: 1; font-size: 13px; color: var(--text-light); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .slug-row span.set { color: var(--primary-dark); font-weight: 700; }
  .slug-row button { padding: 6px 12px; border-radius: 8px; border: none; background: rgba(0,191,165,0.1); color: var(--primary-dark); font-family: inherit; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap; }

  .toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--card-border); }
  .toggle-row:last-child { border-bottom: none; }
  .toggle-row label { font-size: 13px; font-weight: 600; }

  .capacity-picker { display: flex; flex-wrap: wrap; gap: 8px; }
  .capacity-chip { width: 42px; height: 42px; border-radius: 12px; border: 1px solid var(--card-border); background: var(--background); color: var(--text-medium); font-family: inherit; font-size: 15px; font-weight: 800; cursor: pointer; }
  .capacity-chip.selected { background: var(--primary); border-color: var(--primary); color: white; }
  .capacity-label { text-align: center; margin-top: 10px; font-size: 12px; font-weight: 600; color: var(--primary-dark); }
  .prep-time-picker { display: flex; flex-wrap: wrap; gap: 8px; }
  .prep-chip { padding: 8px 12px; border-radius: 20px; border: 1px solid var(--card-border); background: var(--background); color: var(--text-medium); font-family: inherit; font-size: 12px; font-weight: 700; cursor: pointer; }
  .prep-chip.selected { background: #DD6B20; border-color: #DD6B20; color: white; }
  .toggle-row .toggle-sub { font-size: 11px; color: var(--text-light); margin-top: 2px; }

  .btn { padding: 12px 20px; border-radius: 12px; border: none; font-size: 14px; font-weight: 700; font-family: inherit; cursor: pointer; }
  .btn-primary { background: var(--primary); color: white; width: 100%; }

  /* Adresse */
  .address-wrap { position: relative; }
  .address-suggestions { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid var(--card-border); border-radius: 12px; margin-top: 4px; max-height: 220px; overflow-y: auto; z-index: 10; display: none; }
  .address-suggestions.visible { display: block; }
  .address-suggestion { padding: 10px 14px; font-size: 13px; cursor: pointer; border-bottom: 1px solid var(--card-border); }
  .address-suggestion:last-child { border-bottom: none; }
  .address-suggestion:hover { background: var(--background); }
  .locate-btn { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--primary); background: rgba(0,191,165,0.08); border: none; border-radius: 10px; padding: 10px 14px; cursor: pointer; margin-top: 8px; font-family: inherit; }

  /* Horaires */
  .day-row { padding: 10px 0; border-bottom: 1px solid var(--card-border); }
  .day-row:last-child { border-bottom: none; }
  .day-row-top { display: flex; align-items: center; gap: 10px; }
  .day-name { width: 90px; font-size: 13px; font-weight: 700; flex-shrink: 0; }
  .day-times { display: flex; align-items: center; gap: 6px; flex: 1; }
  .day-times input[type=time] { padding: 8px; border: 1px solid var(--card-border); border-radius: 8px; font-size: 12px; font-family: inherit; }
  .day-closed-label { font-size: 12px; color: var(--text-light); flex: 1; }
  .copy-all-btn { display: flex; align-items: center; gap: 4px; margin-top: 6px; margin-left: 100px; border: none; background: none; font-family: inherit; font-size: 11px; font-weight: 600; color: var(--primary); cursor: pointer; padding: 0; }

  .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: var(--text-dark); color: white; padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: 600; z-index: 60; display: none; }
  .toast.visible { display: block; }
  input[type=file] { display: none; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <div style="display:flex; align-items:center; gap:16px">
      <a class="back-link" href="/pro/modules">Modules</a>
      <a class="back-link" href="/pro">Retour au tableau de bord</a>
    </div>
  </div>

  <div class="container">
    <div id="loading">Chargement...</div>
    <div id="content" style="display:none">

      <div class="profile-media" id="profile-media">
        <div class="cover-wrap" id="cover-wrap">
          <div class="cover-placeholder" id="cover-placeholder">Ajouter une banni&egrave;re</div>
          <img id="cover-img" style="display:none">
          <div class="upload-hint">Changer la banni&egrave;re</div>
        </div>
        <div class="logo-wrap" id="logo-wrap">
          <div class="logo-placeholder" id="logo-placeholder">?</div>
          <img id="logo-img" style="display:none">
          <div class="logo-upload-hint">&#9998;</div>
        </div>
      </div>
      <input type="file" id="cover-input" accept="image/*">
      <input type="file" id="logo-input" accept="image/*">

      <div class="tabs">
        <button class="tab-btn active" data-tab="infos">Infos</button>
        <button class="tab-btn" data-tab="adresse">Adresse</button>
        <button class="tab-btn" data-tab="horaires">Horaires</button>
      </div>

      <!-- INFOS -->
      <div class="tab-panel active" id="tab-infos">
        <div class="card">
          <div class="field"><label>Nom du business</label><input type="text" id="f-name"></div>
          <div class="field">
            <label>Votre lien vitrine web</label>
            <div class="slug-row">
              <span id="slug-display">Sauvegardez pour g&eacute;n&eacute;rer votre lien</span>
              <button type="button" id="copy-slug-btn" style="display:none">Copier</button>
            </div>
          </div>
          <div class="field"><label>Description</label><textarea id="f-description"></textarea></div>
          <div class="field"><label>Slogan (50 caract&egrave;res max)</label><input type="text" id="f-slogan" maxlength="50"></div>
          <div class="row2">
            <div class="field"><label>T&eacute;l&eacute;phone</label><input type="tel" id="f-phone"></div>
            <div class="field"><label>Email</label><input type="email" id="f-email"></div>
          </div>
          <div class="field"><label>Site web (optionnel)</label><input type="text" id="f-website"></div>
        </div>

        <div class="card">
          <h3 style="font-size:14px; font-weight:800; margin-bottom:8px">Fermeture exceptionnelle</h3>
          <div class="toggle-row">
            <div><label>Afficher un message de fermeture</label><div class="toggle-sub">Visible par vos clients sur votre page</div></div>
            <input type="checkbox" id="f-closure-enabled">
          </div>
          <div class="field" id="closure-msg-field" style="display:none; margin-top:10px">
            <input type="text" id="f-closure-message" placeholder="Ex: Ferm&eacute; pour cong&eacute;s du 1er au 15 ao&ucirc;t">
          </div>
        </div>

        <div class="card" id="capacity-card" style="display:none">
          <h3 style="font-size:14px; font-weight:800; margin-bottom:4px">Capacit&eacute; simultan&eacute;e</h3>
          <p style="font-size:11px; color:var(--text-medium); margin-bottom:14px">Si&egrave;ges, cabines ou tables disponibles en m&ecirc;me temps</p>
          <div class="capacity-picker" id="capacity-picker"></div>
          <div class="capacity-label" id="capacity-label"></div>
        </div>

        <div class="card" id="order-capacity-card" style="display:none">
          <h3 style="font-size:14px; font-weight:800; margin-bottom:4px">Commandes &amp; file d'attente</h3>
          <p style="font-size:11px; color:var(--text-medium); margin-bottom:14px">Parametres pour les commandes food truck / restaurant</p>
          <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:8px">Temps moyen de pr&eacute;paration</label>
          <div class="prep-time-picker" id="prep-time-picker"></div>
          <label style="display:block; font-size:12px; font-weight:700; color:var(--text-medium); margin:16px 0 8px">Commandes simultan&eacute;es en pr&eacute;paration</label>
          <div class="capacity-picker" id="order-capacity-picker"></div>
          <div class="capacity-label" id="order-capacity-label"></div>
        </div>

        <div class="card">
          <h3 style="font-size:14px; font-weight:800; margin-bottom:4px">Options</h3>
          <div class="toggle-row"><label>WhatsApp active</label><input type="checkbox" id="f-whatsapp"></div>
          <div class="toggle-row"><label>Assujetti &agrave; la TVA</label><input type="checkbox" id="f-tva"></div>
          <div class="toggle-row">
            <div><label>V&eacute;rifier les conflits d'employ&eacute;s</label><div class="toggle-sub">Alerte si un employ&eacute; a d&eacute;j&agrave; un RDV sur le m&ecirc;me cr&eacute;neau</div></div>
            <input type="checkbox" id="f-conflicts">
          </div>
        </div>

        <div class="card" id="legal-card" style="display:none">
          <h3 style="font-size:14px; font-weight:800; margin-bottom:8px">Informations l&eacute;gales</h3>
          <div class="row2">
            <div class="field"><label>Num&eacute;ro de TVA</label><input type="text" id="f-numero-tva"></div>
            <div class="field"><label>SIRET</label><input type="text" id="f-siret"></div>
          </div>
        </div>

        <button class="btn btn-primary" id="save-infos-btn">Enregistrer</button>
      </div>

      <!-- ADRESSE -->
      <div class="tab-panel" id="tab-adresse">
        <div class="card">
          <div class="field address-wrap">
            <label>Adresse</label>
            <input type="text" id="address-input" placeholder="Commencez &agrave; taper votre adresse" autocomplete="off">
            <div class="address-suggestions" id="address-suggestions"></div>
            <button type="button" class="locate-btn" id="locate-btn">Me localiser</button>
          </div>
        </div>
        <button class="btn btn-primary" id="save-address-btn">Enregistrer l'adresse</button>
      </div>

      <!-- HORAIRES -->
      <div class="tab-panel" id="tab-horaires">
        <div class="card" id="hours-container"></div>
        <button class="btn btn-primary" id="save-hours-btn">Enregistrer les horaires</button>
      </div>

    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script type="module">
    import { requireAuth, getBusinessForUser, getActiveModules, fr } from '/pro/js/auth.js';
    import {
      loadFullBusiness, saveBusinessInfo, saveOpeningHours, saveAddress, uploadLogo, uploadCover,
    } from '/pro/js/business.js';

    let business = null;
    let hours = {
      'Lundi': { isOpen: true, start: '09:00', end: '19:00' },
      'Mardi': { isOpen: true, start: '09:00', end: '19:00' },
      'Mercredi': { isOpen: true, start: '09:00', end: '19:00' },
      'Jeudi': { isOpen: true, start: '09:00', end: '19:00' },
      'Vendredi': { isOpen: true, start: '09:00', end: '19:00' },
      'Samedi': { isOpen: true, start: '10:00', end: '18:00' },
      'Dimanche': { isOpen: false, start: '10:00', end: '17:00' },
    };
    let selectedPlace = null;
    let capacity = 1;
    let orderCapacity = 1;
    let prepTime = 15;

    function showToast(msg) {
      const t = document.getElementById('toast');
      t.textContent = fr(msg);
      t.classList.add('visible');
      setTimeout(() => t.classList.remove('visible'), 3000);
    }

    function renderCapacityPicker() {
      const wrap = document.getElementById('capacity-picker');
      wrap.innerHTML = '';
      for (let i = 1; i <= 10; i++) {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'capacity-chip' + (capacity === i ? ' selected' : '');
        chip.textContent = i;
        chip.addEventListener('click', () => { capacity = i; renderCapacityPicker(); });
        wrap.appendChild(chip);
      }
      document.getElementById('capacity-label').textContent = fr(
        capacity === 1 ? '1 client &agrave; la fois' : capacity + ' clients peuvent r&eacute;server le m&ecirc;me cr&eacute;neau'
      );
    }

    function renderOrderCapacityPicker() {
      const wrap = document.getElementById('order-capacity-picker');
      wrap.innerHTML = '';
      for (let i = 1; i <= 10; i++) {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'capacity-chip' + (orderCapacity === i ? ' selected' : '');
        chip.textContent = i;
        chip.addEventListener('click', () => { orderCapacity = i; renderOrderCapacityPicker(); });
        wrap.appendChild(chip);
      }
      document.getElementById('order-capacity-label').textContent = fr(
        orderCapacity === 1 ? '1 commande &agrave; la fois' : 'Vous pr&eacute;parez ' + orderCapacity + ' commandes en m&ecirc;me temps'
      );
    }

    function renderPrepTimePicker() {
      const wrap = document.getElementById('prep-time-picker');
      wrap.innerHTML = '';
      [5, 10, 15, 20, 30, 45, 60].forEach((min) => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'prep-chip' + (prepTime === min ? ' selected' : '');
        chip.textContent = min + ' min';
        chip.addEventListener('click', () => { prepTime = min; renderPrepTimePicker(); });
        wrap.appendChild(chip);
      });
    }

    // ----- Onglets -----
    document.querySelectorAll('.tab-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach((b) => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach((p) => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
      });
    });

    // ----- Photos -----
    document.getElementById('cover-wrap').addEventListener('click', () => document.getElementById('cover-input').click());
    document.getElementById('logo-wrap').addEventListener('click', (e) => { e.stopPropagation(); document.getElementById('logo-input').click(); });
    document.getElementById('cover-input').addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (!file) return;
      try {
        const url = await uploadCover(business.id, file);
        document.getElementById('cover-img').src = url;
        document.getElementById('cover-img').style.display = 'block';
        document.getElementById('cover-placeholder').style.display = 'none';
        showToast('Banniere mise a jour.');
      } catch (err) { console.error(err); showToast('Erreur upload banni&egrave;re.'); }
    });
    document.getElementById('logo-input').addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (!file) return;
      try {
        const url = await uploadLogo(business.id, file);
        document.getElementById('logo-img').src = url;
        document.getElementById('logo-img').style.display = 'block';
        document.getElementById('logo-placeholder').style.display = 'none';
        showToast('Logo mis a jour.');
      } catch (err) { console.error(err); showToast('Erreur upload logo.'); }
    });

    // ----- Infos -----
    document.getElementById('f-tva').addEventListener('change', (e) => {
      document.getElementById('legal-card').style.display = e.target.checked ? 'block' : 'none';
    });
    document.getElementById('f-closure-enabled').addEventListener('change', (e) => {
      document.getElementById('closure-msg-field').style.display = e.target.checked ? 'block' : 'none';
    });
    document.getElementById('copy-slug-btn').addEventListener('click', () => {
      const url = 'https://dambou.fr/' + (business.slug || '');
      navigator.clipboard.writeText(url).then(() => showToast('Lien copie.'));
    });
    document.getElementById('save-infos-btn').addEventListener('click', async () => {
      const name = document.getElementById('f-name').value.trim();
      if (!name) { showToast('Le nom est requis.'); return; }
      const btn = document.getElementById('save-infos-btn');
      btn.disabled = true;
      btn.textContent = 'Enregistrement...';
      try {
        // Le slug n'est jamais modifie ici : il est genere une seule fois a la
        // creation du compte et garanti unique. Le rendre editable risquerait
        // de creer deux business avec le meme slug.
        await saveBusinessInfo(business.id, {
          name: name,
          slug: business.slug,
          description: document.getElementById('f-description').value.trim(),
          slogan: document.getElementById('f-slogan').value.trim(),
          phone: document.getElementById('f-phone').value.trim(),
          email: document.getElementById('f-email').value.trim(),
          website: document.getElementById('f-website').value.trim(),
          whatsappEnabled: document.getElementById('f-whatsapp').checked,
          isTvaAssujetti: document.getElementById('f-tva').checked,
          checkEmployeeConflicts: document.getElementById('f-conflicts').checked,
          numeroTva: document.getElementById('f-numero-tva').value.trim(),
          siret: document.getElementById('f-siret').value.trim(),
          closureEnabled: document.getElementById('f-closure-enabled').checked,
          closureMessage: document.getElementById('f-closure-message').value,
          capacity: capacity,
          orderCapacity: orderCapacity,
          prepTime: prepTime,
        });
        showToast('Informations enregistr&eacute;es.');
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de l\'enregistrement.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Enregistrer';
      }
    });

    // ----- Adresse (Nominatim, meme logique que inscription.php) -----
    const NOMINATIM_URL = 'https://nominatim.openstreetmap.org';
    async function searchAddress(query) {
      if (!query || query.trim().length < 3) return [];
      try {
        const url = NOMINATIM_URL + '/search?q=' + encodeURIComponent(query.trim()) +
          '&format=json&addressdetails=1&limit=6&countrycodes=fr,ma,be,ch,sn,ci&accept-language=fr';
        const res = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
        if (!res.ok) return [];
        return (await res.json()).map(nominatimToPlace);
      } catch (e) { return []; }
    }
    async function reverseGeocode(lat, lng) {
      try {
        const url = NOMINATIM_URL + '/reverse?lat=' + lat + '&lon=' + lng + '&format=json&addressdetails=1&accept-language=fr';
        const res = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
        if (!res.ok) return null;
        return nominatimToPlace(await res.json());
      } catch (e) { return null; }
    }
    function nominatimToPlace(item) {
      const address = item.address || {};
      const houseNumber = address.house_number || '';
      const road = address.road || address.pedestrian || address.footway || '';
      const city = address.city || address.town || address.village || address.municipality || '';
      const postcode = address.postcode || '';
      const country = address.country || '';
      const street = houseNumber ? (houseNumber + ' ' + road).trim() : road;
      const parts = [];
      if (street) parts.push(street);
      if (postcode && city) parts.push(postcode + ' ' + city); else if (city) parts.push(city);
      if (country && country !== 'France') parts.push(country);
      return {
        formatted: parts.length ? parts.join(', ') : (item.display_name || ''),
        lat: parseFloat(item.lat) || 0, lng: parseFloat(item.lon) || 0,
        street: street, city: city, postalCode: postcode, country: country,
      };
    }

    const addressInput = document.getElementById('address-input');
    const suggestionsBox = document.getElementById('address-suggestions');
    let addressDebounce = null;
    addressInput.addEventListener('input', () => {
      selectedPlace = null;
      clearTimeout(addressDebounce);
      const query = addressInput.value;
      addressDebounce = setTimeout(async () => {
        const results = await searchAddress(query);
        if (!results.length) { suggestionsBox.classList.remove('visible'); return; }
        suggestionsBox.innerHTML = '';
        results.forEach((place) => {
          const item = document.createElement('div');
          item.className = 'address-suggestion';
          item.textContent = place.formatted;
          item.addEventListener('click', () => {
            selectedPlace = place;
            addressInput.value = place.formatted;
            suggestionsBox.classList.remove('visible');
          });
          suggestionsBox.appendChild(item);
        });
        suggestionsBox.classList.add('visible');
      }, 400);
    });
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.address-wrap')) suggestionsBox.classList.remove('visible');
    });
    document.getElementById('locate-btn').addEventListener('click', () => {
      const btn = document.getElementById('locate-btn');
      if (!navigator.geolocation) { showToast('Geolocalisation non disponible.'); return; }
      btn.disabled = true;
      btn.textContent = 'Localisation...';
      navigator.geolocation.getCurrentPosition(async (pos) => {
        const place = await reverseGeocode(pos.coords.latitude, pos.coords.longitude);
        btn.disabled = false;
        btn.textContent = 'Me localiser';
        if (place) {
          selectedPlace = place;
          addressInput.value = place.formatted;
          showToast('Position r&eacute;cup&eacute;r&eacute;e.');
        } else {
          showToast("Impossible de r&eacute;cup&eacute;rer l'adresse.");
        }
      }, () => {
        btn.disabled = false;
        btn.textContent = 'Me localiser';
        showToast('Activez la localisation pour utiliser cette fonction.');
      });
    });
    document.getElementById('save-address-btn').addEventListener('click', async () => {
      if (!selectedPlace) { showToast('Choisissez une adresse dans la liste ou utilisez la localisation.'); return; }
      try {
        await saveAddress(business.id, selectedPlace);
        showToast('Adresse enregistr&eacute;e.');
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de l\'enregistrement.');
      }
    });

    // ----- Horaires -----
    function renderHours() {
      const container = document.getElementById('hours-container');
      container.innerHTML = '';
      Object.keys(hours).forEach((day) => {
        const h = hours[day];
        const row = document.createElement('div');
        row.className = 'day-row';
        row.innerHTML =
          '<div class="day-row-top">' +
          '<span class="day-name">' + day + '</span>' +
          '<label style="display:flex; align-items:center; gap:6px"><input type="checkbox" class="day-open-cb" data-day="' + day + '" ' + (h.isOpen ? 'checked' : '') + '></label>' +
          (h.isOpen
            ? '<div class="day-times"><input type="time" class="day-start" data-day="' + day + '" value="' + h.start + '"><span>-</span><input type="time" class="day-end" data-day="' + day + '" value="' + h.end + '"></div>'
            : '<span class="day-closed-label">Ferm&eacute;</span>') +
          '</div>' +
          (h.isOpen ? '<button type="button" class="copy-all-btn" data-day="' + day + '">&#128203; Appliquer &agrave; tous les jours</button>' : '');
        container.appendChild(row);
      });

      container.querySelectorAll('.day-open-cb').forEach((cb) => {
        cb.addEventListener('change', () => {
          hours[cb.dataset.day].isOpen = cb.checked;
          renderHours();
        });
      });
      container.querySelectorAll('.day-start').forEach((inp) => {
        inp.addEventListener('input', () => { hours[inp.dataset.day].start = inp.value; });
      });
      container.querySelectorAll('.day-end').forEach((inp) => {
        inp.addEventListener('input', () => { hours[inp.dataset.day].end = inp.value; });
      });
      container.querySelectorAll('.copy-all-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
          const source = hours[btn.dataset.day];
          Object.keys(hours).forEach((d) => {
            hours[d].start = source.start;
            hours[d].end = source.end;
            hours[d].isOpen = true;
          });
          renderHours();
          showToast('Horaires ' + source.start + ' - ' + source.end + ' appliqu&eacute;s &agrave; tous les jours.');
        });
      });
    }
    document.getElementById('save-hours-btn').addEventListener('click', async () => {
      try {
        await saveOpeningHours(business.id, hours);
        showToast('Horaires enregistres.');
      } catch (err) {
        console.error(err);
        showToast('Erreur lors de l\'enregistrement.');
      }
    });

    // ----- INIT -----
    (async () => {
      const session = await requireAuth();
      if (!session) return;
      const businessBasic = await getBusinessForUser(session.user.id);
      if (!businessBasic) {
        document.getElementById('loading').textContent = fr('Aucun &eacute;tablissement associ&eacute; &agrave; ce compte.');
        return;
      }
      business = await loadFullBusiness(businessBasic.id);

      document.getElementById('f-name').value = business.name || '';
      if (business.slug) {
        document.getElementById('slug-display').textContent = 'dambou.fr/' + business.slug;
        document.getElementById('slug-display').classList.add('set');
        document.getElementById('copy-slug-btn').style.display = 'inline-block';
      }
      document.getElementById('f-description').value = business.description || '';
      document.getElementById('f-slogan').value = business.slogan || '';
      document.getElementById('f-phone').value = business.phone || '';
      document.getElementById('f-email').value = business.email || '';
      document.getElementById('f-website').value = business.website || '';
      document.getElementById('f-whatsapp').checked = !!business.whatsapp_enabled;
      document.getElementById('f-tva').checked = !!business.is_tva_assujetti;
      document.getElementById('legal-card').style.display = business.is_tva_assujetti ? 'block' : 'none';
      document.getElementById('f-conflicts').checked = business.check_employee_conflicts !== false;
      document.getElementById('f-numero-tva').value = business.numero_tva || '';
      document.getElementById('f-siret').value = business.siret || '';
      const hasClosureMsg = !!(business.closure_message && business.closure_message.length);
      document.getElementById('f-closure-enabled').checked = hasClosureMsg;
      document.getElementById('closure-msg-field').style.display = hasClosureMsg ? 'block' : 'none';
      document.getElementById('f-closure-message').value = business.closure_message || '';

      if (business.logo_url) {
        document.getElementById('logo-img').src = business.logo_url;
        document.getElementById('logo-img').style.display = 'block';
        document.getElementById('logo-placeholder').style.display = 'none';
      } else {
        document.getElementById('logo-placeholder').textContent = (business.name || '?').charAt(0).toUpperCase();
      }
      if (business.cover_url) {
        document.getElementById('cover-img').src = business.cover_url;
        document.getElementById('cover-img').style.display = 'block';
        document.getElementById('cover-placeholder').style.display = 'none';
      }

      const address = business.address;
      if (address) {
        addressInput.value = address.formatted || address.street || '';
        selectedPlace = { street: address.street, city: address.city, postalCode: address.postal_code, country: address.country, formatted: address.formatted, lat: address.lat, lng: address.lng };
      }

      if (business.opening_hours) {
        Object.keys(hours).forEach((day) => {
          if (business.opening_hours[day]) hours[day] = Object.assign({}, business.opening_hours[day]);
        });
      }
      renderHours();

      capacity = business.capacity || 1;
      orderCapacity = business.order_capacity || 1;
      prepTime = business.prep_time || 15;
      renderCapacityPicker();
      renderOrderCapacityPicker();
      renderPrepTimePicker();

      const activeModules = await getActiveModules(business.id);
      if (activeModules.some((m) => m.module_type === 'booking')) {
        document.getElementById('capacity-card').style.display = 'block';
      }
      if (activeModules.some((m) => m.module_type === 'orders')) {
        document.getElementById('order-capacity-card').style.display = 'block';
      }

      document.getElementById('loading').style.display = 'none';
      document.getElementById('content').style.display = 'block';
    })();
  </script>
</body>
</html>
