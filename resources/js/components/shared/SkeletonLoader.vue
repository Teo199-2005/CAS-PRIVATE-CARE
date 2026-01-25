<template>
    <div 
        class="skeleton-wrapper" 
        :class="{ 
            'skeleton-animate': animate && !prefersReducedMotion,
            'skeleton-dark': dark 
        }"
        role="status"
        :aria-label="ariaLabel"
    >
        <!-- Card Skeleton -->
        <div v-if="type === 'card'" class="skeleton-card">
            <div v-if="showAvatar" class="skeleton-avatar" :style="{ width: avatarSize, height: avatarSize }"></div>
            <div class="skeleton-content">
                <div class="skeleton-line title" :style="{ width: titleWidth }"></div>
                <div v-for="n in lines" :key="n" class="skeleton-line text" :style="{ width: getLineWidth(n) }"></div>
            </div>
        </div>

        <!-- Stat Card Skeleton -->
        <div v-else-if="type === 'stat'" class="skeleton-stat">
            <div class="skeleton-stat-icon"></div>
            <div class="skeleton-stat-content">
                <div class="skeleton-line stat-value"></div>
                <div class="skeleton-line stat-label"></div>
            </div>
        </div>

        <!-- Table Row Skeleton -->
        <div v-else-if="type === 'table-row'" class="skeleton-table-row">
            <div 
                v-for="(col, index) in columns" 
                :key="index" 
                class="skeleton-line table-cell"
                :style="{ width: typeof col === 'object' ? col.width : 'auto', flex: typeof col === 'object' ? 'none' : 1 }"
            ></div>
        </div>

        <!-- List Item Skeleton -->
        <div v-else-if="type === 'list-item'" class="skeleton-list-item">
            <div v-if="showAvatar" class="skeleton-avatar small"></div>
            <div class="skeleton-content">
                <div class="skeleton-line title short"></div>
                <div class="skeleton-line text"></div>
            </div>
            <div v-if="showAction" class="skeleton-action"></div>
        </div>

        <!-- Button Skeleton -->
        <div v-else-if="type === 'button'" class="skeleton-button" :class="buttonSize"></div>

        <!-- Text Skeleton -->
        <div v-else-if="type === 'text'" class="skeleton-text-block">
            <div v-for="n in lines" :key="n" class="skeleton-line text" :style="{ width: getLineWidth(n) }"></div>
        </div>

        <!-- Image Skeleton -->
        <div v-else-if="type === 'image'" class="skeleton-image" :style="{ aspectRatio: imageAspectRatio }">
            <div class="skeleton-image-icon">
                <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                </svg>
            </div>
        </div>

        <!-- Header Skeleton -->
        <div v-else-if="type === 'header'" class="skeleton-header">
            <div class="skeleton-line heading"></div>
            <div class="skeleton-line subheading"></div>
        </div>

        <!-- Custom slot for custom skeleton layouts -->
        <slot v-else></slot>

        <!-- Screen reader text -->
        <span class="sr-only">{{ loadingText }}</span>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    // Type of skeleton
    type: {
        type: String,
        default: 'card',
        validator: (value) => ['card', 'stat', 'table-row', 'list-item', 'button', 'text', 'image', 'header', 'custom'].includes(value)
    },
    // Whether to animate the skeleton
    animate: {
        type: Boolean,
        default: true
    },
    // Dark mode variant
    dark: {
        type: Boolean,
        default: false
    },
    // Number of text lines
    lines: {
        type: Number,
        default: 2
    },
    // Show avatar placeholder
    showAvatar: {
        type: Boolean,
        default: true
    },
    // Avatar size
    avatarSize: {
        type: String,
        default: '48px'
    },
    // Show action button placeholder
    showAction: {
        type: Boolean,
        default: false
    },
    // Title width
    titleWidth: {
        type: String,
        default: '60%'
    },
    // Columns for table row (array of widths or count)
    columns: {
        type: [Number, Array],
        default: 4
    },
    // Button size
    buttonSize: {
        type: String,
        default: 'medium',
        validator: (value) => ['small', 'medium', 'large'].includes(value)
    },
    // Image aspect ratio
    imageAspectRatio: {
        type: String,
        default: '16/9'
    },
    // Accessibility label
    ariaLabel: {
        type: String,
        default: 'Loading content'
    },
    // Loading text for screen readers
    loadingText: {
        type: String,
        default: 'Loading...'
    }
});

// Check for reduced motion preference
const prefersReducedMotion = ref(false);

