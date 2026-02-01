/**
 * Lightweight global state management using Vue 3 Composition API
 * Alternative to Vuex/Pinia for simpler use cases
 */

import { reactive, readonly, watch, computed } from 'vue';

/**
 * Create a simple store with reactive state
 * @param {Object} options - Store options
 * @returns {Object} Store instance
 */
export function createStore(options = {}) {
    const {
        state: initialState = {},
        getters = {},
        mutations = {},
        actions = {},
        persist = false,
        key = 'app-store'
    } = options;
    
    // Load persisted state if enabled
    let loadedState = initialState;
    if (persist && typeof localStorage !== 'undefined') {
        try {
            const saved = localStorage.getItem(key);
            if (saved) {
                loadedState = { ...initialState, ...JSON.parse(saved) };
            }
        } catch (e) {
            console.warn('Failed to load persisted state:', e);
        }
    }
    
    // Create reactive state
    const state = reactive(loadedState);
    
    // Create computed getters
    const computedGetters = {};
    Object.entries(getters).forEach(([name, getter]) => {
        computedGetters[name] = computed(() => getter(state, computedGetters));
    });
    
    // Create commit function for mutations
    const commit = (type, payload) => {
        if (!mutations[type]) {
            console.warn(`Unknown mutation: ${type}`);
            return;
        }
        mutations[type](state, payload);
    };
    
    // Create dispatch function for actions
    const dispatch = async (type, payload) => {
        if (!actions[type]) {
            console.warn(`Unknown action: ${type}`);
            return;
        }
        
        const context = {
            state: readonly(state),
            getters: computedGetters,
            commit,
            dispatch
        };
        
        return await actions[type](context, payload);
    };
    
    // Persist state on changes if enabled
    if (persist && typeof localStorage !== 'undefined') {
        watch(
            () => state,
            (newState) => {
                try {
                    localStorage.setItem(key, JSON.stringify(newState));
                } catch (e) {
                    console.warn('Failed to persist state:', e);
                }
            },
            { deep: true }
        );
    }
    
    return {
        state: readonly(state),
        getters: computedGetters,
        commit,
        dispatch,
        // Direct state access for mutations (internal use)
        _state: state
    };
}

/**
 * Create a simple reactive store for component-level state
 * @param {Object} initialState - Initial state object
 * @returns {Object} Reactive state with utility methods
 */
export function createLocalStore(initialState = {}) {
    const state = reactive(initialState);
    const history = [];
    let historyIndex = -1;
    const maxHistory = 50;
    
    const saveToHistory = () => {
        // Remove any future history if we're not at the end
        if (historyIndex < history.length - 1) {
            history.splice(historyIndex + 1);
        }
        
        // Add current state to history
        history.push(JSON.stringify(state));
        
        // Limit history size
        if (history.length > maxHistory) {
            history.shift();
        }
        
        historyIndex = history.length - 1;
    };
    
    return {
        state,
        
        // Set a single property
        set(key, value) {
            saveToHistory();
            state[key] = value;
        },
        
        // Set multiple properties
        patch(updates) {
            saveToHistory();
            Object.assign(state, updates);
        },
        
        // Reset to initial state
        reset() {
            saveToHistory();
            Object.keys(state).forEach(key => {
                if (key in initialState) {
                    state[key] = initialState[key];
                } else {
                    delete state[key];
                }
            });
        },
        
        // Undo last change
        undo() {
            if (historyIndex > 0) {
                historyIndex--;
                const previousState = JSON.parse(history[historyIndex]);
                Object.assign(state, previousState);
                return true;
            }
            return false;
        },
        
        // Redo undone change
        redo() {
            if (historyIndex < history.length - 1) {
                historyIndex++;
                const nextState = JSON.parse(history[historyIndex]);
                Object.assign(state, nextState);
                return true;
            }
            return false;
        },
        
        // Check if undo/redo is available
        canUndo: computed(() => historyIndex > 0),
        canRedo: computed(() => historyIndex < history.length - 1),
    };
}

