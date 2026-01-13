<template>
    <div class="landing">
        <header class="nav">
            <a class="brand" href="/">
                <img class="brandLogo" :src="logoUrl" alt="CAS Private Care" />
                <span class="brandName">CAS Private Care</span>
            </a>

            <nav class="navLinks">
                <a href="#services">Services</a>
                <a href="#how-it-works">How it works</a>
                <a href="#reviews">Reviews</a>
                <a href="/blog">Blog</a>
                <a href="/login" class="btn btnGhost">Login</a>
                <a href="/register" class="btn btnPrimary">Get started</a>
            </nav>
        </header>

        <main>
            <section class="hero">
                <div class="heroGrid">
                    <div class="heroCopy">
                        <p class="pill">Verified caregivers • Housekeeping • Personal assistants</p>
                        <h1>Find trusted home care in New York—fast.</h1>
                        <p class="sub">
                            Connect with vetted professionals for caregiving, housekeeping, and personal assistance.
                            Book with confidence and get 24/7 support.
                        </p>

                        <div class="heroTabs" role="tablist" aria-label="Service types">
                            <button
                                v-for="s in services"
                                :key="s.key"
                                class="tab"
                                :class="{ active: activeService === s.key }"
                                type="button"
                                @click="setService(s.key)"
                            >
                                {{ s.label }}
                            </button>
                        </div>

                        <div class="heroCard" id="services">
                            <h2 class="heroCardTitle">{{ current.title }}</h2>
                            <p class="heroCardDesc">{{ current.description }}</p>

                            <div class="heroActions">
                                <a class="btn btnPrimary" :href="current.ctaHref">{{ current.ctaLabel }}</a>
                                <a class="btn btnGhost" href="/register">Create an account</a>
                            </div>
                        </div>

                        <div class="stats" v-if="statsReady">
                            <div class="stat">
                                <div class="statValue">{{ stats.caregivers }}</div>
                                <div class="statLabel">Verified caregivers</div>
                            </div>
                            <div class="stat">
                                <div class="statValue">{{ stats.clients }}</div>
                                <div class="statLabel">Happy clients</div>
                            </div>
                            <div class="stat">
                                <div class="statValue">{{ stats.reviews }}</div>
                                <div class="statLabel">5-star reviews</div>
                            </div>
                        </div>
                    </div>

                    <div class="heroMedia" aria-hidden="true">
                        <div class="mediaCard">
                            <div class="mediaBadge">New</div>
                            <div class="mediaTitle">Care you can trust</div>
                            <div class="mediaText">
                                Background checks, verified profiles, and support every step of the way.
                            </div>
                        </div>

                        <div class="mediaCard alt">
                            <div class="mediaTitle">Transparent pricing</div>
                            <div class="mediaText">See a clear breakdown before you book.</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section" id="how-it-works">
                <h2>How it works</h2>
                <div class="steps">
                    <div class="step">
                        <div class="stepNum">1</div>
                        <div class="stepTitle">Tell us what you need</div>
                        <div class="stepText">Pick a service type and share your schedule.</div>
                    </div>
                    <div class="step">
                        <div class="stepNum">2</div>
                        <div class="stepTitle">We match you</div>
                        <div class="stepText">Get matched with verified professionals in your area.</div>
                    </div>
                    <div class="step">
                        <div class="stepNum">3</div>
                        <div class="stepTitle">Book with confidence</div>
                        <div class="stepText">Confirm your booking and get 24/7 support.</div>
                    </div>
                </div>
            </section>

            <section class="section" id="reviews">
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

        <footer class="footer">
            <div class="footerInner">
                <div class="footBrand">CAS Private Care LLC</div>
                <div class="footLinks">
                    <a href="/about">About</a>
                    <a href="/faq">FAQ</a>
                    <a href="/contact">Contact</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

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
});
</script>

<style scoped>
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
}

.brandLogo {
    width: 34px;
    height: 34px;
    object-fit: contain;
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
</style>
