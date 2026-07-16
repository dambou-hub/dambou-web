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
  body {
    font-family: 'Inter', -apple-system, sans-serif;
    background: var(--background);
    color: var(--text-dark);
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
    border: 1px solid var(--card-border);
  }
  .logo { text-align: center; margin-bottom: 12px; }
  .logo img { height: 40px; width: auto; }
  h1 { text-align: center; font-size: 20px; font-weight: 700; margin-bottom: 4px; color: var(--text-dark); }
  .subtitle { text-align: center; font-size: 14px; color: var(--text-medium); margin-bottom: 28px; }
  label { display: block; font-size: 13px; font-weight: 600; color: var(--text-dark); margin-bottom: 6px; margin-top: 16px; }
  input {
    width: 100%;
    padding: 13px 14px;
    border: 1px solid var(--card-border);
    border-radius: 12px;
    font-size: 15px;
    font-family: inherit;
    outline: none;
    transition: border-color 0.15s;
  }
  input:focus { border-color: var(--primary); border-width: 2px; padding: 12px 13px; }
  button {
    width: 100%;
    margin-top: 24px;
    padding: 14px;
    background: var(--primary);
    color: #ffffff;
    border: none;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.15s;
  }
  button:hover { background: var(--primary-dark); }
  button:disabled { background: var(--text-light); cursor: not-allowed; }
  .error-msg {
    display: none;
    background: rgba(229,62,62,0.08);
    color: var(--error);
    font-size: 13px;
    padding: 10px 12px;
    border-radius: 8px;
    margin-top: 16px;
  }
  .error-msg.visible { display: block; }
  .back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: var(--text-medium);
    text-decoration: none;
  }
  .back-link:hover { color: var(--primary); }
</style>
</head>
<body>
  <div class="card">
    <div class="logo"><img src="/assets/icon.png" alt="Dambou"></div>
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

    <a class="back-link" href="/pro/inscription">Pas encore de compte ? Creer un compte</a>
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
