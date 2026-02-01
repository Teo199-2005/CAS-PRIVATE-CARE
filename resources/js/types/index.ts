/**
 * TypeScript Type Definitions
 * ============================
 * 
 * Central type definitions for the CAS Private Care application.
 * These types ensure type safety across the entire frontend codebase.
 * 
 * @version 1.0.0
 * @date January 28, 2026
 */

// =============================================================================
// User & Authentication Types
// =============================================================================

export type UserType = 
  | 'client' 
  | 'caregiver' 
  | 'housekeeper' 
  | 'admin' 
  | 'adminstaff' 
  | 'marketing' 
  | 'training' 
  | 'training_center';

export type UserStatus = 
  | 'Active' 
  | 'pending' 
  | 'approved' 
  | 'rejected' 
  | 'suspended';

export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  user_type: UserType;
  status: UserStatus;
  avatar?: string;
  email_verified_at?: string | null;
  created_at: string;
  updated_at: string;
  stripe_customer_id?: string;
  stripe_account_id?: string;
}

export interface AuthenticatedUser extends User {
  session_token?: string;
  page_permissions?: Record<string, boolean>;
}

// =============================================================================
// Booking Types
// =============================================================================

export type BookingStatus = 
  | 'pending' 
  | 'approved' 
  | 'confirmed' 
  | 'in_progress' 
  | 'completed' 
  | 'cancelled';

export type PaymentStatus = 
  | 'pending' 
  | 'paid' 
  | 'failed' 
  | 'refunded' 
  | 'partially_refunded';

export type ServiceType = 
  | 'caregiver' 
  | 'housekeeper' 
  | 'personal_assistant';

export type DutyType = 
  | 'hourly' 
  | 'live-in' 
  | 'overnight' 
  | '24-hour';

export interface DaySchedule {
  day: string;
  enabled: boolean;
  start_time?: string;
  end_time?: string;
  hours?: number;
}

export interface Booking {
  id: number;
  client_id: number;
  parent_booking_id?: number;
  service_type: ServiceType;
  duty_type: DutyType;
  borough?: string;
  city?: string;
  county?: string;
  zipcode?: string;
  street_address?: string;
  apartment_unit?: string;
  service_date: string;
  start_time?: string;
  duration_days: number;
  hours_per_day?: number;
  hourly_rate: number;
  total_budget: number;
  status: BookingStatus;
  payment_status: PaymentStatus;
  assignment_status?: 'assigned' | 'unassigned' | 'partial';
  recurring_service: boolean;
  recurring_schedule?: string;
  auto_pay_enabled?: boolean;
  day_schedules?: DaySchedule[];
  special_instructions?: string;
  created_at: string;
  updated_at: string;
  // Relations
  client?: User;
  assignments?: BookingAssignment[];
}

export interface BookingAssignment {
  id: number;
  booking_id: number;
  caregiver_id?: number;
  housekeeper_id?: number;
  status: 'assigned' | 'completed' | 'cancelled';
  assigned_hourly_rate?: number;
  created_at: string;
  // Relations
  caregiver?: Caregiver;
  housekeeper?: Housekeeper;
}

// =============================================================================
// Caregiver & Housekeeper Types
// =============================================================================

export interface Caregiver {
  id: number;
  user_id: number;
  bio?: string;
  years_experience?: number;
  rating?: number;
  total_reviews?: number;
  certifications?: string[];
  languages?: string[];
  salary_min?: number;
  salary_max?: number;
  availability_status?: 'available' | 'busy' | 'unavailable';
  stripe_connect_id?: string;
  stripe_connect_status?: 'not_started' | 'pending' | 'active' | 'restricted';
  // Relations
  user?: User;
}

export interface Housekeeper {
  id: number;
  user_id: number;
  bio?: string;
  years_experience?: number;
  rating?: number;
  services_offered?: string[];
  availability_status?: 'available' | 'busy' | 'unavailable';
  stripe_connect_id?: string;
  stripe_connect_status?: 'not_started' | 'pending' | 'active' | 'restricted';
  // Relations
  user?: User;
}

// =============================================================================
// Payment Types
// =============================================================================

export interface Payment {
  id: number;
  booking_id: number;
  client_id: number;
  amount: number; // In cents
  status: PaymentStatus;
  payment_method: 'credit_card' | 'bank_transfer' | 'stripe';
  stripe_payment_intent_id?: string;
  processing_fee?: number;
  created_at: string;
  updated_at: string;
}

export interface PaymentMethod {
  id: string;
  type: 'card' | 'bank_account';
  card?: {
    brand: string;
    last4: string;
    exp_month: number;
    exp_year: number;
  };
  is_default: boolean;
}

