/**
 * Validation Utilities
 * ====================
 * 
 * Production-grade validation functions for forms, API inputs, and data integrity.
 * All validators return consistent results and handle edge cases.
 * 
 * @version 1.0.0
 * @date January 28, 2026
 */

import type { ValidationResult, ValidationError } from '../types';

// =============================================================================
// Validation Result Builder
// =============================================================================

/**
 * Create a successful validation result
 */
export function validResult<T>(data: T): ValidationResult<T> {
  return { valid: true, data, errors: [] };
}

/**
 * Create a failed validation result
 */
export function invalidResult(errors: ValidationError[]): ValidationResult<never> {
  return { valid: false, errors };
}

/**
 * Create a single validation error
 */
export function createError(field: string, message: string, code?: string): ValidationError {
  return { field, message, code };
}

// =============================================================================
// String Validators
// =============================================================================

/**
 * Check if value is a non-empty string
 */
export function isNonEmptyString(value: unknown): value is string {
  return typeof value === 'string' && value.trim().length > 0;
}

/**
 * Validate email format (RFC 5322 compliant)
 */
export function isValidEmail(email: string): boolean {
  if (!isNonEmptyString(email)) return false;
  
  // RFC 5322 compliant email regex
  const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
  
  if (!emailRegex.test(email)) return false;
  
  // Additional checks
  const [localPart, domain] = email.split('@');
  if (localPart.length > 64) return false;
  if (domain.length > 255) return false;
  if (!domain.includes('.')) return false;
  
  return true;
}

/**
 * Validate phone number (US format)
 */
export function isValidPhoneNumber(phone: string): boolean {
  if (!isNonEmptyString(phone)) return false;
  
  // Remove all non-digits
  const digits = phone.replace(/\D/g, '');
  
  // Must be 10 digits (or 11 starting with 1)
  if (digits.length === 10) return true;
  if (digits.length === 11 && digits[0] === '1') return true;
  
  return false;
}

/**
 * Validate ZIP code (US 5-digit or 9-digit format)
 */
export function isValidZipCode(zip: string, strict5Digit = false): boolean {
  if (!isNonEmptyString(zip)) return false;
  
  if (strict5Digit) {
    return /^\d{5}$/.test(zip);
  }
  
  return /^\d{5}(-\d{4})?$/.test(zip);
}

/**
 * Validate URL format
 */
export function isValidUrl(url: string, requireHttps = false): boolean {
  if (!isNonEmptyString(url)) return false;
  
  try {
    const parsed = new URL(url);
    if (requireHttps && parsed.protocol !== 'https:') return false;
    return ['http:', 'https:'].includes(parsed.protocol);
  } catch {
    return false;
  }
}

// =============================================================================
// Password Validators
// =============================================================================

export interface PasswordStrength {
  score: 0 | 1 | 2 | 3 | 4;
  label: 'Very Weak' | 'Weak' | 'Fair' | 'Strong' | 'Very Strong';
  feedback: string[];
}

/**
 * Check password strength with detailed feedback
 */
export function checkPasswordStrength(password: string): PasswordStrength {
  const feedback: string[] = [];
  let score = 0;

  if (!password || password.length === 0) {
    return { score: 0, label: 'Very Weak', feedback: ['Password is required'] };
  }

  // Length checks
  if (password.length >= 8) score++;
  else feedback.push('Password should be at least 8 characters');

  if (password.length >= 12) score++;
  
  // Character variety
  if (/[a-z]/.test(password)) score += 0.5;
  else feedback.push('Add lowercase letters');

  if (/[A-Z]/.test(password)) score += 0.5;
  else feedback.push('Add uppercase letters');

  if (/\d/.test(password)) score += 0.5;
  else feedback.push('Add numbers');

  if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) score += 0.5;
  else feedback.push('Add special characters');

  // Common patterns (negative)
  const commonPatterns = [
    /^123/,
    /abc/i,
    /password/i,
    /qwerty/i,
    /(.)\1{2,}/, // Repeated characters
  ];
  
  for (const pattern of commonPatterns) {
    if (pattern.test(password)) {
      score = Math.max(0, score - 0.5);
      feedback.push('Avoid common patterns');
      break;
    }
  }

  // Normalize score to 0-4
  const normalizedScore = Math.min(4, Math.max(0, Math.round(score))) as 0 | 1 | 2 | 3 | 4;

  const labels: Record<number, PasswordStrength['label']> = {
    0: 'Very Weak',
    1: 'Weak',
    2: 'Fair',
    3: 'Strong',
    4: 'Very Strong',
  };

  return {
    score: normalizedScore,
    label: labels[normalizedScore],
    feedback: feedback.slice(0, 3), // Max 3 feedback items
  };
}

