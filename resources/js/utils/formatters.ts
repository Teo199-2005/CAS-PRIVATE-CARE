/**
 * Formatting Utilities
 * ====================
 * 
 * Production-grade formatting functions for currency, dates, numbers, and strings.
 * All functions are pure, immutable, and handle edge cases properly.
 * 
 * @version 1.0.0
 * @date January 28, 2026
 */

// =============================================================================
// Currency Formatting
// =============================================================================

/**
 * Format amount as currency string
 * 
 * @param amount - Amount in cents (for precision) or dollars
 * @param options - Formatting options
 * @returns Formatted currency string
 * 
 * @example
 * formatCurrency(2500) // "$25.00"
 * formatCurrency(2500, { inCents: true }) // "$25.00"
 * formatCurrency(25.00, { inCents: false }) // "$25.00"
 */
export function formatCurrency(
  amount: number,
  options: {
    currency?: string;
    locale?: string;
    inCents?: boolean;
    showSymbol?: boolean;
    minimumFractionDigits?: number;
    maximumFractionDigits?: number;
  } = {}
): string {
  const {
    currency = 'USD',
    locale = 'en-US',
    inCents = true,
    showSymbol = true,
    minimumFractionDigits = 2,
    maximumFractionDigits = 2,
  } = options;

  // Handle invalid input
  if (!Number.isFinite(amount)) {
    return showSymbol ? '$0.00' : '0.00';
  }

  // Convert from cents to dollars if needed
  const dollars = inCents ? amount / 100 : amount;

  try {
    const formatter = new Intl.NumberFormat(locale, {
      style: showSymbol ? 'currency' : 'decimal',
      currency: showSymbol ? currency : undefined,
      minimumFractionDigits,
      maximumFractionDigits,
    });

    return formatter.format(dollars);
  } catch {
    // Fallback for unsupported locales/currencies
    const formatted = dollars.toFixed(minimumFractionDigits);
    return showSymbol ? `$${formatted}` : formatted;
  }
}

/**
 * Parse currency string back to number (in cents)
 * 
 * @param value - Currency string to parse
 * @returns Amount in cents
 */
export function parseCurrency(value: string): number {
  if (!value) return 0;
  
  // Remove currency symbols and non-numeric characters except . and -
  const cleaned = value.replace(/[^0-9.-]/g, '');
  const parsed = parseFloat(cleaned);
  
  return Number.isFinite(parsed) ? Math.round(parsed * 100) : 0;
}

// =============================================================================
// Date Formatting
// =============================================================================

/**
 * Format date/datetime with various options
 * 
 * @param date - Date to format (string, Date, or timestamp)
 * @param options - Formatting options
 * @returns Formatted date string
 * 
 * @example
 * formatDate('2026-01-28') // "January 28, 2026"
 * formatDate('2026-01-28', { format: 'short' }) // "1/28/2026"
 * formatDate('2026-01-28T10:30:00', { includeTime: true }) // "January 28, 2026 at 10:30 AM"
 */
export function formatDate(
  date: string | Date | number,
  options: {
    format?: 'full' | 'long' | 'medium' | 'short' | 'iso';
    locale?: string;
    includeTime?: boolean;
    timeFormat?: '12h' | '24h';
    relative?: boolean;
  } = {}
): string {
  const {
    format = 'long',
    locale = 'en-US',
    includeTime = false,
    timeFormat = '12h',
    relative = false,
  } = options;

  // Parse the date
  let dateObj: Date;
  try {
    if (date instanceof Date) {
      dateObj = date;
    } else if (typeof date === 'number') {
      dateObj = new Date(date);
    } else {
      dateObj = new Date(date);
    }

    if (isNaN(dateObj.getTime())) {
      return 'Invalid date';
    }
  } catch {
    return 'Invalid date';
  }

  // Return relative time if requested
  if (relative) {
    return formatRelativeTime(dateObj);
  }

  // ISO format
  if (format === 'iso') {
    return dateObj.toISOString();
  }

  // Map format to Intl options
  const dateStyleMap: Record<string, Intl.DateTimeFormatOptions['dateStyle']> = {
    full: 'full',
    long: 'long',
    medium: 'medium',
    short: 'short',
  };

  try {
    const formatOptions: Intl.DateTimeFormatOptions = {
      dateStyle: dateStyleMap[format],
    };

    if (includeTime) {
      formatOptions.timeStyle = 'short';
      formatOptions.hour12 = timeFormat === '12h';
    }

    return new Intl.DateTimeFormat(locale, formatOptions).format(dateObj);
  } catch {
    // Fallback
    return dateObj.toLocaleDateString();
  }
}

