import { ref, reactive, computed, watch, type Ref, type ComputedRef } from 'vue';
import type { ApiResponse, ValidationResult, Booking, User } from '@/types';
import { api } from '@/services/api';

/**
 * Production-grade form handling composable.
 * 
 * Features:
 * - Automatic validation on change/blur
 * - Dirty state tracking
 * - Loading states
 * - Error handling with field-level messages
 * - Form reset functionality
 * - Submit prevention while loading
 */
export interface UseFormOptions<T extends Record<string, unknown>> {
  initialValues: T;
  validators?: Partial<Record<keyof T, ((value: unknown) => ValidationResult)[]>>;
  validateOnChange?: boolean;
  validateOnBlur?: boolean;
  onSubmit?: (values: T) => Promise<void>;
}

export interface FormState<T extends Record<string, unknown>> {
  values: T;
  errors: Partial<Record<keyof T, string[]>>;
  touched: Partial<Record<keyof T, boolean>>;
  isSubmitting: boolean;
  isValid: boolean;
  isDirty: boolean;
}

export function useForm<T extends Record<string, unknown>>(options: UseFormOptions<T>) {
  const {
    initialValues,
    validators = {},
    validateOnChange = true,
    validateOnBlur = true,
    onSubmit,
  } = options;

  const values = reactive({ ...initialValues }) as T;
  const errors = reactive<Partial<Record<keyof T, string[]>>>({});
  const touched = reactive<Partial<Record<keyof T, boolean>>>({});
  const isSubmitting = ref(false);
  const submitError = ref<string | null>(null);
  const submitCount = ref(0);

  const isDirty = computed(() => {
    return Object.keys(initialValues).some(
      (key) => values[key as keyof T] !== initialValues[key as keyof T]
    );
  });

  const isValid = computed(() => {
    return Object.keys(errors).every(
      (key) => !errors[key as keyof T] || errors[key as keyof T]!.length === 0
    );
  });

  const validateField = (field: keyof T): boolean => {
    const fieldValidators = validators[field] || [];
    const fieldErrors: string[] = [];

    for (const validator of fieldValidators) {
      const result = validator(values[field]);
      if (!result.isValid && result.errors) {
        fieldErrors.push(...result.errors.map((e) => e.message));
      }
    }

    if (fieldErrors.length > 0) {
      errors[field] = fieldErrors;
      return false;
    } else {
      delete errors[field];
      return true;
    }
  };

  const validateAll = (): boolean => {
    let allValid = true;

    for (const field of Object.keys(validators) as (keyof T)[]) {
      if (!validateField(field)) {
        allValid = false;
      }
    }

    return allValid;
  };

  const setFieldValue = (field: keyof T, value: unknown): void => {
    (values as Record<keyof T, unknown>)[field] = value;
    touched[field] = true;

    if (validateOnChange) {
      validateField(field);
    }
  };

  const setFieldTouched = (field: keyof T): void => {
    touched[field] = true;

    if (validateOnBlur) {
      validateField(field);
    }
  };

  const setFieldError = (field: keyof T, error: string): void => {
    errors[field] = [error];
  };

  const setErrors = (newErrors: Partial<Record<keyof T, string[]>>): void => {
    Object.assign(errors, newErrors);
  };

  const reset = (): void => {
    Object.assign(values, initialValues);
    Object.keys(errors).forEach((key) => delete errors[key as keyof T]);
    Object.keys(touched).forEach((key) => delete touched[key as keyof T]);
    submitError.value = null;
    submitCount.value = 0;
  };

  const handleSubmit = async (e?: Event): Promise<void> => {
    e?.preventDefault();

    if (isSubmitting.value) return;

    // Mark all fields as touched
    for (const field of Object.keys(validators) as (keyof T)[]) {
      touched[field] = true;
    }

    // Validate all fields
    if (!validateAll()) {
      return;
    }

    isSubmitting.value = true;
    submitError.value = null;
    submitCount.value++;

    try {
      await onSubmit?.(values);
    } catch (error) {
      if (error instanceof Error) {
        submitError.value = error.message;
      } else {
        submitError.value = 'An unexpected error occurred';
      }
      throw error;
    } finally {
      isSubmitting.value = false;
    }
  };

  // Watch for changes and validate
  if (validateOnChange) {
    watch(
      () => ({ ...values }),
      () => {
        for (const field of Object.keys(touched) as (keyof T)[]) {
          if (touched[field]) {
            validateField(field);
          }
        }
      },
      { deep: true }
    );
  }

  return {
    values,
    errors,
    touched,
    isSubmitting,
    isValid,
    isDirty,
    submitError,
    submitCount,
    setFieldValue,
    setFieldTouched,
    setFieldError,
    setErrors,
    validateField,
    validateAll,
    reset,
    handleSubmit,
  };
}

