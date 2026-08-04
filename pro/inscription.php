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
<title>Creer un compte Pro - Dambou</title>
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
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', -apple-system, sans-serif; background: var(--background); color: var(--text-dark); min-height: 100vh; }
  .page { max-width: 640px; margin: 0 auto; padding: 32px 20px 60px; }

  .progress-head { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
  .back-btn { width: 36px; height: 36px; border-radius: 10px; background: white; border: 1px solid var(--card-border); display: flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; color: var(--text-dark); flex-shrink: 0; }
  .progress-info { flex: 1; }
  .progress-title { font-size: 14px; font-weight: 800; }
  .progress-sub { font-size: 11px; color: var(--text-medium); }
  .progress-pct { background: rgba(0,191,165,0.1); color: var(--primary); font-size: 12px; font-weight: 800; padding: 4px 10px; border-radius: 20px; }
  .progress-bar { display: flex; gap: 4px; margin-bottom: 28px; }
  .progress-bar .seg { flex: 1; height: 4px; border-radius: 2px; background: var(--card-border); }
  .progress-bar .seg.done { background: var(--primary); }

  .card { background: white; border-radius: 16px; border: 1px solid var(--card-border); padding: 28px 24px; }
  .step-title { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
  .step-sub { font-size: 13px; color: var(--text-medium); margin-bottom: 22px; line-height: 1.4; }

  .row { display: flex; gap: 12px; }
  .field { margin-bottom: 14px; flex: 1; }
  .field label { display: block; font-size: 12px; font-weight: 700; color: var(--text-medium); margin-bottom: 5px; }
  .field input, .field select { width: 100%; padding: 12px 14px; border: 1px solid var(--card-border); border-radius: 12px; font-size: 14px; font-family: inherit; outline: none; }
  .field input:focus, .field select:focus { border-color: var(--primary); border-width: 2px; padding: 11px 13px; }

  .phone-row { display: flex; gap: 8px; }
  .phone-prefix { display: flex; align-items: center; gap: 4px; padding: 12px 12px; border: 1px solid var(--card-border); border-radius: 12px; font-size: 13px; font-weight: 700; background: white; cursor: pointer; white-space: nowrap; }
  .phone-prefix select { border: none; background: transparent; font-weight: 700; font-size: 13px; font-family: inherit; cursor: pointer; }

  .checkbox-row { display: flex; align-items: flex-start; gap: 10px; margin: 14px 0; cursor: pointer; }
  .checkbox-row input { margin-top: 2px; }
  .checkbox-row span { font-size: 13px; color: var(--text-medium); }

  .error-msg { display: none; background: rgba(229,62,62,0.08); color: var(--error); font-size: 13px; padding: 10px 12px; border-radius: 8px; margin: 14px 0; }
  .error-msg.visible { display: block; }

  button.primary { width: 100%; padding: 14px; background: var(--primary); color: white; border: none; border-radius: 14px; font-size: 15px; font-weight: 700; font-family: inherit; cursor: pointer; margin-top: 8px; }
  button.primary:hover { background: var(--primary-dark); }
  button.primary:disabled { background: var(--text-light); cursor: not-allowed; }

  .login-link { display: block; text-align: center; margin-top: 16px; font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 700; }

  /* ETAPE 2 - templates */
  .template-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; margin-bottom: 20px; }
  .template-card { border: 1.5px solid var(--card-border); border-radius: 14px; padding: 14px; cursor: pointer; background: white; }
  .template-card.selected { border-color: var(--primary); border-width: 2px; background: rgba(0,191,165,0.06); }
  .template-icon { width: 40px; height: 40px; border-radius: 10px; background: rgba(0,191,165,0.12); display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 10px; }
  .template-name { font-size: 13px; font-weight: 800; margin-bottom: 3px; }
  .template-desc { font-size: 11px; color: var(--text-medium); line-height: 1.3; }
  .templates-loading { text-align: center; padding: 40px; color: var(--text-medium); font-size: 14px; }

  /* ETAPE 3 - adresse */
  .address-wrap { position: relative; }
  .address-suggestions { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid var(--card-border); border-radius: 12px; margin-top: 4px; max-height: 220px; overflow-y: auto; z-index: 10; display: none; }
  .address-suggestions.visible { display: block; }
  .address-suggestion { padding: 10px 14px; font-size: 13px; cursor: pointer; border-bottom: 1px solid var(--card-border); }
  .address-suggestion:last-child { border-bottom: none; }
  .address-suggestion:hover { background: var(--background); }
  .locate-btn { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: var(--primary); background: rgba(0,191,165,0.08); border: none; border-radius: 10px; padding: 10px 14px; cursor: pointer; margin-top: 8px; font-family: inherit; }
  .locate-btn:disabled { opacity: 0.6; cursor: wait; }
  .address-confirmed { font-size: 12px; color: var(--primary-dark); margin-top: 8px; display: none; }
  .address-confirmed.visible { display: block; }

  #loading-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.85); display: none; align-items: center; justify-content: center; font-size: 14px; color: var(--text-medium); font-weight: 600; z-index: 100; }
  #loading-overlay.visible { display: flex; }
</style>
</head>
<body>

<div id="loading-overlay">Cr&eacute;ation de votre espace en cours...</div>

<div class="page">
  <div class="progress-head">
    <a class="back-btn" href="/pro/login" id="back-btn">&larr;</a>
    <div class="progress-info">
      <div class="progress-title" id="step-title">Creer votre compte</div>
      <div class="progress-sub" id="step-sub">Etape 1 sur 3</div>
    </div>
    <div class="progress-pct" id="step-pct">33%</div>
  </div>
  <div class="progress-bar">
    <div class="seg done" id="seg-1"></div>
    <div class="seg" id="seg-2"></div>
    <div class="seg" id="seg-3"></div>
  </div>

  <div class="card">

    <!-- ETAPE 1 : COMPTE -->
    <div id="step-account">
      <div class="step-title">Cr&eacute;ons votre compte</div>
      <div class="step-sub">Vous pourrez g&eacute;rer votre activit&eacute; et recevoir des clients.</div>
      <form id="account-form">
        <div class="row">
          <div class="field"><label>Prenom</label><input type="text" id="first-name" required></div>
          <div class="field"><label>Nom</label><input type="text" id="last-name" required></div>
        </div>
        <div class="field"><label>Email professionnel</label><input type="email" id="email" required></div>
        <div class="field">
          <label>Telephone</label>
          <div class="phone-row">
            <div class="phone-prefix">
              <select id="country-select"></select>
            </div>
            <input type="tel" id="phone" placeholder="6 12 34 56 78" style="flex:1" required>
          </div>
        </div>
        <div class="field"><label>Mot de passe</label><input type="password" id="password" minlength="8" required></div>
        <div class="field"><label>Confirmer le mot de passe</label><input type="password" id="password-confirm" minlength="8" required></div>
        <label class="checkbox-row">
          <input type="checkbox" id="accept-terms">
          <span>J'accepte les <a href="/cgu">Conditions d'utilisation</a> et la <a href="/privacy">Politique de confidentialite</a></span>
        </label>
        <div class="error-msg" id="account-error"></div>
        <button type="submit" class="primary" id="account-submit" disabled>Chargement...</button>
      </form>
      <a class="login-link" href="/pro/login">Deja un compte ? Se connecter</a>
    </div>

    <!-- ETAPE 1bis : VERIFICATION OTP (reproduit otp_verification_screen.dart) -->
    <div id="step-otp" style="display:none">
      <div style="width:76px; height:76px; border-radius:50%; background:rgba(0,191,165,0.1); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:34px">&#128231;</div>
      <div class="step-title" style="text-align:center">V&eacute;rifiez votre email</div>
      <div class="step-sub" style="text-align:center">On vous a envoy&eacute; un code &agrave; 6 chiffres &agrave;<br><strong id="otp-email-display"></strong></div>
      <div id="otp-digits" style="display:flex; justify-content:space-between; gap:8px; margin:28px 0 16px">
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-idx="0" style="width:100%; height:56px; text-align:center; font-size:22px; font-weight:800; border:1.5px solid var(--card-border); border-radius:14px; font-family:inherit">
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-idx="1" style="width:100%; height:56px; text-align:center; font-size:22px; font-weight:800; border:1.5px solid var(--card-border); border-radius:14px; font-family:inherit">
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-idx="2" style="width:100%; height:56px; text-align:center; font-size:22px; font-weight:800; border:1.5px solid var(--card-border); border-radius:14px; font-family:inherit">
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-idx="3" style="width:100%; height:56px; text-align:center; font-size:22px; font-weight:800; border:1.5px solid var(--card-border); border-radius:14px; font-family:inherit">
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-idx="4" style="width:100%; height:56px; text-align:center; font-size:22px; font-weight:800; border:1.5px solid var(--card-border); border-radius:14px; font-family:inherit">
        <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-idx="5" style="width:100%; height:56px; text-align:center; font-size:22px; font-weight:800; border:1.5px solid var(--card-border); border-radius:14px; font-family:inherit">
      </div>
      <div class="error-msg" id="otp-error"></div>
      <button type="button" class="primary" id="otp-verify-btn" disabled>V&eacute;rifier mon email</button>
      <div style="text-align:center; margin-top:20px; font-size:14px; color:var(--text-medium)">
        Pas re&ccedil;u ? <span id="otp-resend-link" style="color:var(--text-light); font-weight:700; cursor:default">Renvoyer dans <span id="otp-countdown">60</span>s</span>
      </div>
    </div>

    <!-- ETAPE 2 : ACTIVITE -->
    <div id="step-activity" style="display:none">
      <div class="step-title">Votre type d'activit&eacute;</div>
      <div class="step-sub">Votre espace sera configur&eacute; automatiquement selon votre activit&eacute;.</div>
      <div id="templates-container" class="templates-loading">Chargement des activit&eacute;s...</div>
      <div class="error-msg" id="activity-error"></div>
      <button type="button" class="primary" id="activity-submit" disabled>Continuer</button>
    </div>

    <!-- ETAPE 3 : BUSINESS -->
    <div id="step-business" style="display:none">
      <div class="step-title">Votre business</div>
      <div class="step-sub">Dernieres infos avant de commencer.</div>
      <form id="business-form">
        <div class="field"><label>Nom de votre business</label><input type="text" id="business-name" placeholder="Ex: Salon Le Chic" required></div>
        <div class="field"><label>Telephone du business</label><input type="tel" id="business-phone" required></div>
        <div class="field address-wrap">
          <label>Adresse</label>
          <input type="text" id="address-input" placeholder="Commencez a taper votre adresse" autocomplete="off">
          <div class="address-suggestions" id="address-suggestions"></div>
          <button type="button" class="locate-btn" id="locate-btn">Me localiser</button>
          <div class="address-confirmed" id="address-confirmed"></div>
        </div>
        <div class="error-msg" id="business-error"></div>
        <button type="submit" class="primary" id="business-submit">Creer mon espace Dambou</button>
      </form>
    </div>

  </div>
</div>

<script type="module">
  import { createAccount, verifyOtpAndCompleteProfile, resendOtp, loadTemplates, searchAddress, reverseGeocode, createBusinessWithTemplate, COUNTRIES, flagEmoji } from '/pro/js/register.js';
  import { supabase, fr } from '/pro/js/auth.js';

  // -----------------------------
  // Etat partage entre les etapes
  // -----------------------------
  let currentUser = null;
  let selectedCountry = COUNTRIES[0];
  let selectedTemplate = null;
  let selectedPlace = null;

  // -----------------------------
  // Selecteur de pays
  // -----------------------------
  const countrySelect = document.getElementById('country-select');
  COUNTRIES.forEach((c) => {
    const opt = document.createElement('option');
    opt.value = c.code;
    opt.textContent = flagEmoji(c.flag) + ' ' + c.phonePrefix;
    countrySelect.appendChild(opt);
  });
  countrySelect.addEventListener('change', () => {
    selectedCountry = COUNTRIES.find((c) => c.code === countrySelect.value) || COUNTRIES[0];
  });

  // Le JS est pret : select rempli et handlers attaches, on peut activer la soumission.
  // Empeche toute soumission native prematuree du formulaire pendant le chargement du module.
  const accountSubmitBtn = document.getElementById('account-submit');
  accountSubmitBtn.disabled = false;
  accountSubmitBtn.textContent = 'Creer mon compte et continuer';

  // -----------------------------
  // Navigation entre etapes
  // -----------------------------
  const steps = ['account', 'activity', 'business'];
  const titles = ['Cr&eacute;er votre compte', 'Votre activit&eacute;', 'Votre business'];
  let currentStepIndex = 0;

  function goToStep(index) {
    currentStepIndex = index;
    steps.forEach((s, i) => {
      document.getElementById('step-' + s).style.display = i === index ? 'block' : 'none';
      document.getElementById('seg-' + (i + 1)).classList.toggle('done', i <= index);
    });
    document.getElementById('step-title').textContent = fr(titles[index]);
    document.getElementById('step-sub').textContent = 'Etape ' + (index + 1) + ' sur 3';
    document.getElementById('step-pct').textContent = Math.round(((index + 1) / 3) * 100) + '%';
    document.getElementById('back-btn').href = index === 0 ? '/pro/login' : '#';
    window.scrollTo(0, 0);
  }

  document.getElementById('back-btn').addEventListener('click', (e) => {
    if (currentStepIndex > 0) {
      e.preventDefault();
      goToStep(currentStepIndex - 1);
    }
  });

  function showError(id, message) {
    const el = document.getElementById(id);
    el.textContent = message;
    el.classList.add('visible');
  }
  function hideError(id) {
    document.getElementById(id).classList.remove('visible');
  }

  // -----------------------------
  // Si deja connecte : ne sauter a l'activite QUE si ce compte n'a pas deja
  // un business (sinon on redirige vers le dashboard -- evite de creer un
  // deuxieme business pour le meme compte en revisitant cette page).
  // -----------------------------
  (async () => {
    const { data } = await supabase.auth.getSession();
    if (data && data.session) {
      currentUser = data.session.user;
      const { data: existing } = await supabase.from('businesses').select('id').eq('owner_id', currentUser.id).limit(1);
      if (existing && existing.length > 0) {
        window.location.href = '/pro';
        return;
      }
      goToStep(1);
      initActivityStep();
    }
  })();

  // -----------------------------
  // ETAPE 1 : COMPTE
  // -----------------------------
  document.getElementById('account-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    hideError('account-error');

    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password-confirm').value;

    if (!document.getElementById('accept-terms').checked) {
      showError('account-error', "Veuillez accepter les conditions d'utilisation.");
      return;
    }
    if (password !== passwordConfirm) {
      showError('account-error', 'Les mots de passe ne correspondent pas.');
      return;
    }
    if (password.length < 8) {
      showError('account-error', fr('8 caract&egrave;res minimum pour le mot de passe.'));
      return;
    }

    const submitBtn = document.getElementById('account-submit');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creation...';

    try {
      const profile = {
        firstName: document.getElementById('first-name').value,
        lastName: document.getElementById('last-name').value,
        phone: document.getElementById('phone').value,
        phonePrefix: selectedCountry.phonePrefix,
      };
      const result = await createAccount({
        firstName: profile.firstName,
        lastName: profile.lastName,
        email: document.getElementById('email').value,
        phone: profile.phone,
        phonePrefix: profile.phonePrefix,
        password: password,
      });
      currentUser = result.user;

      // Garde-fou : supabase.auth.signUp() peut reussir "silencieusement" sur
      // un email deja utilise (reutilise le compte existant sans erreur, pour
      // eviter l'enumeration de comptes). Sans cette verification, on créerait
      // un deuxieme business pour un compte qui en a deja un.
      // (SELECT public sur businesses, fonctionne meme sans session active.)
      const { data: existingBiz } = await supabase.from('businesses').select('id').eq('owner_id', result.user.id).limit(1);
      if (existingBiz && existingBiz.length > 0) {
        showError('account-error', fr('Ce compte est d&eacute;j&agrave; associ&eacute; &agrave; un &eacute;tablissement. Connectez-vous plut&ocirc;t.'));
        submitBtn.disabled = false;
        submitBtn.textContent = 'Creer mon compte et continuer';
        return;
      }

      if (result.session) {
        // Edge case : confirmation par code non requise (deja confirme,
        // ou desactivee cote Supabase) -- on saute directement l'etape OTP.
        goToStep(1);
        initActivityStep();
      } else {
        openOtpStep(document.getElementById('email').value.trim(), profile);
      }
    } catch (err) {
      const msg = (err && err.message) || '';
      if (msg.includes('already registered') || msg.includes('already exists')) {
        showError('account-error', fr('Cet email est d&eacute;j&agrave; utilis&eacute;. Connectez-vous.'));
      } else if (msg.includes('password')) {
        showError('account-error', 'Mot de passe trop faible (8 caracteres minimum).');
      } else {
        showError('account-error', 'Une erreur est survenue. Reessayez.');
      }
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Creer mon compte et continuer';
    }
  });

  // -----------------------------
  // ETAPE 1bis : VERIFICATION OTP (reproduit otp_verification_screen.dart)
  // -----------------------------
  let otpEmail = '';
  let otpProfile = null;
  let otpCountdownTimer = null;

  function openOtpStep(email, profile) {
    otpEmail = email;
    otpProfile = profile;
    document.getElementById('otp-email-display').textContent = email;
    document.getElementById('step-account').style.display = 'none';
    document.getElementById('step-otp').style.display = 'block';
    document.querySelectorAll('.otp-digit').forEach((inp) => { inp.value = ''; });
    document.getElementById('otp-error').classList.remove('visible');
    startOtpCountdown();
    document.querySelector('.otp-digit[data-idx="0"]').focus();
  }

  function otpCode() {
    return Array.from(document.querySelectorAll('.otp-digit')).map((i) => i.value).join('');
  }

  document.querySelectorAll('.otp-digit').forEach((input, idx, all) => {
    input.addEventListener('input', () => {
      input.value = input.value.replace(/[^0-9]/g, '').slice(0, 1);
      if (input.value && idx < all.length - 1) all[idx + 1].focus();
      document.getElementById('otp-verify-btn').disabled = otpCode().length < 6;
      if (otpCode().length === 6) verifyOtpCode();
    });
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !input.value && idx > 0) all[idx - 1].focus();
    });
    input.addEventListener('paste', (e) => {
      e.preventDefault();
      const digits = (e.clipboardData.getData('text') || '').replace(/[^0-9]/g, '').slice(0, 6).split('');
      digits.forEach((d, i) => { if (all[i]) all[i].value = d; });
      if (digits.length) all[Math.min(digits.length, all.length) - 1].focus();
      document.getElementById('otp-verify-btn').disabled = otpCode().length < 6;
      if (otpCode().length === 6) verifyOtpCode();
    });
  });

  function startOtpCountdown() {
    let remaining = 60;
    const link = document.getElementById('otp-resend-link');
    link.style.cursor = 'default';
    link.style.color = 'var(--text-light)';
    link.innerHTML = 'Renvoyer dans <span id="otp-countdown">60</span>s';
    clearInterval(otpCountdownTimer);
    otpCountdownTimer = setInterval(() => {
      remaining--;
      const c = document.getElementById('otp-countdown');
      if (c) c.textContent = remaining;
      if (remaining <= 0) {
        clearInterval(otpCountdownTimer);
        otpCountdownTimer = null;
        link.textContent = 'Renvoyer le code';
        link.style.cursor = 'pointer';
        link.style.color = 'var(--primary)';
      }
    }, 1000);
  }

  document.getElementById('otp-resend-link').addEventListener('click', async () => {
    if (otpCountdownTimer) return; // countdown encore actif
    try {
      await resendOtp(otpEmail);
      startOtpCountdown();
    } catch (err) {
      console.error(err);
      showError('otp-error', 'Impossible de renvoyer le code.');
    }
  });

  async function verifyOtpCode() {
    const code = otpCode();
    if (code.length < 6) return;
    hideError('otp-error');
    const btn = document.getElementById('otp-verify-btn');
    btn.disabled = true;
    btn.textContent = fr('V&eacute;rification...');
    try {
      const user = await verifyOtpAndCompleteProfile(otpEmail, code, otpProfile);
      currentUser = user;
      goToStep(1);
      initActivityStep();
    } catch (err) {
      console.error(err);
      showError('otp-error', fr('Code incorrect. R&eacute;essayez.'));
      document.querySelectorAll('.otp-digit').forEach((inp) => { inp.value = ''; });
      document.querySelector('.otp-digit[data-idx="0"]').focus();
    } finally {
      btn.disabled = otpCode().length < 6;
      btn.textContent = fr('V&eacute;rifier mon email');
    }
  }
  document.getElementById('otp-verify-btn').addEventListener('click', verifyOtpCode);

  // -----------------------------
  // ETAPE 2 : ACTIVITE
  // -----------------------------
  let templatesLoaded = false;
  async function initActivityStep() {
    if (templatesLoaded) return;
    templatesLoaded = true;
    const container = document.getElementById('templates-container');
    const templates = await loadTemplates();

    if (!templates.length) {
      container.textContent = fr('Aucune activit&eacute; disponible pour le moment.');
      return;
    }

    container.className = 'template-grid';
    container.innerHTML = '';
    templates.forEach((t) => {
      const card = document.createElement('div');
      card.className = 'template-card';
      card.innerHTML =
        '<div class="template-icon">' + (t.icon || 'x') + '</div>' +
        '<div class="template-name">' + escapeHtml(t.name || '') + '</div>' +
        '<div class="template-desc">' + escapeHtml(t.description || '') + '</div>';
      card.addEventListener('click', () => {
        document.querySelectorAll('.template-card').forEach((c) => c.classList.remove('selected'));
        card.classList.add('selected');
        selectedTemplate = t;
        document.getElementById('activity-submit').disabled = false;
      });
      container.appendChild(card);
    });
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  document.getElementById('activity-submit').addEventListener('click', () => {
    hideError('activity-error');
    if (!selectedTemplate) {
      showError('activity-error', fr('Choisissez une activit&eacute; pour continuer.'));
      return;
    }
    goToStep(2);
  });

  // -----------------------------
  // ETAPE 3 : ADRESSE (Nominatim)
  // -----------------------------
  const addressInput = document.getElementById('address-input');
  const suggestionsBox = document.getElementById('address-suggestions');
  let addressDebounce = null;

  addressInput.addEventListener('input', () => {
    selectedPlace = null;
    document.getElementById('address-confirmed').classList.remove('visible');
    clearTimeout(addressDebounce);
    const query = addressInput.value;
    addressDebounce = setTimeout(async () => {
      const results = await searchAddress(query);
      if (!results.length) {
        suggestionsBox.classList.remove('visible');
        return;
      }
      suggestionsBox.innerHTML = '';
      results.forEach((place) => {
        const item = document.createElement('div');
        item.className = 'address-suggestion';
        item.textContent = place.formatted;
        item.addEventListener('click', () => {
          selectedPlace = place;
          addressInput.value = place.formatted;
          suggestionsBox.classList.remove('visible');
          const confirmed = document.getElementById('address-confirmed');
          confirmed.textContent = 'Adresse confirmee';
          confirmed.classList.add('visible');
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
    if (!navigator.geolocation) {
      showError('business-error', 'La geolocalisation n\'est pas disponible sur ce navigateur.');
      return;
    }
    btn.disabled = true;
    btn.textContent = 'Localisation...';
    navigator.geolocation.getCurrentPosition(async (pos) => {
      const place = await reverseGeocode(pos.coords.latitude, pos.coords.longitude);
      btn.disabled = false;
      btn.textContent = 'Me localiser';
      if (place) {
        selectedPlace = place;
        addressInput.value = place.formatted;
        const confirmed = document.getElementById('address-confirmed');
        confirmed.textContent = fr('Position r&eacute;cup&eacute;r&eacute;e avec succ&egrave;s');
        confirmed.classList.add('visible');
      } else {
        showError('business-error', fr("Impossible de r&eacute;cup&eacute;rer l'adresse depuis votre position."));
      }
    }, () => {
      btn.disabled = false;
      btn.textContent = 'Me localiser';
      showError('business-error', 'Activez la localisation pour utiliser cette fonction.');
    });
  });

  // -----------------------------
  // ETAPE 3 : SOUMISSION FINALE
  // -----------------------------
  document.getElementById('business-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    hideError('business-error');

    if (!currentUser || !selectedTemplate) {
      showError('business-error', fr('Donn&eacute;es manquantes. Recommencez.'));
      return;
    }

    // Verrou explicite contre les soumissions multiples (double-clic, clics
    // repetes par impatience pendant que la creation prend du temps) --
    // c'est ce qui a cree plusieurs business en double pour un meme compte.
    const submitBtn = document.getElementById('business-submit');
    if (submitBtn.disabled) return;
    submitBtn.disabled = true;
    submitBtn.textContent = fr('Cr&eacute;ation en cours...');

    document.getElementById('loading-overlay').classList.add('visible');

    try {
      const businessId = await createBusinessWithTemplate({
        ownerId: currentUser.id,
        businessName: document.getElementById('business-name').value.trim(),
        templateId: selectedTemplate.id,
        phone: document.getElementById('business-phone').value.trim(),
        email: currentUser.email || '',
        place: selectedPlace,
        currencyCode: selectedCountry.currencyCode,
      });

      window.location.href = '/pro';
    } catch (err) {
      document.getElementById('loading-overlay').classList.remove('visible');
      console.error(err);
      showError('business-error', fr("Erreur lors de la cr&eacute;ation de votre espace. R&eacute;essayez."));
      submitBtn.disabled = false;
      submitBtn.textContent = fr('Cr&eacute;er mon espace Dambou');
    }
  });
</script>

</body>
</html>