/**
 * Format date as relative time (e.g., "2 hours ago", "in 3 days")
 * 
 * @param date - Date to format
 * @param locale - Locale for formatting
 * @returns Relative time string
 */
export function formatRelativeTime(date: Date | string, locale = 'en-US'): string {
  const dateObj = typeof date === 'string' ? new Date(date) : date;
  
  if (isNaN(dateObj.getTime())) {
    return 'Invalid date';
  }

  const now = new Date();
  const diffMs = dateObj.getTime() - now.getTime();
  const diffSec = Math.round(diffMs / 1000);
  const diffMin = Math.round(diffSec / 60);
  const diffHour = Math.round(diffMin / 60);
  const diffDay = Math.round(diffHour / 24);
  const diffWeek = Math.round(diffDay / 7);
  const diffMonth = Math.round(diffDay / 30);
  const diffYear = Math.round(diffDay / 365);

  try {
    const rtf = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });

    if (Math.abs(diffSec) < 60) {
      return rtf.format(diffSec, 'second');
    } else if (Math.abs(diffMin) < 60) {
      return rtf.format(diffMin, 'minute');
    } else if (Math.abs(diffHour) < 24) {
      return rtf.format(diffHour, 'hour');
    } else if (Math.abs(diffDay) < 7) {
      return rtf.format(diffDay, 'day');
    } else if (Math.abs(diffWeek) < 4) {
      return rtf.format(diffWeek, 'week');
    } else if (Math.abs(diffMonth) < 12) {
      return rtf.format(diffMonth, 'month');
    } else {
      return rtf.format(diffYear, 'year');
    }
  } catch {
    // Fallback for unsupported browsers
    if (Math.abs(diffDay) < 1) {
      return diffMs >= 0 ? 'today' : 'today';
    } else if (diffDay === 1) {
      return 'tomorrow';
    } else if (diffDay === -1) {
      return 'yesterday';
    } else if (diffDay > 0) {
      return `in ${diffDay} days`;
    } else {
      return `${Math.abs(diffDay)} days ago`;
    }
  }
}

/**
 * Format time duration (e.g., hours and minutes)
 * 
 * @param minutes - Total minutes
 * @param options - Formatting options
 * @returns Formatted duration string
 */
export function formatDuration(
  minutes: number,
  options: {
    format?: 'short' | 'long' | 'digital';
  } = {}
): string {
  const { format = 'short' } = options;

  if (!Number.isFinite(minutes) || minutes < 0) {
    return format === 'digital' ? '0:00' : '0 min';
  }

  const hours = Math.floor(minutes / 60);
  const mins = Math.round(minutes % 60);

  switch (format) {
    case 'digital':
      return `${hours}:${mins.toString().padStart(2, '0')}`;
    case 'long':
      if (hours === 0) {
        return `${mins} minute${mins !== 1 ? 's' : ''}`;
      } else if (mins === 0) {
        return `${hours} hour${hours !== 1 ? 's' : ''}`;
      } else {
        return `${hours} hour${hours !== 1 ? 's' : ''} ${mins} minute${mins !== 1 ? 's' : ''}`;
      }
    case 'short':
    default:
      if (hours === 0) {
        return `${mins} min`;
      } else if (mins === 0) {
        return `${hours}h`;
      } else {
        return `${hours}h ${mins}m`;
      }
  }
}

// =============================================================================
// Number Formatting
// =============================================================================

