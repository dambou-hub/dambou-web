<?php
// dambou.fr/cgu — Conditions Générales d'Utilisation
// Uploader dans public_html/cgu.php sur Hostinger
// Accessible via https://dambou.fr/cgu
$last_updated = "2 juin 2026";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Conditions Générales d'Utilisation — DAMBOU</title>
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
    h3 { font-size: 15px; font-weight: 700; color: #333; margin: 20px 0 8px; }
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
    .pricing-box {
      background: #f8f9fa;
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      padding: 20px 24px;
      margin: 16px 0;
    }
    .pricing-box strong { color: #00BFA5; font-size: 22px; }
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
  <h1>Conditions Générales d'Utilisation</h1>
  <p class="updated">Dernière mise à jour : <?= $last_updated ?></p>

  <div class="highlight">
    En téléchargeant ou en utilisant l'application DAMBOU, vous acceptez les présentes Conditions Générales d'Utilisation. Veuillez les lire attentivement.
  </div>

  <h2>1. Présentation du service</h2>
  <p>
    DAMBOU est une application mobile disponible sur iOS (App Store) et Android (Google Play), éditée par Jean-Olivier Salque, entrepreneur individuel.
  </p>
  <p>
    L'application permet aux professionnels indépendants (ci-après "Professionnels") de gérer leurs commandes, réservations, encaissements et fidélité clients, et aux clients finaux (ci-après "Clients") de commander et réserver auprès de ces Professionnels.
  </p>
  <p>Contact : <a href="mailto:contact@dambou.fr">contact@dambou.fr</a> — <a href="https://dambou.fr">https://dambou.fr</a></p>

  <h2>2. Acceptation des conditions</h2>
  <p>L'utilisation de l'application DAMBOU vaut acceptation pleine et entière des présentes CGU. Si vous n'acceptez pas ces conditions, vous ne devez pas utiliser l'application.</p>
  <p>DAMBOU se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés de toute modification substantielle par notification dans l'application ou par e-mail. La poursuite de l'utilisation après modification vaut acceptation des nouvelles conditions.</p>

  <h2>3. Création de compte</h2>
  <p>L'utilisation de DAMBOU nécessite la création d'un compte avec une adresse e-mail et un numéro de téléphone valides.</p>
  <p>Vous êtes responsable de la confidentialité de vos identifiants et de toutes les actions effectuées depuis votre compte. Vous vous engagez à nous informer immédiatement en cas d'utilisation non autorisée de votre compte à l'adresse <a href="mailto:contact@dambou.fr">contact@dambou.fr</a>.</p>
  <p>Un même utilisateur ne peut pas simultanément être titulaire de plusieurs comptes Professionnel actifs. DAMBOU se réserve le droit de suspendre tout compte utilisé de manière abusive.</p>

  <h2>4. Utilisation par les Clients</h2>
  <p>L'accès à l'application DAMBOU en tant que Client est <strong>gratuit</strong>. En tant que Client, vous pouvez :</p>
  <ul>
    <li>Parcourir les pages des Professionnels référencés</li>
    <li>Passer des commandes et effectuer des réservations</li>
    <li>Payer en ligne via Stripe (carte bancaire, Apple Pay)</li>
    <li>Suivre vos commandes en temps réel</li>
    <li>Bénéficier des programmes de fidélité des Professionnels</li>
  </ul>
  <p>Les commandes passées via DAMBOU constituent un contrat entre vous et le Professionnel. DAMBOU agit uniquement comme intermédiaire technique et n'est pas partie à ce contrat.</p>

  <h2>5. Abonnement Professionnel</h2>
  <p>L'accès aux fonctionnalités pro de DAMBOU est soumis à un abonnement.</p>

  <div class="pricing-box">
    <p><strong>29 €</strong> / mois (TTC)</p>
    <p style="margin:6px 0 0; font-size:14px; color:#555;">2 mois d'essai gratuit — sans carte bancaire requise à l'inscription</p>
  </div>

  <h3>5.1 Période d'essai</h3>
  <p>Tout nouveau compte Professionnel bénéficie d'une période d'essai gratuite de <strong>2 mois</strong>, sans saisie de carte bancaire requise. À l'issue de cette période, le paiement de l'abonnement est nécessaire pour continuer à utiliser les fonctionnalités pro.</p>

  <h3>5.2 Facturation</h3>
  <p>Le paiement est mensuel, par carte bancaire via Stripe. La facturation débute à l'issue de la période d'essai. Une facture est envoyée par e-mail chaque mois.</p>

  <h3>5.3 Résiliation</h3>
  <p>Le Professionnel peut résilier son abonnement à tout moment depuis l'application ou en contactant <a href="mailto:contact@dambou.fr">contact@dambou.fr</a>. La résiliation prend effet à la fin de la période mensuelle en cours. Aucun remboursement partiel n'est effectué pour la période restante.</p>
  <p>En cas de résiliation, les données du compte sont conservées 30 jours, puis supprimées définitivement.</p>

  <h3>5.4 Suspension pour impayé</h3>
  <p>En cas d'échec de paiement, DAMBOU se réserve le droit de suspendre l'accès aux fonctionnalités pro après un délai de grâce de 7 jours. Un e-mail de relance est envoyé avant toute suspension.</p>

  <h3>5.5 Modification tarifaire</h3>
  <p>DAMBOU peut modifier le tarif de l'abonnement avec un préavis de 30 jours. Les Professionnels seront informés par e-mail et auront la possibilité de résilier sans frais avant l'entrée en vigueur du nouveau tarif.</p>

  <h2>6. Paiements en ligne (Stripe)</h2>
  <p>Les paiements effectués par les Clients via l'application (carte bancaire, Apple Pay) sont traités par <strong>Stripe</strong>, prestataire de services de paiement agréé. DAMBOU ne stocke aucune donnée bancaire.</p>
  <p>Les fonds collectés pour le compte des Professionnels sont transférés sur leur compte bancaire via Stripe Connect, déduction faite des frais Stripe applicables. DAMBOU n'est pas responsable des délais de virement de Stripe.</p>
  <p>En cas de litige sur un paiement, le Client est invité à contacter directement le Professionnel concerné, puis DAMBOU à <a href="mailto:contact@dambou.fr">contact@dambou.fr</a> si nécessaire.</p>

  <h2>7. Obligations des Professionnels</h2>
  <p>En tant que Professionnel utilisant DAMBOU, vous vous engagez à :</p>
  <ul>
    <li>Fournir des informations exactes sur votre activité, vos produits et services</li>
    <li>Respecter la réglementation applicable à votre activité (normes sanitaires, licences, etc.)</li>
    <li>Honorer les commandes et réservations acceptées via l'application</li>
    <li>Ne pas utiliser l'application à des fins illicites ou frauduleuses</li>
    <li>Informer vos clients de vos conditions de vente et de remboursement</li>
    <li>Respecter la réglementation sur la protection des données personnelles de vos clients (RGPD)</li>
  </ul>

  <h2>8. Obligations des Clients</h2>
  <p>En tant que Client, vous vous engagez à :</p>
  <ul>
    <li>Fournir des informations exactes lors de vos commandes et réservations</li>
    <li>Honorer vos commandes payées en ligne</li>
    <li>Utiliser l'application de bonne foi</li>
    <li>Ne pas tenter de contourner les systèmes de paiement ou de fidélité</li>
  </ul>

  <h2>9. Propriété intellectuelle</h2>
  <p>L'application DAMBOU, son code source, son design, son logo, sa marque et l'ensemble de ses contenus sont la propriété exclusive de Jean-Olivier Salque. Toute reproduction, distribution ou exploitation sans autorisation écrite préalable est strictement interdite.</p>
  <p>Les contenus publiés par les Professionnels (photos, descriptions, noms de produits) restent leur propriété. En les publiant sur DAMBOU, ils accordent à DAMBOU une licence non exclusive d'affichage à des fins de fonctionnement du service.</p>

  <h2>10. Responsabilité</h2>
  <p>DAMBOU est une plateforme de mise en relation. À ce titre :</p>
  <ul>
    <li>DAMBOU n'est pas responsable de la qualité des produits et services fournis par les Professionnels</li>
    <li>DAMBOU n'est pas responsable des litiges entre Clients et Professionnels</li>
    <li>DAMBOU s'efforce d'assurer la disponibilité du service, mais ne peut garantir un accès ininterrompu (maintenance, pannes, cas de force majeure)</li>
    <li>DAMBOU ne peut être tenu responsable des dommages indirects résultant de l'utilisation de l'application</li>
  </ul>

  <h2>11. Suspension et résiliation de compte</h2>
  <p>DAMBOU se réserve le droit de suspendre ou de supprimer tout compte en cas de :</p>
  <ul>
    <li>Violation des présentes CGU</li>
    <li>Utilisation frauduleuse ou abusive de l'application</li>
    <li>Non-paiement de l'abonnement (pour les Professionnels)</li>
    <li>Fausse déclaration lors de l'inscription</li>
  </ul>
  <p>Tout utilisateur peut demander la suppression de son compte à <a href="mailto:contact@dambou.fr">contact@dambou.fr</a>.</p>

  <h2>12. Loi applicable et juridiction</h2>
  <p>Les présentes CGU sont soumises au droit français. En cas de litige, et à défaut de résolution amiable, les tribunaux compétents seront ceux du ressort du domicile de l'éditeur.</p>
  <p>Pour tout litige de consommation, vous pouvez également recourir à une procédure de médiation. Plateforme européenne de règlement en ligne des litiges : <a href="https://ec.europa.eu/consumers/odr" target="_blank">https://ec.europa.eu/consumers/odr</a></p>

  <h2>13. Contact</h2>
  <p>Pour toute question relative aux présentes CGU :<br>
  📧 <a href="mailto:contact@dambou.fr">contact@dambou.fr</a><br>
  🌐 <a href="https://dambou.fr">https://dambou.fr</a>
  </p>
</div>

<footer>
  &copy; <?= date('Y') ?> DAMBOU — <a href="https://dambou.fr/cgu">CGU</a> · <a href="https://dambou.fr/privacy">Confidentialité</a>
</footer>

</body>
</html>
