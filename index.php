<?php
$uri = $_SERVER['REQUEST_URI'];
$uriPath = parse_url($uri, PHP_URL_PATH);
$rawParts = trim($uriPath, '/') === '' ? [] : explode('/', trim($uriPath, '/'));
$parts = array_map('strtolower', $rawParts);
if (!empty($parts)) {
    $lastIdx = count($parts) - 1;
    // Retirer une eventuelle extension .php tapee par l'utilisateur
    $parts[$lastIdx] = preg_replace('/\.php$/', '', $parts[$lastIdx]);
}
$fullPath = implode('/', $parts);

$slug = '';
if (count($parts) >= 1 && !empty($parts[0])) {
    $slug = preg_replace('/[^a-z0-9\-]/', '', $parts[0]);
}

// Pages reservees (servies directement, pas traitees comme slug de business)
// Comparaison sur le chemin complet, donc les sous-chemins (ex: pro/login) fonctionnent.
$reservedPages = [
    'privacy'             => 'privacy.php',
    'cgu'                 => 'cgu.php',
    'abonnement'          => 'abonnement.php',
    'abonnement/success'  => 'abonnement/success.php',
    'mentions'            => 'mentions.php',
    'admin'               => 'admin.html',
    'pro'                 => 'pro/dashboard.php',
    'pro/login'           => 'pro/login.php',
    'pro/inscription'     => 'pro/inscription.php',
    'pro/planning'        => 'pro/planning.php',
    'pro/caisse'          => 'pro/caisse.php',
    'pro/business'        => 'pro/business.php',
    'pro/modules'         => 'pro/modules.php',
    'pro/stats'           => 'pro/stats.php',
    'pro/reservations'    => 'pro/reservations.php',
    'pro/employees'       => 'pro/employees.php',
    'pro/commandes'       => 'pro/orders.php',
    'pro/catalogue'       => 'pro/catalogue.php',
    'pro/stock'           => 'pro/stock.php',
    'pro/clients'         => 'pro/clients.php',
    'pro/client'          => 'pro/client.php',
    'pro/manual-client'   => 'pro/manual-client.php',
];
if (isset($reservedPages[$fullPath])) {
    $pageFile = __DIR__ . '/' . $reservedPages[$fullPath];
    if (file_exists($pageFile)) {
        include $pageFile;
        exit;
    }
}

// Chemins /pro/... non reconnus ci-dessus : ne jamais les traiter comme slug business
if (!empty($parts) && $parts[0] === 'pro') {
    http_response_code(404);
    die('Page introuvable.');
}

if (!empty($slug) && count($parts) === 1) {
    $file = __DIR__ . '/pro/page.php';
    if (!file_exists($file)) {
        die("Fichier introuvable : " . $file);
    }
    include $file;
    exit;
}

