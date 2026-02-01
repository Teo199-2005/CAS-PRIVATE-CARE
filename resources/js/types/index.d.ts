/**
 * CAS Private Care - TypeScript Type Definitions
 * 
 * Core type definitions for the application.
 * Use these types to ensure type safety across Vue components.
 */

// ============================================================================
// User Types
// ============================================================================

export type UserType = 'client' | 'caregiver' | 'housekeeper' | 'admin' | 'adminstaff' | 'marketing' | 'training';

export type UserStatus = 'pending' | 'approved' | 'rejected' | 'Active' | 'inactive' | 'suspended';

export interface User {
  id: number;
  name: string;
  email: string;
  user_type: UserType;
  status: UserStatus;
  avatar?: string | null;
  phone?: string | null;
  address?: string | null;
  city?: string | null;
  county?: string | null;
  zip_code?: string | null;
  email_verified_at?: string | null;
  stripe_customer_id?: string | null;
  stripe_account_id?: string | null;
  created_at: string;
  updated_at: string;
}

export interface Caregiver {
  id: number;
  user_id: number;
  user?: User;
  availability_status: 'available' | 'busy' | 'unavailable';
  hourly_rate: number;
  experience_years?: number;
  certifications?: string[];
  rating?: number;
  total_reviews?: number;
  bio?: string;
  specializations?: string[];
  created_at: string;
  updated_at: string;
}

export interface Housekeeper {
  id: number;
  user_id: number;
  user?: User;
  availability_status: 'available' | 'busy' | 'unavailable';
  hourly_rate: number;
  rating?: number;
  total_reviews?: number;
  bio?: string;
  services_offered?: string[];
  created_at: string;
  updated_at: string;
}

// ============================================================================
// Booking Types
// ============================================================================

export type BookingStatus = 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled' | 'on_hold';

export type ServiceType = 'caregiver' | 'housekeeper' | 'both';

export type DutyType = 'live-in' | 'live-out' | 'overnight' | 'hourly';

export interface Booking {
  id: number;
  client_id: number;
  client?: User;
  service_type: ServiceType;
  duty_type: DutyType;
  status: BookingStatus;
  service_date: string;
  start_time?: string;
  end_time?: string;
  duration_days: number;
  duration_hours?: number;
  hourly_rate: number;
  total_budget: number;
  processing_fee?: number;
  discount_amount?: number;
  city?: string;
  county?: string;
  address?: string;
  zip_code?: string;
  special_needs?: string;
  notes?: string;
  is_recurring: boolean;
  recurring_interval?: 'weekly' | 'biweekly' | 'monthly';
  created_at: string;
  updated_at: string;
  
  // Relationships
  assignments?: BookingAssignment[];
  payments?: Payment[];
  reviews?: Review[];
  time_trackings?: TimeTracking[];
}

export interface BookingAssignment {
  id: number;
  booking_id: number;
  caregiver_id?: number;
  housekeeper_id?: number;
  caregiver?: Caregiver;
  housekeeper?: Housekeeper;
  status: 'pending' | 'accepted' | 'rejected' | 'completed';
  assigned_rate: number;
  created_at: string;
}

// ============================================================================
// Payment Types
// ============================================================================

export type PaymentStatus = 'pending' | 'processing' | 'completed' | 'failed' | 'refunded' | 'disputed';

export type PaymentType = 'booking' | 'recurring' | 'refund' | 'payout';

export interface Payment {
  id: number;
  booking_id?: number;
  client_id: number;
  amount: number;
  status: PaymentStatus;
  payment_type: PaymentType;
  stripe_payment_intent_id?: string;
  stripe_charge_id?: string;
  processing_fee?: number;
  net_amount?: number;
  description?: string;
  paid_at?: string;
  refunded_at?: string;
  created_at: string;
  updated_at: string;
}

export interface Payout {
  id: number;
  user_id: number;
  user?: User;
  amount: number;
  status: 'pending' | 'in_transit' | 'paid' | 'failed' | 'cancelled';
  stripe_payout_id?: string;
  stripe_transfer_id?: string;
  arrival_date?: string;
  created_at: string;
}

// ============================================================================
// Time Tracking Types
// ============================================================================

export interface TimeTracking {
  id: number;
  booking_id: number;
  user_id: number;
  clock_in_time: string;
  clock_out_time?: string;
  total_hours?: number;
  notes?: string;
  location?: string;
  is_auto_clocked_out: boolean;
  created_at: string;
}

// ============================================================================
// Review Types
// ============================================================================

export interface Review {
  id: number;
  booking_id: number;
  reviewer_id: number;
  reviewee_id: number;
  reviewer?: User;
  reviewee?: User;
  rating: number; // 1-5
  comment?: string;
  is_approved: boolean;
  created_at: string;
}

// ============================================================================
// Notification Types
// ============================================================================

export type NotificationType = 'info' | 'success' | 'warning' | 'error';

export interface Notification {
  id: number | string;
  type: NotificationType;
  title: string;
  message: string;
  read: boolean;
  link?: string;
  created_at: string;
}

// ============================================================================
// API Response Types
// ============================================================================

export interface ApiResponse<T = unknown> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
  errors?: Record<string, string[]>;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
}

// ============================================================================
// Dashboard Types
// ============================================================================

export interface DashboardStats {
  total_bookings: number;
  active_bookings: number;
  completed_bookings: number;
  pending_bookings: number;
  cancelled_bookings: number;
  total_earnings?: number;
  total_spent?: number;
  this_month_earnings?: number;
  this_month_spent?: number;
}

export interface AdminDashboardStats extends DashboardStats {
  total_caregivers: number;
  total_housekeepers: number;
  total_clients: number;
  pending_caregivers: number;
  pending_housekeepers: number;
  total_revenue: number;
  this_month_revenue: number;
}

// ============================================================================
// Form Types
// ============================================================================

export interface FormState<T = Record<string, unknown>> {
  data: T;
  errors: Partial<Record<keyof T, string>>;
  processing: boolean;
  isDirty: boolean;
}

export interface ValidationRule {
  required?: boolean;
  min?: number;
  max?: number;
  pattern?: RegExp;
  custom?: (value: unknown) => boolean | string;
  message?: string;
}

// ============================================================================
// Component Prop Types
// ============================================================================

export interface BreadcrumbItem {
  label: string;
  path: string;
  icon?: string;
}

export interface MenuItem {
  title: string;
  icon: string;
  path?: string;
  action?: () => void;
  badge?: number | string;
  children?: MenuItem[];
  roles?: UserType[];
}

export interface StatCardData {
  title: string;
  value: number | string;
  icon: string;
  change?: number;
  changeLabel?: string;
  color?: string;
}

export interface TableColumn<T = unknown> {
  key: keyof T | string;
  title: string;
  sortable?: boolean;
  width?: string;
  align?: 'start' | 'center' | 'end';
  format?: (value: unknown, item: T) => string;
}

// ============================================================================
// Event Types
// ============================================================================

export interface CustomEvents {
  'booking:created': Booking;
  'booking:updated': Booking;
  'booking:cancelled': { id: number; reason?: string };
  'payment:completed': Payment;
  'payment:failed': { id: number; error: string };
  'notification:received': Notification;
  'session:expired': void;
}

// ============================================================================
// Utility Types
// ============================================================================

export type DeepPartial<T> = {
  [P in keyof T]?: T[P] extends object ? DeepPartial<T[P]> : T[P];
};

export type Nullable<T> = T | null;

export type Optional<T, K extends keyof T> = Omit<T, K> & Partial<Pick<T, K>>;
