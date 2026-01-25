/**
 * CAS Private Care - Animation Utilities
 * JavaScript utilities for scroll animations, scroll progress, and ripple effects
 */

// =============================================
// Scroll Progress Indicator
// =============================================

export function initScrollProgress() {
    // Check if scroll progress element exists
    let scrollProgress = document.querySelector('.scroll-progress');
    
    // Create it if it doesn't exist
    if (!scrollProgress) {
        scrollProgress = document.createElement('div');
        scrollProgress.className = 'scroll-progress';
        document.body.prepend(scrollProgress);
    }
    
    const updateProgress = () => {
        const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (window.scrollY / scrollHeight) * 100;
        scrollProgress.style.width = `${Math.min(scrollPercent, 100)}%`;
    };
    
    // Throttle scroll handler for performance
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(() => {
                updateProgress();
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
    
    // Initial update
    updateProgress();
    
    return scrollProgress;
}

// =============================================
// Scroll-triggered Animations
// =============================================

export function initScrollAnimations(options = {}) {
    const {
        selector = '.animate-on-scroll',
        threshold = 0.1,
        rootMargin = '0px 0px -50px 0px',
        once = true
    } = options;
    
    const elements = document.querySelectorAll(selector);
    
    if (!elements.length) return;
    
    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    if (prefersReducedMotion) {
        // Make all elements visible immediately for users who prefer reduced motion
        elements.forEach(el => {
            el.classList.add('visible');
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
        return;
    }
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                
                if (once) {
                    observer.unobserve(entry.target);
                }
            } else if (!once) {
                entry.target.classList.remove('visible');
            }
        });
    }, { threshold, rootMargin });
    
    elements.forEach(el => observer.observe(el));
    
    return observer;
}

// =============================================
// Staggered Animation Helper
// =============================================

export function initStaggeredAnimation(containerSelector, itemSelector, options = {}) {
    const {
        delay = 100,
        baseDelay = 0,
        animationClass = 'animate-fade-in-up'
    } = options;
    
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    const items = container.querySelectorAll(itemSelector);
    
    items.forEach((item, index) => {
        item.style.animationDelay = `${baseDelay + (index * delay)}ms`;
        item.classList.add(animationClass);
    });
}

// =============================================
// Ripple Effect
// =============================================

export function initRippleEffect(selector = '.btn-animated, .ripple-effect') {
    const elements = document.querySelectorAll(selector);
    
    elements.forEach(element => {
        element.addEventListener('click', function(e) {
            // Don't add ripple if element is disabled
            if (this.disabled) return;
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            this.appendChild(ripple);
            
            // Remove ripple after animation
            ripple.addEventListener('animationend', () => {
                ripple.remove();
            });
        });
    });
}

// =============================================
// Number Counter Animation
// =============================================

export function animateNumber(element, target, options = {}) {
    const {
        duration = 1000,
        prefix = '',
        suffix = '',
        decimals = 0,
        easing = 'easeOutExpo'
    } = options;
    
    const easingFunctions = {
        linear: t => t,
        easeOutExpo: t => t === 1 ? 1 : 1 - Math.pow(2, -10 * t),
        easeOutCubic: t => 1 - Math.pow(1 - t, 3),
        easeInOutQuad: t => t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2
    };
    
    const easeFn = easingFunctions[easing] || easingFunctions.easeOutExpo;
    
    const startValue = 0;
    const startTime = performance.now();
    
    const animate = (currentTime) => {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easedProgress = easeFn(progress);
        
        const currentValue = startValue + (target - startValue) * easedProgress;
        element.textContent = prefix + currentValue.toFixed(decimals) + suffix;
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    };
    
    requestAnimationFrame(animate);
}

// =============================================
// Initialize Number Counters on Scroll
// =============================================

export function initNumberCounters(selector = '[data-count-to]') {
    const elements = document.querySelectorAll(selector);
    
    if (!elements.length) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseFloat(el.dataset.countTo);
                const prefix = el.dataset.prefix || '';
                const suffix = el.dataset.suffix || '';
                const decimals = parseInt(el.dataset.decimals) || 0;
                const duration = parseInt(el.dataset.duration) || 1000;
                
                animateNumber(el, target, { prefix, suffix, decimals, duration });
                
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    
    elements.forEach(el => observer.observe(el));
}

