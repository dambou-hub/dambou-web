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
<title>Modules - Dambou Pro</title>
<meta name="theme-color" content="#00BFA5">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #00BFA5; --primary-dark: #00897B; --text-dark: #2D3748; --text-medium: #718096;
    --text-light: #A0AEC0; --background: #F7F8FA; --card-border: #E2E8F0;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .topbar { background: white; border-bottom: 1px solid var(--card-border); padding: 14px 24px; display: flex; align-items: center; justify-content: space-between; }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 17px; text-decoration: none; color: var(--text-dark); }
  .brand img { height: 26px; width: auto; }
  .back-link { font-size: 13px; color: var(--text-medium); text-decoration: none; font-weight: 600; }
  .back-link:hover { color: var(--primary); }

  .container { max-width: 640px; margin: 0 auto; padding: 24px 24px 60px; }
  h1 { font-size: 22px; font-weight: 800; margin-bottom: 6px; }
  .subtitle { font-size: 13px; color: var(--text-medium); margin-bottom: 20px; }
  #loading { text-align: center; padding: 60px 20px; color: var(--text-medium); }

  .category-title { font-size: 12px; font-weight: 800; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; margin: 20px 0 10px; }
  .category-title:first-of-type { margin-top: 0; }

  .module-card { background: white; border: 1px solid var(--card-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; }
  .module-row { display: flex; align-items: flex-start; gap: 12px; }
  .module-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; background: rgba(0,191,165,0.1); }
  .module-info { flex: 1; min-width: 0; }
  .module-name { font-size: 14px; font-weight: 700; margin-bottom: 2px; }
  .module-desc { font-size: 12px; color: var(--text-medium); line-height: 1.4; }

  .toggle { position: relative; width: 42px; height: 24px; flex-shrink: 0; }
  .toggle input { opacity: 0; width: 0; height: 0; }
  .toggle-slider { position: absolute; inset: 0; background: var(--card-border); border-radius: 24px; cursor: pointer; transition: background 0.15s; }
  .toggle-slider::before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: white; border-radius: 50%; transition: transform 0.15s; }
  .toggle input:checked + .toggle-slider { background: var(--primary); }
  .toggle input:checked + .toggle-slider::before { transform: translateX(18px); }

  .sub-toggle { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--card-border); font-size: 12px; }

  .mobile-only-note { background: rgba(113,128,150,0.08); border: 1px solid var(--card-border); border-radius: 12px; padding: 14px; font-size: 12px; color: var(--text-medium); line-height: 1.5; margin-top: 24px; }
  .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: var(--text-dark); color: white; padding: 12px 20px; border-radius: 12px; font-size: 13px; font-weight: 600; z-index: 60; display: none; }
  .toast.visible { display: block; }
