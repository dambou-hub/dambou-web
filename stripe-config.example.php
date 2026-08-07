<?php
// MODELE -- copier ce fichier en stripe-config.php (sans .example) et y
// mettre les vraies valeurs. stripe-config.php n'est JAMAIS commite
// sur GitHub (voir .gitignore) -- a uploader uniquement sur Hostinger via
// le Gestionnaire de fichiers ou FTP.
return array(
  // Cle secrete API Stripe (Developpeurs > Cles API > Cle secrete)
  'secret_key' => 'sk_live_VOTRE_VRAIE_CLE_ICI',

  // Secret de signature du webhook (Developpeurs > Webhooks > cliquer sur
  // l'endpoint stripe-webhook.php une fois cree > "Signing secret")
  'webhook_secret' => 'whsec_VOTRE_SECRET_ICI',

  // Cle service_role Supabase (Project Settings > API > service_role secret)
  // -- PAS la cle anon utilisee ailleurs sur le site. Cette cle contourne
  // les RLS, necessaire car le webhook n'a pas de session utilisateur
  // authentifiee pour ecrire dans businesses.
  'supabase_service_role' => 'VOTRE_SERVICE_ROLE_KEY_ICI',
);
