/**
 * ========================================================================
 * CAS Private Care - NY ZIP Code Validation Composable
 * ========================================================================
 * 
 * Provides New York State ZIP code validation utilities.
 * 
 * NY ZIP Code Rules:
 * - Must be 5 digits (optionally with -XXXX for ZIP+4)
 * - First two digits must be 10-14 (standard NY range)
 * - OR must be one of the special NY ZIPs: 00501, 00544, 06390
 * 
 * Valid examples: 10001, 11215, 12550, 14043, 00501
 * Invalid examples: 09000, 15001, 20001
 * 
 * @example
 * import { useNYZipCode } from '@/composables';
 * const { isValidNYZip, getNYRegion, validateNYZip } = useNYZipCode();
 * 
 * if (isValidNYZip('11300')) {
 *   console.log('Valid NY ZIP!');
 * }
 * 
 * @version 1.0.0
 * @since 2026-01-30
 */

import { ref, computed } from 'vue';

/**
 * Special NY ZIP codes that don't follow the standard 10-14 prefix rule
 */
const SPECIAL_NY_ZIPS = ['00501', '00544', '06390'];

/**
 * NY ZIP code validation regex
 * Matches: 00501, 00544, 06390 (special cases) OR 10xxx-14xxx (standard NY range)
 * Optional: -XXXX suffix for ZIP+4 format
 */
const NY_ZIP_REGEX = /^(00501|00544|06390|1[0-4]\d{3})(-\d{4})?$/;

/**
 * Basic ZIP format regex (5 digits or ZIP+4)
 */
const ZIP_FORMAT_REGEX = /^\d{5}(-\d{4})?$/;

/**
 * Check if a value is a valid ZIP code format
 * @param {string} zip ZIP code to validate
 * @returns {boolean}
 */
export function isValidZipFormat(zip) {
  if (!zip) return false;
  return ZIP_FORMAT_REGEX.test(String(zip).trim());
}

/**
 * Check if a ZIP code is a valid New York State ZIP code
 * @param {string} zip ZIP code to validate
 * @returns {boolean}
 */
export function isValidNYZip(zip) {
  if (!zip) return false;
  return NY_ZIP_REGEX.test(String(zip).trim());
}

/**
 * Get the 5-digit base ZIP code from a ZIP+4 format
 * @param {string} zip Full ZIP code (5 or 9 digit format)
 * @returns {string} 5-digit base ZIP code
 */
export function getBaseZip(zip) {
  if (!zip) return '';
  const trimmed = String(zip).trim();
  const match = trimmed.match(/^(\d{5})(-\d{4})?$/);
  return match ? match[1] : trimmed;
}

/**
 * Check if ZIP is a special NY ZIP (00501, 00544, 06390)
 * @param {string} zip ZIP code to check
 * @returns {boolean}
 */
export function isSpecialNYZip(zip) {
  return SPECIAL_NY_ZIPS.includes(getBaseZip(zip));
}

/**
 * Get the NY region based on ZIP code prefix
 * @param {string} zip 5-digit ZIP code
 * @returns {string|null} Region name or null if not a valid NY ZIP
 */
export function getNYRegion(zip) {
  const baseZip = getBaseZip(zip);
  
  if (!isValidNYZip(baseZip)) {
    return null;
  }

  // Handle special ZIP codes
  if (baseZip === '00501' || baseZip === '00544') {
    return 'Holtsville, NY'; // IRS/USPS special
  }
  if (baseZip === '06390') {
    return 'Fishers Island, NY';
  }

  // Get first 3 digits for region mapping
  const prefix = parseInt(baseZip.substring(0, 3), 10);

  // NYC regions
  if (prefix >= 100 && prefix <= 102) return 'Manhattan, NY';
  if (prefix === 103) return 'Staten Island, NY';
  if (prefix === 104) return 'Bronx, NY';
  if (prefix >= 105 && prefix <= 109) return 'Westchester, NY';
  if (prefix >= 110 && prefix <= 111) return 'Long Island, NY'; // Queens/Nassau
  if (prefix === 112) return 'Brooklyn, NY';
  if (prefix >= 113 && prefix <= 114) return 'Long Island, NY'; // Queens
  if (prefix >= 115 && prefix <= 119) return 'Long Island, NY'; // Nassau/Suffolk

  // Upstate regions
  if (prefix >= 120 && prefix <= 129) return 'Capital Region, NY'; // Albany, Hudson Valley
  if (prefix >= 130 && prefix <= 139) return 'Central NY'; // Syracuse area
  if (prefix >= 140 && prefix <= 149) return 'Western NY'; // Buffalo, Rochester area

  return 'New York, NY';
}

