import { ref, reactive, computed } from 'vue';

/**
 * Composable for data table operations
 * Extracts table pagination, filtering, and selection logic
 */
export function useDataTable(options = {}) {
    const {
        defaultItemsPerPage = 10,
        defaultSortBy = null,
        defaultSortDesc = false,
        searchFields = [],
    } = options;

    // Table state
    const items = ref([]);
    const selectedItems = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const searchQuery = ref('');
    const currentPage = ref(1);
    const itemsPerPage = ref(defaultItemsPerPage);
    const sortBy = ref(defaultSortBy);
    const sortDesc = ref(defaultSortDesc);
    const totalItems = ref(0);

    // Pagination computed
    const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));
    const hasNextPage = computed(() => currentPage.value < totalPages.value);
    const hasPreviousPage = computed(() => currentPage.value > 1);

    // Filtered items based on search
    const filteredItems = computed(() => {
        if (!searchQuery.value || searchFields.length === 0) {
            return items.value;
        }

        const query = searchQuery.value.toLowerCase();
        return items.value.filter(item => {
            return searchFields.some(field => {
                const value = getNestedValue(item, field);
                return value && String(value).toLowerCase().includes(query);
            });
        });
    });

    // Sorted items
    const sortedItems = computed(() => {
        if (!sortBy.value) return filteredItems.value;

        return [...filteredItems.value].sort((a, b) => {
            const aVal = getNestedValue(a, sortBy.value);
            const bVal = getNestedValue(b, sortBy.value);

            if (aVal === bVal) return 0;
            if (aVal === null || aVal === undefined) return 1;
            if (bVal === null || bVal === undefined) return -1;

            const comparison = aVal < bVal ? -1 : 1;
            return sortDesc.value ? -comparison : comparison;
        });
    });

    // Paginated items
    const paginatedItems = computed(() => {
        const start = (currentPage.value - 1) * itemsPerPage.value;
        const end = start + itemsPerPage.value;
        return sortedItems.value.slice(start, end);
    });

    // Helper to get nested object values
    function getNestedValue(obj, path) {
        return path.split('.').reduce((current, key) => current?.[key], obj);
    }

    // Actions
    const setItems = (newItems) => {
        items.value = newItems;
        totalItems.value = newItems.length;
    };

    const nextPage = () => {
        if (hasNextPage.value) {
            currentPage.value++;
        }
    };

    const previousPage = () => {
        if (hasPreviousPage.value) {
            currentPage.value--;
        }
    };

    const goToPage = (page) => {
        if (page >= 1 && page <= totalPages.value) {
            currentPage.value = page;
        }
    };

    const setSort = (field, descending = false) => {
        if (sortBy.value === field) {
            sortDesc.value = !sortDesc.value;
        } else {
            sortBy.value = field;
            sortDesc.value = descending;
        }
    };

    const clearSelection = () => {
        selectedItems.value = [];
    };

    const selectAll = () => {
        selectedItems.value = [...paginatedItems.value];
    };

    const toggleSelection = (item) => {
        const index = selectedItems.value.findIndex(i => i === item || i.id === item.id);
        if (index === -1) {
            selectedItems.value.push(item);
        } else {
            selectedItems.value.splice(index, 1);
        }
    };

    const isSelected = (item) => {
        return selectedItems.value.some(i => i === item || i.id === item.id);
    };

    const reset = () => {
        currentPage.value = 1;
        searchQuery.value = '';
        selectedItems.value = [];
        sortBy.value = defaultSortBy;
        sortDesc.value = defaultSortDesc;
    };

    return {
        // State
        items,
        selectedItems,
        loading,
        error,
        searchQuery,
        currentPage,
        itemsPerPage,
        sortBy,
        sortDesc,
        totalItems,

        // Computed
        totalPages,
        hasNextPage,
        hasPreviousPage,
        filteredItems,
        sortedItems,
        paginatedItems,

        // Actions
        setItems,
        nextPage,
        previousPage,
        goToPage,
        setSort,
        clearSelection,
        selectAll,
        toggleSelection,
        isSelected,
        reset,
    };
}
