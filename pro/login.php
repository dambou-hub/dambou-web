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
<title>Connexion Pro - Dambou</title>
<meta name="theme-color" content="#00BFA5">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #f5f7fa;
    color: #1a1a2e;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
  }
  .card {
    background: #ffffff;
    border-radius: 16px;
    padding: 40px 32px;
    width: 100%;
    max-width: 380px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    border: 1px solid #e8ecf0;
  }
  .logo { text-align: center; margin-bottom: 8px; font-size: 40px; }
  h1 { text-align: center; font-size: 22px; font-weight: 800; margin-bottom: 4px; color: #1a1a2e; }
  .subtitle { text-align: center; font-size: 14px; color: #666666; margin-bottom: 28px; }
  label { display: block; font-size: 13px; font-weight: 600; color: #1a1a2e; margin-bottom: 6px; margin-top: 16px; }
  input {
    width: 100%;
    padding: 13px 14px;
    border: 1px solid #e8ecf0;
    border-radius: 12px;
    font-size: 15px;
    font-family: inherit;
    outline: none;
    transition: border-color 0.15s;
  }
  input:focus { border-color: #00BFA5; }
  button {
    width: 100%;
    margin-top: 24px;
    padding: 14px;
    background: #00BFA5;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.15s;
  }
  button:hover { background: #008f7a; }
  button:disabled { background: #a0d9cf; cursor: not-allowed; }
  .error-msg {
    display: none;
    background: #fdecea;
    color: #dc2626;
    font-size: 13px;
    padding: 10px 12px;
    border-radius: 10px;
    margin-top: 16px;
  }
  .error-msg.visible { display: block; }
  .back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: #666666;
    text-decoration: none;
  }
  .back-link:hover { color: #00BFA5; }
</style>
</head>
<body>
  <div class="card">
    <div class="logo">&#127855;</div>
    <h1>Espace Pro</h1>
    <p class="subtitle">Connectez-vous pour gerer votre activite</p>

    <form id="login-form">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required autocomplete="email">

      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" required autocomplete="current-password">

      <div class="error-msg" id="error-msg"></div>

      <button type="submit" id="submit-btn">Se connecter</button>
    </form>

    <a class="back-link" href="/">&larr; Retour a l'accueil</a>
  </div>

  <script type="module">
    import { supabase, getBusinessForUser } from '/pro/js/auth.js';

    const form = document.getElementById('login-form');
    const errorMsg = document.getElementById('error-msg');
    const submitBtn = document.getElementById('submit-btn');

    // Si deja connecte avec un business valide, on saute direct au dashboard.
    (async () => {
      const { data } = await supabase.auth.getSession();
      if (data && data.session) {
        window.location.href = '/pro';
      }
    })();

    function showError(message) {
      errorMsg.textContent = message;
      errorMsg.classList.add('visible');
    }

    function hideError() {
      errorMsg.classList.remove('visible');
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      hideError();
      submitBtn.disabled = true;
      submitBtn.textContent = 'Connexion...';

      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;

      const { data, error } = await supabase.auth.signInWithPassword({ email, password });

      if (error) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Se connecter';
        showError('Email ou mot de passe incorrect.');
        return;
      }

      const user = data.user;
      const business = await getBusinessForUser(user.id);

      if (!business) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Se connecter';
        showError("Aucun etablissement associe a ce compte. Utilisez l'application mobile Dambou pour creer votre compte pro.");
        await supabase.auth.signOut();
        return;
      }

      window.location.href = '/pro';
    });
  </script>
</body>
</html>
