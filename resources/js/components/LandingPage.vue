<template>
    <div class="landing">
        <!-- Scroll Progress Indicator -->
        <div class="scroll-progress-bar" :style="{ width: scrollProgress + '%' }"></div>
        
        <!-- Floating Background Blobs -->
        <div class="background-blobs" aria-hidden="true">
            <div class="blob blob-blue"></div>
            <div class="blob blob-green"></div>
            <div class="blob blob-purple"></div>
        </div>

        <header class="nav" role="banner">
            <a class="brand" href="/" aria-label="CAS Private Care Home">
                <img class="brandLogo" :src="logoUrl" alt="CAS Private Care Logo" />
                <span class="brandName">CAS Private Care</span>
            </a>

            <nav class="navLinks" role="navigation" aria-label="Main navigation">
                <a href="#services" class="nav-link-animated" aria-label="View our services">Services</a>
                <a href="#how-it-works" class="nav-link-animated" aria-label="Learn how it works">How it works</a>
                <a href="#reviews" class="nav-link-animated" aria-label="Read customer reviews">Reviews</a>
                <a href="/blog" class="nav-link-animated" aria-label="Read our blog">Blog</a>
                <a href="/login" class="btn btnGhost btn-animated" aria-label="Login to your account">Login</a>
                <a href="/register" class="btn btnPrimary btn-animated" aria-label="Get started with CAS Private Care">Get started</a>
            </nav>
        </header>

        <main role="main">
            <section class="hero" aria-label="Hero section">
                <div class="heroGrid">
                    <div class="heroCopy animate-fade-in">
                        <p class="pill animate-slide-down">Verified caregivers • Housekeeping • Personal assistants</p>
                        <h1 class="animate-title">Find trusted home care in New York—fast.</h1>
                        <p class="sub animate-fade-up">
                            Connect with vetted professionals for caregiving, housekeeping, and personal assistance.
                            Book with confidence and get 24/7 support.
                        </p>

                        <div class="heroTabs" role="tablist" aria-label="Service types">
                            <button
                                v-for="(s, index) in services"
                                :key="s.key"
                                class="tab tab-animated"
                                :class="{ active: activeService === s.key }"
                                :style="{ animationDelay: (index * 100) + 'ms' }"
                                type="button"
                                @click="setService(s.key)"
                            >
                                {{ s.label }}
                            </button>
                        </div>

                        <transition name="tab-fade" mode="out-in">
                            <div class="heroCard" id="services" :key="activeService">
                                <h2 class="heroCardTitle">{{ current.title }}</h2>
                                <p class="heroCardDesc">{{ current.description }}</p>

                                <div class="heroActions">
                                    <a class="btn btnPrimary btn-animated" :href="current.ctaHref">{{ current.ctaLabel }}</a>
                                    <a class="btn btnGhost btn-animated" href="/register">Create an account</a>
                                </div>
                            </div>
                        </transition>

                        <div class="stats" v-if="statsReady">
                            <div class="stat stagger-item" :style="{ animationDelay: '200ms' }">
                                <div class="statValue stat-value-hover">{{ stats.caregivers }}</div>
                                <div class="statLabel">Verified caregivers</div>
                            </div>
                            <div class="stat stagger-item" :style="{ animationDelay: '300ms' }">
                                <div class="statValue stat-value-hover">{{ stats.clients }}</div>
                                <div class="statLabel">Happy clients</div>
                            </div>
                            <div class="stat stagger-item" :style="{ animationDelay: '400ms' }">
                                <div class="statValue stat-value-hover">{{ stats.reviews }}</div>
                                <div class="statLabel">5-star reviews</div>
                            </div>
                        </div>
                    </div>

                    <div class="heroMedia animate-fade-right" aria-hidden="true">
                        <div class="mediaCard card-hover">
                            <div class="mediaBadge pulse-badge">New</div>
                            <div class="mediaTitle">Care you can trust</div>
                            <div class="mediaText">
                                Background checks, verified profiles, and support every step of the way.
                            </div>
                        </div>

                        <div class="mediaCard alt card-hover">
                            <div class="mediaTitle">Transparent pricing</div>
                            <div class="mediaText">See a clear breakdown before you book.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section section-animated" id="how-it-works">
                <h2 class="section-title-animated">How it works</h2>
                <div class="steps">
                    <div class="step step-animated card-hover" style="animation-delay: 0ms">
                        <div class="stepNum stepNum-animated">1</div>
                        <div class="stepTitle">Tell us what you need</div>
                        <div class="stepText">Pick a service type and share your schedule.</div>
                    </div>
                    <div class="step step-animated card-hover" style="animation-delay: 150ms">
                        <div class="stepNum stepNum-animated">2</div>
                        <div class="stepTitle">We match you</div>
                        <div class="stepText">Get matched with verified professionals in your area.</div>
                    </div>
                    <div class="step step-animated card-hover" style="animation-delay: 300ms">
                        <div class="stepNum stepNum-animated">3</div>
                        <div class="stepTitle">Book with confidence</div>
                        <div class="stepText">Confirm your booking and get 24/7 support.</div>
                    </div>
                </div>
            </section>

            <section class="section section-animated" id="reviews">
                <h2>What people say</h2>
                <div class="quoteGrid">
                    <figure class="quote">
                        <blockquote>“So simple to use. We found an amazing caregiver quickly.”</blockquote>
                        <figcaption>— Client in NYC</figcaption>
                    </figure>
                    <figure class="quote">
                        <blockquote>“Professional, responsive, and transparent.”</blockquote>
                        <figcaption>— Family caregiver</figcaption>
                    </figure>
                    <figure class="quote">
                        <blockquote>“The booking process was smooth from start to finish.”</blockquote>
                        <figcaption>— Housekeeping client</figcaption>
                    </figure>
                </div>
            </section>
        </main>

        <footer class="footer" role="contentinfo">
            <div class="footerInner">
                <div class="footBrand">CAS Private Care LLC</div>
                <nav class="footLinks" aria-label="Footer navigation">
                    <a href="/about" aria-label="About CAS Private Care">About</a>
                    <a href="/faq" aria-label="Frequently asked questions">FAQ</a>
                    <a href="/contact" aria-label="Contact us">Contact</a>
                </nav>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Scroll progress tracking
