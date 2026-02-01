<template>
  <component
    :is="as"
    :role="role"
    :aria-label="ariaLabel"
    :aria-labelledby="ariaLabelledby"
    :aria-describedby="ariaDescribedby"
    :aria-expanded="ariaExpanded"
    :aria-hidden="ariaHidden"
    :aria-live="ariaLive"
    :aria-atomic="ariaAtomic"
    :aria-busy="ariaBusy"
    :aria-current="ariaCurrent"
    :aria-disabled="ariaDisabled"
    :aria-haspopup="ariaHaspopup"
    :aria-pressed="ariaPressed"
    :aria-selected="ariaSelected"
    :tabindex="computedTabindex"
    @keydown="handleKeydown"
    @focus="handleFocus"
    @blur="handleBlur"
  >
    <slot />
  </component>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

/**
 * Accessible component wrapper that provides proper ARIA attributes
 * and keyboard navigation support.
 * 
 * Features:
 * - Automatic ARIA attribute management
 * - Keyboard navigation (Enter, Space, Escape, Arrow keys)
 * - Focus management
 * - Screen reader announcements
 */

interface Props {
  /** The HTML element or component to render */
  as?: string;
  /** ARIA role */
  role?: string;
  /** Accessible label */
  ariaLabel?: string;
  /** ID of element that labels this */
  ariaLabelledby?: string;
  /** ID of element that describes this */
  ariaDescribedby?: string;
  /** Expanded state for collapsible elements */
  ariaExpanded?: boolean;
  /** Hidden from assistive technologies */
  ariaHidden?: boolean;
  /** Live region announcement type */
  ariaLive?: 'polite' | 'assertive' | 'off';
  /** Announce all changes in live region */
  ariaAtomic?: boolean;
  /** Busy state for loading elements */
  ariaBusy?: boolean;
  /** Current item indicator */
  ariaCurrent?: 'page' | 'step' | 'location' | 'date' | 'time' | 'true' | 'false';
  /** Disabled state */
  ariaDisabled?: boolean;
  /** Has popup menu/dialog */
  ariaHaspopup?: 'menu' | 'listbox' | 'tree' | 'grid' | 'dialog' | 'true' | 'false';
  /** Pressed state for toggle buttons */
  ariaPressed?: boolean | 'mixed';
  /** Selected state */
  ariaSelected?: boolean;
  /** Tab index override */
  tabindex?: number;
  /** Whether element is focusable */
  focusable?: boolean;
  /** Enable arrow key navigation */
  arrowNavigation?: boolean;
  /** Items for arrow navigation */
  navigationItems?: unknown[];
  /** Current navigation index */
  navigationIndex?: number;
}

interface Emits {
  (e: 'keydown', event: KeyboardEvent): void;
  (e: 'enter', event: KeyboardEvent): void;
  (e: 'space', event: KeyboardEvent): void;
  (e: 'escape', event: KeyboardEvent): void;
  (e: 'arrowUp', event: KeyboardEvent): void;
  (e: 'arrowDown', event: KeyboardEvent): void;
  (e: 'arrowLeft', event: KeyboardEvent): void;
  (e: 'arrowRight', event: KeyboardEvent): void;
  (e: 'focus', event: FocusEvent): void;
  (e: 'blur', event: FocusEvent): void;
  (e: 'update:navigationIndex', index: number): void;
}

const props = withDefaults(defineProps<Props>(), {
  as: 'div',
  role: undefined,
  ariaLabel: undefined,
  ariaLabelledby: undefined,
  ariaDescribedby: undefined,
  ariaExpanded: undefined,
  ariaHidden: undefined,
  ariaLive: undefined,
  ariaAtomic: undefined,
  ariaBusy: undefined,
  ariaCurrent: undefined,
  ariaDisabled: undefined,
  ariaHaspopup: undefined,
  ariaPressed: undefined,
  ariaSelected: undefined,
  tabindex: undefined,
  focusable: true,
  arrowNavigation: false,
  navigationItems: () => [],
  navigationIndex: 0,
});

const emit = defineEmits<Emits>();

const isFocused = ref(false);

const computedTabindex = computed(() => {
  if (props.tabindex !== undefined) {
    return props.tabindex;
  }
  if (props.ariaDisabled) {
    return -1;
  }
  if (props.focusable) {
    return 0;
  }
  return undefined;
});

