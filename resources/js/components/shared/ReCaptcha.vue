<template>
  <div class="recaptcha-container" :class="{ 'recaptcha-compact': compact }">
    <div 
      :id="containerId" 
      class="g-recaptcha"
      :data-sitekey="siteKey"
      :data-size="size"
      :data-theme="theme"
      :data-callback="callbackName"
      :data-expired-callback="expiredCallbackName"
      :data-error-callback="errorCallbackName"
    ></div>
    <input type="hidden" :name="inputName" :value="token" />
    <p v-if="error" class="recaptcha-error">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  siteKey: {
    type: String,
    default: () => window.RECAPTCHA_SITE_KEY || ''
  },
  size: {
    type: String,
    default: 'normal',
    validator: (v) => ['normal', 'compact', 'invisible'].includes(v)
  },
  theme: {
    type: String,
    default: 'light',
    validator: (v) => ['light', 'dark'].includes(v)
  },
  inputName: {
    type: String,
    default: 'g-recaptcha-response'
  },
  compact: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['verify', 'expire', 'error']);

const token = ref('');
const error = ref('');
const widgetId = ref(null);
const containerId = `recaptcha-${Math.random().toString(36).substr(2, 9)}`;
const callbackName = `recaptchaCallback_${containerId}`;
const expiredCallbackName = `recaptchaExpired_${containerId}`;
const errorCallbackName = `recaptchaError_${containerId}`;

// Global callbacks
window[callbackName] = (response) => {
  token.value = response;
  error.value = '';
  emit('verify', response);
};

window[expiredCallbackName] = () => {
  token.value = '';
  error.value = 'reCAPTCHA expired. Please verify again.';
  emit('expire');
};

window[errorCallbackName] = () => {
  token.value = '';
  error.value = 'reCAPTCHA error. Please try again.';
  emit('error');
};

const loadRecaptchaScript = () => {
  return new Promise((resolve, reject) => {
    if (window.grecaptcha && window.grecaptcha.render) {
      resolve();
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://www.google.com/recaptcha/api.js?onload=recaptchaOnLoad&render=explicit';
    script.async = true;
    script.defer = true;

    window.recaptchaOnLoad = () => {
      resolve();
    };

    script.onerror = () => {
      reject(new Error('Failed to load reCAPTCHA'));
    };

    document.head.appendChild(script);
  });
};

const renderRecaptcha = () => {
  if (window.grecaptcha && window.grecaptcha.render) {
    try {
      widgetId.value = window.grecaptcha.render(containerId, {
        sitekey: props.siteKey,
        size: props.size,
        theme: props.theme,
        callback: callbackName,
        'expired-callback': expiredCallbackName,
        'error-callback': errorCallbackName
      });
    } catch (e) {
      console.error('reCAPTCHA render error:', e);
    }
  }
};

const reset = () => {
  if (window.grecaptcha && widgetId.value !== null) {
    window.grecaptcha.reset(widgetId.value);
    token.value = '';
  }
};

const execute = () => {
  if (window.grecaptcha && widgetId.value !== null) {
    window.grecaptcha.execute(widgetId.value);
  }
};

const getToken = () => token.value;

// Expose methods
defineExpose({ reset, execute, getToken });

onMounted(async () => {
  if (!props.siteKey) {
    console.warn('reCAPTCHA site key not provided');
    return;
  }

  try {
    await loadRecaptchaScript();
    renderRecaptcha();
  } catch (e) {
    error.value = 'Failed to load reCAPTCHA';
    console.error(e);
  }
});

onUnmounted(() => {
  // Cleanup global callbacks
  delete window[callbackName];
  delete window[expiredCallbackName];
  delete window[errorCallbackName];
});
</script>

<style scoped>
.recaptcha-container {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  margin: 1rem 0;
}

.recaptcha-compact .g-recaptcha {
  transform: scale(0.85);
  transform-origin: left top;
}

.recaptcha-error {
  color: #dc2626;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

@media (max-width: 480px) {
  .recaptcha-container .g-recaptcha {
    transform: scale(0.9);
    transform-origin: left top;
  }
}
</style>