const scrollProgress = ref(0);

// Scroll-triggered animation observer
let scrollObserver = null;

const services = [
    {
        key: 'caregiver',
        label: 'Caregivers',
        title: 'Caregivers',
        description:
            'Elderly care, companionship, and daily assistance from verified caregivers.',
        ctaLabel: 'Find a Caregiver',
        ctaHref: '#services',
    },
    {
        key: 'housekeeping',
        label: 'Housekeeping',
        title: 'Housekeeping',
        description:
            'Reliable home helpers for cleaning, errands, and keeping your space comfortable.',
        ctaLabel: 'Find a Housekeeper',
        ctaHref: '#services',
    },
    {
        key: 'personal',
        label: 'Personal Assistants',
        title: 'Personal Assistants',
        description:
            'Support with daily tasks, appointments, organization, and light household needs.',
        ctaLabel: 'Find Personal Care',
        ctaHref: '#services',
    },
];

const activeService = ref('caregiver');
const current = computed(() => services.find((s) => s.key === activeService.value) || services[0]);

function setService(key) {
    activeService.value = key;
}

const logoUrl = '/logo flower.png';

const stats = ref({ caregivers: '—', clients: '—', reviews: '—' });
const statsReady = computed(() => !!stats.value);

let rotateTimerId;

// Update scroll progress
function updateScrollProgress() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    scrollProgress.value = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
}

// Setup scroll-triggered animations
function setupScrollAnimations() {
    const animatedSections = document.querySelectorAll('.section-animated');
    
    scrollObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        root: null,
        rootMargin: '0px 0px -10% 0px',
        threshold: 0.1
    });

    animatedSections.forEach(section => {
        scrollObserver.observe(section);
    });
}

