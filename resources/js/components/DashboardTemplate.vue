<template>
  <v-app :data-user-role="userRole">
    <!-- ARIA Live Region for Screen Reader Announcements -->
    <AriaAnnouncer ref="ariaAnnouncer" />

    <!-- Skip to main content link (Accessibility) -->
    <a href="#main-content" class="skip-link" @click.prevent="skipToMain">
      Skip to main content
    </a>

    <!-- Mobile App Bar (visible on small screens) -->
    <v-app-bar class="mobile-app-bar" dense elevation="2" v-if="isMobile">
      <v-btn icon @click="toggleDrawer" aria-label="Toggle navigation menu" aria-expanded="drawer" aria-controls="navigation-drawer">
        <v-icon>{{ drawer ? 'mdi-close' : 'mdi-menu' }}</v-icon>
      </v-btn>
      <div class="mobile-app-title">{{ headerTitle }}</div>
      <div style="flex:1"></div>
      <slot name="header-left"></slot>
    </v-app-bar>

    <v-navigation-drawer 
      v-model="drawer" 
      :temporary="isMobile" 
      :width="drawerWidth" 
      class="sidebar"
      id="navigation-drawer"
      ref="drawerRef"
      :aria-label="isMobile ? 'Navigation menu' : 'Main navigation'"
      role="navigation"
    >
      <div class="sidebar-header pa-6">
        <div class="d-flex align-center mb-4">
          <v-img src="/logo%20flower.png" max-width="50" class="sidebar-logo" />
          <div class="ml-4">
            <div class="sidebar-brand">CAS Private Care</div>
            <div class="sidebar-tagline">Comfort & Support</div>
          </div>
        </div>
        <v-divider class="my-4" />
        <div class="text-center">
          <h3 class="welcome-title mb-2" style="color: #0B4FA2 !important;">{{ welcomeMessage }}</h3>
          <p class="welcome-subtitle" style="color: #475569 !important;">{{ subtitle }}</p>
        </div>
      </div>

      <v-list density="compact" nav class="sidebar-nav px-3">
        <template v-for="(item, index) in navItems" :key="item.value">
          <div v-if="item.category && (index === 0 || navItems[index - 1].category !== item.category)" class="category-label px-3 mt-4 mb-2">{{ item.category }}</div>
          
          <template v-if="item.isToggle">
            <v-list-item 
              :prepend-icon="item.icon" 
              :title="item.title" 
              @click="$emit('toggle-click', item)"
              class="nav-item mb-1 toggle-item"
              :class="{ 'nav-item-disabled': item.disabled }"
            >
              <template v-slot:append>
                <v-icon v-if="item.disabled" size="small" color="grey" class="mr-2">mdi-lock</v-icon>
                <v-icon size="small" :class="{ 'rotate-180': item.expanded }" class="toggle-icon">
                  mdi-chevron-down
                </v-icon>
              </template>
            </v-list-item>
            
            <v-expand-transition>
              <div v-show="item.expanded" class="sub-items">
                <v-list-item 
                  v-for="child in item.children" 
                  :key="child.value"
                  :prepend-icon="child.disabled ? 'mdi-lock' : child.icon" 
                  :title="child.title" 
                  @click="handleNavItemClick(child)"
                  :class="{ 
                    'active-nav': currentSection === child.value && !child.disabled,
                    'nav-item-disabled': child.disabled
                  }"
                  class="nav-item sub-nav-item mb-1 ml-4"
                  :disabled="child.disabled"
                >
                  <template v-if="child.disabled" v-slot:append>
                    <v-icon size="x-small" color="grey-darken-1">mdi-lock</v-icon>
                  </template>
                </v-list-item>
              </div>
            </v-expand-transition>
          </template>
          
          <v-list-item 
            v-else
            :prepend-icon="item.disabled ? 'mdi-lock' : item.icon" 
            :title="item.title" 
            @click="handleNavItemClick(item)"
            :class="{ 
              'active-nav': currentSection === item.value && !item.disabled,
              'nav-item-disabled': item.disabled
            }"
            class="nav-item mb-1"
            :disabled="item.disabled"
          >
            <template v-if="item.badge && !item.disabled" v-slot:append>
              <div class="notification-indicator"></div>
            </template>
            <template v-else-if="item.disabled" v-slot:append>
              <v-icon size="x-small" color="grey-darken-1">mdi-lock</v-icon>
            </template>
          </v-list-item>
        </template>
        
        <!-- Divider before logout -->
        <v-divider class="my-2"></v-divider>
        
        <!-- Logout button directly in the list, after Profile -->
        <v-list-item 
          prepend-icon="mdi-logout" 
          title="Logout" 
          @click="handleLogout"
          class="nav-item logout-nav-item mb-1"
        >
        </v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main id="main-content" class="main-content" tabindex="-1" role="main" aria-label="Main content">
      <div class="main-wrapper">
        <v-container fluid class="py-6 px-4 content-container">
          <!-- Desktop Header -->
          <v-card class="mb-8 dashboard-header desktop-header" :class="headerRoleClass" elevation="0">
            <v-card-text class="d-flex justify-space-between align-center pa-4">
              <slot name="header-left"></slot>
              <div class="text-center header-content">
                <h1 class="header-title mb-2" :class="headerTitleClass">{{ headerTitle }}</h1>
                <p class="header-subtitle" :class="headerSubtitleClass">{{ headerSubtitle }}</p>
              </div>
              <div class="user-info-card" :class="userRoleClass" :style="(userRole === 'caregiver' || userRole === 'housekeeper') ? 'flex: 1; justify-content: flex-end;' : ''">
                <div class="user-info-inner d-flex align-center">
                  <div class="avatar-wrapper">
                    <v-avatar :color="roleAvatarColor" size="60" class="user-avatar elevation-3">
                      <img v-if="userAvatar && userAvatar.length > 0" :src="userAvatar" :alt="`${userName}'s profile photo`" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" />
                      <span v-else class="avatar-initials">{{ userInitials }}</span>
                    </v-avatar>
                    <div class="avatar-status-dot" :class="roleStatusClass"></div>
                  </div>
                  <div class="ml-4 user-details">
                    <div class="user-name-styled" :class="roleTextClass">{{ userName }}</div>
                    <div class="user-role-badge">
                      <v-chip :color="roleBadgeColor" size="x-small" variant="flat" class="role-chip">
                        <v-icon start size="x-small">{{ roleIcon }}</v-icon>
                        {{ roleDisplayName }}
                      </v-chip>
                    </div>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>

          <!-- Mobile Header Section (below mobile app bar) -->
          <div class="mobile-header-section" v-if="isMobile">
            <div class="mobile-header-content mb-4">
              <div class="d-flex align-center mb-3">
                <v-avatar :color="roleAvatarColor" size="48" class="mr-3">
                  <img v-if="userAvatar && userAvatar.length > 0" :src="userAvatar" :alt="`${userName}'s profile photo`" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" />
                  <span v-else class="text-white font-weight-bold">{{ userInitials }}</span>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="mobile-user-name" :class="roleTextClass">{{ userName }}</div>
                  <v-chip :color="roleBadgeColor" size="x-small" variant="flat" class="role-chip-mobile mt-1">
                    <v-icon start size="x-small">{{ roleIcon }}</v-icon>
                    {{ roleDisplayName }}
                  </v-chip>
                </div>
              </div>
              <div class="mobile-header-text">
                <h2 class="mobile-header-title" :class="headerTitleClass">{{ headerTitle }}</h2>
                <p class="mobile-header-subtitle" :class="headerSubtitleClass">{{ headerSubtitle }}</p>
              </div>
            </div>
          </div>

          <slot></slot>
        </v-container>
        <!-- Mobile Bottom Navigation - Enhanced with labels -->
        <v-bottom-navigation 
          v-if="isMobile" 
          class="mobile-bottom-nav" 
          grow
          role="navigation"
          aria-label="Quick navigation"
        >
          <v-btn
            v-for="(item, idx) in mobileNavItems"
            :key="item.value"
            variant="flat"
            class="mobile-nav-btn"
            :class="{ 'mobile-active': currentSection === item.value }"
            @click="handleMobileNavClick(item.value)"
            :title="item.title"
            :aria-label="item.title"
            :aria-current="currentSection === item.value ? 'page' : undefined"
          >
            <v-icon size="22">{{ item.icon }}</v-icon>
            <span class="mobile-nav-label">{{ item.shortTitle || item.title.split(' ')[0] }}</span>
          </v-btn>
        </v-bottom-navigation>

        <Footer />
      </div>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import Footer from './shared/Footer.vue';
import NotificationPopup from './shared/NotificationPopup.vue';
import AriaAnnouncer from './shared/AriaAnnouncer.vue';
import { useMobileAccessibility } from '../composables/useMobileAccessibility';

// Initialize mobile accessibility features
const {
  trapFocus,
  releaseFocus,
  useReducedMotion,
  handleSwipeGestures
} = useMobileAccessibility();

// ==========================================
// Props & Emits
// ==========================================
const props = defineProps({
  userRole: { type: String, required: true },
  userName: { type: String, required: true },
  userInitials: { type: String, required: true },
  userAvatar: { type: String, default: '' },
  welcomeMessage: { type: String, required: true },
  subtitle: { type: String, required: true },
  headerTitle: { type: String, required: true },
  headerSubtitle: { type: String, required: true },
  navItems: { type: Array, required: true },
  currentSection: { type: String, required: true }
});

const emit = defineEmits(['section-change', 'logout', 'toggle-click', 'disabled-click']);

// ==========================================
// Refs
// ==========================================
const drawer = ref(true);
const drawerRef = ref(null);
const ariaAnnouncer = ref(null);
const isMobile = ref(typeof window !== 'undefined' ? window.innerWidth <= 960 : false);
const prefersReducedMotion = ref(false);

