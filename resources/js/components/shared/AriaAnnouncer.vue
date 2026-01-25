<template>
    <!-- 
        ARIA Live Region Component
        ==========================
        Provides screen reader announcements for dynamic content changes.
        
        Usage:
        <AriaAnnouncer ref="announcer" />
        
        // In script:
        announcer.value.announce('Your booking has been confirmed');
        announcer.value.announceAssertive('Error: Payment failed');
    -->
    <div class="aria-announcer">
        <!-- Polite announcements (non-urgent updates) -->
        <div
            role="status"
            aria-live="polite"
            aria-atomic="true"
            class="sr-only"
        >
            {{ politeMessage }}
        </div>

        <!-- Assertive announcements (urgent/important updates) -->
        <div
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
            class="sr-only"
        >
            {{ assertiveMessage }}
        </div>

        <!-- Log region for accumulated announcements (optional) -->
        <div
            v-if="enableLog"
            role="log"
            aria-live="off"
            aria-relevant="additions"
            class="sr-only"
        >
            <div v-for="(msg, index) in messageLog" :key="index">
                {{ msg.text }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    // Enable message log (for complex apps with many announcements)
    enableLog: {
        type: Boolean,
        default: false
    },
    // Maximum messages to keep in log
    maxLogSize: {
        type: Number,
        default: 50
    },
    // Auto-clear messages after timeout (ms)
    autoClearTimeout: {
        type: Number,
        default: 5000
    }
});

const politeMessage = ref('');
const assertiveMessage = ref('');
const messageLog = ref([]);

let politeTimer = null;
let assertiveTimer = null;

/**
 * Announce a message to screen readers
 * @param {string} message - The message to announce
 * @param {string} priority - 'polite' or 'assertive'
 */
const announce = (message, priority = 'polite') => {
    if (!message) return;

    // Add to log if enabled
    if (props.enableLog) {
        messageLog.value.push({
            text: message,
            priority,
            timestamp: new Date()
        });

        // Trim log if needed
        if (messageLog.value.length > props.maxLogSize) {
            messageLog.value = messageLog.value.slice(-props.maxLogSize);
        }
    }

    if (priority === 'assertive') {
        // Clear existing timer
        if (assertiveTimer) clearTimeout(assertiveTimer);
        
        // Clear and re-set to ensure announcement
        assertiveMessage.value = '';
        requestAnimationFrame(() => {
            assertiveMessage.value = message;
        });

        // Auto-clear after timeout
        assertiveTimer = setTimeout(() => {
            assertiveMessage.value = '';
        }, props.autoClearTimeout);
    } else {
        // Clear existing timer
        if (politeTimer) clearTimeout(politeTimer);
        
        // Clear and re-set to ensure announcement
        politeMessage.value = '';
        requestAnimationFrame(() => {
            politeMessage.value = message;
        });

        // Auto-clear after timeout
        politeTimer = setTimeout(() => {
            politeMessage.value = '';
        }, props.autoClearTimeout);
    }
};

/**
 * Announce with polite priority
 * @param {string} message - The message to announce
 */
const announcePolite = (message) => announce(message, 'polite');

/**
 * Announce with assertive priority (interrupts current speech)
 * @param {string} message - The message to announce
 */
const announceAssertive = (message) => announce(message, 'assertive');

/**
 * Clear all announcements
 */
const clearAnnouncements = () => {
    politeMessage.value = '';
    assertiveMessage.value = '';
    if (politeTimer) clearTimeout(politeTimer);
    if (assertiveTimer) clearTimeout(assertiveTimer);
};

/**
 * Clear the message log
 */
const clearLog = () => {
    messageLog.value = [];
};

// Expose methods for parent component usage
defineExpose({
    announce,
    announcePolite,
    announceAssertive,
    clearAnnouncements,
    clearLog,
    messageLog
});
</script>

<style scoped>
/* Screen reader only - hidden but accessible */
.sr-only {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
}

.aria-announcer {
    /* Ensure the component doesn't affect layout */
    position: absolute;
    pointer-events: none;
}
</style>