async function loadStats() {
    try {
        const res = await fetch('/api/landing/stats', {
            headers: {
                'Accept': 'application/json',
            },
        });

        if (!res.ok) return;
        const data = await res.json();

        // Controller returns { success: true, stats: {...} } (or similar). Be defensive.
        const raw = data?.stats || data?.data || data;
        if (!raw) return;

        stats.value = {
            caregivers: raw.caregivers ?? raw.total_caregivers ?? '—',
            clients: raw.clients ?? raw.total_clients ?? '—',
            reviews: raw.reviews ?? raw.total_reviews ?? '—',
        };
    } catch {
        // Non-blocking
    }
}

onMounted(() => {
    // Setup scroll progress tracking
    window.addEventListener('scroll', updateScrollProgress, { passive: true });
    updateScrollProgress();
    
    // Setup scroll-triggered animations
    setupScrollAnimations();

    // Auto-rotate like the old Blade slider.
    rotateTimerId = window.setInterval(() => {
        const idx = services.findIndex((s) => s.key === activeService.value);
        const next = services[(idx + 1) % services.length];
        activeService.value = next.key;
    }, 5000);

    loadStats();
});

onUnmounted(() => {
    if (rotateTimerId) window.clearInterval(rotateTimerId);
    window.removeEventListener('scroll', updateScrollProgress);
    if (scrollObserver) {
        scrollObserver.disconnect();
    }
});
</script>

<style scoped>
/* ========================================
   CSS Variables for Consistent Transitions
   ======================================== */
:root {
    --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
    --transition-normal: 250ms cubic-bezier(0.4, 0, 0.2, 1);
    --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
}

/* ========================================
   Scroll Progress Bar
   ======================================== */
.scroll-progress-bar {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, #3b82f6, #10b981);
    z-index: 10000;
    transition: width 50ms linear;
    box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
}

/* ========================================
   Floating Background Blobs
   ======================================== */
.background-blobs {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: -1;
    overflow: hidden;
}

.blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.5;
}

.blob-blue {
    width: 600px;
    height: 600px;
    background: rgba(59, 130, 246, 0.2);
    top: -200px;
    left: -100px;
    animation: floatBlob 8s ease-in-out infinite;
}

.blob-green {
    width: 500px;
    height: 500px;
    background: rgba(16, 185, 129, 0.15);
    top: 50%;
    right: -150px;
    animation: floatBlobReverse 10s ease-in-out infinite;
}

.blob-purple {
    width: 400px;
    height: 400px;
    background: rgba(139, 92, 246, 0.12);
    bottom: -100px;
    left: 30%;
    animation: floatBlob 12s ease-in-out infinite;
}

@keyframes floatBlob {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(20px, -20px) scale(1.05); }
    50% { transform: translate(30px, -30px) scale(1.1); }
    75% { transform: translate(10px, -15px) scale(1.03); }
}

@keyframes floatBlobReverse {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(-15px, 20px) scale(1.03); }
    50% { transform: translate(-25px, 25px) scale(1.08); }
    75% { transform: translate(-10px, 10px) scale(1.02); }
}

/* ========================================
   Entrance Animations
   ======================================== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes titleReveal {
    from {
        opacity: 0;
        transform: translateY(40px);
        filter: blur(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
    }
}

@keyframes staggerFadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.05); }
}

/* Animation Classes */
.animate-fade-in {
    animation: fadeInUp 0.8s ease-out forwards;
}

.animate-fade-up {
    animation: fadeInUp 0.6s ease-out 0.2s forwards;
    opacity: 0;
}

.animate-fade-right {
    animation: fadeInRight 0.8s ease-out 0.3s forwards;
    opacity: 0;
}

.animate-slide-down {
    animation: slideDown 0.6s ease-out forwards;
}

