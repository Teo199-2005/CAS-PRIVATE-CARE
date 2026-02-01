/**
 * Roving Tabindex Composable
 * For navigating within tab lists, menus, etc.
 */

import { ref, onMounted, onUnmounted } from 'vue';

export function useRovingTabindex(containerRef, options = {}) {
  const {
    orientation = 'horizontal',
    loop = true,
    selector = '[role="tab"], [role="menuitem"], button'
  } = options;

  const currentIndex = ref(0);

  const getItems = () => {
    if (!containerRef.value) return [];
    return Array.from(containerRef.value.querySelectorAll(selector));
  };

  const focusItem = (index) => {
    const items = getItems();
    if (items.length === 0) return;

    // Update tabindex
    items.forEach((item, i) => {
      item.setAttribute('tabindex', i === index ? '0' : '-1');
    });

    // Focus the item
    items[index]?.focus();
    currentIndex.value = index;
  };

  const handleKeyDown = (event) => {
    const items = getItems();
    if (items.length === 0) return;

    const isHorizontal = orientation === 'horizontal';
    const prevKey = isHorizontal ? 'ArrowLeft' : 'ArrowUp';
    const nextKey = isHorizontal ? 'ArrowRight' : 'ArrowDown';

    let newIndex = currentIndex.value;

    switch (event.key) {
      case prevKey:
        event.preventDefault();
        newIndex = currentIndex.value - 1;
        if (newIndex < 0) {
          newIndex = loop ? items.length - 1 : 0;
        }
        focusItem(newIndex);
        break;

      case nextKey:
        event.preventDefault();
        newIndex = currentIndex.value + 1;
        if (newIndex >= items.length) {
          newIndex = loop ? 0 : items.length - 1;
        }
        focusItem(newIndex);
        break;

      case 'Home':
        event.preventDefault();
        focusItem(0);
        break;

      case 'End':
        event.preventDefault();
        focusItem(items.length - 1);
        break;
    }
  };

  onMounted(() => {
    if (containerRef.value) {
      containerRef.value.addEventListener('keydown', handleKeyDown);
      
      // Initialize tabindex
      const items = getItems();
      items.forEach((item, i) => {
        item.setAttribute('tabindex', i === 0 ? '0' : '-1');
      });
    }
  });

  onUnmounted(() => {
    if (containerRef.value) {
      containerRef.value.removeEventListener('keydown', handleKeyDown);
    }
  });

  return {
    currentIndex,
    focusItem
  };
}