/**
 * Validate a NY ZIP code with detailed result
 * @param {string} zip ZIP code to validate
 * @returns {Object} Validation result with format_valid, ny_valid, and message
 */
export function validateNYZip(zip) {
  const trimmed = String(zip || '').trim();
  
  // Check format first
  if (!isValidZipFormat(trimmed)) {
    return {
      format_valid: false,
      ny_valid: false,
      message: 'ZIP code must be 5 digits'
    };
  }
  
  // Check if it's a valid NY ZIP
  if (!isValidNYZip(trimmed)) {
    return {
      format_valid: true,
      ny_valid: false,
      message: 'Not a valid NY ZIP code (must be 10xxx-14xxx)'
    };
  }
  
  return {
    format_valid: true,
    ny_valid: true,
    message: 'Valid NY ZIP code',
    region: getNYRegion(trimmed)
  };
}

/**
 * Composable for NY ZIP code validation with reactive state
 * @param {string} initialZip Initial ZIP code value
 * @returns {Object} Reactive validation utilities
 */
export function useNYZipCode(initialZip = '') {
  const zip = ref(initialZip);
  const isLoading = ref(false);
  const location = ref('');
  const error = ref('');

  const validation = computed(() => validateNYZip(zip.value));
  const isValid = computed(() => validation.value.ny_valid);
  const isFormatValid = computed(() => validation.value.format_valid);
  const region = computed(() => getNYRegion(zip.value));

  /**
   * Validate and lookup ZIP code location via API
   * @returns {Promise<string|null>} Location string or null
   */
  async function lookupLocation() {
    const baseZip = getBaseZip(zip.value);
    
    if (!baseZip || baseZip.length !== 5) {
      location.value = '';
      error.value = '';
      return null;
    }

    // Client-side NY validation first
    if (!isValidNYZip(baseZip)) {
      location.value = '';
      error.value = 'Not a NY ZIP code (must be 10xxx-14xxx)';
      return null;
    }

    isLoading.value = true;
    error.value = '';

    try {
      const response = await fetch(`/api/zipcode-lookup/${baseZip}`);
      
      if (response.ok) {
        const data = await response.json();
        if (data.success && data.location) {
          location.value = data.location;
          error.value = '';
          return data.location;
        }
      }
      
      // Fallback to region for valid NY ZIPs
      const fallbackLocation = getNYRegion(baseZip);
      if (fallbackLocation) {
        location.value = fallbackLocation;
        error.value = '';
        return fallbackLocation;
      }

      location.value = '';
      error.value = 'ZIP not found';
      return null;
    } catch (err) {
      console.error('ZIP lookup error:', err);
      // Fallback to region
      const fallbackLocation = getNYRegion(baseZip);
      if (fallbackLocation) {
        location.value = fallbackLocation;
        error.value = '';
        return fallbackLocation;
      }
      location.value = '';
      error.value = 'Lookup failed';
      return null;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Set ZIP value
   * @param {string} newZip New ZIP code
   */
  function setZip(newZip) {
    zip.value = String(newZip || '').replace(/[^\d-]/g, '').substring(0, 10);
  }

  /**
   * Reset state
   */
  function reset() {
    zip.value = '';
    location.value = '';
    error.value = '';
    isLoading.value = false;
  }

  return {
    // State
    zip,
    isLoading,
    location,
    error,
    
    // Computed
    validation,
    isValid,
    isFormatValid,
    region,
    
    // Methods
    lookupLocation,
    setZip,
    reset,
    
    // Static utilities (for convenience)
    isValidNYZip,
    isValidZipFormat,
    getBaseZip,
    getNYRegion,
    validateNYZip
  };
}

// Default export
export default useNYZipCode;