/**
 * Validate password meets security requirements
 */
export function isValidPassword(password: string): ValidationResult<string> {
  const errors: ValidationError[] = [];

  if (!password || password.length === 0) {
    return invalidResult([createError('password', 'Password is required', 'REQUIRED')]);
  }

  if (password.length < 8) {
    errors.push(createError('password', 'Password must be at least 8 characters', 'MIN_LENGTH'));
  }

  if (password.length > 128) {
    errors.push(createError('password', 'Password must be less than 128 characters', 'MAX_LENGTH'));
  }

  if (!/[a-z]/.test(password)) {
    errors.push(createError('password', 'Password must contain a lowercase letter', 'LOWERCASE'));
  }

  if (!/[A-Z]/.test(password)) {
    errors.push(createError('password', 'Password must contain an uppercase letter', 'UPPERCASE'));
  }

  if (!/\d/.test(password)) {
    errors.push(createError('password', 'Password must contain a number', 'NUMBER'));
  }

  if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
    errors.push(createError('password', 'Password must contain a special character', 'SPECIAL'));
  }

  if (errors.length > 0) {
    return invalidResult(errors);
  }

  return validResult(password);
}

// =============================================================================
// Number Validators
// =============================================================================

/**
 * Check if value is a finite number
 */
export function isValidNumber(value: unknown): value is number {
  return typeof value === 'number' && Number.isFinite(value);
}

/**
 * Check if value is a positive number
 */
export function isPositiveNumber(value: unknown): value is number {
  return isValidNumber(value) && value > 0;
}

/**
 * Check if value is within range (inclusive)
 */
export function isInRange(value: number, min: number, max: number): boolean {
  return isValidNumber(value) && value >= min && value <= max;
}

/**
 * Validate currency amount (in cents)
 */
export function isValidCurrencyAmount(amount: number, options: { min?: number; max?: number } = {}): boolean {
  const { min = 0, max = 999999999 } = options; // Max ~$10M
  
  if (!Number.isInteger(amount)) return false;
  if (amount < min || amount > max) return false;
  
  return true;
}

// =============================================================================
// Date Validators
// =============================================================================

/**
 * Check if date is valid
 */
export function isValidDate(date: unknown): boolean {
  if (!date) return false;
  
  const dateObj = date instanceof Date ? date : new Date(date as string);
  return !isNaN(dateObj.getTime());
}

/**
 * Check if date is in the future
 */
export function isFutureDate(date: Date | string): boolean {
  if (!isValidDate(date)) return false;
  
  const dateObj = date instanceof Date ? date : new Date(date);
  return dateObj.getTime() > Date.now();
}

/**
 * Check if date is in the past
 */
export function isPastDate(date: Date | string): boolean {
  if (!isValidDate(date)) return false;
  
  const dateObj = date instanceof Date ? date : new Date(date);
  return dateObj.getTime() < Date.now();
}

/**
 * Check if date is within a range
 */
export function isDateInRange(date: Date | string, start: Date | string, end: Date | string): boolean {
  if (!isValidDate(date) || !isValidDate(start) || !isValidDate(end)) return false;
  
  const dateTime = (date instanceof Date ? date : new Date(date)).getTime();
  const startTime = (start instanceof Date ? start : new Date(start)).getTime();
  const endTime = (end instanceof Date ? end : new Date(end)).getTime();
  
  return dateTime >= startTime && dateTime <= endTime;
}

// =============================================================================
// Form Validation
// =============================================================================