// =============================================
// Parallax Effect
// =============================================

export function initParallax(selector = '.parallax-section') {
    const elements = document.querySelectorAll(selector);
    
    if (!elements.length) return;
    
    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;
    
    let ticking = false;
    
    const updateParallax = () => {
        elements.forEach(element => {
            const speed = parseFloat(element.dataset.parallaxSpeed) || 0.5;
            const rect = element.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
            
            if (isVisible) {
                const yPos = (rect.top - window.innerHeight) * speed;
                element.style.backgroundPositionY = `${yPos}px`;
            }
        });
    };
    
    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(() => {
                updateParallax();
                ticking = false;
            });
            ticking = true;
        }
    }, { passive: true });
    
    // Initial update
    updateParallax();
}

// =============================================
// Smooth Scroll to Anchor
// =============================================

export function initSmoothScroll(offset = 80) {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                e.preventDefault();
                
                const targetPosition = targetElement.getBoundingClientRect().top + window.scrollY - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Update URL without scrolling
                history.pushState(null, null, targetId);
            }
        });
    });
}

// =============================================
// Dark Mode Toggle
// =============================================

export function initDarkModeToggle(toggleSelector = '.dark-mode-toggle') {
    const toggles = document.querySelectorAll(toggleSelector);
    
    // Check for saved preference or system preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    const setTheme = (isDark) => {
        document.documentElement.classList.toggle('dark-mode', isDark);
        document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        
        // Update toggle buttons
        toggles.forEach(toggle => {
            toggle.setAttribute('aria-pressed', isDark);
        });
    };
    
    // Set initial theme
    if (savedTheme) {
        setTheme(savedTheme === 'dark');
    } else if (prefersDark) {
        setTheme(true);
    }
    
    // Add click handlers
    toggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const isDark = !document.documentElement.classList.contains('dark-mode');
            setTheme(isDark);
        });
    });
    
    // Listen for system preference changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            setTheme(e.matches);
        }
    });
}

// =============================================
// Particles Generator
// =============================================

export function initParticles(containerId = 'particles-container', options = {}) {
    const {
        count = 50,
        minSize = 2,
        maxSize = 6,
        minDuration = 10,
        maxDuration = 20,
        colors = ['rgba(255,255,255,0.8)', 'rgba(59,130,246,0.6)', 'rgba(249,115,22,0.6)']
    } = options;
    
    let container = document.getElementById(containerId);
    
    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.className = 'particles-container';
        document.body.appendChild(container);
    }
    
    // Check for reduced motion preference
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        container.style.display = 'none';
        return;
    }
    
    // Create particles
    for (let i = 0; i < count; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        const size = Math.random() * (maxSize - minSize) + minSize;
        const duration = Math.random() * (maxDuration - minDuration) + minDuration;
        const left = Math.random() * 100;
        const delay = Math.random() * duration;
        const drift = (Math.random() - 0.5) * 100;
        const color = colors[Math.floor(Math.random() * colors.length)];
        
        particle.style.cssText = `
            width: ${size}px;
            height: ${size}px;
            left: ${left}%;
            background: radial-gradient(circle, ${color} 0%, transparent 70%);
            animation-duration: ${duration}s;
            animation-delay: -${delay}s;
            --drift: ${drift}px;
        `;
        
        container.appendChild(particle);
    }
    
    return container;
}

// =============================================
// Initialize All Animations
// =============================================

export function initAllAnimations(options = {}) {
    const {
        scrollProgress = true,
        scrollAnimations = true,
        rippleEffect = true,
        numberCounters = true,
        parallax = false,
        smoothScroll = true,
        darkMode = false,
        particles = false
    } = options;
    
    // Wait for DOM to be ready
    const init = () => {
        if (scrollProgress) initScrollProgress();
        if (scrollAnimations) initScrollAnimations();
        if (rippleEffect) initRippleEffect();
        if (numberCounters) initNumberCounters();
        if (parallax) initParallax();
        if (smoothScroll) initSmoothScroll();
        if (darkMode) initDarkModeToggle();
        if (particles) initParticles();
    };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}

// Export default init function
export default initAllAnimations;
