/**
 * Form Validation Composable
 * Provides reactive form validation with comprehensive rules
 */

import { ref, reactive, computed, watch } from 'vue';

/**
 * Built-in validation rules
 */
export const rules = {
  required: (value) => {
    if (Array.isArray(value)) return value.length > 0 || 'This field is required';
    if (typeof value === 'boolean') return value || 'This field is required';
    return !!value && value.toString().trim() !== '' || 'This field is required';
  },

  email: (value) => {
    if (!value) return true;
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(value) || 'Invalid email address';
  },

  phone: (value) => {
    if (!value) return true;
    const pattern = /^\+?[\d\s\-\(\)]{10,}$/;
    return pattern.test(value) || 'Invalid phone number';
  },

  url: (value) => {
    if (!value) return true;
    try {
      new URL(value);
      return true;
    } catch {
      return 'Invalid URL';
    }
  },

  minLength: (min) => (value) => {
    if (!value) return true;
    return value.length >= min || `Must be at least ${min} characters`;
  },

  maxLength: (max) => (value) => {
    if (!value) return true;
    return value.length <= max || `Must be at most ${max} characters`;
  },

  min: (minVal) => (value) => {
    if (value === '' || value === null || value === undefined) return true;
    return Number(value) >= minVal || `Must be at least ${minVal}`;
  },

  max: (maxVal) => (value) => {
    if (value === '' || value === null || value === undefined) return true;
    return Number(value) <= maxVal || `Must be at most ${maxVal}`;
  },

  numeric: (value) => {
    if (!value) return true;
    return !isNaN(Number(value)) || 'Must be a number';
  },

  integer: (value) => {
    if (!value) return true;
    return Number.isInteger(Number(value)) || 'Must be an integer';
  },

  alpha: (value) => {
    if (!value) return true;
    return /^[a-zA-Z]+$/.test(value) || 'Only letters allowed';
  },

  alphaNumeric: (value) => {
    if (!value) return true;
    return /^[a-zA-Z0-9]+$/.test(value) || 'Only letters and numbers allowed';
  },

  pattern: (regex, message = 'Invalid format') => (value) => {
    if (!value) return true;
    return regex.test(value) || message;
  },

  match: (fieldName, fieldValue) => (value) => {
    return value === fieldValue || `Must match ${fieldName}`;
  },

  password: (value) => {
    if (!value) return true;
    const hasUpperCase = /[A-Z]/.test(value);
    const hasLowerCase = /[a-z]/.test(value);
    const hasNumber = /\d/.test(value);
    const hasMinLength = value.length >= 8;
    
    if (!hasMinLength) return 'Password must be at least 8 characters';
    if (!hasUpperCase) return 'Password must contain an uppercase letter';
    if (!hasLowerCase) return 'Password must contain a lowercase letter';
    if (!hasNumber) return 'Password must contain a number';
    
    return true;
  },

  date: (value) => {
    if (!value) return true;
    const date = new Date(value);
    return !isNaN(date.getTime()) || 'Invalid date';
  },

  futureDate: (value) => {
    if (!value) return true;
    const date = new Date(value);
    return date > new Date() || 'Date must be in the future';
  },

  pastDate: (value) => {
    if (!value) return true;
    const date = new Date(value);
    return date < new Date() || 'Date must be in the past';
  },

  creditCard: (value) => {
    if (!value) return true;
    const cleaned = value.replace(/\D/g, '');
    return /^\d{13,19}$/.test(cleaned) || 'Invalid card number';
  },

  ssn: (value) => {
    if (!value) return true;
    const cleaned = value.replace(/\D/g, '');
    return /^\d{9}$/.test(cleaned) || 'Invalid SSN';
  },

  zipCode: (value) => {
    if (!value) return true;
    return /^\d{5}(-\d{4})?$/.test(value) || 'Invalid ZIP code';
  },

  /**
   * New York State ZIP code validation
   * Valid NY ZIPs: 10xxx-14xxx range OR special cases (00501, 00544, 06390)
   * Supports both 5-digit and ZIP+4 formats
   */
  nyZipCode: (value) => {
    if (!value) return true;
    // NY ZIP regex: special cases OR 10xxx-14xxx range, with optional -XXXX suffix
    const nyZipRegex = /^(00501|00544|06390|1[0-4]\d{3})(-\d{4})?$/;
    if (!nyZipRegex.test(value)) {
      // First check if it's at least a valid format
      if (!/^\d{5}(-\d{4})?$/.test(value)) {
        return 'ZIP code must be 5 digits';
      }
      return 'Must be a valid New York State ZIP code (10xxx-14xxx)';
    }
    return true;
  },

  custom: (validator, message) => (value) => {
    return validator(value) || message;
  }
};

/**
 * Main form validation composable
 */