export interface FieldValidator<T = unknown> {
  field: string;
  value: T;
  rules: ValidationRule<T>[];
}

export interface ValidationRule<T> {
  validate: (value: T) => boolean;
  message: string;
  code?: string;
}

/**
 * Validate a single field with multiple rules
 */
export function validateField<T>(
  field: string,
  value: T,
  rules: ValidationRule<T>[]
): ValidationError[] {
  const errors: ValidationError[] = [];
  
  for (const rule of rules) {
    if (!rule.validate(value)) {
      errors.push(createError(field, rule.message, rule.code));
    }
  }
  
  return errors;
}

/**
 * Validate multiple fields
 */
export function validateForm(validators: FieldValidator[]): ValidationResult<void> {
  const allErrors: ValidationError[] = [];
  
  for (const { field, value, rules } of validators) {
    const fieldErrors = validateField(field, value, rules as ValidationRule<unknown>[]);
    allErrors.push(...fieldErrors);
  }
  
  if (allErrors.length > 0) {
    return invalidResult(allErrors);
  }
  
  return validResult(undefined);
}

// =============================================================================
// Common Validation Rules Factory
// =============================================================================

export const rules = {
  required: <T>(message = 'This field is required'): ValidationRule<T> => ({
    validate: (value) => {
      if (value === null || value === undefined) return false;
      if (typeof value === 'string') return value.trim().length > 0;
      if (Array.isArray(value)) return value.length > 0;
      return true;
    },
    message,
    code: 'REQUIRED',
  }),

  minLength: (min: number, message?: string): ValidationRule<string> => ({
    validate: (value) => typeof value === 'string' && value.length >= min,
    message: message || `Must be at least ${min} characters`,
    code: 'MIN_LENGTH',
  }),

  maxLength: (max: number, message?: string): ValidationRule<string> => ({
    validate: (value) => typeof value === 'string' && value.length <= max,
    message: message || `Must be no more than ${max} characters`,
    code: 'MAX_LENGTH',
  }),

  email: (message = 'Invalid email address'): ValidationRule<string> => ({
    validate: isValidEmail,
    message,
    code: 'INVALID_EMAIL',
  }),

  phone: (message = 'Invalid phone number'): ValidationRule<string> => ({
    validate: isValidPhoneNumber,
    message,
    code: 'INVALID_PHONE',
  }),

  zipCode: (message = 'Invalid ZIP code'): ValidationRule<string> => ({
    validate: (value) => isValidZipCode(value, true),
    message,
    code: 'INVALID_ZIP',
  }),

  min: (min: number, message?: string): ValidationRule<number> => ({
    validate: (value) => isValidNumber(value) && value >= min,
    message: message || `Must be at least ${min}`,
    code: 'MIN_VALUE',
  }),

  max: (max: number, message?: string): ValidationRule<number> => ({
    validate: (value) => isValidNumber(value) && value <= max,
    message: message || `Must be no more than ${max}`,
    code: 'MAX_VALUE',
  }),

  pattern: (regex: RegExp, message: string): ValidationRule<string> => ({
    validate: (value) => typeof value === 'string' && regex.test(value),
    message,
    code: 'PATTERN',
  }),

  match: <T>(otherValue: T, message = 'Values must match'): ValidationRule<T> => ({
    validate: (value) => value === otherValue,
    message,
    code: 'MISMATCH',
  }),
};

// =============================================================================
// Sanitization Functions
// =============================================================================

/**
 * Sanitize string for safe HTML display (prevent XSS)
 */
export function sanitizeHtml(str: string): string {
  if (!str) return '';
  
  const map: Record<string, string> = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
    '/': '&#x2F;',
    '`': '&#x60;',
    '=': '&#x3D;',
  };
  
  return str.replace(/[&<>"'`=\/]/g, (char) => map[char]);
}

/**
 * Strip HTML tags from string
 */
export function stripHtml(str: string): string {
  if (!str) return '';
  return str.replace(/<[^>]*>/g, '');
}

/**
 * Trim and normalize whitespace
 */
export function normalizeWhitespace(str: string): string {
  if (!str) return '';
  return str.trim().replace(/\s+/g, ' ');
}