// Touch gesture tracking
const touchStartX = ref(0);
const touchStartY = ref(0);
const touchStartTime = ref(0);

// Focus trap state
const previousActiveElement = ref(null);
const isDrawerFocusTrapped = ref(false);

// ==========================================
// Computed Properties
// ==========================================

// Responsive drawer width based on viewport - OPTIMIZED for 100/100 score
const drawerWidth = computed(() => {
  if (!isMobile.value) return 300;
  const vw = typeof window !== 'undefined' ? window.innerWidth : 360;
  
  // Ultra-small screens (Galaxy Fold, etc.)
  if (vw <= 320) return Math.min(260, Math.floor(vw * 0.92));
  // Very small phones
  if (vw <= 360) return Math.min(280, Math.floor(vw * 0.88));
  // Standard small phones
  if (vw <= 400) return Math.min(300, Math.floor(vw * 0.85));
  // Standard phones
  if (vw <= 480) return Math.min(320, Math.floor(vw * 0.82));
  // Large phones / small tablets
  return 320;
});


// ==========================================
// Accessibility: Focus Trap for Mobile Drawer
// ==========================================

const getFocusableElements = (container) => {
  if (!container) return [];
  return Array.from(container.querySelectorAll(
    'button:not([disabled]):not([tabindex="-1"]), ' +
    '[href]:not([tabindex="-1"]), ' +
    'input:not([disabled]):not([tabindex="-1"]), ' +
    'select:not([disabled]):not([tabindex="-1"]), ' +
    'textarea:not([disabled]):not([tabindex="-1"]), ' +
    '[tabindex]:not([tabindex="-1"])'
  )).filter(el => {
    const style = window.getComputedStyle(el);
    return style.display !== 'none' && 
           style.visibility !== 'hidden' && 
           el.offsetParent !== null;
  });
};

const handleDrawerKeyDown = (e) => {
  if (!isMobile.value || !drawer.value) return;
  
  const drawerEl = document.querySelector('#navigation-drawer');
  if (!drawerEl) return;
  
  // Escape key closes drawer
  if (e.key === 'Escape') {
    e.preventDefault();
    drawer.value = false;
    return;
  }
  
  // Tab key focus trap
  if (e.key !== 'Tab') return;
  
  const focusables = getFocusableElements(drawerEl);
  if (focusables.length === 0) return;
  
  const firstElement = focusables[0];
  const lastElement = focusables[focusables.length - 1];
  
  if (e.shiftKey && document.activeElement === firstElement) {
    e.preventDefault();
    lastElement.focus();
  } else if (!e.shiftKey && document.activeElement === lastElement) {
    e.preventDefault();
    firstElement.focus();
  }
};

const activateFocusTrap = async () => {
  await nextTick();
  
  if (!isMobile.value) return;
  
  previousActiveElement.value = document.activeElement;
  isDrawerFocusTrapped.value = true;
  
  const drawerEl = document.querySelector('#navigation-drawer');
  if (drawerEl) {
    const focusables = getFocusableElements(drawerEl);
    if (focusables.length > 0) {
      focusables[0].focus();
    }
  }
  
  document.addEventListener('keydown', handleDrawerKeyDown);
};

const deactivateFocusTrap = () => {
  isDrawerFocusTrapped.value = false;
  document.removeEventListener('keydown', handleDrawerKeyDown);
  
  if (previousActiveElement.value && isMobile.value) {
    previousActiveElement.value.focus();
  }
};

// Watch drawer state for focus trap
watch(drawer, async (isOpen) => {
  if (isMobile.value) {
    if (isOpen) {
      await activateFocusTrap();
      announceToScreenReader('Navigation menu opened');
    } else {
      deactivateFocusTrap();
      announceToScreenReader('Navigation menu closed');
    }
  }
});

// ==========================================
// Accessibility: ARIA Announcements
// ==========================================

const announceToScreenReader = (message, priority = 'polite') => {
  if (ariaAnnouncer.value) {
    ariaAnnouncer.value.announce(message, priority);
  }
};

// ==========================================
// Touch Gesture Handling
// ==========================================

const SWIPE_THRESHOLD = 80;
const EDGE_THRESHOLD = 30;
const MAX_SWIPE_TIME = 300;

const handleTouchStart = (e) => {
  touchStartX.value = e.touches[0].clientX;
  touchStartY.value = e.touches[0].clientY;
  touchStartTime.value = Date.now();
};

const handleTouchEnd = (e) => {
  if (!isMobile.value) return;
  
  const touchEndX = e.changedTouches[0].clientX;
  const touchEndY = e.changedTouches[0].clientY;
  const deltaX = touchEndX - touchStartX.value;
  const deltaY = Math.abs(touchEndY - touchStartY.value);
  const swipeTime = Date.now() - touchStartTime.value;
  
  // Ignore if vertical movement is significant or swipe was too slow
  if (deltaY > 50 || swipeTime > MAX_SWIPE_TIME) return;
  
  // Edge swipe to open drawer (from left edge)
  if (!drawer.value && touchStartX.value <= EDGE_THRESHOLD && deltaX > SWIPE_THRESHOLD) {
    drawer.value = true;
    return;
  }
  
  // Swipe left to close drawer (when drawer is open)
  if (drawer.value && deltaX < -SWIPE_THRESHOLD) {
    drawer.value = false;
  }
};

// ==========================================
// Navigation Handlers
// ==========================================

const toggleDrawer = () => {
  drawer.value = !drawer.value;
};

const handleResize = () => {
  isMobile.value = window.innerWidth <= 960;
  if (isMobile.value) {
    drawer.value = false;
  } else {
    drawer.value = true;
  }
};

// Handle section change and close drawer on mobile
const handleSectionChange = (section) => {
  emit('section-change', section);
  announceToScreenReader(`Navigated to ${section} section`);
  if (isMobile.value) {
    drawer.value = false;
  }
};

// Handle mobile bottom nav click
const handleMobileNavClick = (section) => {
  // "More" button opens the side drawer
  if (section === 'more') {
    drawer.value = true;
    announceToScreenReader('Navigation menu opened');
    return;
  }
  emit('section-change', section);
  announceToScreenReader(`Navigated to ${section}`);
};

// Handle nav item click - check if disabled first
const handleNavItemClick = (item) => {
  if (item.disabled) {
    emit('disabled-click', item.value);
    announceToScreenReader(`${item.title} is currently locked`, 'assertive');
    return;
  }
  handleSectionChange(item.value);
};

// Handle logout and close drawer on mobile
const handleLogout = () => {
  emit('logout');
  if (isMobile.value) {
    drawer.value = false;
  }
};

// Skip to main content (accessibility)
const skipToMain = () => {
  const mainContent = document.getElementById('main-content');
  if (mainContent) {
    mainContent.focus();
    mainContent.scrollIntoView({ behavior: 'smooth' });
  }
};

// ==========================================
// Lifecycle Hooks
// ==========================================

onMounted(() => {
  handleResize();
  window.addEventListener('resize', handleResize);
  
  // Add touch gesture listeners
  if (typeof window !== 'undefined') {
    document.addEventListener('touchstart', handleTouchStart, { passive: true });
    document.addEventListener('touchend', handleTouchEnd, { passive: true });
    
    // Check reduced motion preference
    prefersReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (e) => {
      prefersReducedMotion.value = e.matches;
    });
  }
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize);
  document.removeEventListener('touchstart', handleTouchStart);
  document.removeEventListener('touchend', handleTouchEnd);
  document.removeEventListener('keydown', handleDrawerKeyDown);
});
const themeColor = computed(() => {
  if (props.userRole === 'client') return 'primary';
  if (props.userRole === 'admin') return 'error';
  if (props.userRole === 'marketing') return 'grey-darken-2';
  if (props.userRole === 'training') return 'grey-darken-2';
  if (props.userRole === 'housekeeper') return 'deep-purple';
  return 'success';
});

// Role-specific styling computed properties
const userRoleClass = computed(() => {
  const roleClasses = {
    'caregiver': 'role-caregiver',
    'admin': 'role-admin',
    'client': 'role-client',
    'marketing': 'role-marketing',
    'training': 'role-training'
  };
  return roleClasses[props.userRole] || 'role-caregiver';
});

const roleAvatarColor = computed(() => {
  const colors = {
    'caregiver': '#2E7D32',  // Green
    'housekeeper': '#7B1FA2',  // Purple/Violet
    'admin': '#C62828',      // Red
    'client': '#1565C0',     // Blue
    'marketing': '#6A1B9A',  // Purple
    'training': '#EF6C00'    // Orange
  };
  return colors[props.userRole] || '#2E7D32';
});

const roleTextClass = computed(() => {
  const classes = {
    'caregiver': 'text-green',
    'housekeeper': 'text-purple',
    'admin': 'text-red',
    'client': 'text-blue',
    'marketing': 'text-purple',
    'training': 'text-orange'
  };
  return classes[props.userRole] || 'text-green';
});

const roleStatusClass = computed(() => {
  const classes = {
    'caregiver': 'status-green',
    'housekeeper': 'status-purple',
    'admin': 'status-red',
    'client': 'status-blue',
    'marketing': 'status-purple',
    'training': 'status-orange'
  };
  return classes[props.userRole] || 'status-green';
});

const roleBadgeColor = computed(() => {
  const colors = {
    'caregiver': 'success',
    'housekeeper': 'deep-purple',
    'admin': 'error',
    'client': 'primary',
    'marketing': 'deep-purple',
    'training': 'orange'
  };
  return colors[props.userRole] || 'success';
});

