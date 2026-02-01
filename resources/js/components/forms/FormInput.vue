<template>
  <v-text-field
    v-model="internalValue"
    :label="label"
    :type="computedType"
    :autocomplete="computedAutocomplete"
    :inputmode="computedInputmode"
    :rules="rules"
    :required="required"
    :disabled="disabled"
    :readonly="readonly"
    :hint="hint"
    :persistent-hint="persistentHint"
    :prepend-inner-icon="prependIcon"
    :append-inner-icon="appendIcon"
    :aria-label="ariaLabel || label"
    :aria-required="required"
    :aria-invalid="hasError"
    :aria-describedby="describedBy"
    :error-messages="errorMessages"
    :counter="counter"
    :maxlength="maxlength"
    :placeholder="placeholder"
    variant="outlined"
    density="comfortable"
    @update:model-value="$emit('update:modelValue', $event)"
    @blur="$emit('blur', $event)"
    @focus="$emit('focus', $event)"
  >
    <template v-slot:append-inner v-if="type === 'password'">
      <v-btn
        :icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
        variant="text"
        size="small"
        @click="showPassword = !showPassword"
        :aria-label="showPassword ? 'Hide password' : 'Show password'"
        :aria-pressed="showPassword"
        tabindex="-1"
      ></v-btn>
    </template>
    
    <template v-slot:prepend-inner v-if="$slots['prepend-inner']">
      <slot name="prepend-inner"></slot>
    </template>
    
    <template v-slot:append v-if="$slots.append">
      <slot name="append"></slot>
    </template>
  </v-text-field>
</template>

<script setup>
/**
 * FormInput Component
 * Accessible form input with proper autocomplete, inputmode, and ARIA attributes
 * Supports all standard input types with enhanced accessibility
 */

import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, required: true },
  type: { type: String, default: 'text' },
  inputType: { 
    type: String, 
    default: null,
    validator: (v) => [
      'email', 'password', 'newPassword', 'tel', 'firstName', 'lastName',
      'name', 'address', 'city', 'state', 'zip', 'country', 'creditCard',
      'ccExpiry', 'ccCvc', 'organization', 'username', null
    ].includes(v)
  },
  autocomplete: { type: String, default: null },
  inputmode: { type: String, default: null },
  rules: { type: Array, default: () => [] },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  hint: { type: String, default: '' },
  persistentHint: { type: Boolean, default: false },
  prependIcon: { type: String, default: null },
  appendIcon: { type: String, default: null },
  ariaLabel: { type: String, default: null },
  describedBy: { type: String, default: null },
  hasError: { type: Boolean, default: false },
  errorMessages: { type: [String, Array], default: () => [] },
  counter: { type: [Boolean, Number, String], default: false },
  maxlength: { type: [Number, String], default: null },
  placeholder: { type: String, default: null }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const showPassword = ref(false);

const internalValue = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

const computedType = computed(() => {
  if (props.type === 'password' && showPassword.value) {
    return 'text';
  }
  return props.type;
});

// Autocomplete mapping based on input type
const autocompleteMap = {
  email: 'email',
  password: 'current-password',
  newPassword: 'new-password',
  tel: 'tel',
  firstName: 'given-name',
  lastName: 'family-name',
  name: 'name',
  address: 'street-address',
  city: 'address-level2',
  state: 'address-level1',
  zip: 'postal-code',
  country: 'country-name',
  creditCard: 'cc-number',
  ccExpiry: 'cc-exp',
  ccCvc: 'cc-csc',
  organization: 'organization',
  username: 'username'
};

// Inputmode mapping based on input type
const inputmodeMap = {
  email: 'email',
  tel: 'tel',
  zip: 'numeric',
  creditCard: 'numeric',
  ccCvc: 'numeric'
};

const computedAutocomplete = computed(() => {
  if (props.autocomplete) return props.autocomplete;
  if (props.inputType && autocompleteMap[props.inputType]) {
    return autocompleteMap[props.inputType];
  }
  // Infer from type prop
  if (props.type === 'email') return 'email';
  if (props.type === 'tel') return 'tel';
  return 'off';
});

const computedInputmode = computed(() => {
  if (props.inputmode) return props.inputmode;
  if (props.inputType && inputmodeMap[props.inputType]) {
    return inputmodeMap[props.inputType];
  }
  // Infer from type prop
  if (props.type === 'email') return 'email';
  if (props.type === 'tel') return 'tel';
  if (props.type === 'number') return 'numeric';
  return null;
});
</script>

<style scoped>
/* Password toggle button styling */
:deep(.v-field__append-inner .v-btn) {
  opacity: 0.7;
  transition: opacity 0.2s;
}

:deep(.v-field__append-inner .v-btn:hover) {
  opacity: 1;
}

/* Focus visible styling for accessibility */
:deep(.v-field:focus-within) {
  outline: 2px solid var(--v-theme-primary);
  outline-offset: 2px;
}
</style>