const handleKeydown = (event: KeyboardEvent): void => {
  emit('keydown', event);

  switch (event.key) {
    case 'Enter':
      emit('enter', event);
      break;

    case ' ':
    case 'Spacebar':
      emit('space', event);
      break;

    case 'Escape':
      emit('escape', event);
      break;

    case 'ArrowUp':
      emit('arrowUp', event);
      if (props.arrowNavigation && props.navigationItems?.length) {
        event.preventDefault();
        const newIndex = Math.max(0, props.navigationIndex - 1);
        emit('update:navigationIndex', newIndex);
      }
      break;

    case 'ArrowDown':
      emit('arrowDown', event);
      if (props.arrowNavigation && props.navigationItems?.length) {
        event.preventDefault();
        const newIndex = Math.min(
          props.navigationItems.length - 1,
          props.navigationIndex + 1
        );
        emit('update:navigationIndex', newIndex);
      }
      break;

    case 'ArrowLeft':
      emit('arrowLeft', event);
      break;

    case 'ArrowRight':
      emit('arrowRight', event);
      break;

    case 'Home':
      if (props.arrowNavigation && props.navigationItems?.length) {
        event.preventDefault();
        emit('update:navigationIndex', 0);
      }
      break;

    case 'End':
      if (props.arrowNavigation && props.navigationItems?.length) {
        event.preventDefault();
        emit('update:navigationIndex', props.navigationItems.length - 1);
      }
      break;
  }
};

const handleFocus = (event: FocusEvent): void => {
  isFocused.value = true;
  emit('focus', event);
};

const handleBlur = (event: FocusEvent): void => {
  isFocused.value = false;
  emit('blur', event);
};
</script>

<script lang="ts">
/**
 * Skip link component for keyboard navigation
 */
export const SkipLink = {
  name: 'SkipLink',
  props: {
    to: {
      type: String,
      required: true,
    },
    label: {
      type: String,
      default: 'Skip to main content',
    },
  },
  template: `
    <a
      :href="'#' + to"
      class="skip-link"
      @click.prevent="skipToContent"
    >
      {{ label }}
    </a>
  `,
  methods: {
    skipToContent() {
      const target = document.getElementById(this.to);
      if (target) {
        target.focus();
        target.scrollIntoView({ behavior: 'smooth' });
      }
    },
  },
};

/**
 * Live region for screen reader announcements
 */
export const LiveRegion = {
  name: 'LiveRegion',
  props: {
    politeness: {
      type: String,
      default: 'polite',
      validator: (value: string) => ['polite', 'assertive', 'off'].includes(value),
    },
    atomic: {
      type: Boolean,
      default: true,
    },
    message: {
      type: String,
      default: '',
    },
  },
  template: `
    <div
      role="status"
      :aria-live="politeness"
      :aria-atomic="atomic"
      class="sr-only"
    >
      {{ message }}
    </div>
  `,
};

/**
 * Focus trap component for modals and dialogs
 */
export const FocusTrap = {
  name: 'FocusTrap',
  props: {
    active: {
      type: Boolean,
      default: true,
    },
    returnFocusOnDeactivate: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      previouslyFocused: null as HTMLElement | null,
    };
  },
  mounted() {
    if (this.active) {
      this.activate();
    }
  },
  beforeUnmount() {
    this.deactivate();
  },
  watch: {
    active(newValue: boolean) {
      if (newValue) {
        this.activate();
      } else {
        this.deactivate();
      }
    },
  },
  methods: {
    activate() {
      this.previouslyFocused = document.activeElement as HTMLElement;
      this.focusFirstElement();
      document.addEventListener('keydown', this.handleKeydown);
    },
    deactivate() {
      document.removeEventListener('keydown', this.handleKeydown);
      if (this.returnFocusOnDeactivate && this.previouslyFocused) {
        this.previouslyFocused.focus();
      }
    },
    getFocusableElements() {
      const container = this.$el as HTMLElement;
      const selector = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
      ].join(', ');
      return Array.from(container.querySelectorAll<HTMLElement>(selector));
    },
    focusFirstElement() {
      const elements = this.getFocusableElements();
      if (elements.length > 0) {
        elements[0].focus();
      }
    },
    handleKeydown(event: KeyboardEvent) {
      if (event.key !== 'Tab') return;

      const elements = this.getFocusableElements();
      if (elements.length === 0) return;

      const firstElement = elements[0];
      const lastElement = elements[elements.length - 1];

      if (event.shiftKey) {
        if (document.activeElement === firstElement) {
          event.preventDefault();
          lastElement.focus();
        }
      } else {
        if (document.activeElement === lastElement) {
          event.preventDefault();
          firstElement.focus();
        }
      }
    },
  },
  template: `
    <div>
      <slot />
    </div>
  `,
};
</script>

<style scoped>
.skip-link {
  position: absolute;
  top: -40px;
  left: 0;
  padding: 8px 16px;
  background: var(--color-primary, #3b82f6);
  color: white;
  text-decoration: none;
  z-index: 100;
  transition: top 0.2s ease;
}

.skip-link:focus {
  top: 0;
}

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
</style>