/**
 * Composable for async data fetching with loading/error states.
 */
export interface UseAsyncOptions<T> {
  immediate?: boolean;
  onSuccess?: (data: T) => void;
  onError?: (error: Error) => void;
}

export interface UseAsyncReturn<T, P extends unknown[]> {
  data: Ref<T | null>;
  error: Ref<Error | null>;
  isLoading: Ref<boolean>;
  isError: ComputedRef<boolean>;
  isSuccess: ComputedRef<boolean>;
  execute: (...params: P) => Promise<T | null>;
  reset: () => void;
}

export function useAsync<T, P extends unknown[] = []>(
  asyncFn: (...params: P) => Promise<T>,
  options: UseAsyncOptions<T> = {}
): UseAsyncReturn<T, P> {
  const { immediate = false, onSuccess, onError } = options;

  const data = ref<T | null>(null) as Ref<T | null>;
  const error = ref<Error | null>(null);
  const isLoading = ref(false);

  const isError = computed(() => error.value !== null);
  const isSuccess = computed(() => data.value !== null && !isError.value);

  const execute = async (...params: P): Promise<T | null> => {
    isLoading.value = true;
    error.value = null;

    try {
      const result = await asyncFn(...params);
      data.value = result;
      onSuccess?.(result);
      return result;
    } catch (e) {
      const err = e instanceof Error ? e : new Error(String(e));
      error.value = err;
      onError?.(err);
      return null;
    } finally {
      isLoading.value = false;
    }
  };

  const reset = (): void => {
    data.value = null;
    error.value = null;
    isLoading.value = false;
  };

  if (immediate) {
    execute(...([] as unknown as P));
  }

  return {
    data,
    error,
    isLoading,
    isError,
    isSuccess,
    execute,
    reset,
  };
}

/**
 * Composable for paginated data fetching.
 */
export interface PaginationState<T> {
  items: T[];
  currentPage: number;
  totalPages: number;
  totalItems: number;
  perPage: number;
  isLoading: boolean;
  hasMore: boolean;
}