const roleIcon = computed(() => {
  const icons = {
    'caregiver': 'mdi-heart-pulse',
    'housekeeper': 'mdi-home-heart',
    'admin': 'mdi-shield-crown',
    'client': 'mdi-account-star',
    'marketing': 'mdi-bullhorn',
    'training': 'mdi-school'
  };
  return icons[props.userRole] || 'mdi-account';
});

const roleDisplayName = computed(() => {
  const names = {
    'caregiver': 'Caregiver',
    'housekeeper': 'Housekeeper',
    'admin': 'Administrator',
    'client': 'Premium Client',
    'marketing': 'Marketing Staff',
    'training': 'Training Center'
  };
  return names[props.userRole] || 'User';
});

// Header styling computed properties
const headerRoleClass = computed(() => {
  const classes = {
    'caregiver': 'header-caregiver',
    'housekeeper': 'header-housekeeper',
    'admin': 'header-admin',
    'client': 'header-client',
    'marketing': 'header-marketing',
    'training': 'header-training'
  };
  return classes[props.userRole] || 'header-caregiver';
});

const headerTitleClass = computed(() => {
  const classes = {
    'caregiver': 'title-green',
    'housekeeper': 'title-purple',
    'admin': 'title-red',
    'client': 'title-blue',
    'marketing': 'title-purple',
    'training': 'title-orange'
  };
  return classes[props.userRole] || 'title-green';
});

const headerSubtitleClass = computed(() => {
  const classes = {
    'caregiver': 'subtitle-green',
    'housekeeper': 'subtitle-purple',
    'admin': 'subtitle-red',
    'client': 'subtitle-blue',
    'marketing': 'subtitle-purple',
    'training': 'subtitle-orange'
  };
  return classes[props.userRole] || 'subtitle-green';
});

const getUserId = () => {
  // Map user roles to user IDs for demo purposes
  const userIdMap = {
    'client': 1,
    'caregiver': 2,
    'admin': 3,
    'marketing': 4
  };
  return userIdMap[props.userRole] || 1;
};

// More menu state
const showMoreMenu = ref(false);

const mobileNavItems = computed(() => {
  if (props.navItems && props.navItems.length > 0) {
    // Flatten nav items (include children from toggle items)
    const flatItems = props.navItems.reduce((acc, item) => {
      if (item.isToggle && item.children) {
        // Skip the parent toggle, add children
        acc.push(...item.children);
      } else {
        acc.push(item);
      }
      return acc;
    }, []);
    
    // Role-specific priority configurations
    const rolePriorities = {
      'admin': ['dashboard', 'client-bookings', 'notifications', 'profile'],
      'adminstaff': ['dashboard', 'client-bookings', 'notifications', 'profile'],
      'client': ['dashboard', 'book-form', 'notifications', 'profile'],
      'caregiver': ['dashboard', 'available-clients', 'notifications', 'profile'],
      'housekeeper': ['dashboard', 'available-clients', 'notifications', 'profile'],
      'marketing': ['dashboard', 'analytics', 'notifications', 'profile'],
      'training': ['dashboard', 'notifications', 'profile']
    };
    
    const priorityOrder = rolePriorities[props.userRole] || ['dashboard', 'notifications', 'profile'];
    
    // Find items using EXACT value match only
    let result = priorityOrder
      .map(p => flatItems.find(item => item.value === p))
      .filter(Boolean)
      .slice(0, 4);
    
    // If we don't have enough items, fill from remaining
    if (result.length < 4) {
      const remaining = flatItems.filter(item => 
        !result.find(p => p.value === item.value)
      );
      result.push(...remaining.slice(0, 4 - result.length));
    }
    
    // Add "More" button if there are more items than shown
    if (flatItems.length > 4) {
      result = result.slice(0, 4); // Ensure max 4 before More
      result.push({
        value: 'more',
        icon: 'mdi-dots-horizontal',
        title: 'More',
        shortTitle: 'More'
      });
    }
    
    return result.map(i => ({
      value: i.value,
      icon: i.icon || 'mdi-view-dashboard',
      title: i.title,
      shortTitle: i.shortTitle || getShortTitle(i.title)
    }));
  }
  
  // Default fallback navigation
  return [
    { value: 'dashboard', icon: 'mdi-view-dashboard', title: 'Home', shortTitle: 'Home' },
    { value: 'book', icon: 'mdi-calendar-plus', title: 'Book', shortTitle: 'Book' },
    { value: 'bookings', icon: 'mdi-calendar-check', title: 'Bookings', shortTitle: 'My' },
    { value: 'notifications', icon: 'mdi-bell', title: 'Alerts', shortTitle: 'Alerts' },
    { value: 'profile', icon: 'mdi-account', title: 'Profile', shortTitle: 'Me' }
  ];
});

// Helper to get short title
const getShortTitle = (title) => {
  if (!title) return 'Menu';
  const shortMap = {
    'Dashboard': 'Dashboard',
    'Notifications': 'Alerts',
    'Client Bookings': 'Bookings',
    'Profile': 'Profile',
    'Profile (1099 Contractors)': 'Profile',
    'More': 'More',
    'Book Service': 'Book',
    'Browse Caregivers': 'Browse',
    'Payment Information': 'Payment',
    'Transaction History': 'History',
    'Job Listings': 'Jobs',
    'Available Clients': 'Jobs',
    'Earnings Report': 'Earnings',
    'Analytics Reports': 'Analytics',
    'Analytics': 'Analytics'
  };
  return shortMap[title] || title.split(' ')[0];
};
</script>

<style scoped>
/* Apple-inspired Typography */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

/* ========================================
   CSS Variables for Consistent Transitions
   Using design tokens from design-tokens.css
   ======================================== */
:root {
  --transition-fast: var(--timing-fast, 150ms) var(--ease-out, cubic-bezier(0.4, 0, 0.2, 1));
  --transition-normal: var(--timing-normal, 250ms) var(--ease-out, cubic-bezier(0.4, 0, 0.2, 1));
  --transition-slow: var(--timing-slow, 350ms) var(--ease-out, cubic-bezier(0.4, 0, 0.2, 1));
}

/* ========================================
   Skeleton Loading (uses global animations.css)
   ======================================== */
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 37%, #f0f0f0 63%);
  background-size: 200% 100%;
  animation: shimmer 1.5s ease-in-out infinite;
  border-radius: 8px;
}

/* ========================================
   Focus States (Accessibility)
   ======================================== */
*:focus-visible {
  outline: 3px solid rgba(59, 130, 246, 0.5);
  outline-offset: 2px;
  border-radius: 4px;
}

/* ========================================
   Reduced Motion Preferences
   ======================================== */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.sidebar {
  background: #ffffff !important;
  box-shadow: 0 0 40px rgba(0, 0, 0, 0.06) !important;
  border-right: 1px solid #f0f0f0 !important;
  animation: slideInFromLeft 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  color: #1e293b !important;
}

/* Force dark text on all sidebar content */
.sidebar * {
  --v-theme-on-surface: 30, 41, 59 !important;
}

.sidebar h3,
.sidebar p,
.sidebar .welcome-title,
.sidebar .welcome-subtitle {
  color: inherit;
}

