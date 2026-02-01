/**
 * Touch Swipe Directive for Vue 3
 * 
 * Enables swipe gesture detection on mobile devices.
 * 
 * Usage:
 * <div v-swipe="{ onSwipeLeft: handleLeft, onSwipeRight: handleRight }">
 *   Swipeable content
 * </div>
 * 
 * Options:
 * - onSwipeLeft: Function - Called on left swipe
 * - onSwipeRight: Function - Called on right swipe
 * - onSwipeUp: Function - Called on up swipe
 * - onSwipeDown: Function - Called on down swipe
 * - onSwipe: Function - Called on any swipe with direction
 * - threshold: Number - Minimum swipe distance (default: 50px)
 * - timeout: Number - Max time for swipe gesture (default: 300ms)
 * - disableScroll: Boolean - Prevent scroll during horizontal swipe
 */

const defaultOptions = {
  threshold: 50,
  timeout: 300,
  disableScroll: false
};

export const vSwipe = {
  mounted(el, binding) {
    const options = { ...defaultOptions, ...binding.value };
    
    let touchStartX = 0;
    let touchStartY = 0;
    let touchStartTime = 0;
    let isScrolling = null;

    const handleTouchStart = (e) => {
      const touch = e.touches[0];
      touchStartX = touch.clientX;
      touchStartY = touch.clientY;
      touchStartTime = Date.now();
      isScrolling = null;
    };

    const handleTouchMove = (e) => {
      if (!touchStartX || !touchStartY) return;

      const touch = e.touches[0];
      const deltaX = touch.clientX - touchStartX;
      const deltaY = touch.clientY - touchStartY;

      // Determine if scrolling vertically or horizontally
      if (isScrolling === null) {
        isScrolling = Math.abs(deltaY) > Math.abs(deltaX);
      }

      // Prevent scroll if horizontal swipe and option enabled
      if (!isScrolling && options.disableScroll) {
        e.preventDefault();
      }
    };

    const handleTouchEnd = (e) => {
      if (!touchStartX || !touchStartY) return;

      const touch = e.changedTouches[0];
      const deltaX = touch.clientX - touchStartX;
      const deltaY = touch.clientY - touchStartY;
      const deltaTime = Date.now() - touchStartTime;

      // Reset
      const startX = touchStartX;
      const startY = touchStartY;
      touchStartX = 0;
      touchStartY = 0;

      // Check if within timeout
      if (deltaTime > options.timeout) return;

      const absX = Math.abs(deltaX);
      const absY = Math.abs(deltaY);

      // Must exceed threshold
      if (Math.max(absX, absY) < options.threshold) return;

      // Determine direction
      let direction = null;
      
      if (absX > absY) {
        // Horizontal swipe
        direction = deltaX > 0 ? 'right' : 'left';
      } else {
        // Vertical swipe
        direction = deltaY > 0 ? 'down' : 'up';
      }

      // Create event data
      const eventData = {
        direction,
        deltaX,
        deltaY,
        deltaTime,
        startX,
        startY,
        endX: touch.clientX,
        endY: touch.clientY,
        velocity: Math.max(absX, absY) / deltaTime
      };

      // Call direction-specific handler
      const handlerName = `onSwipe${direction.charAt(0).toUpperCase() + direction.slice(1)}`;
      if (typeof options[handlerName] === 'function') {
        options[handlerName](eventData);
      }

      // Call generic handler
      if (typeof options.onSwipe === 'function') {
        options.onSwipe(eventData);
      }
    };

    // Store handlers for cleanup
    el._swipeHandlers = {
      touchstart: handleTouchStart,
      touchmove: handleTouchMove,
      touchend: handleTouchEnd
    };

    // Attach listeners with passive option for better scroll performance
    el.addEventListener('touchstart', handleTouchStart, { passive: true });
    el.addEventListener('touchmove', handleTouchMove, { passive: !options.disableScroll });
    el.addEventListener('touchend', handleTouchEnd, { passive: true });
  },

  updated(el, binding) {
    // Update options if binding changes
    if (el._swipeHandlers) {
      const options = { ...defaultOptions, ...binding.value };
      el._swipeOptions = options;
    }
  },

  unmounted(el) {
    if (el._swipeHandlers) {
      el.removeEventListener('touchstart', el._swipeHandlers.touchstart);
      el.removeEventListener('touchmove', el._swipeHandlers.touchmove);
      el.removeEventListener('touchend', el._swipeHandlers.touchend);
      delete el._swipeHandlers;
    }
  }
};

/**
 * Long Press Directive
 * 
 * Detects long press / touch hold gestures.
 * 
 * Usage:
 * <div v-longpress="{ onLongPress: handleLongPress, duration: 500 }">
 *   Long press me
 * </div>
 */