export function useFormValidation(initialValues = {}, validationRules = {}) {
  // Form data
  const form = reactive({ ...initialValues });
  
  // Validation state
  const errors = reactive({});
  const touched = reactive({});
  const dirty = reactive({});
  
  // Submission state
  const isSubmitting = ref(false);
  const submitError = ref(null);
  const submitCount = ref(0);

  /**
   * Check if form is valid
   */
  const isValid = computed(() => {
    return Object.keys(errors).every(key => !errors[key]);
  });

  /**
   * Check if form has any touched fields
   */
  const isTouched = computed(() => {
    return Object.values(touched).some(Boolean);
  });

  /**
   * Check if form has any dirty fields
   */
  const isDirty = computed(() => {
    return Object.values(dirty).some(Boolean);
  });

  /**
   * Validate a single field
   */
  const validateField = (fieldName) => {
    const fieldRules = validationRules[fieldName];
    if (!fieldRules) {
      errors[fieldName] = null;
      return true;
    }

    const value = form[fieldName];
    const ruleList = Array.isArray(fieldRules) ? fieldRules : [fieldRules];

    for (const rule of ruleList) {
      const result = rule(value, form);
      if (result !== true) {
        errors[fieldName] = result;
        return false;
      }
    }

    errors[fieldName] = null;
    return true;
  };

  /**
   * Validate all fields
   */
  const validateAll = () => {
    let isValid = true;
    
    for (const fieldName of Object.keys(validationRules)) {
      if (!validateField(fieldName)) {
        isValid = false;
      }
    }
    
    return isValid;
  };

  /**
   * Touch a field
   */
  const touchField = (fieldName) => {
    touched[fieldName] = true;
    validateField(fieldName);
  };

  /**
   * Touch all fields
   */
  const touchAll = () => {
    Object.keys(validationRules).forEach(fieldName => {
      touched[fieldName] = true;
    });
    validateAll();
  };

  /**
   * Set field value
   */
  const setFieldValue = (fieldName, value) => {
    form[fieldName] = value;
    dirty[fieldName] = true;
    
    // Validate on change if already touched
    if (touched[fieldName]) {
      validateField(fieldName);
    }
  };

  /**
   * Set multiple field values
   */
  const setValues = (values) => {
    Object.entries(values).forEach(([key, value]) => {
      setFieldValue(key, value);
    });
  };

  /**
   * Reset form to initial values
   */
  const reset = (newInitialValues = null) => {
    const values = newInitialValues || initialValues;
    
    Object.keys(form).forEach(key => {
      form[key] = values[key] ?? null;
    });
    
    Object.keys(errors).forEach(key => {
      errors[key] = null;
    });
    
    Object.keys(touched).forEach(key => {
      touched[key] = false;
    });
    
    Object.keys(dirty).forEach(key => {
      dirty[key] = false;
    });
    
    submitError.value = null;
    submitCount.value = 0;
  };

  /**
   * Reset specific field
   */
  const resetField = (fieldName) => {
    form[fieldName] = initialValues[fieldName] ?? null;
    errors[fieldName] = null;
    touched[fieldName] = false;
    dirty[fieldName] = false;
  };

  /**
   * Get field props for v-model binding
   */
  const getFieldProps = (fieldName) => ({
    modelValue: form[fieldName],
    'onUpdate:modelValue': (value) => setFieldValue(fieldName, value),
    onBlur: () => touchField(fieldName),
    error: touched[fieldName] && !!errors[fieldName],
    errorMessages: touched[fieldName] ? errors[fieldName] : null
  });

  /**
   * Handle form submission
   */
  const handleSubmit = (submitHandler) => async (event) => {
    if (event) {
      event.preventDefault();
    }
    
    touchAll();
    
    if (!isValid.value) {
      // Focus first error field
      const firstErrorField = Object.keys(errors).find(key => errors[key]);
      if (firstErrorField) {
        const element = document.querySelector(`[name="${firstErrorField}"]`);
        element?.focus();
      }
      return;
    }
    
    isSubmitting.value = true;
    submitError.value = null;
    submitCount.value++;
    
    try {
      await submitHandler(form);
    } catch (error) {
      submitError.value = error.message || 'Submission failed';
      throw error;
    } finally {
      isSubmitting.value = false;
    }
  };

  /**
   * Watch form changes for real-time validation
   */
  const watchValidation = (immediate = false) => {
    Object.keys(validationRules).forEach(fieldName => {
      watch(
        () => form[fieldName],
        () => {
          if (touched[fieldName]) {
            validateField(fieldName);
          }
        },
        { immediate }
      );
    });
  };

  return {
    form,
    errors,
    touched,
    dirty,
    isValid,
    isTouched,
    isDirty,
    isSubmitting,
    submitError,
    submitCount,
    validateField,
    validateAll,
    touchField,
    touchAll,
    setFieldValue,
    setValues,
    reset,
    resetField,
    getFieldProps,
    handleSubmit,
    watchValidation
  };
}

/**
 * Simple field-level validation composable
 */
export function useFieldValidation(initialValue, fieldRules = []) {
  const value = ref(initialValue);
  const error = ref(null);
  const isTouched = ref(false);
  const isDirty = ref(false);

  const isValid = computed(() => !error.value);

  const validate = () => {
    for (const rule of fieldRules) {
      const result = rule(value.value);
      if (result !== true) {
        error.value = result;
        return false;
      }
    }
    error.value = null;
    return true;
  };

  const touch = () => {
    isTouched.value = true;
    validate();
  };

  const reset = (newValue = initialValue) => {
    value.value = newValue;
    error.value = null;
    isTouched.value = false;
    isDirty.value = false;
  };

  watch(value, () => {
    isDirty.value = true;
    if (isTouched.value) {
      validate();
    }
  });

  return {
    value,
    error,
    isTouched,
    isDirty,
    isValid,
    validate,
    touch,
    reset
  };
}

export default useFormValidation;
