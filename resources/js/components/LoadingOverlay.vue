<template>
    <Teleport to="body">
        <Transition name="loading-fade">
            <div v-if="visible" class="loading-overlay">
                <div class="loading-container">
                    <div class="loading-logo-wrapper">
                        <div class="loading-ring-glow"></div>
                        <div class="loading-ring"></div>
                        <div class="loading-ring-animated"></div>
                        <div class="loading-logo">
                            <img
                                v-if="!imageError"
                                :src="logoSrc"
                                alt="CAS Private Care"
                                @error="handleImageError"
                                @load="imageLoaded = true"
                                class="logo-image"
                            />
                            <div v-else class="fallback-logo">
                                <div class="fallback-logo-text">CAS</div>
                                <div class="fallback-logo-tagline">Private Care</div>
                            </div>
                        </div>
                    </div>
                    <div class="loading-text">{{ currentMessage }}</div>
                    <div class="loading-dots">
                        <div class="loading-dot"></div>
                        <div class="loading-dot"></div>
                        <div class="loading-dot"></div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script>
export default {
    name: 'LoadingOverlay',
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        message: {
            type: String,
            default: ''
        },
        tagline: {
            type: String,
            default: 'Comfort & Support'
        },
        context: {
            type: String,
            default: 'default'
        },
        progress: {
            type: Number,
            default: null
        }
    },
    data() {
        return {
            messageIndex: 0,
            messageInterval: null,
            imageError: false,
            imageLoaded: false,
            logoAttempt: 0,
            logoSrc: '/logo.png',
            // Fallback logo sources to try
            logoSources: [
                '/logo.png',
                '/images/logo.png',
                '/assets/logo.png',
                '/build/logo.png'
            ],
            contextMessages: {
                default: [
                    'Loading...',
                    'Preparing your experience...',
                    'Almost ready...'
                ],
                dashboard: [
                    'Loading dashboard data...',
                    'Fetching statistics...',
                    'Preparing overview...'
                ],
                admin: [
                    'Loading admin panel...',
                    'Fetching system data...',
                    'Preparing controls...'
                ],
                adminStaff: [
                    'Loading admin panel...',
                    'Fetching staff data...',
                    'Preparing dashboard...'
                ],
                client: [
                    'Loading your dashboard...',
                    'Fetching bookings...',
                    'Preparing your care info...'
                ],
                caregiver: [
                    'Loading caregiver portal...',
                    'Fetching assignments...',
                    'Preparing schedule...'
                ],
                housekeeper: [
                    'Loading housekeeper portal...',
                    'Fetching tasks...',
                    'Preparing schedule...'
                ],
                marketing: [
                    'Loading marketing panel...',
                    'Fetching analytics...',
                    'Preparing campaigns...'
                ],
                training: [
                    'Loading training center...',
                    'Fetching applications...',
                    'Preparing trainee data...'
                ]
            }
        };
    },
    computed: {
        currentMessage() {
            if (this.message) return this.message;
            const messages = this.contextMessages[this.context] || this.contextMessages.default;
            return messages[this.messageIndex % messages.length];
        }
    },
    watch: {
        visible(newVal) {
            if (newVal) {
                this.startMessageRotation();
                // Reset image state when overlay becomes visible
                this.resetImageState();
            } else {
                this.stopMessageRotation();
            }
        }
    },
    mounted() {
        if (this.visible) {
            this.startMessageRotation();
        }
        // Preload the logo image
        this.preloadLogo();
    },
    beforeUnmount() {
        this.stopMessageRotation();
    },
    methods: {
        resetImageState() {
            this.imageError = false;
            this.imageLoaded = false;
            this.logoAttempt = 0;
            this.logoSrc = this.logoSources[0];
        },
        preloadLogo() {
            const img = new Image();
            img.src = '/logo.png';
        },
        startMessageRotation() {
            this.messageIndex = 0;
            this.messageInterval = setInterval(() => {
                this.messageIndex++;
            }, 1500);
        },
        stopMessageRotation() {
            if (this.messageInterval) {
                clearInterval(this.messageInterval);
                this.messageInterval = null;
            }
        },
        handleImageError() {
            this.logoAttempt++;
            
            // Try next logo source
            if (this.logoAttempt < this.logoSources.length) {
                this.logoSrc = this.logoSources[this.logoAttempt];
            } else {
                // All sources failed, show fallback text logo
                this.imageError = true;
            }
        }
    }
};
</script>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    background-color: #ffffff;
    background-image: url("https://www.transparenttextures.com/patterns/batthern.png");
    background-repeat: repeat;
    background-size: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-container {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
}

.loading-logo-wrapper {
    position: relative;
    width: 240px;
    height: 240px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loading-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 3px solid rgba(11, 79, 162, 0.1);
}

.loading-ring-animated {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 3px solid transparent;
    border-top-color: #0B4FA2;
    border-right-color: #f97316;
    animation: spin-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
}

.loading-ring-glow {
    position: absolute;
    width: 110%;
    height: 110%;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(11, 79, 162, 0.08) 0%, transparent 70%);
    animation: pulse-glow 2s ease-in-out infinite;
}

.loading-logo {
    position: relative;
    z-index: 2;
    animation: logo-float 2.5s ease-in-out infinite;
}

.loading-logo img {
    height: 160px;
    width: auto;
    box-shadow: none;
    filter: drop-shadow(0 10px 30px rgba(11, 79, 162, 0.15));
    transition: transform 0.3s ease;
}

/* Fallback text logo when image fails to load */
.fallback-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px 30px;
    animation: logo-float 2.5s ease-in-out infinite;
}

.fallback-logo-text {
    font-family: 'Sora', 'Segoe UI', sans-serif;
    font-size: 64px;
    font-weight: 700;
    background: linear-gradient(135deg, #0B4FA2, #f97316);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 4px;
    line-height: 1;
}

.fallback-logo-tagline {
    font-family: 'Sora', 'Segoe UI', sans-serif;
    font-size: 18px;
    font-weight: 500;
    color: #0B4FA2;
    letter-spacing: 2px;
    margin-top: 8px;
    opacity: 0.8;
}

.loading-text {
    font-family: 'Sora', sans-serif;
    font-size: 1.1rem;
    font-weight: 500;
    color: #0B4FA2;
    letter-spacing: 0.5px;
    opacity: 0.9;
}

.loading-dots {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.loading-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0B4FA2, #f97316);
    animation: dot-bounce 1.4s ease-in-out infinite;
}

.loading-dot:nth-child(1) { animation-delay: 0s; }
.loading-dot:nth-child(2) { animation-delay: 0.2s; }
.loading-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes spin-ring {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes pulse-glow {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.05); }
}

@keyframes logo-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

@keyframes dot-bounce {
    0%, 80%, 100% { 
        transform: scale(0.6);
        opacity: 0.5;
    }
    40% { 
        transform: scale(1);
        opacity: 1;
    }
}

/* Mobile: smaller logo */
@media (max-width: 768px) {
    .loading-logo-wrapper {
        width: 200px;
        height: 200px;
    }
    .loading-logo img,
    .loading-logo .logo-image {
        height: 140px !important;
        width: auto !important;
        max-width: 160px;
    }
    .fallback-logo-text {
        font-size: 44px;
    }
    .fallback-logo-tagline {
        font-size: 14px;
    }
    .loading-text {
        font-size: 1rem;
    }
}

/* Transition */
.loading-fade-enter-active,
.loading-fade-leave-active {
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.loading-fade-enter-from,
.loading-fade-leave-to {
    opacity: 0;
    transform: scale(1.05);
}

</style>