// Page d'accueil
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dambou - La plateforme tout-en-un pour gérer votre activité. Réservations, commandes, caisse, IA et plus encore.">
    <meta name="keywords" content="logiciel gestion, réservation, caisse, IA, food truck, beauté, artisan, formation">
    <meta name="author" content="Dambou">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Dambou - Le logiciel qui s'adapte à votre métier">
    <meta property="og:description" content="Une seule plateforme pour toutes vos activités. Réservations, commandes, caisse, statistiques et IA intégrée.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://dambou.fr">
    <meta property="og:image" content="https://dambou.fr/assets/og-image.jpg">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Dambou - Le logiciel qui s'adapte à votre métier">
    <meta name="twitter:description" content="Une seule plateforme pour toutes vos activités.">
    
    <title>Dambou - Le logiciel qui s'adapte à votre métier</title>
    
    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="/style.css">
    
    <!-- Schema.org -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Dambou",
        "description": "Plateforme de gestion tout-en-un pour professionnels",
        "applicationCategory": "BusinessApplication",
        "offers": {
            "@type": "Offer",
            "price": "29",
            "priceCurrency": "EUR"
        }
    }
    </script>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav" id="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <a href="#" class="nav-logo">
                    <img src="assets/logos/logo.png" alt="Dambou" class="logo-image">
                </a>
            </div>
            
            <div class="nav-center">
                <a href="#fonctionnalites" class="nav-link">Fonctionnalités</a>
                <a href="#metiers" class="nav-link">Métiers</a>
                <a href="#ia" class="nav-link">Intelligence Artificielle</a>
                <a href="#tarifs" class="nav-link">Tarifs</a>
                <a href="#tarifs" class="nav-link">Télécharger</a>
            </div>
            
            <div class="nav-right">
                <a href="/pro/login" class="nav-link nav-login">Connexion</a>
                <a href="/abonnement" class="nav-btn-primary">Essayer gratuitement</a>
                <button class="nav-toggle" id="navToggle" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="nav-mobile" id="navMobile">
            <a href="#fonctionnalites" class="nav-mobile-link">Fonctionnalités</a>
            <a href="#metiers" class="nav-mobile-link">Métiers</a>
            <a href="#ia" class="nav-mobile-link">Intelligence Artificielle</a>
            <a href="#tarifs" class="nav-mobile-link">Tarifs</a>
            <a href="#tarifs" class="nav-mobile-link">Télécharger</a>
            <a href="/pro/login" class="nav-mobile-link">Connexion</a>
            <a href="/abonnement" class="nav-mobile-btn">Essayer gratuitement</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <span class="badge-icon">&#127873;</span>
                        <span>2 MOIS GRATUITS &bull; SANS ENGAGEMENT</span>
                    </div>
                    
                    <h1 class="hero-title">
                        Le logiciel qui<br>
                        <span class="text-gradient">s'adapte à votre métier.</span>
                    </h1>
                    
                    <p class="hero-subtitle">
                        Food Truck &bull; Beauté &bull; Artisan &bull; Thérapeute &bull; Formation &bull; Bien-être
                    </p>
                    
                    <p class="hero-text">
                        Gérez vos rendez-vous, commandes, paiements, caisse, fidélité, équipe et bien plus encore depuis une seule plateforme.
                    </p>
                    
                    <p class="hero-highlight">
                        Une seule plateforme. Tous vos outils. Tous vos métiers.
                    </p>
                    
                    <div class="hero-buttons">
                        <a href="/abonnement" class="btn btn-primary btn-large">
                            Commencer gratuitement pendant 2 mois
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                        <a href="#fonctionnalites" class="btn btn-secondary btn-large">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="2"/>
                                <path d="M10 6V14M6 10H14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Découvrir Dambou
                        </a>
                    </div>
                    
                    <div class="hero-features">
                        <div class="hero-feature">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18Z" stroke="#22C55E" stroke-width="2"/>
                                <path d="M6 10L9 13L14 7" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Sans carte bancaire</span>
                        </div>
                        <div class="hero-feature">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18Z" stroke="#22C55E" stroke-width="2"/>
                                <path d="M6 10L9 13L14 7" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Sans engagement</span>
                        </div>
                        <div class="hero-feature">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M10 18C14.4183 18 18 14.4183 18 10C18 5.58172 14.4183 2 10 2C5.58172 2 2 5.58172 2 10C2 14.4183 5.58172 18 10 18Z" stroke="#22C55E" stroke-width="2"/>
                                <path d="M6 10L9 13L14 7" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Annulable à tout moment</span>
                        </div>
                    </div>
                    
                    <div class="hero-social">
                        <div class="hero-avatars">
                            <img src="/assets/logos/avatar1.jpg" alt="Utilisateur" class="avatar">
                            <img src="/assets/logos/avatar2.jpg" alt="Utilisateur" class="avatar">
                            <img src="/assets/logos/avatar3.jpg" alt="Utilisateur" class="avatar">
                            <img src="/assets/logos/avatar4.jpg" alt="Utilisateur" class="avatar">
                            <div class="avatar-more">+1200</div>
                        </div>
                        <div class="hero-rating">
                            <div class="stars">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="#F59E0B">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="#F59E0B">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="#F59E0B">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="#F59E0B">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="#F59E0B">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                            <p>Déjà adopté par plus de<br><strong>1 200 professionnels</strong></p>
                        </div>
                    </div>
                    
                    <div class="hero-logos">
                        <span class="logo-item">Google Play</span>
                        <span class="logo-item">App Store</span>
                        <span class="logo-item">PCI DSS</span>
                        <span class="logo-item">Stripe</span>
                        <span class="logo-item">SSL</span>
                    </div>
                </div>
                
                <div class="hero-image">
                    <div class="phone-mockup">
                        <img src="/assets/screens/hero-phone.png" alt="Interface Dambou" class="phone-image">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats section-padding">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card animate-on-scroll">
                    <div class="stat-icon"></div>
                    <div class="stat-number">+12</div>
                    <div class="stat-label">métiers</div>
                </div>
                <div class="stat-card animate-on-scroll">
                    <div class="stat-icon">&#127919;</div>
                    <div class="stat-number">1</div>
                    <div class="stat-label">plateforme unique</div>
                </div>
                <div class="stat-card animate-on-scroll">
                    <div class="stat-icon">&#129302;</div>
                    <div class="stat-number">IA</div>
                    <div class="stat-label">intégrée</div>
                </div>
                <div class="stat-card animate-on-scroll">
                    <div class="stat-icon">&#128176;</div>
                    <div class="stat-number">29&euro;</div>
                    <div class="stat-label">/mois</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Métiers Section -->
    <section class="metiers section-padding bg-light" id="metiers">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Choisissez <span class="text-gradient">votre métier</span></h2>
            </div>
            
            <div class="metiers-grid">
                <div class="metier-card animate-on-scroll">
                    <div class="metier-image">
                        <img src="/assets/metiers/foodtruck.jpg" alt="Food Truck" loading="lazy">
                    </div>
                    <div class="metier-content">
                        <h3>Food Truck</h3>
                        <p>Gérez vos commandes et votre planning</p>
                        <a href="#" class="metier-link">En savoir plus &rarr;</a>
                    </div>
                </div>
                
                <div class="metier-card animate-on-scroll">
                    <div class="metier-image">
                        <img src="/assets/metiers/beauty.jpg" alt="Beauté" loading="lazy">
                    </div>
                    <div class="metier-content">
                        <h3>Beauté</h3>
                        <p>Réservations et gestion client simplifiées</p>
                        <a href="#" class="metier-link">En savoir plus &rarr;</a>
                    </div>
                </div>
                
                <div class="metier-card animate-on-scroll">
                    <div class="metier-image">
                        <img src="/assets/metiers/massage.jpg" alt="Massage Bien-être" loading="lazy">
                    </div>
                    <div class="metier-content">
                        <h3>Massage Bien-être</h3>
                        <p>Planning et paiements en ligne</p>
                        <a href="#" class="metier-link">En savoir plus &rarr;</a>
                    </div>
                </div>
                
                <div class="metier-card animate-on-scroll">
                    <div class="metier-image">
                        <img src="/assets/metiers/therapeute.jpg" alt="Thérapeute" loading="lazy">
                    </div>
                    <div class="metier-content">
                        <h3>Thérapeute</h3>
                        <p>Gestion des rendez-vous et dossiers patients</p>
                        <a href="#" class="metier-link">En savoir plus &rarr;</a>
                    </div>
                </div>
                
                <div class="metier-card animate-on-scroll">
                    <div class="metier-image">
                        <img src="/assets/metiers/formation.jpg" alt="Formation" loading="lazy">
                    </div>
                    <div class="metier-content">
                        <h3>Formation</h3>
                        <p>Gestion des sessions et inscriptions</p>
                        <a href="#" class="metier-link">En savoir plus &rarr;</a>
                    </div>
                </div>
                
                <div class="metier-card animate-on-scroll">
                    <div class="metier-image">
                        <img src="/assets/metiers/artisan.jpg" alt="Artisan" loading="lazy">
                    </div>
                    <div class="metier-content">
                        <h3>Artisan</h3>
                        <p>Devis, factures et suivi de chantier</p>
                        <a href="#" class="metier-link">En savoir plus &rarr;</a>
                    </div>
                </div>
                
                <div class="metier-card animate-on-scroll">
                    <div class="metier-image">
                        <img src="/assets/metiers/restaurant.jpg" alt="Petite restauration" loading="lazy">
                    </div>
                    <div class="metier-content">
                        <h3>Petite restauration</h3>
                        <p>Caisse, stocks et commandes</p>
                        <a href="#" class="metier-link">En savoir plus &rarr;</a>
                    </div>
                </div>
                
                <div class="metier-card animate-on-scroll">
                    <div class="metier-image">
                        <img src="/assets/metiers/coach.jpg" alt="Coach" loading="lazy">
                    </div>
                    <div class="metier-content">
                        <h3>Coach</h3>
                        <p>Suivi des clients et planning</p>
                        <a href="#" class="metier-link">En savoir plus &rarr;</a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-8">
                <a href="#" class="btn btn-secondary">Voir tous les métiers &rarr;</a>
            </div>
        </div>
    </section>

    <!-- Why Section -->
    <section class="why section-padding">
        <div class="container">
            <div class="why-grid">
                <div class="why-content">
                    <h2 class="section-title">Pourquoi les meilleurs outils seraient-ils réservés aux grandes entreprises ?</h2>
                    <p class="why-text">
                        Après plus de 30 ans à concevoir des logiciels métier, une conviction s'est imposée : chaque professionnel mérite des outils puissants, simples et accessibles.
                    </p>
                    <p class="why-text">
                        C'est de cette idée qu'est née Dambou.
                    </p>
                </div>
                
                <div class="why-cards">
                    <div class="why-card animate-on-scroll">
                        <div class="why-card-icon">&#128161;</div>
                        <h3>Pensé pour vous</h3>
                        <p>Conçu à partir des besoins réels des professionnels de terrain.</p>
                    </div>
                    
                    <div class="why-card animate-on-scroll">
                        <div class="why-card-icon">&#9889;</div>
                        <h3>Simple et complet</h3>
                        <p>Toutes les fonctionnalités dont vous avez besoin, sans superflu.</p>
                    </div>
                    
                    <div class="why-card animate-on-scroll">
                        <div class="why-card-icon">&#128275;</div>
                        <h3>Accessible</h3>
                        <p>Une tarification claire et juste, adaptée à votre activité.</p>
                    </div>
                    
                    <div class="why-card animate-on-scroll">
                        <div class="why-card-icon"></div>
                        <h3>Évolutif</h3>
                        <p>Dambou grandit avec vous et s'adapte à vos besoins.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features section-padding bg-light" id="fonctionnalites">
        <div class="container">
            <div class="section-header text-center mb-12">
                <h2 class="section-title">Toutes les fonctionnalités pour <span class="text-gradient">développer votre activité</span></h2>
            </div>
            
            <div class="features-grid">
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon">&#128197;</div>
                    <h3>Agenda et réservations</h3>
                    <p>Planifiez vos rendez-vous en toute simplicité</p>
                </div>
                
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon">&#128230;</div>
                    <h3>Commandes et livraisons</h3>
                    <p>Gérez vos commandes du début à la fin</p>
                </div>
                
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon">&#128179;</div>
                    <h3>Paiement en ligne et sur place</h3>
                    <p>Acceptez tous les moyens de paiement</p>
                </div>
                
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon"></div>
                    <h3>Caisse enregistreuse</h3>
                    <p>Une caisse moderne et intuitive</p>
                </div>
                
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon">&#128218;</div>
                    <h3>Catalogue produits / services</h3>
                    <p>Organisez votre offre facilement</p>
                </div>
                
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon">&#128202;</div>
                    <h3>Gestion des stocks et inventaire</h3>
                    <p>Ne soyez plus jamais en rupture</p>
                </div>
                
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon">&#127873;</div>
                    <h3>Fidélité et cartes cadeaux</h3>
                    <p>Fidélisez vos clients efficacement</p>
                </div>
                
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon"></div>
                    <h3>Clients et CRM</h3>
                    <p>Conservez l'historique de vos clients</p>
                </div>
                
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon">&#128200;</div>
                    <h3>Statistiques et rapports</h3>
                    <p>Analysez votre activité en temps réel</p>
                </div>
                
                <div class="feature-item animate-on-scroll">
                    <div class="feature-icon">&#129302;</div>
                    <h3>Assistant IA intégré</h3>
                    <p>Une intelligence artificielle à votre service</p>
                </div>
            </div>
            
            <div class="text-center mt-8">
                <a href="#" class="btn btn-secondary">Découvrir toutes les fonctionnalités &rarr;</a>
            </div>
        </div>
    </section>

    <!-- AI Section -->
    <section class="ai-section section-padding" id="ia">
        <div class="container">
            <div class="ai-grid">
                <div class="ai-content">
                    <div class="ai-badge">ASSISTANT IA</div>
                    <h2 class="section-title">Une intelligence artificielle qui connaît <span class="text-gradient">votre activité</span></h2>
                    <p class="ai-text">
                        Posez vos questions naturellement. Dambou IA vous répond instantanément avec les informations de votre entreprise.
                    </p>
                    <a href="#" class="btn btn-primary">En savoir plus sur Dambou IA &rarr;</a>
                </div>
                
                <div class="ai-image">
                    <div class="phone-ai">
                        <img src="/assets/screens/ai-phone.png" alt="Assistant IA Dambou" loading="lazy">
                    </div>
                    <div class="ai-stats">
                        <div class="ai-stat">
                            <div class="stat-value">8h 30</div>
                            <div class="stat-label">Temps gagné par semaine</div>
                        </div>
                        <div class="ai-stat">
                            <div class="stat-value">24/7</div>
                            <div class="stat-label">Réponses instantanées</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Steps Section -->
    <section class="steps section-padding bg-light">
        <div class="container">
            <div class="section-header text-center mb-12">
                <h2 class="section-title">Dambou, c'est <span class="text-gradient">simple comme bonjour</span></h2>
            </div>
            
            <div class="steps-grid">
                <div class="step-card animate-on-scroll">
                    <div class="step-number">01</div>
                    <div class="step-icon">&#128100;</div>
                    <h3>Créez votre compte</h3>
                    <p>Inscription rapide en moins d'une minute.</p>
                </div>
                
                <div class="step-card animate-on-scroll">
                    <div class="step-number">02</div>
                    <div class="step-icon">&#127919;</div>
                    <h3>Choisissez votre métier</h3>
                    <p>Dambou configure les outils adaptés.</p>
                </div>
                
                <div class="step-card animate-on-scroll">
                    <div class="step-number">03</div>
                    <div class="step-icon">&#10133;</div>
                    <h3>Ajoutez votre contenu</h3>
                    <p>Catalogues, horaires, équipes, services...</p>
                </div>
                
                <div class="step-card animate-on-scroll">
                    <div class="step-number">04</div>
                    <div class="step-icon"></div>
                    <h3>Développez votre activité</h3>
                    <p>Recevez des clients, gérez et progressez.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing section-padding" id="tarifs">
        <div class="container">
            <div class="pricing-card animate-on-scroll">
                <div class="pricing-header">
                    <h2 class="pricing-title">Un prix juste. Zéro surprise.</h2>
                    <div class="pricing-price">
                        <span class="price-amount">29</span>
                        <span class="price-currency">&euro;</span>
                        <span class="price-period">/mois</span>
                    </div>
                </div>
                
                <div class="pricing-features">
                    <div class="pricing-feature">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M6 10L9 13L14 7" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>2 mois gratuits</span>
                    </div>
                    <div class="pricing-feature">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M6 10L9 13L14 7" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Sans carte bancaire</span>
                    </div>
                    <div class="pricing-feature">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M6 10L9 13L14 7" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Sans engagement</span>
                    </div>
                    <div class="pricing-feature">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M6 10L9 13L14 7" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Annulable à tout moment</span>
                    </div>
                </div>
                
                <div class="pricing-cta">
                    <a href="/abonnement" class="btn btn-primary btn-large btn-full">
                        Commencer gratuitement pendant 2 mois
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <p class="pricing-note">Rejoignez plus de 1 200 professionnels qui font confiance à Dambou.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <div class="logo-icon">D</div>
                        <span class="logo-text">DAMBOU</span>
                    </div>
                    <p>La plateforme tout-en-un qui s'adapte à votre métier pour vous faire gagner du temps et développer votre activité.</p>
                </div>
                
                <div class="footer-links">
                    <h4>Produit</h4>
                    <ul>
                        <li><a href="#">Fonctionnalités</a></li>
                        <li><a href="#">Tarifs</a></li>
                        <li><a href="#">Dambou IA</a></li>
                        <li><a href="#">Sécurité</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Métiers</h4>
                    <ul>
                        <li><a href="#">Food Truck</a></li>
                        <li><a href="#">Beauté</a></li>
                        <li><a href="#">Massage</a></li>
                        <li><a href="#">Voir tous les métiers</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Ressources</h4>
                    <ul>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Guides</a></li>
                        <li><a href="#">Centre d'aide</a></li>
                        <li><a href="#">Vidéos tutoriels</a></li>
                    </ul>
                </div>
                
                <div class="footer-links">
                    <h4>Entreprise</h4>
                    <ul>
                        <li><a href="#">À propos</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Mentions l&eacute;gales</a></li>
                        <li><a href="/cgu">CGU</a></li>
                    </ul>
                </div>
                
                <div class="footer-social">
                    <h4>Suivez-nous</h4>
                    <div class="social-icons">
                        <a href="#" aria-label="Facebook">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Instagram">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="TikTok">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="LinkedIn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                    <div class="app-stores">
                        <a href="#" class="app-store">
                            <img src="/assets/logos/app-store.png" alt="Télécharger sur l'App Store">
                        </a>
                        <a href="#" class="app-store">
                            <img src="/assets/logos/google-play.png" alt="Disponible sur Google Play">
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 Dambou. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="/script.js"></script>
</body>
</html>
