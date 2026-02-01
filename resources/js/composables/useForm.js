import { ref, reactive } from 'vue';

/**
 * Composable for form handling with validation
 * Extracts form state, validation, and submission logic
 */
export function useForm(initialData = {}, validationRules = {}) {
    // Form state
    const form = reactive({ ...initialData });
    const errors = reactive({});
    const touched = reactive({});
    const submitting = ref(false);
    const submitted = ref(false);
    const isDirty = ref(false);

    // Track original values for dirty checking
    const originalData = { ...initialData };

    // Check if a field has errors
    const hasError = (field) => {
        return Boolean(errors[field]);
    };

    // Get error message for a field
    const getError = (field) => {
        return errors[field] || '';
    };

    // Mark field as touched
    const touch = (field) => {
        touched[field] = true;
    };

    // Validate a single field
    const validateField = (field) => {
        const rules = validationRules[field];
        if (!rules) return true;

        const value = form[field];
        errors[field] = '';

        for (const rule of rules) {
            const result = rule(value, form);
            if (result !== true) {
                errors[field] = result;
                return false;
            }
        }

        return true;
    };

    // Validate all fields
    const validate = () => {
        let isValid = true;

        for (const field of Object.keys(validationRules)) {
            if (!validateField(field)) {
                isValid = false;
            }
        }

        return isValid;
    };

    // Set server-side errors
    const setErrors = (serverErrors) => {
        for (const [field, messages] of Object.entries(serverErrors)) {
            errors[field] = Array.isArray(messages) ? messages[0] : messages;
        }
    };

    // Clear errors
    const clearErrors = (field = null) => {
        if (field) {
            delete errors[field];
        } else {
            Object.keys(errors).forEach(key => delete errors[key]);
        }
    };

    // Reset form to initial values
    const reset = () => {
        Object.assign(form, { ...originalData });
        Object.keys(errors).forEach(key => delete errors[key]);
        Object.keys(touched).forEach(key => delete touched[key]);
        isDirty.value = false;
        submitted.value = false;
    };

    // Update form data
    const setData = (data) => {
        Object.assign(form, data);
        isDirty.value = true;
    };

    // Handle input change
    const handleChange = (field, value) => {
        form[field] = value;
        isDirty.value = true;

        // Validate on change if field has been touched
        if (touched[field]) {
            validateField(field);
        }
    };

    // Handle blur (validate on blur)
    const handleBlur = (field) => {
        touch(field);
        validateField(field);
    };

    // Submit handler
    const handleSubmit = async (submitFn) => {
        submitted.value = true;

        // Mark all fields as touched
        Object.keys(validationRules).forEach(field => {
            touched[field] = true;
        });

        if (!validate()) {
            return { success: false, errors };
        }

        submitting.value = true;

        try {
            const result = await submitFn(form);
            isDirty.value = false;
            return { success: true, data: result };
        } catch (error) {
            if (error.response?.data?.errors) {
                setErrors(error.response.data.errors);
            }
            return { success: false, error };
        } finally {
            submitting.value = false;
        }
    };

    return {
        form,
        errors,
        touched,
        submitting,
        submitted,
        isDirty,
        hasError,
        getError,
        touch,
        validateField,
        validate,
        setErrors,
        clearErrors,
        reset,
        setData,
        handleChange,
        handleBlur,
        handleSubmit,
    };
}

// Common validation rules
export const validators = {
    required: (message = 'This field is required') => (value) => {
        if (value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0)) {
            return message;
        }
        return true;
    },

    email: (message = 'Please enter a valid email') => (value) => {
        if (!value) return true;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(value) || message;
    },

    minLength: (min, message) => (value) => {
        if (!value) return true;
        return value.length >= min || (message || `Minimum ${min} characters required`);
    },

    maxLength: (max, message) => (value) => {
        if (!value) return true;
        return value.length <= max || (message || `Maximum ${max} characters allowed`);
    },

    min: (minValue, message) => (value) => {
        if (value === null || value === undefined || value === '') return true;
        return Number(value) >= minValue || (message || `Minimum value is ${minValue}`);
    },

    max: (maxValue, message) => (value) => {
        if (value === null || value === undefined || value === '') return true;
        return Number(value) <= maxValue || (message || `Maximum value is ${maxValue}`);
    },

    pattern: (regex, message = 'Invalid format') => (value) => {
        if (!value) return true;
        return regex.test(value) || message;
    },

    phone: (message = 'Please enter a valid phone number') => (value) => {
        if (!value) return true;
        const phoneRegex = /^\+?[\d\s\-()]{10,}$/;
        return phoneRegex.test(value) || message;
    },

    zipCode: (message = 'Please enter a valid ZIP code') => (value) => {
        if (!value) return true;
        const zipRegex = /^\d{5}(-\d{4})?$/;
        return zipRegex.test(value) || message;
    },

    url: (message = 'Please enter a valid URL') => (value) => {
        if (!value) return true;
        try {
            new URL(value);
            return true;
        } catch {
            return message;
        }
    },

    date: (message = 'Please enter a valid date') => (value) => {
        if (!value) return true;
        const date = new Date(value);
        return !isNaN(date.getTime()) || message;
    },

    dateAfter: (afterDate, message) => (value) => {
        if (!value) return true;
        return new Date(value) > new Date(afterDate) || (message || `Date must be after ${afterDate}`);
    },

    dateBefore: (beforeDate, message) => (value) => {
        if (!value) return true;
        return new Date(value) < new Date(beforeDate) || (message || `Date must be before ${beforeDate}`);
    },

    matches: (fieldName, message) => (value, form) => {
        if (!value) return true;
        return value === form[fieldName] || (message || `Must match ${fieldName}`);
    },

    integer: (message = 'Please enter a whole number') => (value) => {
        if (value === null || value === undefined || value === '') return true;
        return Number.isInteger(Number(value)) || message;
    },

    decimal: (places = 2, message) => (value) => {
        if (value === null || value === undefined || value === '') return true;
        const regex = new RegExp(`^-?\\d+(\\.\\d{1,${places}})?$`);
        return regex.test(String(value)) || (message || `Maximum ${places} decimal places allowed`);
    },
};
