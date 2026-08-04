/**
 * DAMBOU - JavaScript Functions
 * Premium SaaS Landing Page
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all modules
    initNavigation();
    initScrollAnimations();
    initSmoothScroll();
    initMobileMenu();
});

/**
 * Navigation
 * Handles fixed navbar behavior and mobile menu
 */
function initNavigation() {
    const navbar = document.getElementById('navbar');
    const navToggle = document.getElementById('navToggle');
    const navMobile = document.getElementById('navMobile');
    
    // Navbar scroll effect
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 50) {
            navbar.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
        } else {
            navbar.style.boxShadow = 'none';
        }
        
        lastScroll = currentScroll;
    });
    
    // Mobile menu toggle
    if (navToggle && navMobile) {
        navToggle.addEventListener('click', () => {
            navMobile.classList.toggle('active');
            navToggle.classList.toggle('active');
            
            // Animate hamburger
            const spans = navToggle.querySelectorAll('span');
            if (navToggle.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });
        
        // Close mobile menu on link click
        const mobileLinks = navMobile.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMobile.classList.remove('active');
                navToggle.classList.remove('active');
                
                // Reset hamburger
                const spans = navToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            });
        });
    }
}

/**
 * Scroll Animations
 * Intersection Observer for fade-in animations
 */
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.animate-on-scroll');
    
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };
    
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    animatedElements.forEach(element => {
        observer.observe(element);
    });
    
    // Stagger animation for grid items
    const gridItems = document.querySelectorAll('.metiers-grid .metier-card, .features-grid .feature-item, .steps-grid .step-card');
    
    gridItems.forEach((item, index) => {
        item.style.transitionDelay = `${index * 100}ms`;
    });
}

/**
 * Smooth Scroll
 * Handles anchor link scrolling
 */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                
                if (target) {
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

/**
 * Mobile Menu
 * Additional mobile menu functionality
 */
function initMobileMenu() {
    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        const navMobile = document.getElementById('navMobile');
        const navToggle = document.getElementById('navToggle');
        
        if (navMobile && navToggle && 
            !navMobile.contains(e.target) && 
            !navToggle.contains(e.target)) {
            navMobile.classList.remove('active');
            navToggle.classList.remove('active');
            
            // Reset hamburger
            const spans = navToggle.querySelectorAll('span');
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        }
    });
}

/**
 * Lazy Loading Images
 * Progressive image loading for performance
 */
function initLazyLoading() {
    if ('loading' in HTMLImageElement.prototype) {
        const images = document.querySelectorAll('img[loading="lazy"]');
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    } else {
        // Fallback for browsers that don't support lazy loading
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
        document.body.appendChild(script);
    }
}

/**
 * Performance Monitoring
 * Track page load performance
 */
function trackPerformance() {
    if (window.performance) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                const perfData = window.performance.timing;
                const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
                
                console.log(`Page Load Time: ${pageLoadTime}ms`);
                
                // Log to analytics if needed
                // analytics.track('Page Load', { time: pageLoadTime });
            }, 0);
        });
    }
}

/**
 * Form Validation (if needed)
 * Basic form validation utilities
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
        
        if (input.type === 'email' && !validateEmail(input.value)) {
            isValid = false;
            input.classList.add('error');
        }
    });
    
    return isValid;
}

/**
 * Counter Animation
 * Animate numbers when they come into view
 */
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
}

/**
 * Initialize counters when visible
 */
function initCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.textContent);
                if (!isNaN(target)) {
                    animateCounter(entry.target, target);
                }
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
}

/**
 * Error Handling
 * Global error handler
 */
window.addEventListener('error', (e) => {
    console.error('Global error:', e.error);
    // Send to error tracking service if needed
});

/**
 * Service Worker Registration (PWA ready)
 */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // Uncomment to enable PWA
        // navigator.serviceWorker.register('/sw.js')
        //     .then(registration => {
        //         console.log('SW registered:', registration);
        //     })
        //     .catch(error => {
        //         console.log('SW registration failed:', error);
        //     });
    });
}

/**
 * Console Branding
 */
console.log('%c🚀 DAMBOU', 'font-size: 24px; font-weight: bold; color: #12C7B7;');
console.log('%cPremium SaaS Platform', 'font-size: 14px; color: #6B7280;');
console.log('%cBuilt with ❤️ for professionals', 'font-size: 12px; color: #9CA3AF;');