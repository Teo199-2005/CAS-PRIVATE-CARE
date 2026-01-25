/**
 * CAS Private Care - Animation Controller
 * Handles scroll-triggered animations, scroll progress, and interactive effects
 */

class AnimationController {
    constructor() {
        this.scrollProgress = null;
        this.observedElements = [];
        this.init();
    }

    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.createScrollProgress();
        this.setupScrollAnimations();
        this.setupRippleEffect();
        this.setupCountUpAnimations();
        this.setupParallaxEffects();
    }

    // ========================================
    // Scroll Progress Indicator
    // ========================================
    createScrollProgress() {
        // Check if progress bar already exists
        if (document.querySelector('.scroll-progress')) return;

        // Create the progress bar
        this.scrollProgress = document.createElement('div');
        this.scrollProgress.className = 'scroll-progress';
        document.body.prepend(this.scrollProgress);

        // Update on scroll
        window.addEventListener('scroll', () => this.updateScrollProgress(), { passive: true });
        this.updateScrollProgress();
    }

    updateScrollProgress() {
        if (!this.scrollProgress) return;

        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
        
        this.scrollProgress.style.width = `${Math.min(progress, 100)}%`;
    }

    // ========================================
    // Scroll-triggered Animations
    // ========================================
    setupScrollAnimations() {
        // Find all elements with scroll animation class
        const animatedElements = document.querySelectorAll('.animate-on-scroll');
        
        if (animatedElements.length === 0) return;

        // Create intersection observer
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -10% 0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Optionally stop observing after animation
                    // observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        animatedElements.forEach(el => observer.observe(el));
    }

    // ========================================
    // Button Ripple Effect
    // ========================================
    setupRippleEffect() {
        document.addEventListener('click', (e) => {
            const button = e.target.closest('.btn-animated, .btn-ripple');
            if (!button) return;

            // Create ripple element
            const ripple = document.createElement('span');
            ripple.className = 'ripple';

            // Get button dimensions and click position
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            // Apply styles
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;

            // Add to button and remove after animation
            button.appendChild(ripple);
            ripple.addEventListener('animationend', () => ripple.remove());
        });
    }

    // ========================================
    // Count-up Animation for Numbers
    // ========================================
    setupCountUpAnimations() {
        const countElements = document.querySelectorAll('[data-count-up]');
        
        if (countElements.length === 0) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCount(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        countElements.forEach(el => observer.observe(el));
    }

    animateCount(element) {
        const target = parseInt(element.getAttribute('data-count-up'), 10);
        const duration = parseInt(element.getAttribute('data-duration'), 10) || 2000;
        const suffix = element.getAttribute('data-suffix') || '';
        const prefix = element.getAttribute('data-prefix') || '';
        
        if (isNaN(target)) return;

        const startTime = performance.now();
        const startValue = 0;

        const updateCount = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-out-expo)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.floor(startValue + (target - startValue) * easeOut);
            
            element.textContent = prefix + currentValue.toLocaleString() + suffix;

            if (progress < 1) {
                requestAnimationFrame(updateCount);
            } else {
                element.textContent = prefix + target.toLocaleString() + suffix;
            }
        };

        requestAnimationFrame(updateCount);
    }

    // ========================================
    // Parallax Effects
    // ========================================
    setupParallaxEffects() {
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        
        if (parallaxElements.length === 0) return;

        window.addEventListener('scroll', () => {
            const scrollY = window.pageYOffset;

            parallaxElements.forEach(el => {
                const speed = parseFloat(el.getAttribute('data-parallax')) || 0.5;
                const yOffset = scrollY * speed;
                el.style.transform = `translateY(${yOffset}px)`;
            });
        }, { passive: true });
    }

    // ========================================
    // Stagger Animation Helper
    // ========================================
    static staggerAnimation(elements, baseDelay = 100) {
        elements.forEach((el, index) => {
            el.style.animationDelay = `${index * baseDelay}ms`;
            el.classList.add('stagger-item');
        });
    }

    // ========================================
    // Fade In Elements Programmatically
    // ========================================
    static fadeIn(element, duration = 300) {
        element.style.opacity = 0;
        element.style.display = 'block';
        
        let start = null;
        
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = (timestamp - start) / duration;
            
            element.style.opacity = Math.min(progress, 1);
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    // ========================================
    // Fade Out Elements Programmatically
    // ========================================
    static fadeOut(element, duration = 300) {
        let start = null;
        const initialOpacity = parseFloat(getComputedStyle(element).opacity);
        
        const animate = (timestamp) => {
            if (!start) start = timestamp;
            const progress = (timestamp - start) / duration;
            
            element.style.opacity = initialOpacity * (1 - Math.min(progress, 1));
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.style.display = 'none';
            }
        };
        
        requestAnimationFrame(animate);
    }
}

// Initialize the animation controller
const animationController = new AnimationController();

// Export for use in Vue components
if (typeof window !== 'undefined') {
    window.AnimationController = AnimationController;
    window.animationController = animationController;
}

export default AnimationController;
