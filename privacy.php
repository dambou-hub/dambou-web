<?php
// dambou.fr/privacy — Politique de confidentialité
// Uploader dans public_html/privacy.php sur Hostinger
// Accessible via https://dambou.fr/privacy
$last_updated = "2 juin 2026";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Politique de confidentialité — DAMBOU</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f8f9fa;
      color: #2c2c2c;
      line-height: 1.7;
    }
    header {
      background: #00BFA5;
      padding: 20px 24px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    header a { text-decoration: none; }
    header .logo {
      font-size: 22px;
      font-weight: 800;
      color: #fff;
      letter-spacing: 1px;
    }
    header .emoji { font-size: 26px; }
    .container {
      max-width: 760px;
      margin: 40px auto;
      background: #fff;
      border-radius: 12px;
      padding: 40px 48px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    }
    h1 { font-size: 26px; color: #00BFA5; margin-bottom: 8px; }
    .updated { font-size: 13px; color: #888; margin-bottom: 32px; }
    h2 { font-size: 17px; font-weight: 700; color: #1a1a1a; margin: 32px 0 10px; }
    p { margin-bottom: 14px; font-size: 15px; }
    ul { margin: 10px 0 14px 22px; font-size: 15px; }
    ul li { margin-bottom: 6px; }
    a { color: #00BFA5; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .highlight {
      background: #f0fdf9;
      border-left: 4px solid #00BFA5;
      padding: 14px 18px;
      border-radius: 6px;
      margin: 20px 0;
      font-size: 14px;
    }
    footer {
      text-align: center;
      padding: 28px;
      font-size: 13px;
      color: #999;
    }
    @media (max-width: 600px) {
      .container { margin: 16px; padding: 24px 20px; }
    }
  </style>
</head>
<body>

<header>
  <a href="https://dambou.fr">
    <span class="logo">🥟 DAMBOU</span>
  </a>
</header>

<div class="container">
  <h1>Politique de confidentialité</h1>
  <p class="updated">Dernière mise à jour : <?= $last_updated ?></p>

  <div class="highlight">
    Cette politique décrit comment <strong>DAMBOU</strong> collecte, utilise et protège vos données personnelles lorsque vous utilisez notre application mobile ou notre site web.
  </div>

  <h2>1. Qui sommes-nous ?</h2>
  <p>
    DAMBOU est une application mobile éditée par Jean Orlandi.<br>
    Contact : <a href="mailto:contact@dambou.fr">contact@dambou.fr</a><br>
    Site web : <a href="https://dambou.fr">https://dambou.fr</a>
  </p>

  <h2>2. Données collectées</h2>
  <p>Lors de la création de votre compte et de l'utilisation de l'application, nous collectons les données suivantes :</p>
  <ul>
    <li><strong>Données d'identité :</strong> prénom, nom</li>
    <li><strong>Données de contact :</strong> adresse e-mail, numéro de téléphone</li>
    <li><strong>Données de localisation :</strong> position géographique (uniquement si vous l'autorisez, pour trouver les professionnels proches de vous)</li>
    <li><strong>Données de paiement :</strong> informations de carte bancaire (traitées exclusivement par Stripe — nous n'y avons pas accès)</li>
    <li><strong>Données d'utilisation :</strong> commandes passées, réservations, points de fidélité, transactions</li>
    <li><strong>Token de notification :</strong> identifiant technique (FCM token) permettant l'envoi de notifications push</li>
  </ul>

  <h2>3. Finalité du traitement</h2>
  <p>Vos données sont utilisées pour :</p>
  <ul>
    <li>Créer et gérer votre compte utilisateur</li>
    <li>Permettre la mise en relation entre clients et professionnels</li>
    <li>Traiter vos commandes et réservations</li>
    <li>Envoyer des notifications push (nouvelles commandes, statut de commande, etc.)</li>
    <li>Gérer votre programme de fidélité</li>
    <li>Traiter les paiements en ligne via Stripe</li>
    <li>Améliorer le fonctionnement de l'application</li>
    <li>Envoyer des communications liées à votre compte (factures, confirmations)</li>
  </ul>

  <h2>4. Base légale du traitement</h2>
  <p>Le traitement de vos données repose sur :</p>
  <ul>
    <li>L'<strong>exécution du contrat</strong> (utilisation de l'application)</li>
    <li>Votre <strong>consentement</strong> pour les notifications push et la localisation</li>
    <li>Notre <strong>intérêt légitime</strong> pour améliorer nos services</li>
  </ul>

  <h2>5. Prestataires tiers</h2>
  <p>Vos données peuvent être transmises aux prestataires suivants, dans les limites strictement nécessaires à leur mission :</p>
  <ul>
    <li><strong>Supabase</strong> (Supabase Inc., États-Unis) — hébergement de la base de données et authentification. <a href="https://supabase.com/privacy" target="_blank">Politique de confidentialité Supabase</a></li>
    <li><strong>Firebase / Google</strong> (Google LLC, États-Unis) — notifications push (FCM). <a href="https://firebase.google.com/support/privacy" target="_blank">Politique de confidentialité Firebase</a></li>
    <li><strong>Stripe</strong> (Stripe Inc., États-Unis) — traitement des paiements en ligne. Stripe est certifié PCI-DSS. <a href="https://stripe.com/fr/privacy" target="_blank">Politique de confidentialité Stripe</a></li>
    <li><strong>Resend</strong> — envoi d'e-mails transactionnels (factures, confirmations). <a href="https://resend.com/privacy" target="_blank">Politique de confidentialité Resend</a></li>
  </ul>
  <p>Ces prestataires sont contractuellement tenus de protéger vos données et de ne pas les utiliser à d'autres fins.</p>

  <h2>6. Transfert hors Union européenne</h2>
  <p>Certains prestataires (Supabase, Firebase, Stripe, Resend) sont basés aux États-Unis. Ces transferts sont encadrés par des clauses contractuelles types (CCT) conformes au RGPD ou des mécanismes équivalents.</p>

  <h2>7. Durée de conservation</h2>
  <ul>
    <li><strong>Données de compte actif :</strong> conservées pendant toute la durée de votre utilisation de l'application</li>
    <li><strong>Données de compte supprimé :</strong> effacées dans un délai de 30 jours après suppression du compte</li>
    <li><strong>Données de transactions et factures :</strong> conservées 10 ans conformément aux obligations comptables légales</li>
  </ul>

  <h2>8. Vos droits</h2>
  <p>Conformément au RGPD, vous disposez des droits suivants :</p>
  <ul>
    <li><strong>Droit d'accès :</strong> obtenir une copie de vos données personnelles</li>
    <li><strong>Droit de rectification :</strong> corriger des données inexactes</li>
    <li><strong>Droit à l'effacement :</strong> demander la suppression de votre compte et de vos données</li>
    <li><strong>Droit à la portabilité :</strong> recevoir vos données dans un format structuré</li>
    <li><strong>Droit d'opposition :</strong> vous opposer à certains traitements</li>
    <li><strong>Droit de retrait du consentement :</strong> à tout moment pour les notifications push (via les réglages de votre téléphone) et la localisation</li>
  </ul>
  <p>Pour exercer ces droits, contactez-nous à : <a href="mailto:contact@dambou.fr">contact@dambou.fr</a></p>
  <p>Vous avez également le droit d'introduire une réclamation auprès de la <strong>CNIL</strong> (<a href="https://www.cnil.fr" target="_blank">www.cnil.fr</a>).</p>

  <h2>9. Suppression du compte</h2>
  <p>Vous pouvez demander la suppression complète de votre compte et de vos données en envoyant un e-mail à <a href="mailto:contact@dambou.fr">contact@dambou.fr</a> avec l'objet "Suppression de compte". Votre demande sera traitée dans un délai de 30 jours.</p>

  <h2>10. Sécurité</h2>
  <p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données : chiffrement des communications (HTTPS/TLS), contrôle d'accès par authentification, isolation des données par utilisateur (Row Level Security sur la base de données).</p>

  <h2>11. Cookies</h2>
  <p>L'application mobile DAMBOU n'utilise pas de cookies. Le site web dambou.fr peut utiliser des cookies techniques strictement nécessaires à son fonctionnement (sans dépôt de cookies tiers de tracking).</p>

  <h2>12. Modifications</h2>
  <p>Nous nous réservons le droit de modifier cette politique à tout moment. En cas de modification substantielle, vous serez informé par notification dans l'application ou par e-mail.</p>

  <h2>13. Contact</h2>
  <p>Pour toute question relative à cette politique ou au traitement de vos données :<br>
  📧 <a href="mailto:contact@dambou.fr">contact@dambou.fr</a><br>
  🌐 <a href="https://dambou.fr">https://dambou.fr</a>
  </p>
</div>

<footer>
  &copy; <?= date('Y') ?> DAMBOU — <a href="https://dambou.fr/cgu">CGU</a> · <a href="https://dambou.fr/privacy">Confidentialité</a>
</footer>

</body>
</html>