export const vLongpress = {
  mounted(el, binding) {
    const options = {
      duration: 500,
      onLongPress: null,
      ...binding.value
    };

    let pressTimer = null;
    let startX = 0;
    let startY = 0;
    const moveThreshold = 10;

    const start = (e) => {
      if (e.touches) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
      }

      pressTimer = setTimeout(() => {
        if (typeof options.onLongPress === 'function') {
          // Haptic feedback if available
          if (navigator.vibrate) {
            navigator.vibrate(50);
          }
          options.onLongPress(e);
        }
      }, options.duration);
    };

    const cancel = () => {
      if (pressTimer) {
        clearTimeout(pressTimer);
        pressTimer = null;
      }
    };

    const move = (e) => {
      if (!pressTimer) return;
      
      if (e.touches) {
        const deltaX = Math.abs(e.touches[0].clientX - startX);
        const deltaY = Math.abs(e.touches[0].clientY - startY);
        
        if (deltaX > moveThreshold || deltaY > moveThreshold) {
          cancel();
        }
      }
    };

    el._longpressHandlers = { start, cancel, move };

    el.addEventListener('touchstart', start, { passive: true });
    el.addEventListener('touchend', cancel, { passive: true });
    el.addEventListener('touchmove', move, { passive: true });
    el.addEventListener('touchcancel', cancel, { passive: true });
    
    // Also support mouse
    el.addEventListener('mousedown', start);
    el.addEventListener('mouseup', cancel);
    el.addEventListener('mouseleave', cancel);
  },

  unmounted(el) {
    if (el._longpressHandlers) {
      const { start, cancel, move } = el._longpressHandlers;
      el.removeEventListener('touchstart', start);
      el.removeEventListener('touchend', cancel);
      el.removeEventListener('touchmove', move);
      el.removeEventListener('touchcancel', cancel);
      el.removeEventListener('mousedown', start);
      el.removeEventListener('mouseup', cancel);
      el.removeEventListener('mouseleave', cancel);
      delete el._longpressHandlers;
    }
  }
};

/**
 * Pull to Refresh Directive
 * 
 * Enables pull-to-refresh gesture at the top of scrollable containers.
 * 
 * Usage:
 * <div v-pull-refresh="{ onRefresh: handleRefresh }">
 *   Scrollable content
 * </div>
 */
export const vPullRefresh = {
  mounted(el, binding) {
    const options = {
      threshold: 80,
      onRefresh: null,
      resistance: 2.5,
      ...binding.value
    };

    let startY = 0;
    let currentY = 0;
    let isPulling = false;
    let indicator = null;

    // Create indicator element
    const createIndicator = () => {
      indicator = document.createElement('div');
      indicator.className = 'pull-refresh-indicator';
      indicator.innerHTML = `
        <div class="pull-refresh-spinner">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23,4 23,10 17,10"/>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
          </svg>
        </div>
      `;
      indicator.style.cssText = `
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%) translateY(-100%);
        padding: 12px;
        opacity: 0;
        transition: opacity 0.2s;
        z-index: 100;
      `;
      el.style.position = 'relative';
      el.insertBefore(indicator, el.firstChild);
    };

    createIndicator();

    const handleTouchStart = (e) => {
      if (el.scrollTop === 0) {
        startY = e.touches[0].clientY;
        isPulling = true;
      }
    };

    const handleTouchMove = (e) => {
      if (!isPulling || el.scrollTop > 0) return;

      currentY = e.touches[0].clientY;
      const pullDistance = (currentY - startY) / options.resistance;

      if (pullDistance > 0) {
        e.preventDefault();
        
        const progress = Math.min(pullDistance / options.threshold, 1);
        indicator.style.opacity = progress;
        indicator.style.transform = `translateX(-50%) translateY(${pullDistance - 40}px)`;
        
        const spinner = indicator.querySelector('.pull-refresh-spinner');
        if (spinner) {
          spinner.style.transform = `rotate(${progress * 180}deg)`;
        }
      }
    };

    const handleTouchEnd = async () => {
      if (!isPulling) return;

      const pullDistance = (currentY - startY) / options.resistance;
      
      if (pullDistance >= options.threshold && typeof options.onRefresh === 'function') {
        // Trigger refresh
        indicator.classList.add('refreshing');
        
        try {
          await options.onRefresh();
        } finally {
          indicator.classList.remove('refreshing');
        }
      }

      // Reset
      isPulling = false;
      startY = 0;
      currentY = 0;
      indicator.style.opacity = 0;
      indicator.style.transform = 'translateX(-50%) translateY(-100%)';
    };

    el._pullRefreshHandlers = {
      touchstart: handleTouchStart,
      touchmove: handleTouchMove,
      touchend: handleTouchEnd
    };

    el.addEventListener('touchstart', handleTouchStart, { passive: true });
    el.addEventListener('touchmove', handleTouchMove, { passive: false });
    el.addEventListener('touchend', handleTouchEnd, { passive: true });
  },

  unmounted(el) {
    if (el._pullRefreshHandlers) {
      el.removeEventListener('touchstart', el._pullRefreshHandlers.touchstart);
      el.removeEventListener('touchmove', el._pullRefreshHandlers.touchmove);
      el.removeEventListener('touchend', el._pullRefreshHandlers.touchend);
      delete el._pullRefreshHandlers;
    }
    
    const indicator = el.querySelector('.pull-refresh-indicator');
    if (indicator) {
      indicator.remove();
    }
  }
};

export default {
  install(app) {
    app.directive('swipe', vSwipe);
    app.directive('longpress', vLongpress);
    app.directive('pull-refresh', vPullRefresh);
  }
};
