/**
 * Validation utilities for form handling
 * Provides common validation patterns and helpers
 */

/**
 * Email validation regex (RFC 5322 simplified)
 */
const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/**
 * Phone validation regex (US format)
 */
const PHONE_REGEX = /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/;

/**
 * URL validation regex
 */
const URL_REGEX = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;

/**
 * Validation rule creators
 */
export const rules = {
    /**
     * Required field validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    required(message = 'This field is required') {
        return (value) => {
            if (value === null || value === undefined) return message;
            if (typeof value === 'string' && value.trim() === '') return message;
            if (Array.isArray(value) && value.length === 0) return message;
            return true;
        };
    },

    /**
     * Email validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    email(message = 'Please enter a valid email address') {
        return (value) => {
            if (!value) return true; // Use with required() for mandatory emails
            return EMAIL_REGEX.test(value) || message;
        };
    },

    /**
     * Minimum length validation
     * @param {number} min - Minimum length
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    minLength(min, message) {
        return (value) => {
            if (!value) return true;
            const length = typeof value === 'string' ? value.length : value.toString().length;
            return length >= min || (message || `Must be at least ${min} characters`);
        };
    },

    /**
     * Maximum length validation
     * @param {number} max - Maximum length
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    maxLength(max, message) {
        return (value) => {
            if (!value) return true;
            const length = typeof value === 'string' ? value.length : value.toString().length;
            return length <= max || (message || `Must be no more than ${max} characters`);
        };
    },

    /**
     * Minimum value validation (for numbers)
     * @param {number} min - Minimum value
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    min(min, message) {
        return (value) => {
            if (value === null || value === undefined || value === '') return true;
            const num = Number(value);
            return num >= min || (message || `Must be at least ${min}`);
        };
    },

    /**
     * Maximum value validation (for numbers)
     * @param {number} max - Maximum value
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    max(max, message) {
        return (value) => {
            if (value === null || value === undefined || value === '') return true;
            const num = Number(value);
            return num <= max || (message || `Must be no more than ${max}`);
        };
    },

    /**
     * Phone number validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    phone(message = 'Please enter a valid phone number') {
        return (value) => {
            if (!value) return true;
            // Remove common formatting characters for validation
            const cleaned = value.replace(/[\s\-\(\)\.]/g, '');
            return (cleaned.length >= 10 && /^\+?[0-9]+$/.test(cleaned)) || message;
        };
    },

    /**
     * URL validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    url(message = 'Please enter a valid URL') {
        return (value) => {
            if (!value) return true;
            return URL_REGEX.test(value) || message;
        };
    },

    /**
     * Pattern/regex validation
     * @param {RegExp} pattern - Regex pattern
     * @param {string} message - Error message
     * @returns {Function} Validation function
     */
    pattern(pattern, message = 'Invalid format') {
        return (value) => {
            if (!value) return true;
            return pattern.test(value) || message;
        };
    },

    /**
     * Matches another field validation
     * @param {Function} getOtherValue - Function to get the other field's value
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    matches(getOtherValue, message = 'Fields do not match') {
        return (value) => {
            if (!value) return true;
            return value === getOtherValue() || message;
        };
    },

    /**
     * Password strength validation
     * @param {Object} options - Strength requirements
     * @returns {Function} Validation function
     */
    password(options = {}) {
        const {
            minLength = 8,
            requireUppercase = true,
            requireLowercase = true,
            requireNumber = true,
            requireSpecial = false
        } = options;

        return (value) => {
            if (!value) return true;
            
            const errors = [];
            
            if (value.length < minLength) {
                errors.push(`at least ${minLength} characters`);
            }
            if (requireUppercase && !/[A-Z]/.test(value)) {
                errors.push('an uppercase letter');
            }
            if (requireLowercase && !/[a-z]/.test(value)) {
                errors.push('a lowercase letter');
            }
            if (requireNumber && !/[0-9]/.test(value)) {
                errors.push('a number');
            }
            if (requireSpecial && !/[!@#$%^&*(),.?":{}|<>]/.test(value)) {
                errors.push('a special character');
            }
            
            if (errors.length > 0) {
                return `Password must contain ${errors.join(', ')}`;
            }
            
            return true;
        };
    },

    /**
     * Numeric validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    numeric(message = 'Must be a number') {
        return (value) => {
            if (value === null || value === undefined || value === '') return true;
            return !isNaN(Number(value)) || message;
        };
    },

    /**
     * Integer validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    integer(message = 'Must be a whole number') {
        return (value) => {
            if (value === null || value === undefined || value === '') return true;
            return Number.isInteger(Number(value)) || message;
        };
    },

    /**
     * Currency/money validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    money(message = 'Please enter a valid amount') {
        return (value) => {
            if (!value) return true;
            const num = parseFloat(value.toString().replace(/[$,]/g, ''));
            return (!isNaN(num) && num >= 0) || message;
        };
    },

    /**
     * Date validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    date(message = 'Please enter a valid date') {
        return (value) => {
            if (!value) return true;
            const date = new Date(value);
            return !isNaN(date.getTime()) || message;
        };
    },

    /**
     * Future date validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    futureDate(message = 'Date must be in the future') {
        return (value) => {
            if (!value) return true;
            const date = new Date(value);
            return date > new Date() || message;
        };
    },

    /**
     * Past date validation
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    pastDate(message = 'Date must be in the past') {
        return (value) => {
            if (!value) return true;
            const date = new Date(value);
            return date < new Date() || message;
        };
    },

    /**
     * Age validation (must be at least X years old)
     * @param {number} minAge - Minimum age in years
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    minAge(minAge, message) {
        return (value) => {
            if (!value) return true;
            const birthDate = new Date(value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            return age >= minAge || (message || `Must be at least ${minAge} years old`);
        };
    },

    /**
     * File size validation
     * @param {number} maxSize - Maximum size in bytes
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    maxFileSize(maxSize, message) {
        const formatSize = (bytes) => {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        };

        return (file) => {
            if (!file) return true;
            const size = file.size || file;
            return size <= maxSize || (message || `File must be smaller than ${formatSize(maxSize)}`);
        };
    },

    /**
     * File type validation
     * @param {Array} allowedTypes - Allowed MIME types or extensions
     * @param {string} message - Custom error message
     * @returns {Function} Validation function
     */
    fileType(allowedTypes, message) {
        return (file) => {
            if (!file) return true;
            const type = file.type || '';
            const name = file.name || '';
            const ext = name.split('.').pop().toLowerCase();
            
            const valid = allowedTypes.some(allowed => {
                if (allowed.includes('/')) {
                    // MIME type
                    if (allowed.endsWith('/*')) {
                        return type.startsWith(allowed.replace('/*', '/'));
                    }
                    return type === allowed;
                }
                // Extension
                return ext === allowed.replace('.', '').toLowerCase();
            });
            
            return valid || (message || `Allowed types: ${allowedTypes.join(', ')}`);
        };
    }
};

/**
 * Combine multiple validation rules
 * @param  {...Function} validators - Validation functions
 * @returns {Function} Combined validation function
 */
export function combineRules(...validators) {
    return (value) => {
        for (const validator of validators) {
            const result = validator(value);
            if (result !== true) {
                return result;
            }
        }
        return true;
    };
}

/**
 * Validate an object against a schema
 * @param {Object} data - Data to validate
 * @param {Object} schema - Validation schema (field -> rules array)
 * @returns {Object} Validation result { valid, errors }
 */
export function validateObject(data, schema) {
    const errors = {};
    
    for (const [field, fieldRules] of Object.entries(schema)) {
        const value = data[field];
        const rulesArray = Array.isArray(fieldRules) ? fieldRules : [fieldRules];
        
        for (const rule of rulesArray) {
            const result = rule(value);
            if (result !== true) {
                errors[field] = result;
                break;
            }
        }
    }
    
    return {
        valid: Object.keys(errors).length === 0,
        errors
    };
}

/**
 * Create a form validator helper
 * @param {Object} schema - Validation schema
 * @returns {Object} Validator helper
 */
export function createValidator(schema) {
    const errors = {};
    let touched = {};
    
    return {
        /**
         * Validate a single field
         * @param {string} field - Field name
         * @param {*} value - Field value
         * @returns {string|true} Error message or true if valid
         */
        validateField(field, value) {
            const fieldRules = schema[field];
            if (!fieldRules) return true;
            
            const rulesArray = Array.isArray(fieldRules) ? fieldRules : [fieldRules];
            
            for (const rule of rulesArray) {
                const result = rule(value);
                if (result !== true) {
                    errors[field] = result;
                    return result;
                }
            }
            
            delete errors[field];
            return true;
        },
        
        /**
         * Validate all fields
         * @param {Object} data - Form data
         * @returns {boolean} Whether all fields are valid
         */
        validateAll(data) {
            const result = validateObject(data, schema);
            Object.assign(errors, result.errors);
            return result.valid;
        },
        
        /**
         * Mark a field as touched
         * @param {string} field - Field name
         */
        touch(field) {
            touched[field] = true;
        },
        
        /**
         * Get error for a field (only if touched)
         * @param {string} field - Field name
         * @returns {string|null} Error message or null
         */
        getError(field) {
            return touched[field] ? errors[field] : null;
        },
        
        /**
         * Get all errors
         * @returns {Object} All errors
         */
        getAllErrors() {
            return { ...errors };
        },
        
        /**
         * Check if form is valid
         * @returns {boolean} Whether form is valid
         */
        get isValid() {
            return Object.keys(errors).length === 0;
        },
        
        /**
         * Reset validation state
         */
        reset() {
            Object.keys(errors).forEach(key => delete errors[key]);
            touched = {};
        }
    };
}

export default {
    rules,
    combineRules,
    validateObject,
    createValidator
};
