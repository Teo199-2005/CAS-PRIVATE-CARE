import { ref, reactive } from 'vue';

/**
 * Composable for API requests with loading states
 * Extracts common API call patterns for reuse
 */
export function useApi() {
    const loading = ref(false);
    const error = ref(null);
    const data = ref(null);

    // CSRF token getter
    const getCsrfToken = () => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    };

    // Base request function
    const request = async (url, options = {}) => {
        loading.value = true;
        error.value = null;

        const defaultHeaders = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        };

        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    ...defaultHeaders,
                    ...options.headers,
                },
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            data.value = result;
            return { data: result, error: null };
        } catch (err) {
            error.value = err.message;
            return { data: null, error: err.message };
        } finally {
            loading.value = false;
        }
    };

    // HTTP method shortcuts
    const get = (url, params = {}) => {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;
        return request(fullUrl, { method: 'GET' });
    };

    const post = (url, body = {}) => {
        return request(url, {
            method: 'POST',
            body: JSON.stringify(body),
        });
    };

    const put = (url, body = {}) => {
        return request(url, {
            method: 'PUT',
            body: JSON.stringify(body),
        });
    };

    const patch = (url, body = {}) => {
        return request(url, {
            method: 'PATCH',
            body: JSON.stringify(body),
        });
    };

    const del = (url) => {
        return request(url, { method: 'DELETE' });
    };

    // Form submission with file upload
    const postForm = async (url, formData) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: formData,
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            data.value = result;
            return { data: result, error: null };
        } catch (err) {
            error.value = err.message;
            return { data: null, error: err.message };
        } finally {
            loading.value = false;
        }
    };

    // Reset state
    const reset = () => {
        loading.value = false;
        error.value = null;
        data.value = null;
    };

    return {
        loading,
        error,
        data,
        request,
        get,
        post,
        put,
        patch,
        del,
        postForm,
        reset,
        getCsrfToken,
    };
}

/**
 * Create a specific API resource with CRUD operations
 */
export function useResourceApi(baseUrl) {
    const api = useApi();
    const items = ref([]);
    const currentItem = ref(null);

    const list = async (params = {}) => {
        const result = await api.get(baseUrl, params);
        if (result.data) {
            items.value = result.data.data || result.data;
        }
        return result;
    };

    const show = async (id) => {
        const result = await api.get(`${baseUrl}/${id}`);
        if (result.data) {
            currentItem.value = result.data.data || result.data;
        }
        return result;
    };

    const create = async (data) => {
        const result = await api.post(baseUrl, data);
        if (result.data) {
            const newItem = result.data.data || result.data;
            items.value.unshift(newItem);
        }
        return result;
    };

    const update = async (id, data) => {
        const result = await api.put(`${baseUrl}/${id}`, data);
        if (result.data) {
            const updatedItem = result.data.data || result.data;
            const index = items.value.findIndex(item => item.id === id);
            if (index !== -1) {
                items.value[index] = updatedItem;
            }
        }
        return result;
    };

    const destroy = async (id) => {
        const result = await api.del(`${baseUrl}/${id}`);
        if (!result.error) {
            items.value = items.value.filter(item => item.id !== id);
        }
        return result;
    };

    return {
        ...api,
        items,
        currentItem,
        list,
        show,
        create,
        update,
        destroy,
    };
}