.sidebar-header {
  background: linear-gradient(180deg, #fafafa 0%, #ffffff 100%);
  border-bottom: 1px solid #f0f0f0;
  animation: fadeInUp 0.4s ease-out;
  color: #1e293b !important;
}

.sidebar-logo {
  background: white;
  padding: 8px;
  border-radius: var(--card-radius-md, 16px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  transition: box-shadow 200ms ease-out;
}

/* Removed scale+rotate transform on logo hover */
.sidebar-logo:hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.sidebar-brand {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1a1a !important;
  line-height: 1.2;
  letter-spacing: -0.03em;
}

.sidebar-tagline {
  font-size: 0.75rem;
  font-weight: 500;
  letter-spacing: 0.02em;
  color: #666 !important;
  margin-top: 2px;
}

.welcome-title {
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  line-height: 1.3;
  color: #0B4FA2 !important;
  opacity: 1 !important;
}

.welcome-subtitle {
  font-size: 0.875rem;
  color: #475569 !important;
  font-weight: 500;
  line-height: 1.4;
  opacity: 1 !important;
}

.category-label {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #999;
}

.nav-item {
  border-radius: var(--card-radius-sm, 12px) !important;
  margin: 1px 0;
  font-size: 0.9rem;
  font-weight: 500;
  transition: background 150ms ease-out;
}

.nav-item:not(.active-nav):hover {
  background: #f5f5f5 !important;
}

.nav-item :deep(.v-list-item-title) {
  font-weight: 500 !important;
}

.active-nav {
  background: linear-gradient(135deg, v-bind('themeColor === "success" ? "#10b981" : themeColor === "error" ? "#dc2626" : themeColor === "grey-darken-2" ? "#616161" : "#3b82f6"') 0%, v-bind('themeColor === "success" ? "#059669" : themeColor === "error" ? "#b91c1c" : themeColor === "grey-darken-2" ? "#424242" : "#2563eb"') 100%) !important;
  color: white !important;
  font-weight: 600 !important;
  box-shadow: 0 4px 16px v-bind('themeColor === "success" ? "rgba(16, 185, 129, 0.25)" : themeColor === "error" ? "rgba(220, 38, 38, 0.25)" : themeColor === "grey-darken-2" ? "rgba(97, 97, 97, 0.25)" : "rgba(59, 130, 246, 0.25)"');
}

.active-nav :deep(.v-list-item__prepend) {
  color: white !important;
}

.active-nav :deep(.v-list-item-title) {
  color: white !important;
  font-weight: 600 !important;
}

.disabled-nav {
  opacity: 0.4 !important;
  pointer-events: none !important;
  cursor: not-allowed !important;
}

.disabled-nav :deep(.v-list-item__prepend) {
  color: #9e9e9e !important;
}

.disabled-nav :deep(.v-list-item-title) {
  color: #9e9e9e !important;
}

/* Disabled nav item styling for permission-restricted pages */
.nav-item-disabled {
  opacity: 0.55 !important;
  cursor: pointer !important;
  background: #f5f5f5 !important;
}

.nav-item-disabled:hover {
  background: #eeeeee !important;
}

.nav-item-disabled :deep(.v-list-item__prepend) {
  color: #757575 !important;
}

.nav-item-disabled :deep(.v-list-item-title) {
  color: #757575 !important;
  font-style: italic;
}

.badge-chip {
  font-weight: 700;
  font-size: 0.7rem;
  background: #ef4444 !important;
  color: white !important;
}

.notification-indicator {
  width: 8px;
  height: 8px;
  background: #ef4444;
  border-radius: 50%;
  margin-right: 8px;
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
  animation: pulse 2s ease-in-out infinite;
}

/* Note: pulse animation is defined in global animations.css */

.logout-nav-item {
  border-radius: 12px !important;
  margin: 1px 0 !important;
  font-size: 0.9rem !important;
  font-weight: 500 !important;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.logout-nav-item:not(.active-nav):hover {
  background: #f5f5f5 !important;
}

.logout-nav-item :deep(.v-list-item-title) {
  font-weight: 500 !important;
}

.logout-nav-item :deep(.v-list-item__prepend) {
  color: inherit !important;
}

/* ============================================
   STICKY FOOTER LAYOUT
   Global styles in app.css handle the layout
   ============================================ */

/* Main content area background */
.main-content {
  background: #f9fafb !important;
}

.v-application .main-content {
  background: #f9fafb url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><circle cx="10" cy="10" r="1" fill="%23d1d5db"/></svg>') !important;
}

/* Main wrapper - flex container for sticky footer */
.main-wrapper {
  display: flex;
  flex-direction: column;
  min-height: auto;
}

/* Content container styling */
.content-container {
  flex: 1 0 auto;
}

/* Override global container padding for dashboard - reduce left/right padding */
/* Use high specificity to override mobile-fixes.css rules */
.v-application .main-content .main-wrapper .content-container.v-container,
.main-content .content-container.v-container {
  padding: 24px 12px !important;
  max-width: none !important;
  margin-inline: 0 !important;
}

@media (min-width: 960px) {
  .v-application .main-content .main-wrapper .content-container.v-container,
  .main-content .content-container.v-container {
    padding: 24px 16px !important;
    max-width: none !important;
  }
}

@media (min-width: 1280px) {
  .v-application .main-content .main-wrapper .content-container.v-container,
  .main-content .content-container.v-container {
    max-width: none !important;
    margin-inline: 0 !important;
  }
}

.dashboard-header {
  background: white !important;
  border-radius: 24px !important;
  border: none !important;
  box-shadow: none !important;
}

.header-title {
  font-size: 2.5rem;
  font-weight: 800;
  letter-spacing: -0.04em;
  line-height: 1.1;
  text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-subtitle {
  font-size: 1.15rem;
  font-weight: 600;
  letter-spacing: -0.01em;
  margin-top: 8px;
}

/* Role-specific header title colors - with darker/black tones */
.title-green {
  background: linear-gradient(135deg, #0a3d1a 0%, #166534 30%, #22c55e 60%, #14532d 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  background-clip: text !important;
}

.title-red {
  background: linear-gradient(135deg, #450a0a 0%, #991b1b 30%, #ef4444 60%, #7f1d1d 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  background-clip: text !important;
}

.title-blue {
  background: linear-gradient(135deg, #1e1b4b 0%, #1e40af 30%, #3b82f6 60%, #1e3a8a 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  background-clip: text !important;
}

.title-purple {
  background: linear-gradient(135deg, #2e1065 0%, #6b21a8 30%, #a855f7 60%, #581c87 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  background-clip: text !important;
}

.title-orange {
  background: linear-gradient(135deg, #431407 0%, #9a3412 30%, #f97316 60%, #7c2d12 100%) !important;
  -webkit-background-clip: text !important;
  -webkit-text-fill-color: transparent !important;
  background-clip: text !important;
}

/* Role-specific header subtitle colors - darker */
.subtitle-green {
  color: #14532d !important;
}

.subtitle-red {
  color: #7f1d1d !important;
}

.subtitle-blue {
  color: #1e3a8a !important;
}

.subtitle-purple {
  color: #581c87 !important;
}

.subtitle-orange {
  color: #7c2d12 !important;
}

/* Role-specific header backgrounds */
.header-caregiver {
  background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%) !important;
}

.header-housekeeper {
  background: linear-gradient(135deg, #ffffff 0%, #f3e5f5 100%) !important;
}

.header-admin {
  background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%) !important;
}

.header-client {
  background: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%) !important;
}

.header-marketing {
  background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%) !important;
}

.header-training {
  background: linear-gradient(135deg, #ffffff 0%, #fff7ed 100%) !important;
}

/* Responsive tweaks */
@media (max-width: 960px) {
  .sidebar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    height: 100vh !important;
    height: 100dvh !important; /* Dynamic viewport height for mobile */
    max-height: 100vh !important;
    max-height: 100dvh !important;
    z-index: 1200 !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    padding-bottom: 0 !important; /* No bottom padding needed, sidebar scrolls independently */
  }

  .sidebar :deep(.v-navigation-drawer__content) {
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
    max-height: 100% !important;
    overflow: hidden !important;
    position: relative !important;
  }

  /* Target the append slot wrapper */
  .sidebar :deep(.v-navigation-drawer__content > div:last-child) {
    position: relative !important;
  }

  .sidebar :deep(.v-navigation-drawer__content > *) {
    max-width: 100% !important;
  }

  /* Ensure the drawer wrapper also respects height */
  .sidebar :deep(.v-navigation-drawer__wrapper) {
    height: 100% !important;
    max-height: 100% !important;
    overflow: hidden !important;
  }

  .sidebar-header {
    flex-shrink: 0 !important;
    padding: 12px 16px !important;
  }

  .sidebar-header .sidebar-logo {
    max-width: 40px !important;
  }

  .sidebar-header .sidebar-brand {
    font-size: 0.95rem !important;
  }

  .sidebar-header .sidebar-tagline {
    font-size: 0.7rem !important;
  }

  .sidebar-header .welcome-title {
    font-size: 1rem !important;
    margin-bottom: 4px !important;
    line-height: 1.2 !important;
  }

  .sidebar-header .welcome-subtitle {
    font-size: 0.7rem !important;
    line-height: 1.3 !important;
  }

  .sidebar-header .mb-4 {
    margin-bottom: 8px !important;
  }

  .sidebar-header .my-4 {
    margin-top: 6px !important;
    margin-bottom: 6px !important;
  }

  .sidebar-header .text-center {
    padding: 0 !important;
  }

  .sidebar-nav {
    flex: 1 1 auto !important;
    min-height: 0 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    -webkit-overflow-scrolling: touch !important;
    padding: 8px 12px 80px 12px !important; /* Extra bottom padding to ensure logout is visible */
  }

  .sidebar-nav .category-label {
    font-size: 0.65rem !important;
    margin-top: 12px !important;
    margin-bottom: 6px !important;
  }

  .sidebar-nav .nav-item {
    font-size: 0.85rem !important;
    min-height: 40px !important;
    padding: 8px 12px !important;
  }

  .sidebar-nav .nav-item :deep(.v-list-item__prepend) {
    margin-right: 12px !important;
  }

  /* Logout nav item styling */
  .logout-nav-item {
    font-size: 0.85rem !important;
    min-height: 40px !important;
    padding: 8px 12px !important;
  }

  .mobile-app-bar {
    display: flex;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 1300;
    background: white;
  }

  .mobile-app-title {
    font-weight: 700;
    margin-left: 8px;
    font-size: 0.9375rem;
  }

  /* Mobile app bar button styling */
  .mobile-app-bar :deep(.v-btn:not(.v-btn--icon)) {
    min-width: auto !important;
    padding: 0.5rem 1rem !important;
    font-size: 0.8125rem !important;
    text-transform: none !important;
    border-radius: 6px !important;
    height: 36px !important;
    font-weight: 600 !important;
  }

  .mobile-app-bar :deep(.v-btn--icon) {
    padding: 0.5rem !important;
    width: 40px !important;
    height: 40px !important;
  }

  /* Book Now button in mobile app bar */
  .mobile-app-bar :deep(.book-now-btn) {
    background: #10b981 !important;
    color: white !important;
    padding: 0.5rem 1rem !important;
    height: 36px !important;
    font-size: 0.8125rem !important;
  }

  .mobile-app-bar :deep(.book-now-btn .v-icon) {
    font-size: 18px !important;
    margin-right: 0.375rem !important;
  }

  .content-container {
    padding: 1rem !important;
  }

  /* Hide desktop header on mobile */
  .desktop-header {
    display: none !important;
  }

  /* Mobile Header Section Styles */
  .mobile-header-section {
    margin-bottom: 1.5rem;
  }

  .mobile-header-content {
    background: white;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 1rem;
  }

  .mobile-user-name {
    font-size: 1.125rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    line-height: 1.2;
  }

  .role-chip-mobile {
    font-size: 0.6875rem !important;
    height: 20px !important;
    padding: 0 8px !important;
  }

  .role-chip-mobile .v-icon {
    font-size: 12px !important;
  }

  .mobile-header-text {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
  }

  .mobile-header-title {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.2;
    margin-bottom: 0.5rem;
  }

  .mobile-header-subtitle {
    font-size: 0.9375rem;
    font-weight: 500;
    color: #64748b;
    line-height: 1.4;
    margin: 0;
  }

/* Tables: enable horizontal scrolling when needed */
  .modern-activity-table {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
  .modern-activity-table table {
    min-width: 100% !important;
    width: max-content;
  }

  /* Generic v-data-table fallback: allow horizontal scroll if no stacked mode */
  :deep(.v-data-table .v-table__wrapper) {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
  }

  /* If table cells include data-label attributes we'll use stacked card layout; otherwise horizontal scroll will be used */
  :deep(.v-data-table[data-has-stacked]) .v-table__wrapper,
  .modern-activity-table[data-has-stacked] .v-table__wrapper {
    overflow: visible !important;
  }

  /* Stacked table rows for small screens */
  .modern-activity-table table,
  .modern-activity-table thead,
  .modern-activity-table tbody,
  .modern-activity-table th,
  .modern-activity-table td,
  .modern-activity-table tr {
    display: block !important;
    width: 100% !important;
  }

  .modern-activity-table thead tr {
    display: none !important;
  }

  .modern-activity-table tr {
    margin-bottom: 12px !important;
    background: white !important;
    padding: 12px !important;
    border-radius: 12px !important;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06) !important;
  }

  .modern-activity-table td {
    display: flex !important;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px !important;
    border: none !important;
    gap: 8px !important;
  }

  .modern-activity-table td::before {
    content: attr(data-label) " : ";
    font-weight: 700;
    color: #64748b;
    margin-right: 8px;
  }

  /* Better label/value alignment */
  .modern-activity-table td {
    flex-direction: row !important;
  }

  .modern-activity-table td > *:first-child {
    flex: 0 0 auto;
    color: #475569;
    font-weight: 700;
    min-width: 90px;
  }

  .modern-activity-table td > *:last-child {
    flex: 1 1 auto;
    text-align: right;
    font-weight: 600;
    color: #0f172a;
  }

  /* Amount styling */
  .modern-activity-table .transaction-amount {
    font-weight: 800 !important;
    color: #0b4fa2 !important;
  }

  /* Status chip styling to be compact on mobile */
  .modern-activity-table .modern-type-chip {
    font-size: 12px !important;
    padding: 4px 8px !important;
  }

  /* Action buttons wrap - Enhanced touch targets */
  .modern-activity-table .action-buttons {
    justify-content: flex-end !important;
    gap: 8px !important;
    flex-wrap: wrap !important;
  }

  /* Action buttons - ensure 44px minimum touch target */
  :deep(.action-buttons) {
    flex-wrap: wrap !important;
    gap: 8px !important;
    justify-content: flex-end !important;
  }
  
  :deep(.action-buttons .v-btn) {
    min-width: 44px !important;
    min-height: 44px !important;
  }

  /* Dialogs / Modals: make card inside dialog full width with small margins */
  .v-dialog__content .v-card {
    width: calc(100% - 32px) !important;
    margin: 12px !important;
    max-height: calc(100vh - 48px) !important;
  }

  .user-info-card {
    display: none !important; /* hide large user card to save space */
  }
}

/* Additional table mobile responsiveness */
@media (max-width: 960px) {
  /* Make all v-data-tables mobile-friendly with scroll indicators */
  :deep(.v-data-table) {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  :deep(.v-data-table .v-table__wrapper) {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
    /* Scroll shadow indicators */
    background: 
        linear-gradient(to right, white 30%, transparent),
        linear-gradient(to right, transparent, white 70%) 100% 0,
        linear-gradient(to right, rgba(0,0,0,.08), transparent),
        linear-gradient(to left, rgba(0,0,0,.08), transparent) 100% 0 !important;
    background-repeat: no-repeat !important;
    background-size: 40px 100%, 40px 100%, 14px 100%, 14px 100% !important;
    background-attachment: local, local, scroll, scroll !important;
  }

  /* Table pagination mobile adjustments */
  :deep(.v-data-table-footer) {
    flex-wrap: wrap !important;
    padding: 0.5rem !important;
    gap: 0.5rem !important;
  }

  :deep(.v-data-table-footer__items-per-page) {
    font-size: 0.75rem !important;
    margin-right: 0.5rem !important;
  }

  :deep(.v-data-table-footer__info) {
    font-size: 0.75rem !important;
  }

  :deep(.v-data-table-footer__pagination) {
    font-size: 0.75rem !important;
  }
  
  /* Vuetify tabs - ensure touch targets */
  :deep(.v-tab) {
    min-height: 48px !important;
    min-width: 72px !important;
  }

  /* Make table cards scrollable on mobile */
  :deep(.v-card) {
    overflow-x: auto;
  }
}

/* Additional small-screen adjustments */
@media (max-width: 480px) {
  /* Stack table rows as cards on very small screens */
  :deep(.v-data-table .v-table__wrapper table) {
    display: block !important;
  }

  :deep(.v-data-table .v-table__wrapper thead) {
    display: none !important;
  }

  :deep(.v-data-table .v-table__wrapper tbody) {
    display: block !important;
  }

  :deep(.v-data-table .v-table__wrapper tr) {
    display: block !important;
    margin-bottom: 0.75rem !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 8px !important;
    padding: 0.75rem !important;
    background: white !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
  }

  :deep(.v-data-table .v-table__wrapper td) {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 0.5rem 0.75rem !important;
    border: none !important;
    border-bottom: 1px solid #f3f4f6 !important;
  }

  :deep(.v-data-table .v-table__wrapper td:last-child) {
    border-bottom: none !important;
  }

  :deep(.v-data-table .v-table__wrapper td::before) {
    content: attr(data-label) ": " !important;
    font-weight: 600 !important;
    color: #6b7280 !important;
    margin-right: 0.5rem !important;
    font-size: 0.75rem !important;
  }

  /* Pagination compact on mobile */
  :deep(.v-data-table-footer) {
    padding: 0.5rem !important;
    font-size: 0.6875rem !important;
  }

  :deep(.v-data-table-footer__items-per-page) {
    display: none !important;
  }

  :deep(.v-data-table-footer__info) {
    font-size: 0.6875rem !important;
  }
}

/* Mobile Stats Cards and General Elements */
@media (max-width: 960px) {
  /* Stats cards spacing */
  .mb-4 {
    margin-bottom: 1rem !important;
  }

  /* Vuetify rows spacing on mobile */
  :deep(.v-row) {
    margin-left: -0.5rem !important;
    margin-right: -0.5rem !important;
  }

  :deep(.v-col) {
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
  }

  /* Cards mobile adjustments */
  :deep(.v-card) {
    margin-bottom: 1rem !important;
  }

  :deep(.v-card-text) {
    padding: 1rem !important;
  }

  /* Buttons mobile */
  :deep(.v-btn) {
    font-size: 0.875rem !important;
  }

  /* Icons mobile */
  :deep(.v-icon) {
    font-size: 20px !important;
  }
}

@media (max-width: 480px) {
  .mobile-header-content {
    padding: 1rem;
  }

  .mobile-user-name {
    font-size: 1rem;
  }

  .mobile-header-title {
    font-size: 1.25rem;
  }

  .mobile-header-subtitle {
    font-size: 0.875rem;
  }

  /* Mobile app bar button styling for small screens */
  .mobile-app-bar :deep(.v-btn:not(.v-btn--icon)) {
    padding: 0.4375rem 0.75rem !important;
    font-size: 0.75rem !important;
    height: 32px !important;
  }

  .mobile-app-bar :deep(.book-now-btn .v-icon) {
    font-size: 16px !important;
    margin-right: 0.25rem !important;
  }

  /* Stats cards spacing very tight */
  .mb-4 {
    margin-bottom: 0.75rem !important;
  }

  /* Tighter row spacing */
  :deep(.v-row) {
    margin-left: -0.375rem !important;
    margin-right: -0.375rem !important;
  }

  :deep(.v-col) {
    padding-left: 0.375rem !important;
    padding-right: 0.375rem !important;
  }

  /* Cards very compact */
  :deep(.v-card-text) {
    padding: 0.875rem !important;
  }
}

@media (max-width: 460px) {
  .header-title {
    font-size: 1.25rem !important;
  }

  .nav-item {
    font-size: 0.85rem !important;
  }
}

/* =============================================================
   MOBILE BOTTOM NAVIGATION - Premium Touch Experience
   ============================================================= */
.mobile-bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1400;
  border-radius: 0;
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.08);
  background: white;
  border-top: 1px solid #e5e7eb;
  padding: 4px 8px;
  height: 64px;
  /* Safe area support for iPhone X+ */
  padding-bottom: max(4px, env(safe-area-inset-bottom, 0px));
}

@supports (padding: env(safe-area-inset-bottom)) {
  .mobile-bottom-nav {
    height: calc(64px + env(safe-area-inset-bottom));
  }
}

/* Nav button - 56px minimum touch target (exceeds 44px WCAG) */
.mobile-nav-btn {
  min-width: 56px !important;
  min-height: 56px !important;
  width: auto !important;
  height: 56px !important;
  border-radius: 12px !important;
  transition: background 0.15s ease, transform 0.1s ease !important;
  flex-direction: column !important;
  gap: 2px !important;
}

.mobile-nav-btn .v-icon {
  font-size: 22px !important;
  margin-bottom: 2px !important;
}

.mobile-nav-label {
  font-size: 0.65rem !important;
  font-weight: 500 !important;
  text-transform: capitalize !important;
  letter-spacing: 0.01em !important;
  color: inherit !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  max-width: 64px !important;
}

.mobile-nav-btn:active {
  transform: scale(0.95) !important;
}

.mobile-active {
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%) !important;
  color: white !important;
}

.mobile-active .v-icon {
  color: white !important;
  transform: scale(1.1);
}

.mobile-active .mobile-nav-label {
  color: white !important;
  font-weight: 600 !important;
}

/* Content spacing to prevent bottom nav overlap */
@media (max-width: 960px) {
  .main-wrapper {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 56px);
    padding-bottom: calc(80px + env(safe-area-inset-bottom, 0px));
  }
}

/* Safe Area Insets for notched devices */
@supports (padding: env(safe-area-inset-top)) {
  .mobile-app-bar {
    padding-top: env(safe-area-inset-top) !important;
    height: calc(56px + env(safe-area-inset-top)) !important;
  }
  
  .sidebar {
    padding-top: env(safe-area-inset-top) !important;
  }
}

.user-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1a1a1a;
  letter-spacing: -0.02em;
}

.user-role {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
  margin-top: 2px;
}

/* Enhanced User Info Card Styles */
.user-info-card {
  display: flex !important;
  align-items: center !important;
  padding: 16px 24px !important;
  border-radius: 20px !important;
  background: linear-gradient(145deg, #ffffff 0%, #f8fafc 50%, #f1f5f9 100%) !important;
  box-shadow: none !important;
  backdrop-filter: blur(20px) !important;
  transition: none !important;
  position: relative !important;
  overflow: hidden !important;
  min-width: 220px !important;
}

.user-info-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, transparent 40%, rgba(255,255,255,0.4) 100%);
  pointer-events: none;
}

.user-info-card:hover {
  transform: none !important;
  box-shadow: none !important;
}

.user-info-inner {
  display: flex !important;
  align-items: center !important;
  position: relative;
  z-index: 1;
}

.avatar-wrapper {
  position: relative !important;
  flex-shrink: 0;
}

.user-avatar {
  font-weight: 800 !important;
  font-size: 1.4rem !important;
  letter-spacing: 1px !important;
  border: 4px solid white !important;
  box-shadow: none !important;
}

.avatar-initials {
  color: white !important;
  font-weight: 800 !important;
  text-shadow: none !important;
  font-size: 1.3rem !important;
}

.avatar-status-dot {
  position: absolute !important;
  bottom: 0 !important;
  right: 0 !important;
  width: 18px !important;
  height: 18px !important;
  border-radius: 50% !important;
  border: 3px solid white !important;
  box-shadow: none !important;
}

.status-green { background: linear-gradient(135deg, #22c55e, #15803d) !important; }
.status-red { background: linear-gradient(135deg, #ef4444, #b91c1c) !important; }
.status-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important; }
.status-purple { background: linear-gradient(135deg, #a855f7, #7c3aed) !important; }
.status-orange { background: linear-gradient(135deg, #f97316, #c2410c) !important; }

.user-details {
  display: flex !important;
  flex-direction: column !important;
  gap: 6px !important;
  margin-left: 16px !important;
}

.user-name-styled {
  font-size: 1.15rem !important;
  font-weight: 800 !important;
  letter-spacing: -0.03em !important;
  line-height: 1.2 !important;
  text-shadow: none !important;
}

.text-green { color: #15803d !important; }
.text-red { color: #b91c1c !important; }
.text-blue { color: #1d4ed8 !important; }
.text-purple { color: #7c3aed !important; }
.text-orange { color: #c2410c !important; }

.user-role-badge {
  margin-top: 4px !important;
}

.role-chip {
  font-weight: 700 !important;
  font-size: 0.72rem !important;
  letter-spacing: 0.5px !important;
  padding: 0 14px !important;
  height: 26px !important;
  text-transform: uppercase !important;
  box-shadow: none !important;
}

/* Role-specific card accents */
.role-caregiver {
  background: linear-gradient(145deg, #ffffff 0%, #f0fdf4 30%, #dcfce7 100%) !important;
}

.role-admin {
  background: linear-gradient(145deg, #ffffff 0%, #fef2f2 30%, #fee2e2 100%) !important;
}

.role-client {
  background: linear-gradient(145deg, #ffffff 0%, #eff6ff 30%, #dbeafe 100%) !important;
}

.role-marketing {
  background: linear-gradient(145deg, #ffffff 0%, #faf5ff 30%, #f3e8ff 100%) !important;
}

.role-training {
  background: linear-gradient(145deg, #ffffff 0%, #fff7ed 30%, #ffedd5 100%) !important;
}

.gradient-avatar {
  background: linear-gradient(135deg, v-bind('themeColor === "success" ? "#10b981" : themeColor === "error" ? "#dc2626" : themeColor === "grey-darken-2" ? "#616161" : "#3b82f6"'), v-bind('themeColor === "success" ? "#059669" : themeColor === "error" ? "#b91c1c" : themeColor === "grey-darken-2" ? "#424242" : "#1e40af"')) !important;
  font-size: 1.25rem;
  font-weight: 700;
}

.toggle-item {
  background: #f8f9fa !important;
}

.toggle-item.active-nav {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
  color: white !important;
  font-weight: 600 !important;
  box-shadow: 0 4px 16px rgba(220, 38, 38, 0.25);
}

.toggle-item.active-nav :deep(.v-list-item__prepend) {
  color: white !important;
}

.toggle-item.active-nav :deep(.v-list-item-title) {
  color: white !important;
  font-weight: 600 !important;
}

.toggle-item.active-nav .toggle-icon {
  color: white !important;
}

.toggle-icon {
  transition: transform 0.2s ease;
}

.rotate-180 {
  transform: rotate(180deg);
}

.sub-items {
  margin-top: 4px;
  margin-bottom: 8px;
}

.sub-nav-item {
  background: #f1f3f4 !important;
  margin-left: 16px !important;
  border-radius: 8px !important;
}

.sub-nav-item:hover {
  background: #e8f0fe !important;
}

.sub-nav-item.active-nav {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
  color: white !important;
  font-weight: 600 !important;
  box-shadow: 0 4px 16px rgba(220, 38, 38, 0.25);
}

.sub-nav-item.active-nav :deep(.v-list-item__prepend) {
  color: white !important;
}

.sub-nav-item.active-nav :deep(.v-list-item-title) {
  color: white !important;
  font-weight: 600 !important;
}

/* Modern Table Styling */
:deep(.v-data-table) {
  background: transparent !important;
  box-shadow: none !important;
}

:deep(.v-data-table .v-table__wrapper) {
  border-radius: 16px !important;
  overflow-x: auto !important;
  overflow-y: hidden !important;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
  border: 1px solid #f1f5f9 !important;
  width: 100% !important;
  min-width: 100% !important;
}

:deep(.v-data-table table) {
  border-collapse: separate !important;
  border-spacing: 0 !important;
  background: white !important;
  width: 100% !important;
  table-layout: fixed !important;
}

:deep(.v-data-table th:last-child),
:deep(.v-data-table td:last-child) {
  width: 150px !important;
  min-width: 150px !important;
  text-align: center !important;
}

:deep(.v-data-table th),
:deep(.v-data-table td) {
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

:deep(.v-data-table thead) {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
  border-bottom: 2px solid #e2e8f0 !important;
}

:deep(.v-data-table thead th) {
  background: transparent !important;
  color: v-bind('themeColor === "success" ? "#10b981" : themeColor === "error" ? "#dc2626" : themeColor === "grey-darken-2" ? "#616161" : "#3b82f6"') !important;
  font-weight: 800 !important;
  font-size: 0.75rem !important;
  letter-spacing: 0.1em !important;
  text-transform: uppercase !important;
  padding: 16px 20px !important;
  border: none !important;
  border-right: 1px solid #d1d5db !important;
  position: relative !important;
}

:deep(.v-data-table thead th:last-child) {
  border-right: none !important;
}

:deep(.v-data-table thead th::after) {
  content: '' !important;
  position: absolute !important;
  bottom: 0 !important;
  left: 0 !important;
  right: 0 !important;
  height: 3px !important;
  background: v-bind('themeColor === "success" ? "linear-gradient(90deg, #10b981, #059669)" : themeColor === "error" ? "linear-gradient(90deg, #dc2626, #b91c1c)" : themeColor === "grey-darken-2" ? "linear-gradient(90deg, #616161, #424242)" : "linear-gradient(90deg, #3b82f6, #2563eb)"') !important;
  opacity: 0.6 !important;
}

:deep(.v-data-table tbody tr) {
  background: white !important;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
  border-bottom: 1px solid #f1f5f9 !important;
}

:deep(.v-data-table tbody tr:hover) {
  background: v-bind('themeColor === "success" ? "#f0fdf4" : themeColor === "error" ? "#fef2f2" : themeColor === "grey-darken-2" ? "#fafafa" : "#f0f9ff"') !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

:deep(.v-data-table tbody tr:last-child) {
  border-bottom: none !important;
}

:deep(.v-data-table tbody td) {
  font-size: 0.875rem !important;
  font-weight: 500 !important;
  padding: 14px 20px !important;
  color: #334155 !important;
  border: none !important;
  border-right: 1px solid #d1d5db !important;
  vertical-align: middle !important;
}

:deep(.v-data-table tbody td:last-child) {
  border-right: none !important;
  padding: 12px 16px !important;
}

/* Action Buttons Styling */
:deep(.action-buttons) {
  display: flex;
  gap: 6px;
  align-items: center;
  justify-content: center;
  flex-wrap: nowrap;
  min-width: 140px;
}

:deep(.action-btn-view),
:deep(.action-btn-edit),
:deep(.action-btn-delete),
:deep(.action-btn-mark-paid),
:deep(.action-btn-pay-now) {
  width: 36px !important;
  height: 36px !important;
  min-width: 36px !important;
  padding: 0 !important;
  border-radius: 8px !important;
  transition: all 0.2s ease !important;
  flex-shrink: 0 !important;
}

:deep(.action-btn-view) {
  background-color: #3b82f6 !important;
  color: white !important;
  box-shadow: 0 3px 8px rgba(59, 130, 246, 0.25), 0 1px 3px rgba(59, 130, 246, 0.15) !important;
}

:deep(.action-btn-view:hover) {
  background-color: #2563eb !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35), 0 2px 6px rgba(59, 130, 246, 0.2) !important;
}

:deep(.action-btn-edit) {
  background-color: #f59e0b !important;
  color: white !important;
  box-shadow: 0 3px 8px rgba(245, 158, 11, 0.25), 0 1px 3px rgba(245, 158, 11, 0.15) !important;
}

:deep(.action-btn-edit:hover) {
  background-color: #d97706 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35), 0 2px 6px rgba(245, 158, 11, 0.2) !important;
}

:deep(.action-btn-delete) {
  background-color: #ef4444 !important;
  color: white !important;
  box-shadow: 0 3px 8px rgba(239, 68, 68, 0.25), 0 1px 3px rgba(239, 68, 68, 0.15) !important;
}

:deep(.action-btn-delete:hover) {
  background-color: #dc2626 !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35), 0 2px 6px rgba(239, 68, 68, 0.2) !important;
}

:deep(.action-btn-view .v-icon),
:deep(.action-btn-edit .v-icon),
:deep(.action-btn-delete .v-icon),
:deep(.action-btn-mark-paid .v-icon),
:deep(.action-btn-pay-now .v-icon) {
  color: white !important;
  font-size: 16px !important;
}

:deep(.v-data-table tbody td:first-child) {
  font-weight: 600 !important;
  color: #1e293b !important;
}

:deep(.v-data-table-footer) {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
  padding: 20px 24px !important;
  border: none !important;
  border-top: 1px solid #e2e8f0 !important;
  margin-top: 0 !important;
}

:deep(.v-data-table-footer__items-per-page) {
  align-items: center;
  gap: 12px;
}

:deep(.v-data-table-footer__items-per-page > span) {
  color: #1a1a1a !important;
  font-weight: 600 !important;
  font-size: 0.9rem !important;
}

:deep(.v-data-table-footer .v-select) {
  max-width: 80px;
}

:deep(.v-data-table-footer .v-select .v-field) {
  border-radius: 10px !important;
  border: 2px solid #e5e7eb !important;
  background: white !important;
  font-weight: 600 !important;
  transition: all 0.2s ease;
  min-width: 80px !important;
}

:deep(.v-data-table-footer .v-select .v-field__input) {
  min-height: 40px !important;
  padding: 8px 12px !important;
}

:deep(.v-data-table-footer .v-select .v-field__input input) {
  font-size: 14px !important;
  font-weight: 600 !important;
  text-align: center !important;
}

:deep(.v-data-table-footer .v-select .v-field__append-inner .v-icon) {
  display: none !important;
}

:deep(.v-data-table-footer .v-select .v-field__input::after) {
  content: '' !important;
  position: absolute !important;
  right: 8px !important;
  top: 50% !important;
  transform: translateY(-50%) !important;
  width: 0 !important;
  height: 0 !important;
  border-left: 4px solid transparent !important;
  border-right: 4px solid transparent !important;
  border-top: 4px solid #666 !important;
}

:deep(.v-data-table-footer .v-select .v-field:hover) {
  border-color: v-bind('themeColor === "success" ? "#10b981" : themeColor === "error" ? "#dc2626" : "#3b82f6"') !important;
}

:deep(.v-data-table-footer__info) {
  color: #1a1a1a !important;
  font-weight: 600 !important;
  font-size: 0.9rem !important;
}

:deep(.v-data-table-footer__pagination) {
  gap: 8px;
}

:deep(.v-data-table-footer .v-btn--icon) {
  border-radius: 10px !important;
  border: 2px solid #e5e7eb !important;
  background: white !important;
  transition: all 0.2s ease;
}

:deep(.v-data-table-footer .v-btn--icon:hover:not(:disabled)) {
  border-color: v-bind('themeColor === "success" ? "#10b981" : themeColor === "error" ? "#dc2626" : "#3b82f6"') !important;
  background: v-bind('themeColor === "success" ? "#f0fdf4" : themeColor === "error" ? "#fef2f2" : "#eff6ff"') !important;
  color: v-bind('themeColor === "success" ? "#10b981" : themeColor === "error" ? "#dc2626" : "#3b82f6"') !important;
}

:deep(.v-data-table-footer .v-btn--icon:disabled) {
  opacity: 0.3;
}

:deep(.v-chip) {
  font-weight: 600 !important;
  font-size: 0.8rem !important;
  padding: 4px 12px !important;
  border-radius: 8px !important;
  text-transform: capitalize !important;
  color: white !important;
}

:deep(.v-chip .v-chip__prepend) {
  margin-right: 4px !important;
}

:deep(.v-chip .v-icon) {
  font-size: 14px !important;
  color: white !important;
}

:deep(.v-chip--variant-flat) {
  opacity: 1 !important;
}

:deep(.v-chip.text-success) {
  background-color: #10b981 !important;
  color: white !important;
}

:deep(.v-chip.text-primary) {
  background-color: #3b82f6 !important;
  color: white !important;
}

:deep(.v-chip.text-warning) {
  background-color: #f59e0b !important;
  color: white !important;
}

:deep(.v-chip.text-error) {
  background-color: #ef4444 !important;
  color: white !important;
}

:deep(.v-chip.text-info) {
  background-color: #06b6d4 !important;
  color: white !important;
}

/* Global Dialog/Modal Styling */
:deep(.v-dialog .v-card) {
  border-radius: 20px !important;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
  overflow: hidden !important;
}

:deep(.v-dialog .v-card .card-header) {
  background: linear-gradient(135deg, v-bind('themeColor === "success" ? "#10b981" : themeColor === "error" ? "#dc2626" : "#3b82f6"') 0%, v-bind('themeColor === "success" ? "#059669" : themeColor === "error" ? "#b91c1c" : "#2563eb"') 100%) !important;
  color: white !important;
  border-bottom: none !important;
}

:deep(.v-dialog .v-card .card-header .section-title) {
  color: white !important;
  font-size: 1.5rem !important;
  font-weight: 700 !important;
  letter-spacing: -0.02em !important;
}

:deep(.v-dialog .v-card .card-header .v-btn) {
  color: white !important;
}

:deep(.v-dialog .v-card-text) {
  padding: 24px !important;
}

:deep(.v-dialog .v-card-actions) {
  padding: 16px 24px !important;
  background: #f9fafb !important;
  border-top: 1px solid #e5e7eb !important;
}

:deep(.v-dialog .v-text-field .v-field),
:deep(.v-dialog .v-select .v-field),
:deep(.v-dialog .v-textarea .v-field) {
  border-radius: 12px !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  transition: box-shadow 0.2s ease !important;
}

:deep(.v-dialog .v-text-field .v-field:hover),
:deep(.v-dialog .v-select .v-field:hover),
:deep(.v-dialog .v-textarea .v-field:hover) {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08) !important;
}

:deep(.v-dialog .v-text-field .v-field--focused),
:deep(.v-dialog .v-select .v-field--focused),
:deep(.v-dialog .v-textarea .v-field--focused) {
  box-shadow: 0 4px 12px v-bind('themeColor === "success" ? "rgba(16, 185, 129, 0.15)" : themeColor === "error" ? "rgba(220, 38, 38, 0.15)" : "rgba(59, 130, 246, 0.15)"'), 0 2px 4px v-bind('themeColor === "success" ? "rgba(16, 185, 129, 0.1)" : themeColor === "error" ? "rgba(220, 38, 38, 0.1)" : "rgba(59, 130, 246, 0.1)"') !important;
}

:deep(.v-dialog .v-btn) {
  border-radius: 12px !important;
  font-weight: 600 !important;
  text-transform: none !important;
  letter-spacing: -0.01em !important;
  padding: 12px 24px !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
  transition: all 0.2s ease !important;
}

:deep(.v-dialog .v-btn:hover) {
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

:deep(.v-dialog .v-btn--variant-outlined) {
  border-width: 2px !important;
}

:deep(.v-dialog .v-switch) {
  margin-top: 8px !important;
}

:deep(.v-dialog .v-switch .v-label) {
  font-weight: 500 !important;
  color: #374151 !important;
}
</style>

<style>
/* ========================================
   GLOBAL SIDEBAR TEXT COLOR FIXES
   Force dark text on light sidebar background
   ======================================== */

/* Force sidebar to have dark text */
.v-navigation-drawer.sidebar {
  color: #1e293b !important;
}

.v-navigation-drawer.sidebar .sidebar-header {
  color: #1e293b !important;
}

.v-navigation-drawer.sidebar .sidebar-brand {
  color: #1a1a1a !important;
}

.v-navigation-drawer.sidebar .sidebar-tagline {
  color: #666666 !important;
}

.v-navigation-drawer.sidebar .welcome-title {
  color: #0B4FA2 !important;
  opacity: 1 !important;
  display: block !important;
  visibility: visible !important;
}

.v-navigation-drawer.sidebar .welcome-subtitle {
  color: #475569 !important;
  opacity: 1 !important;
  display: block !important;
  visibility: visible !important;
}

/* Override Vuetify's default text colors in sidebar */
.v-navigation-drawer.sidebar h1,
.v-navigation-drawer.sidebar h2,
.v-navigation-drawer.sidebar h3,
.v-navigation-drawer.sidebar h4,
.v-navigation-drawer.sidebar h5,
.v-navigation-drawer.sidebar h6 {
  color: #0B4FA2 !important;
  opacity: 1 !important;
}

.v-navigation-drawer.sidebar p {
  color: #475569 !important;
  opacity: 1 !important;
}

/* End Sidebar Text Fixes */

/* Global Dialog/Modal Styling - Unscoped for child components */
.v-dialog .v-card {
  border-radius: 20px !important;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
  overflow: hidden !important;
}

/* Force all dialog headers to have gradient - fallback */
.v-dialog > .v-overlay__content > .v-card > .v-card-item:first-child,
.v-dialog > .v-overlay__content > .v-card > .v-card-title:first-child {
  padding: 24px !important;
}

/* Admin Dashboard - Red */
.v-app[data-user-role="admin"] .v-dialog .v-card .card-header,
.v-app[data-user-role="admin"] .v-dialog .v-card > .v-card-title:first-child {
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
  color: white !important;
  border-bottom: none !important;
}

/* Client Dashboard - Blue */
.v-app[data-user-role="client"] .v-dialog .v-card .card-header,
.v-app[data-user-role="client"] .v-dialog .v-card > .v-card-title:first-child {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
  color: white !important;
  border-bottom: none !important;
}

/* Caregiver Dashboard - Green */
.v-app[data-user-role="caregiver"] .v-dialog .v-card .card-header,
.v-app[data-user-role="caregiver"] .v-dialog .v-card > .v-card-title:first-child {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
  color: white !important;
  border-bottom: none !important;
}

/* Housekeeper Dashboard - Green */
.v-app[data-user-role="housekeeper"] .v-dialog .v-card .card-header,
.v-app[data-user-role="housekeeper"] .v-dialog .v-card > .v-card-title:first-child {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
  color: white !important;
  border-bottom: none !important;
}

/* Marketing Dashboard - Grey */
.v-app[data-user-role="marketing"] .v-dialog .v-card .card-header,
.v-app[data-user-role="marketing"] .v-dialog .v-card > .v-card-title:first-child {
  background: linear-gradient(135deg, #616161 0%, #424242 100%) !important;
  color: white !important;
  border-bottom: none !important;
}

.v-dialog .v-card .card-header .section-title,
.v-dialog .v-card > .v-card-title {
  color: white !important;
  font-size: 1.5rem !important;
  font-weight: 700 !important;
  letter-spacing: -0.02em !important;
  padding: 24px !important;
}

.v-dialog .v-card .v-card-title *,
.v-dialog .v-card .card-header * {
  color: white !important;
}

.v-dialog .v-card .card-header .v-btn {
  color: white !important;
}

.v-dialog .v-card-text {
  padding: 24px !important;
}

.v-dialog .v-card-actions {
  padding: 16px 24px !important;
  background: #f9fafb !important;
  border-top: 1px solid #e5e7eb !important;
  display: flex !important;
  justify-content: flex-end !important;
  align-items: center !important;
  gap: 12px !important;
}

.v-dialog .v-card-actions .v-btn {
  margin: 0 !important;
}

.v-dialog .v-text-field .v-field,
.v-dialog .v-select .v-field,
.v-dialog .v-textarea .v-field {
  border-radius: 12px !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04) !important;
  transition: box-shadow 0.2s ease !important;
}

.v-dialog .v-text-field .v-field:hover,
.v-dialog .v-select .v-field:hover,
.v-dialog .v-textarea .v-field:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08) !important;
}

/* Admin Dashboard - Red focus */
.v-app[data-user-role="admin"] .v-dialog .v-text-field .v-field--focused,
.v-app[data-user-role="admin"] .v-dialog .v-select .v-field--focused,
.v-app[data-user-role="admin"] .v-dialog .v-textarea .v-field--focused {
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15), 0 2px 4px rgba(220, 38, 38, 0.1) !important;
}

/* Client Dashboard - Blue focus */
.v-app[data-user-role="client"] .v-dialog .v-text-field .v-field--focused,
.v-app[data-user-role="client"] .v-dialog .v-select .v-field--focused,
.v-app[data-user-role="client"] .v-dialog .v-textarea .v-field--focused {
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15), 0 2px 4px rgba(59, 130, 246, 0.1) !important;
}

/* Caregiver Dashboard - Green focus */
.v-app[data-user-role="caregiver"] .v-dialog .v-text-field .v-field--focused,
.v-app[data-user-role="caregiver"] .v-dialog .v-select .v-field--focused,
.v-app[data-user-role="caregiver"] .v-dialog .v-textarea .v-field--focused {
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15), 0 2px 4px rgba(16, 185, 129, 0.1) !important;
}

/* Housekeeper Dashboard - Green focus */
.v-app[data-user-role="housekeeper"] .v-dialog .v-text-field .v-field--focused,
.v-app[data-user-role="housekeeper"] .v-dialog .v-select .v-field--focused,
.v-app[data-user-role="housekeeper"] .v-dialog .v-textarea .v-field--focused {
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15), 0 2px 4px rgba(16, 185, 129, 0.1) !important;
}

/* Marketing Dashboard - Grey focus */
.v-app[data-user-role="marketing"] .v-dialog .v-text-field .v-field--focused,
.v-app[data-user-role="marketing"] .v-dialog .v-select .v-field--focused,
.v-app[data-user-role="marketing"] .v-dialog .v-textarea .v-field--focused {
  box-shadow: 0 4px 12px rgba(97, 97, 97, 0.15), 0 2px 4px rgba(97, 97, 97, 0.1) !important;
}

.v-dialog .v-btn {
  border-radius: 12px !important;
  font-weight: 600 !important;
  text-transform: none !important;
  letter-spacing: -0.01em !important;
  padding: 12px 24px !important;
  height: 44px !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
  transition: all 0.2s ease !important;
}

.v-dialog .v-btn:hover {
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.v-dialog .v-btn--variant-outlined {
  border-width: 2px !important;
}

.v-dialog .v-switch {
  margin-top: 8px !important;
}

.v-dialog .v-switch .v-label {
  font-weight: 500 !important;
  color: #374151 !important;
}

/* ============================================
   P2-02: DASHBOARD HEADER FIX - iPad Overlap
   Added: January 24, 2026
   Fixes header content overlap at 960-1200px
   ============================================ */

@media (min-width: 961px) and (max-width: 1200px) {
  .dashboard-header .v-card-text {
    flex-wrap: wrap !important;
    gap: 1rem !important;
  }
  
  .header-content {
    order: -1;
    flex: 0 0 100%;
    margin-bottom: 1rem;
    text-align: center;
  }
  
  .user-info-card {
    margin-left: auto;
    margin-right: auto;
  }
  
  .header-title {
    font-size: 2rem !important;
  }
  
  .header-subtitle {
    font-size: 1rem !important;
  }
}

/* ============================================
   MOBILE NAVIGATION DRAWER WIDTH FIX
   Consistent 85% width across devices
   ============================================ */

@media (max-width: 320px) {
  .sidebar,
  .v-navigation-drawer {
    max-width: 272px !important;
    width: 85vw !important;
  }
}

@media (min-width: 321px) and (max-width: 360px) {
  .sidebar,
  .v-navigation-drawer {
    max-width: 288px !important;
    width: 85vw !important;
  }
}

@media (min-width: 361px) and (max-width: 480px) {
  .sidebar,
  .v-navigation-drawer {
    max-width: 320px !important;
    width: 85vw !important;
  }
}

/* ============================================
   TABLE SCROLL INDICATORS
   Visual cues for horizontally scrollable tables
   ============================================ */

@media (max-width: 768px) {
  :deep(.v-data-table .v-table__wrapper),
  .table-scroll-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    position: relative;
    
    /* Gradient shadows indicate scrollable content */
    background: 
      linear-gradient(to right, white 20px, transparent 60px),
      linear-gradient(to left, white 20px, transparent 60px) 100% 0,
      linear-gradient(to right, rgba(0,0,0,0.08) 0, transparent 15px),
      linear-gradient(to left, rgba(0,0,0,0.08) 0, transparent 15px) 100% 0;
    background-repeat: no-repeat;
    background-size: 60px 100%, 60px 100%, 15px 100%, 15px 100%;
    background-attachment: local, local, scroll, scroll;
  }
}

/* ============================================
   TOUCH TARGET IMPROVEMENTS
   WCAG 2.1 AA: 44px minimum
   ============================================ */

@media (max-width: 768px) {
  /* Action buttons in tables/cards */
  :deep(.action-buttons) {
    gap: 8px !important;
    flex-wrap: wrap;
  }
  
  :deep(.action-buttons .v-btn) {
    min-width: 44px !important;
    min-height: 44px !important;
  }
  
  /* Navigation items */
  .nav-item {
    min-height: 48px !important;
  }
  
  /* Category labels spacing */
  .category-label {
    padding-top: 8px !important;
    padding-bottom: 8px !important;
  }
}

/* ============================================
   ENHANCED FOCUS STATES (Accessibility)
   ============================================ */

.nav-item:focus-visible {
  outline: 3px solid rgba(59, 130, 246, 0.6) !important;
  outline-offset: 2px !important;
  border-radius: 12px !important;
}

.mobile-nav-btn:focus-visible {
  outline: 3px solid rgba(59, 130, 246, 0.6) !important;
  outline-offset: 2px !important;
}

/* ============================================
   BATTERY OPTIMIZATION
   Pause animations when tab is hidden
   ============================================ */

.page-hidden .notification-indicator,
.page-hidden .notification-dot {
  animation-play-state: paused !important;
}

:root[data-page-hidden] .notification-indicator,
:root[data-page-hidden] .notification-dot {
  animation-play-state: paused !important;
}

@media (max-width: 768px) {
  .notification-indicator {
    animation-iteration-count: 6 !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .notification-indicator,
  .notification-dot {
    animation: none !important;
  }
}
</style>
