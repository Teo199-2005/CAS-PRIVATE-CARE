<template>
    <Teleport to="body">
        <Transition name="loading-fade">
            <div v-if="visible" class="loading-overlay">
                <div class="loading-content">
                    <!-- Logo/Branding -->
                    <div class="loading-brand">
                        <img 
                            src="/logo%20flower.png" 
                            alt="CAS Private Care" 
                            class="loading-logo"
                            @error="handleImageError"
                        />
                        <div class="loading-brand-text">
                            <span class="loading-brand-name">CAS Private Care</span>
                            <span class="loading-brand-tagline">{{ tagline }}</span>
                        </div>
                    </div>

                    <!-- Modern Circular Spinner with Gradient -->
                    <div class="loading-spinner-container">
                        <svg class="loading-spinner" viewBox="0 0 50 50">
                            <defs>
                                <linearGradient id="spinner-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#f97316" />
                                    <stop offset="100%" stop-color="#3b82f6" />
                                </linearGradient>
                            </defs>
                            <circle
                                class="loading-spinner-track"
                                cx="25"
                                cy="25"
                                r="20"
                                fill="none"
                                stroke-width="4"
                            />
                            <circle
                                class="loading-spinner-progress"
                                cx="25"
                                cy="25"
                                r="20"
                                fill="none"
                                stroke-width="4"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>

                    <!-- Loading Message -->
                    <p class="loading-text">{{ currentMessage }}</p>
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
        }
    },
    data() {
        return {
            messageIndex: 0,
            messageInterval: null,
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
            } else {
                this.stopMessageRotation();
            }
        }
    },
    mounted() {
        if (this.visible) {
            this.startMessageRotation();
        }
    },
    beforeUnmount() {
        this.stopMessageRotation();
    },
    methods: {
        startMessageRotation() {
            this.messageIndex = 0;
            this.messageInterval = setInterval(() => {
                this.messageIndex++;
            }, 1500); // Faster rotation for better UX
        },
        stopMessageRotation() {
            if (this.messageInterval) {
                clearInterval(this.messageInterval);
                this.messageInterval = null;
            }
        },
        handleImageError(event) {
            // Fallback: try alternative path if URL-encoded path fails
            if (event.target.src.includes('%20')) {
                event.target.src = '/logo flower.png';
            } else {
                // If both fail, hide the broken image
                event.target.style.display = 'none';
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
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.loading-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
    padding: 2rem;
    text-align: center;
}

/* Branding */
.loading-brand {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.loading-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    filter: drop-shadow(0 4px 12px rgba(59, 130, 246, 0.15));
}

.loading-brand-text {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.loading-brand-name {
    font-family: 'Sora', sans-serif;
    font-size: 1.75rem;
    font-weight: 700;
    background: linear-gradient(135deg, #f97316 0%, #3b82f6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
}

.loading-brand-tagline {
    font-size: 0.9rem;
    color: #64748b;
    font-weight: 500;
}

/* Modern Spinner */
.loading-spinner-container {
    width: 56px;
    height: 56px;
    position: relative;
}

.loading-spinner {
    width: 100%;
    height: 100%;
    animation: spinner-rotate 1s linear infinite;
}

@keyframes spinner-rotate {
    100% {
        transform: rotate(360deg);
    }
}

.loading-spinner-track {
    stroke: #e2e8f0;
}

.loading-spinner-progress {
    stroke: url(#spinner-gradient);
    stroke-dasharray: 80, 150;
    stroke-dashoffset: 0;
    animation: spinner-dash 1.4s ease-in-out infinite;
}

@keyframes spinner-dash {
    0% {
        stroke-dasharray: 1, 150;
        stroke-dashoffset: 0;
    }
    50% {
        stroke-dasharray: 80, 150;
        stroke-dashoffset: -30;
    }
    100% {
        stroke-dasharray: 80, 150;
        stroke-dashoffset: -120;
    }
}

/* Loading Message */
.loading-text {
    font-size: 0.95rem;
    color: #475569;
    font-weight: 500;
    margin: 0;
    letter-spacing: 0.01em;
}

/* Transition */
.loading-fade-enter-active,
.loading-fade-leave-active {
    transition: opacity 0.3s ease;
}

.loading-fade-enter-from,
.loading-fade-leave-to {
    opacity: 0;
}
</style>
