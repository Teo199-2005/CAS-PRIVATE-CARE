import { ref, reactive, computed } from 'vue';

/**
 * Composable for toast notifications
 * Provides a centralized notification system
 */

// Global notification state
const notifications = ref([]);
let notificationId = 0;

// Default options
const defaultOptions = {
    duration: 5000,
    type: 'info', // 'info', 'success', 'warning', 'error'
    position: 'top-right',
    dismissible: true,
    icon: true,
};

// Type to icon mapping
const typeIcons = {
    info: 'mdi-information',
    success: 'mdi-check-circle',
    warning: 'mdi-alert',
    error: 'mdi-alert-circle',
};

// Type to color mapping
const typeColors = {
    info: 'blue',
    success: 'green',
    warning: 'orange',
    error: 'red',
};

export function useNotifications() {
    /**
     * Add a notification
     */
    const notify = (message, options = {}) => {
        const id = ++notificationId;
        const opts = { ...defaultOptions, ...options };

        const notification = {
            id,
            message,
            type: opts.type,
            icon: opts.icon ? typeIcons[opts.type] : null,
            color: typeColors[opts.type],
            dismissible: opts.dismissible,
            position: opts.position,
            createdAt: Date.now(),
        };

        notifications.value.push(notification);

        // Auto-dismiss after duration
        if (opts.duration > 0) {
            setTimeout(() => {
                dismiss(id);
            }, opts.duration);
        }

        return id;
    };

    /**
     * Dismiss a notification by ID
     */
    const dismiss = (id) => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index !== -1) {
            notifications.value.splice(index, 1);
        }
    };

    /**
     * Dismiss all notifications
     */
    const dismissAll = () => {
        notifications.value = [];
    };

    /**
     * Success notification shorthand
     */
    const success = (message, options = {}) => {
        return notify(message, { ...options, type: 'success' });
    };

    /**
     * Error notification shorthand
     */
    const error = (message, options = {}) => {
        return notify(message, { ...options, type: 'error', duration: 8000 });
    };

    /**
     * Warning notification shorthand
     */
    const warning = (message, options = {}) => {
        return notify(message, { ...options, type: 'warning', duration: 6000 });
    };

    /**
     * Info notification shorthand
     */
    const info = (message, options = {}) => {
        return notify(message, { ...options, type: 'info' });
    };

    /**
     * Promise-based notification (shows loading, then success/error)
     */
    const promise = async (promise, messages = {}) => {
        const loadingId = notify(messages.loading || 'Loading...', {
            type: 'info',
            duration: 0,
            dismissible: false,
        });

        try {
            const result = await promise;
            dismiss(loadingId);
            success(messages.success || 'Operation completed successfully');
            return result;
        } catch (err) {
            dismiss(loadingId);
            error(messages.error || err.message || 'An error occurred');
            throw err;
        }
    };

    /**
     * Computed: Get notifications by position
     */
    const getByPosition = (position) => {
        return computed(() => notifications.value.filter(n => n.position === position));
    };

    return {
        notifications,
        notify,
        dismiss,
        dismissAll,
        success,
        error,
        warning,
        info,
        promise,
        getByPosition,
    };
}