export function usePagination<T>(
  fetchFn: (page: number, perPage: number) => Promise<{
    data: T[];
    total: number;
    current_page: number;
    last_page: number;
    per_page: number;
  }>,
  initialPerPage = 15
) {
  const state = reactive<PaginationState<T>>({
    items: [],
    currentPage: 1,
    totalPages: 0,
    totalItems: 0,
    perPage: initialPerPage,
    isLoading: false,
    hasMore: false,
  });

  const error = ref<Error | null>(null);

  const fetchPage = async (page: number): Promise<void> => {
    if (state.isLoading) return;

    state.isLoading = true;
    error.value = null;

    try {
      const response = await fetchFn(page, state.perPage);
      state.items = response.data;
      state.currentPage = response.current_page;
      state.totalPages = response.last_page;
      state.totalItems = response.total;
      state.hasMore = response.current_page < response.last_page;
    } catch (e) {
      error.value = e instanceof Error ? e : new Error(String(e));
    } finally {
      state.isLoading = false;
    }
  };

  const nextPage = async (): Promise<void> => {
    if (state.currentPage < state.totalPages) {
      await fetchPage(state.currentPage + 1);
    }
  };

  const prevPage = async (): Promise<void> => {
    if (state.currentPage > 1) {
      await fetchPage(state.currentPage - 1);
    }
  };

  const goToPage = async (page: number): Promise<void> => {
    if (page >= 1 && page <= state.totalPages) {
      await fetchPage(page);
    }
  };

  const setPerPage = async (perPage: number): Promise<void> => {
    state.perPage = perPage;
    await fetchPage(1);
  };

  const refresh = async (): Promise<void> => {
    await fetchPage(state.currentPage);
  };

  // Generate page numbers for pagination UI
  const pageNumbers = computed(() => {
    const pages: (number | '...')[] = [];
    const current = state.currentPage;
    const total = state.totalPages;

    if (total <= 7) {
      for (let i = 1; i <= total; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);

      if (current > 3) {
        pages.push('...');
      }

      const start = Math.max(2, current - 1);
      const end = Math.min(total - 1, current + 1);

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }

      if (current < total - 2) {
        pages.push('...');
      }

      pages.push(total);
    }

    return pages;
  });

  return {
    ...toRefs(state),
    error,
    pageNumbers,
    fetchPage,
    nextPage,
    prevPage,
    goToPage,
    setPerPage,
    refresh,
  };
}

/**
 * Composable for debounced search.
 */
export function useSearch(
  searchFn: (query: string) => Promise<void>,
  delay = 300
) {
  const query = ref('');
  const isSearching = ref(false);
  let timeoutId: ReturnType<typeof setTimeout> | null = null;

  const search = (newQuery: string): void => {
    query.value = newQuery;

    if (timeoutId) {
      clearTimeout(timeoutId);
    }

    if (!newQuery.trim()) {
      isSearching.value = false;
      return;
    }

    isSearching.value = true;

    timeoutId = setTimeout(async () => {
      try {
        await searchFn(newQuery);
      } finally {
        isSearching.value = false;
      }
    }, delay);
  };

  const clear = (): void => {
    query.value = '';
    if (timeoutId) {
      clearTimeout(timeoutId);
    }
    isSearching.value = false;
  };

  return {
    query,
    isSearching,
    search,
    clear,
  };
}

/**
 * Composable for toast notifications.
 */
export interface Toast {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  message: string;
  duration?: number;
}

const toasts = ref<Toast[]>([]);

export function useToast() {
  const show = (
    message: string,
    type: Toast['type'] = 'info',
    duration = 5000
  ): string => {
    const id = Math.random().toString(36).substring(7);

    toasts.value.push({ id, type, message, duration });

    if (duration > 0) {
      setTimeout(() => {
        dismiss(id);
      }, duration);
    }

    return id;
  };

  const success = (message: string, duration?: number): string =>
    show(message, 'success', duration);

  const error = (message: string, duration?: number): string =>
    show(message, 'error', duration);

  const warning = (message: string, duration?: number): string =>
    show(message, 'warning', duration);

  const info = (message: string, duration?: number): string =>
    show(message, 'info', duration);

  const dismiss = (id: string): void => {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index !== -1) {
      toasts.value.splice(index, 1);
    }
  };

  const dismissAll = (): void => {
    toasts.value = [];
  };

  return {
    toasts,
    show,
    success,
    error,
    warning,
    info,
    dismiss,
    dismissAll,
  };
}

// Helper function for reactive refs
function toRefs<T extends object>(obj: T) {
  const result = {} as { [K in keyof T]: Ref<T[K]> };

  for (const key of Object.keys(obj) as (keyof T)[]) {
    result[key] = computed({
      get: () => obj[key],
      set: (val) => {
        obj[key] = val;
      },
    });
  }

  return result;
}