// =============================================================================
// Notification Types
// =============================================================================

export type NotificationType = 
  | 'info' 
  | 'success' 
  | 'warning' 
  | 'error' 
  | 'booking' 
  | 'payment' 
  | 'system';

export interface Notification {
  id: number;
  user_id: number;
  type: NotificationType;
  title: string;
  message: string;
  read: boolean;
  action_url?: string;
  created_at: string;
}

// =============================================================================
// Time Tracking Types
// =============================================================================

export interface TimeTracking {
  id: number;
  booking_id: number;
  caregiver_id?: number;
  housekeeper_id?: number;
  clock_in_time: string;
  clock_out_time?: string;
  hours_worked?: number;
  minutes_worked?: number;
  hourly_rate: number;
  total_earnings?: number;
  total_client_charge?: number;
  platform_fee?: number;
  status: 'clocked_in' | 'clocked_out' | 'approved' | 'paid';
  notes?: string;
  created_at: string;
}

// =============================================================================
// Review Types
// =============================================================================

export interface Review {
  id: number;
  booking_id: number;
  client_id: number;
  caregiver_id?: number;
  housekeeper_id?: number;
  rating: number; // 1-5
  comment?: string;
  created_at: string;
}

// =============================================================================
// API Response Types
// =============================================================================

export interface ApiResponse<T = unknown> {
  success: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> extends ApiResponse<T[]> {
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
  links: {
    first: string;
    last: string;
    prev?: string;
    next?: string;
  };
}

// =============================================================================
// Dashboard Stats Types
// =============================================================================

export interface AdminStats {
  total_users: number;
  total_clients: number;
  total_caregivers: number;
  total_housekeepers: number;
  active_bookings: number;
  total_revenue: number;
  user_growth: number;
  booking_growth: number;
  pending_applications: number;
}

export interface ClientStats {
  total_bookings: number;
  active_bookings: number;
  completed_bookings: number;
  total_spent: number;
  upcoming_booking?: Booking;
}

export interface CaregiverStats {
  total_hours: number;
  total_earnings: number;
  pending_earnings: number;
  active_assignments: number;
  rating: number;
  total_reviews: number;
}

// =============================================================================
// Form Types
// =============================================================================

export interface BookingFormData {
  service_type: ServiceType;
  duty_type: DutyType;
  borough?: string;
  city?: string;
  county?: string;
  zipcode: string;
  street_address?: string;
  apartment_unit?: string;
  service_date: string;
  start_time?: string;
  duration_days: number;
  hours_per_day: number;
  gender_preference?: 'male' | 'female' | 'no_preference';
  language_preference?: string;
  client_age?: string;
  mobility_level?: string;
  medical_conditions?: string[];
  specific_skills?: string[];
  transportation_needed: boolean;
  recurring_service: boolean;
  recurring_schedule?: string;
  day_schedules?: DaySchedule[];
  special_instructions?: string;
  referral_code?: string;
}

export interface ContactFormData {
  name: string;
  email: string;
  phone?: string;
  subject?: string;
  message: string;
  recaptcha_token?: string;
}

// =============================================================================
// Component Props Types
// =============================================================================

export interface StatCardProps {
  icon: string;
  value: string | number;
  label: string;
  change?: string;
  changeColor?: string;
  changeIcon?: string;
  iconClass?: string;
  staggerIndex?: number;
}

export interface LoadingOverlayProps {
  visible: boolean;
  context?: 'admin' | 'client' | 'caregiver' | 'housekeeper' | 'general';
  tagline?: string;
}

export interface NotificationToastProps {
  modelValue: boolean;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  timeout?: number;
}

// =============================================================================
// Event Types
// =============================================================================

export interface BookingCreatedEvent {
  booking: Booking;
  redirect?: string;
}

export interface PaymentCompletedEvent {
  payment: Payment;
  booking_id: number;
}

export interface SectionChangeEvent {
  section: string;
  previousSection?: string;
}

// =============================================================================
// Utility Types
// =============================================================================

export type Nullable<T> = T | null;
export type Optional<T> = T | undefined;
export type DeepPartial<T> = {
  [P in keyof T]?: T[P] extends object ? DeepPartial<T[P]> : T[P];
};

/** Makes specified keys required */
export type RequiredKeys<T, K extends keyof T> = T & Required<Pick<T, K>>;

/** Makes specified keys optional */
export type OptionalKeys<T, K extends keyof T> = Omit<T, K> & Partial<Pick<T, K>>;

/** Extract keys of type from object */
export type KeysOfType<T, V> = { [K in keyof T]: T[K] extends V ? K : never }[keyof T];

/** Strict object with no extra keys allowed */
export type Strict<T> = T & { [K in Exclude<string, keyof T>]?: never };

// =============================================================================
// Validation Types
// =============================================================================

export interface ValidationError {
  field: string;
  message: string;
  code?: string;
}

export interface ValidationResult<T = unknown> {
  valid: boolean;
  data?: T;
  errors: ValidationError[];
}

// =============================================================================
// Security Types
// =============================================================================

export interface CsrfToken {
  token: string;
  expires_at: number;
}

export interface RateLimitInfo {
  remaining: number;
  limit: number;
  reset_at: number;
}

// =============================================================================
// Error Types
// =============================================================================

export interface AppError {
  code: string;
  message: string;
  details?: Record<string, unknown>;
  stack?: string;
  timestamp: string;
}

export type ErrorSeverity = 'info' | 'warning' | 'error' | 'critical';

// =============================================================================
// Accessibility Types
// =============================================================================

export interface A11yProps {
  'aria-label'?: string;
  'aria-labelledby'?: string;
  'aria-describedby'?: string;
  'aria-hidden'?: boolean;
  'aria-live'?: 'off' | 'polite' | 'assertive';
  'aria-atomic'?: boolean;
  role?: string;
  tabIndex?: number;
}

// =============================================================================
// Performance Types
// =============================================================================

export interface PerformanceMetrics {
  lcp?: number;  // Largest Contentful Paint
  fid?: number;  // First Input Delay
  cls?: number;  // Cumulative Layout Shift
  ttfb?: number; // Time to First Byte
  fcp?: number;  // First Contentful Paint
}

// =============================================================================
// Image Types (for responsive images)
// =============================================================================

export interface ResponsiveImageSrc {
  src: string;
  srcset?: string;
  sizes?: string;
  width?: number;
  height?: number;
  alt: string;
  loading?: 'lazy' | 'eager';
  decoding?: 'sync' | 'async' | 'auto';
  fetchPriority?: 'high' | 'low' | 'auto';
}

// =============================================================================
// Constants & Mappings
// =============================================================================

// Status color mapping helper - frozen to prevent mutation
export const STATUS_COLORS: Readonly<Record<string, string>> = Object.freeze({
  Active: 'success',
  approved: 'success',
  completed: 'success',
  paid: 'success',
  pending: 'warning',
  in_progress: 'info',
  rejected: 'error',
  cancelled: 'error',
  failed: 'error',
  suspended: 'grey',
});

// HTTP Status codes for API responses
export const HTTP_STATUS = Object.freeze({
  OK: 200,
  CREATED: 201,
  NO_CONTENT: 204,
  BAD_REQUEST: 400,
  UNAUTHORIZED: 401,
  FORBIDDEN: 403,
  NOT_FOUND: 404,
  UNPROCESSABLE_ENTITY: 422,
  TOO_MANY_REQUESTS: 429,
  INTERNAL_SERVER_ERROR: 500,
  SERVICE_UNAVAILABLE: 503,
} as const);

export type HttpStatusCode = typeof HTTP_STATUS[keyof typeof HTTP_STATUS];

// =============================================================================
// Function Types
// =============================================================================

/** Format currency with proper locale and symbol */
export type CurrencyFormatter = (amount: number, currency?: string, locale?: string) => string;

/** Format date with various format options */
export type DateFormatter = (date: string | Date, format?: string, locale?: string) => string;

/** Debounced function type */
export type DebouncedFn<T extends (...args: unknown[]) => unknown> = T & {
  cancel: () => void;
  flush: () => ReturnType<T>;
};

/** Throttled function type */
export type ThrottledFn<T extends (...args: unknown[]) => unknown> = T & {
  cancel: () => void;
};

// =============================================================================
// Type Guards (runtime type checking)
// =============================================================================

/** Check if value is a valid User object */
export function isUser(value: unknown): value is User {
  return (
    typeof value === 'object' &&
    value !== null &&
    'id' in value &&
    'email' in value &&
    'user_type' in value
  );
}

/** Check if value is a valid Booking object */
export function isBooking(value: unknown): value is Booking {
  return (
    typeof value === 'object' &&
    value !== null &&
    'id' in value &&
    'client_id' in value &&
    'service_type' in value
  );
}

/** Check if value is a valid API response */
export function isApiResponse<T>(value: unknown): value is ApiResponse<T> {
  return (
    typeof value === 'object' &&
    value !== null &&
    'success' in value &&
    typeof (value as ApiResponse<T>).success === 'boolean'
  );
}

/** Check if value is a valid error response */
export function isApiError(value: unknown): value is ApiResponse<never> {
  return isApiResponse(value) && !value.success;
}