/**
 * Format number with locale-aware separators
 * 
 * @param value - Number to format
 * @param options - Formatting options
 * @returns Formatted number string
 */
export function formatNumber(
  value: number,
  options: {
    locale?: string;
    minimumFractionDigits?: number;
    maximumFractionDigits?: number;
    notation?: 'standard' | 'scientific' | 'engineering' | 'compact';
  } = {}
): string {
  const {
    locale = 'en-US',
    minimumFractionDigits = 0,
    maximumFractionDigits = 2,
    notation = 'standard',
  } = options;

  if (!Number.isFinite(value)) {
    return '0';
  }

  try {
    return new Intl.NumberFormat(locale, {
      minimumFractionDigits,
      maximumFractionDigits,
      notation,
    }).format(value);
  } catch {
    return value.toFixed(maximumFractionDigits);
  }
}

/**
 * Format number as percentage
 * 
 * @param value - Number to format (0-1 or 0-100)
 * @param options - Formatting options
 * @returns Formatted percentage string
 */
export function formatPercentage(
  value: number,
  options: {
    locale?: string;
    decimals?: number;
    isDecimal?: boolean;
  } = {}
): string {
  const { locale = 'en-US', decimals = 1, isDecimal = true } = options;

  if (!Number.isFinite(value)) {
    return '0%';
  }

  const percentage = isDecimal ? value : value / 100;

  try {
    return new Intl.NumberFormat(locale, {
      style: 'percent',
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    }).format(percentage);
  } catch {
    return `${(percentage * 100).toFixed(decimals)}%`;
  }
}

// =============================================================================
// String Formatting
// =============================================================================

/**
 * Capitalize first letter of string
 * 
 * @param str - String to capitalize
 * @returns Capitalized string
 */
export function capitalize(str: string): string {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

/**
 * Title case a string
 * 
 * @param str - String to title case
 * @returns Title-cased string
 */
export function titleCase(str: string): string {
  if (!str) return '';
  return str
    .toLowerCase()
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
}

/**
 * Truncate string with ellipsis
 * 
 * @param str - String to truncate
 * @param maxLength - Maximum length
 * @param ellipsis - Ellipsis string
 * @returns Truncated string
 */
export function truncate(str: string, maxLength: number, ellipsis = '...'): string {
  if (!str) return '';
  if (str.length <= maxLength) return str;
  return str.slice(0, maxLength - ellipsis.length).trim() + ellipsis;
}

/**
 * Format phone number to (XXX) XXX-XXXX
 * 
 * @param phone - Phone number string
 * @returns Formatted phone number
 */
export function formatPhoneNumber(phone: string): string {
  if (!phone) return '';
  
  // Remove all non-digits
  const digits = phone.replace(/\D/g, '');
  
  // Handle different lengths
  if (digits.length === 10) {
    return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6)}`;
  } else if (digits.length === 11 && digits[0] === '1') {
    return `+1 (${digits.slice(1, 4)}) ${digits.slice(4, 7)}-${digits.slice(7)}`;
  }
  
  return phone;
}

/**
 * Mask sensitive data (for display)
 * 
 * @param value - Value to mask
 * @param visibleChars - Number of chars to keep visible at end
 * @param maskChar - Character to use for masking
 * @returns Masked string
 */
export function maskSensitiveData(
  value: string,
  visibleChars = 4,
  maskChar = '•'
): string {
  if (!value) return '';
  if (value.length <= visibleChars) return maskChar.repeat(value.length);
  
  const masked = maskChar.repeat(value.length - visibleChars);
  const visible = value.slice(-visibleChars);
  
  return masked + visible;
}

/**
 * Format file size in human-readable format
 * 
 * @param bytes - File size in bytes
 * @param decimals - Number of decimal places
 * @returns Formatted file size
 */
export function formatFileSize(bytes: number, decimals = 2): string {
  if (bytes === 0) return '0 Bytes';
  if (!Number.isFinite(bytes) || bytes < 0) return 'Invalid size';

  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(decimals))} ${sizes[i]}`;
}