onMounted(() => {
    prefersReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
});

// Generate varied line widths for more natural appearance
const getLineWidth = (lineIndex) => {
    const widths = ['100%', '90%', '75%', '85%', '70%', '95%'];
    // Last line is usually shorter
    if (lineIndex === props.lines) {
        return '40%';
    }
    return widths[(lineIndex - 1) % widths.length];
};

// Convert columns prop to array if needed
const columnsArray = computed(() => {
    if (Array.isArray(props.columns)) {
        return props.columns;
    }
    return Array.from({ length: props.columns }, () => ({ width: 'auto' }));
});
</script>

<style scoped>
/* Base skeleton styles */
.skeleton-wrapper {
    width: 100%;
}

.skeleton-line {
    background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
    background-size: 200% 100%;
    border-radius: 4px;
    height: 1rem;
}

/* Animation */
.skeleton-animate .skeleton-line,
.skeleton-animate .skeleton-avatar,
.skeleton-animate .skeleton-stat-icon,
.skeleton-animate .skeleton-button,
.skeleton-animate .skeleton-image,
.skeleton-animate .skeleton-action {
    animation: shimmer 1.5s ease-in-out infinite;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

/* Reduced motion - no animation, just solid color */
@media (prefers-reduced-motion: reduce) {
    .skeleton-animate .skeleton-line,
    .skeleton-animate .skeleton-avatar,
    .skeleton-animate .skeleton-stat-icon,
    .skeleton-animate .skeleton-button,
    .skeleton-animate .skeleton-image,
    .skeleton-animate .skeleton-action {
        animation: none;
        background: #e5e7eb;
    }
}

/* Card skeleton */
.skeleton-card {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
}

.skeleton-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
    background-size: 200% 100%;
    flex-shrink: 0;
}

.skeleton-avatar.small {
    width: 36px;
    height: 36px;
}

.skeleton-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.skeleton-line.title {
    height: 1.25rem;
    margin-bottom: 0.25rem;
}

.skeleton-line.title.short {
    width: 50% !important;
}

.skeleton-line.text {
    height: 0.875rem;
}

/* Stat skeleton */
.skeleton-stat {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
}

.skeleton-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
    background-size: 200% 100%;
    flex-shrink: 0;
}

.skeleton-stat-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.skeleton-line.stat-value {
    height: 1.5rem;
    width: 60%;
}

.skeleton-line.stat-label {
    height: 0.75rem;
    width: 80%;
}

/* Table row skeleton */
.skeleton-table-row {
    display: flex;
    gap: 1rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    align-items: center;
}

.skeleton-line.table-cell {
    height: 0.875rem;
    flex: 1;
}

/* List item skeleton */
.skeleton-list-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-bottom: 1px solid #f1f5f9;
}

.skeleton-action {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
    background-size: 200% 100%;
    flex-shrink: 0;
}

/* Button skeleton */
.skeleton-button {
    background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
    background-size: 200% 100%;
    border-radius: 8px;
}

.skeleton-button.small {
    width: 80px;
    height: 32px;
}

.skeleton-button.medium {
    width: 120px;
    height: 40px;
}

.skeleton-button.large {
    width: 160px;
    height: 48px;
}

/* Image skeleton */
.skeleton-image {
    background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
    background-size: 200% 100%;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 120px;
}

.skeleton-image-icon {
    color: #d1d5db;
    opacity: 0.5;
}

/* Header skeleton */
.skeleton-header {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem 0;
}

.skeleton-line.heading {
    height: 2rem;
    width: 40%;
}

.skeleton-line.subheading {
    height: 1rem;
    width: 60%;
}

/* Dark mode */
.skeleton-dark .skeleton-line,
.skeleton-dark .skeleton-avatar,
.skeleton-dark .skeleton-stat-icon,
.skeleton-dark .skeleton-button,
.skeleton-dark .skeleton-image,
.skeleton-dark .skeleton-action {
    background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
    background-size: 200% 100%;
}

.skeleton-dark .skeleton-card,
.skeleton-dark .skeleton-stat {
    background: #1f2937;
    border-color: #374151;
}

/* Screen reader only */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Mobile adjustments */
@media (max-width: 480px) {
    .skeleton-card {
        padding: 0.75rem;
        gap: 0.75rem;
    }
    
    .skeleton-avatar {
        width: 40px;
        height: 40px;
    }
    
    .skeleton-stat {
        padding: 1rem;
    }
    
    .skeleton-stat-icon {
        width: 40px;
        height: 40px;
    }
}
</style>