.animate-title {
    animation: titleReveal 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.stagger-item {
    opacity: 0;
    animation: staggerFadeIn 0.5s ease forwards;
}

.pulse-badge {
    animation: pulse 2s ease-in-out infinite;
}

/* ========================================
   Tab Transition
   ======================================== */
.tab-fade-enter-active {
    animation: tabFadeIn 0.3s ease-out;
}

.tab-fade-leave-active {
    animation: tabFadeOut 0.2s ease-in;
}

@keyframes tabFadeIn {
    from {
        opacity: 0;
        transform: translateX(-10px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes tabFadeOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(10px);
    }
}

/* ========================================
   Section Animations (Scroll-triggered)
   ======================================== */
.section-animated {
    opacity: 0;
    transform: translateY(40px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

.section-animated.visible {
    opacity: 1;
    transform: translateY(0);
}

.section-title-animated {
    opacity: 0;
    animation: fadeInUp 0.6s ease-out forwards;
}

.section-animated.visible .section-title-animated {
    opacity: 1;
}

.step-animated,
.quote-animated {
    opacity: 0;
    animation: staggerFadeIn 0.5s ease forwards;
    animation-play-state: paused;
}

.section-animated.visible .step-animated,
.section-animated.visible .quote-animated {
    animation-play-state: running;
}

/* ========================================
   Card Hover Effects
   ======================================== */
.card-hover {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
                box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 50px rgba(59, 130, 246, 0.15);
}

/* ========================================
   Button Animations
   ======================================== */
.btn-animated {
    position: relative;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-animated::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.5s ease, height 0.5s ease;
}

.btn-animated:active::after {
    width: 300px;
    height: 300px;
}

.btn-animated:active {
    transform: scale(0.97);
}

.btn-animated:focus-visible {
    outline: 3px solid rgba(59, 130, 246, 0.5);
    outline-offset: 2px;
}

/* ========================================
   Navigation Link Animations
   ======================================== */
.nav-link-animated {
    position: relative;
}

.nav-link-animated::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #3b82f6, #10b981);
    transition: width 0.3s ease;
}

.nav-link-animated:hover::after {
    width: 100%;
}

/* ========================================
   Tab Animations
   ======================================== */
.tab-animated {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
    animation: staggerFadeIn 0.4s ease forwards;
}

.tab-animated:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.tab-animated.active {
    transform: scale(1.02);
}

/* ========================================
   Stat Value Hover Effect
   ======================================== */
.stat-value-hover {
    transition: transform 0.2s ease, color 0.2s ease;
}

.stat:hover .stat-value-hover {
    transform: scale(1.1);
    color: #3b82f6;
}

/* ========================================
   StepNum Animation
   ======================================== */
.stepNum-animated {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.step:hover .stepNum-animated {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

/* ========================================
   Footer Link Animations
   ======================================== */
.footer-link-animated {
    position: relative;
    transition: color 0.2s ease;
}

.footer-link-animated::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: #1e40af;
    transition: width 0.3s ease;
}

.footer-link-animated:hover::after {
    width: 100%;
}

.footer-link-animated:hover {
    color: #1e40af;
}

/* ========================================
   Focus States (Accessibility)
   ======================================== */
*:focus-visible {
    outline: 3px solid rgba(59, 130, 246, 0.5);
    outline-offset: 2px;
    border-radius: 4px;
}

/* ========================================
   Reduced Motion Preferences
   ======================================== */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .section-animated {
        opacity: 1;
        transform: none;
    }
    
    .blob {
        animation: none;
    }
}

/* Use the loaded Google Fonts */
.landing {
    font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #0f172a;
    background: radial-gradient(1200px circle at 20% 10%, rgba(59, 130, 246, 0.14), transparent 55%),
        radial-gradient(900px circle at 80% 0%, rgba(16, 185, 129, 0.12), transparent 45%),
        #ffffff;
    min-height: 100vh;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    position: relative;
    overflow-x: hidden;
}

.nav {
    max-width: 1120px;
    margin: 0 auto;
    padding: 18px 20px;
    display: flex;
    gap: 16px;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    z-index: 100;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
}

.brand {
    display: flex;
    gap: 10px;
    align-items: center;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s ease;
}

.brand:hover {
    transform: scale(1.02);
}

.brandLogo {
    width: 34px;
    height: 34px;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.brand:hover .brandLogo {
    transform: rotate(10deg);
}

.brandName {
    font-family: 'Sora', sans-serif;
    font-weight: 800;
    letter-spacing: -0.02em;
    font-size: 1.1rem;
    color: #1e40af;
}

.navLinks {
    display: flex;
    gap: 14px;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.navLinks a {
    text-decoration: none;
    color: #0f172a;
    font-weight: 600;
    opacity: 0.92;
    transition: opacity 0.2s ease, color 0.2s ease;
}

.navLinks a:hover {
    opacity: 1;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 10px 14px;
    font-weight: 800;
    text-decoration: none;
    border: 1px solid transparent;
    transition: all 160ms ease;
}

.btnPrimary {
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
}

.btnPrimary:hover {
    filter: brightness(1.05);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

.btnGhost {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(15, 23, 42, 0.14);
}

.btnGhost:hover {
    border-color: rgba(15, 23, 42, 0.28);
    transform: translateY(-1px);
}

.hero {
    max-width: 1120px;
    margin: 0 auto;
    padding: 60px 20px 40px;
}

.heroGrid {
    display: grid;
    grid-template-columns: 1.1fr 0.9fr;
    gap: 40px;
    align-items: center;
}

.pill {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 999px;
    font-weight: 800;
    background: rgba(59, 130, 246, 0.08);
    border: 1px solid rgba(59, 130, 246, 0.16);
    color: #1e40af;
    margin: 0 0 14px;
}

.heroCopy h1 {
    font-family: 'Sora', sans-serif;
    margin: 0 0 16px;
    font-size: 3.2rem;
    line-height: 1.1;
    letter-spacing: -0.03em;
    font-weight: 800;
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #0891b2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.sub {
    margin: 0 0 20px;
    color: rgba(15, 23, 42, 0.75);
    font-size: 1.1rem;
    line-height: 1.7;
    max-width: 540px;
}

.heroTabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin: 12px 0 14px;
}

.tab {
    border: 1px solid rgba(15, 23, 42, 0.14);
    background: rgba(255, 255, 255, 0.85);
    padding: 9px 12px;
    border-radius: 999px;
    cursor: pointer;
    font-weight: 800;
    color: #0f172a;
}

.tab.active {
    border-color: rgba(59, 130, 246, 0.4);
    background: rgba(59, 130, 246, 0.12);
    color: #1e40af;
}

.heroCard {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(15, 23, 42, 0.1);
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 20px 50px rgba(2, 6, 23, 0.08);
    transition: all 0.3s ease;
}

.heroCard:hover {
    box-shadow: 0 25px 60px rgba(59, 130, 246, 0.12);
}

.heroCardTitle {
    font-family: 'Sora', sans-serif;
    margin: 0 0 8px;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e40af;
}

.heroCardDesc {
    margin: 0 0 12px;
    color: rgba(15, 23, 42, 0.78);
    line-height: 1.6;
}

.heroActions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.stats {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 14px;
}

.stat {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(15, 23, 42, 0.06);
    border-radius: 16px;
    padding: 16px;
    text-align: center;
    transition: all 0.3s ease;
}

.stat:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59, 130, 246, 0.1);
}

.statValue {
    font-family: 'Sora', sans-serif;
    font-weight: 800;
    font-size: 1.5rem;
    color: #1e40af;
}

.statLabel {
    color: rgba(15, 23, 42, 0.72);
    font-weight: 700;
    margin-top: 2px;
    font-size: 13px;
}

.heroMedia {
    display: grid;
    gap: 14px;
}

.mediaCard {
    background: linear-gradient(145deg, rgba(59, 130, 246, 0.08) 0%, rgba(16, 185, 129, 0.05) 100%);
    border: 1px solid rgba(15, 23, 42, 0.1);
    border-radius: 20px;
    padding: 24px;
    min-height: 180px;
    box-shadow: 0 30px 80px rgba(2, 6, 23, 0.06);
    transition: all 0.3s ease;
}

.mediaCard:hover {
    transform: translateY(-4px);
    box-shadow: 0 35px 90px rgba(59, 130, 246, 0.12);
}

.mediaCard.alt {
    background: linear-gradient(140deg, rgba(16, 185, 129, 0.12), rgba(59, 130, 246, 0.06));
}

.mediaBadge {
    display: inline-block;
    font-weight: 900;
    font-size: 12px;
    background: rgba(59, 130, 246, 0.18);
    border: 1px solid rgba(59, 130, 246, 0.26);
    color: #1e40af;
    border-radius: 999px;
    padding: 6px 10px;
    margin-bottom: 10px;
}

.mediaTitle {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    font-size: 1.15rem;
    margin-bottom: 8px;
    color: #1e40af;
}

.mediaText {
    color: rgba(15, 23, 42, 0.78);
    line-height: 1.65;
}

.section {
    max-width: 1120px;
    margin: 0 auto;
    padding: 60px 20px;
}

.section h2 {
    font-family: 'Sora', sans-serif;
    margin: 0 0 24px;
    font-size: 2rem;
    letter-spacing: -0.02em;
    font-weight: 700;
    color: #1e40af;
    text-align: center;
}

.steps {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.step {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 20px;
    padding: 24px;
    transition: all 0.3s ease;
}

.step:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(59, 130, 246, 0.12);
    border-color: rgba(59, 130, 246, 0.2);
}

.stepNum {
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    border: none;
    font-weight: 900;
    color: #ffffff;
    margin-bottom: 14px;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.stepTitle {
    font-family: 'Sora', sans-serif;
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 1.1rem;
    color: #1e40af;
}

.stepText {
    color: rgba(15, 23, 42, 0.76);
    line-height: 1.6;
}

.quoteGrid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.quote {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 20px;
    padding: 24px;
    margin: 0;
    transition: all 0.3s ease;
}

.quote:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(16, 185, 129, 0.12);
    border-color: rgba(16, 185, 129, 0.2);
}

.quote blockquote {
    margin: 0;
    font-weight: 600;
    line-height: 1.7;
    font-size: 1rem;
    color: #334155;
    font-style: italic;
}

.quote figcaption {
    margin-top: 10px;
    color: rgba(15, 23, 42, 0.7);
    font-weight: 700;
}

.footer {
    padding: 32px 20px 40px;
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.8), rgba(248, 250, 252, 1));
}

.footerInner {
    max-width: 1120px;
    margin: 0 auto;
    display: flex;
    gap: 20px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
}

.footBrand {
    font-family: 'Sora', sans-serif;
    font-weight: 800;
    color: #1e40af;
    font-size: 1.1rem;
}

.footLinks {
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}

.footLinks a {
    text-decoration: none;
    color: rgba(15, 23, 42, 0.78);
    font-weight: 700;
}

.footLinks a:hover {
    color: rgba(15, 23, 42, 1);
}

@media (max-width: 980px) {
    .heroGrid {
        grid-template-columns: 1fr;
    }

    .heroCopy h1 {
        font-size: 36px;
    }
}

@media (max-width: 640px) {
    .steps,
    .quoteGrid,
    .stats {
        grid-template-columns: 1fr;
    }
}

/* ============================================
   MOBILE BATTERY OPTIMIZATION - v1.0
   Added: January 24, 2026
   Hide decorative blobs on mobile to save battery
   ============================================ */

@media (max-width: 768px) {
    /* Hide background blobs on mobile - major battery drain */
    .blob,
    .blob-blue,
    .blob-green,
    .blob-purple,
    .background-blobs {
        display: none !important;
    }
}

/* Pause blob animations when page is hidden (desktop) */
.page-hidden .blob,
.page-hidden .blob-blue,
.page-hidden .blob-green,
.page-hidden .blob-purple {
    animation-play-state: paused !important;
}

/* Pause during scrolling for better performance */
.is-scrolling .blob,
.is-scrolling .blob-blue,
.is-scrolling .blob-green,
.is-scrolling .blob-purple {
    animation-play-state: paused !important;
}

/* Respect reduced motion preference */
@media (prefers-reduced-motion: reduce) {
    .blob,
    .blob-blue,
    .blob-green,
    .blob-purple {
        animation: none !important;
    }
}
</style>