</style>
</head>
<body>
  <div class="topbar">
    <a class="brand" href="/pro"><img src="/assets/icon.png" alt=""> Dambou Pro</a>
    <div style="display:flex; align-items:center; gap:16px">
      <a class="back-link" href="/pro/business">Mon business</a>
      <a class="back-link" href="/pro">Retour au tableau de bord</a>
    </div>
  </div>

  <div class="container">
    <h1>Modules</h1>
    <div class="subtitle">Activez uniquement ce dont vous avez besoin.</div>
    <div id="loading">Chargement...</div>
    <div id="modules-content" style="display:none"></div>

    <div class="mobile-only-note">
      &#8505; Le <strong>Terminal de paiement</strong> (lecteur carte Bluetooth) et le <strong>Paiement en ligne</strong> (Stripe) n&eacute;cessitent une configuration qui se fait depuis l'application mobile. Le module <strong>Multi-activit&eacute;</strong> &eacute;galement, car il r&eacute;organise votre catalogue.
    </div>
  </div>

  <div class="toast" id="toast"></div>

  <script type="module">
    import { requireAuth, getBusinessForUser, fr } from '/pro/js/auth.js';
    import { ALL_MODULES, loadModuleStates, toggleModule, toggleOnlineOrders, saveAiContext } from '/pro/js/modules.js';

    let business = null;
    let moduleStates = {};

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

    function render() {
      const container = document.getElementById('modules-content');
      container.innerHTML = '';

      const categories = [];
      ALL_MODULES.forEach((m) => { if (!categories.includes(m.category)) categories.push(m.category); });

      categories.forEach((cat) => {
        const title = document.createElement('div');
        title.className = 'category-title';
        title.textContent = cat;
        container.appendChild(title);

        ALL_MODULES.filter((m) => m.category === cat).forEach((m) => {
          const state = moduleStates[m.type];
          const isEnabled = !!(state && state.is_enabled);

          const card = document.createElement('div');
          card.className = 'module-card';
          card.innerHTML =
            '<div class="module-row">' +
            '<div class="module-icon">' + m.emoji + '</div>' +
            '<div class="module-info"><div class="module-name">' + escapeHtml(m.name) + '</div>' +
            '<div class="module-desc">' + escapeHtml(m.desc) + '</div></div>' +
            '<label class="toggle"><input type="checkbox" class="module-cb" data-type="' + m.type + '" ' + (isEnabled ? 'checked' : '') + '><span class="toggle-slider"></span></label>' +
            '</div>';

          if (m.type === 'orders' && isEnabled) {
            const sub = document.createElement('div');
            sub.className = 'sub-toggle';
            const onlineEnabled = !!(state && state.online_enabled);
            sub.innerHTML =
              '<span>Autoriser les commandes en ligne depuis l\'app client</span>' +
              '<label class="toggle" style="width:36px; height:20px"><input type="checkbox" id="online-orders-cb" ' + (onlineEnabled ? 'checked' : '') + '><span class="toggle-slider"></span></label>';
            card.querySelector('.module-row').parentNode.appendChild(sub);
          }

          if (m.type === 'ai_assistant' && isEnabled) {
            const aiZone = document.createElement('div');
            aiZone.style.cssText = 'margin-top:10px; padding-top:10px; border-top:1px solid var(--card-border)';
            aiZone.innerHTML =
              '<div style="font-size:12px; font-weight:700; color:var(--text-medium); margin-bottom:4px">Instructions pour l\'assistant</div>' +
              '<div style="font-size:11px; color:var(--text-light); margin-bottom:8px; line-height:1.4">Ajoutez des informations sp&eacute;cifiques : r&egrave;gles de prise de RDV, promotions, FAQ, conditions particuli&egrave;res...</div>' +
              '<textarea id="ai-context-input" rows="5" placeholder="Ex: Le stationnement est gratuit. Pr&eacute;voir 10 min suppl&eacute;mentaires pour la 1&egrave;re consultation..." ' +
              'style="width:100%; padding:10px; border:none; background:var(--background); border-radius:10px; font-size:12px; font-family:inherit; resize:vertical; margin-bottom:8px">' + escapeHtml(business.ai_context || '') + '</textarea>' +
              '<button type="button" id="ai-context-save-btn" style="width:100%; padding:10px; border:none; border-radius:10px; background:var(--primary); color:white; font-size:12px; font-weight:700; font-family:inherit; cursor:pointer">Sauvegarder</button>';
            card.appendChild(aiZone);
          }

          container.appendChild(card);
        });
      });

      container.querySelectorAll('.module-cb').forEach((cb) => {
        cb.addEventListener('change', async () => {
          const type = cb.dataset.type;
          const enabled = cb.checked;
          cb.disabled = true;
          try {
            await toggleModule(business.id, type, enabled);
            if (!moduleStates[type]) moduleStates[type] = { module_type: type };
            moduleStates[type].is_enabled = enabled;
            showToast(enabled ? 'Module activ&eacute;.' : 'Module d&eacute;sactiv&eacute;.');
            render();
          } catch (err) {
            console.error(err);
            showToast('Erreur lors de la mise &agrave; jour.');
            cb.checked = !enabled;
          } finally {
            cb.disabled = false;
          }
        });
      });

      const onlineCb = document.getElementById('online-orders-cb');
      if (onlineCb) {
        onlineCb.addEventListener('change', async () => {
          const enabled = onlineCb.checked;
          onlineCb.disabled = true;
          try {
            await toggleOnlineOrders(business.id, enabled);
            moduleStates.orders.online_enabled = enabled;
            showToast(enabled ? 'Commandes en ligne activ&eacute;es.' : 'Commandes en ligne d&eacute;sactiv&eacute;es.');
          } catch (err) {
            console.error(err);
            showToast('Erreur lors de la mise &agrave; jour.');
            onlineCb.checked = !enabled;
          } finally {
            onlineCb.disabled = false;
          }
        });
      }

      const aiSaveBtn = document.getElementById('ai-context-save-btn');
      if (aiSaveBtn) {
        aiSaveBtn.addEventListener('click', async () => {
          aiSaveBtn.disabled = true;
          aiSaveBtn.textContent = 'Enregistrement...';
          try {
            const value = document.getElementById('ai-context-input').value;
            await saveAiContext(business.id, value);
            business.ai_context = value;
            showToast('Instructions sauvegardees.');
          } catch (err) {
            console.error(err);
            showToast('Erreur lors de l\'enregistrement.');
          } finally {
            aiSaveBtn.disabled = false;
            aiSaveBtn.textContent = 'Sauvegarder';
          }
        });
      }
    }

    (async () => {
      const session = await requireAuth();
      if (!session) return;
      business = await getBusinessForUser(session.user.id);
      if (!business) {
        document.getElementById('loading').textContent = fr('Aucun &eacute;tablissement associ&eacute; &agrave; ce compte.');
        return;
      }
      moduleStates = await loadModuleStates(business.id);
      document.getElementById('loading').style.display = 'none';
      document.getElementById('modules-content').style.display = 'block';
      render();
    })();
  </script>
</body>
</html>