/**
 * Create a feature flag store
 * @param {Object} flags - Initial feature flags
 * @returns {Object} Feature flag manager
 */
export function createFeatureFlags(flags = {}) {
    const state = reactive(flags);
    
    return {
        // Check if feature is enabled
        isEnabled(flag) {
            return state[flag] === true;
        },
        
        // Enable a feature
        enable(flag) {
            state[flag] = true;
        },
        
        // Disable a feature
        disable(flag) {
            state[flag] = false;
        },
        
        // Toggle a feature
        toggle(flag) {
            state[flag] = !state[flag];
        },
        
        // Get all flags
        getAll() {
            return { ...state };
        },
        
        // Load flags from server
        async loadFromServer(url) {
            try {
                const response = await fetch(url);
                const serverFlags = await response.json();
                Object.assign(state, serverFlags);
                return true;
            } catch (e) {
                console.warn('Failed to load feature flags:', e);
                return false;
            }
        }
    };
}

/**
 * Create a loading state manager
 * @returns {Object} Loading state manager
 */
export function createLoadingManager() {
    const loadingStates = reactive({});
    const errors = reactive({});
    
    return {
        // Start loading for a key
        start(key) {
            loadingStates[key] = true;
            delete errors[key];
        },
        
        // Stop loading for a key
        stop(key) {
            loadingStates[key] = false;
        },
        
        // Set error for a key
        error(key, message) {
            loadingStates[key] = false;
            errors[key] = message;
        },
        
        // Check if key is loading
        isLoading(key) {
            return loadingStates[key] === true;
        },
        
        // Get error for key
        getError(key) {
            return errors[key];
        },
        
        // Check if any key is loading
        get anyLoading() {
            return Object.values(loadingStates).some(v => v === true);
        },
        
        // Wrap an async function with loading state
        wrap(key, fn) {
            return async (...args) => {
                this.start(key);
                try {
                    const result = await fn(...args);
                    this.stop(key);
                    return result;
                } catch (e) {
                    this.error(key, e.message);
                    throw e;
                }
            };
        },
        
        // Get all loading states
        states: readonly(loadingStates),
        
        // Get all errors
        allErrors: readonly(errors)
    };
}

/**
 * Create a notification/toast store
 * @param {Object} options - Options
 * @returns {Object} Notification manager
 */
export function createNotificationStore(options = {}) {
    const { maxNotifications = 5, defaultDuration = 5000 } = options;
    
    const notifications = reactive([]);
    let nextId = 1;
    
    const add = (notification) => {
        const id = nextId++;
        const item = {
            id,
            type: 'info',
            duration: defaultDuration,
            dismissible: true,
            ...notification,
            timestamp: Date.now()
        };
        
        notifications.push(item);
        
        // Remove oldest if over limit
        while (notifications.length > maxNotifications) {
            notifications.shift();
        }
        
        // Auto-dismiss after duration
        if (item.duration > 0) {
            setTimeout(() => {
                remove(id);
            }, item.duration);
        }
        
        return id;
    };
    
    const remove = (id) => {
        const index = notifications.findIndex(n => n.id === id);
        if (index !== -1) {
            notifications.splice(index, 1);
        }
    };
    
    const clear = () => {
        notifications.splice(0, notifications.length);
    };
    
    return {
        notifications: readonly(notifications),
        add,
        remove,
        clear,
        
        // Convenience methods
        success(message, options = {}) {
            return add({ message, type: 'success', ...options });
        },
        error(message, options = {}) {
            return add({ message, type: 'error', duration: 0, ...options });
        },
        warning(message, options = {}) {
            return add({ message, type: 'warning', ...options });
        },
        info(message, options = {}) {
            return add({ message, type: 'info', ...options });
        }
    };
}

export default {
    createStore,
    createLocalStore,
    createFeatureFlags,
    createLoadingManager,
    createNotificationStore
};
