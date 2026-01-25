<template>
  <figure :class="containerClass">
    <picture v-if="showPicture">
      <!-- WebP format for modern browsers -->
      <source 
        v-if="webpSrcset"
        :srcset="webpSrcset" 
        :sizes="computedSizes"
        type="image/webp" 
      />
      
      <!-- AVIF format for cutting-edge browsers -->
      <source 
        v-if="avifSrcset"
        :srcset="avifSrcset" 
        :sizes="computedSizes"
        type="image/avif" 
      />
      
      <!-- Fallback format -->
      <source 
        v-if="srcset"
        :srcset="srcset" 
        :sizes="computedSizes"
        :type="mimeType" 
      />
      
      <img 
        ref="imageRef"
        :src="currentSrc"
        :alt="alt"
        :width="width"
        :height="height"
        :loading="loading"
        :decoding="decoding"
        :fetchpriority="fetchpriority"
        :class="imageClass"
        :style="imageStyle"
        @load="onLoad"
        @error="onError"
      />
    </picture>
    
    <img 
      v-else
      ref="imageRef"
      :src="currentSrc"
      :alt="alt"
      :width="width"
      :height="height"
      :loading="loading"
      :decoding="decoding"
      :fetchpriority="fetchpriority"
      :class="imageClass"
      :style="imageStyle"
      @load="onLoad"
      @error="onError"
    />
    
    <!-- Loading placeholder -->
    <div 
      v-if="showPlaceholder && !isLoaded && !hasError" 
      class="image-placeholder"
      :style="placeholderStyle"
    >
      <slot name="placeholder">
        <div class="placeholder-shimmer"></div>
      </slot>
    </div>
    
    <!-- Error fallback -->
    <div 
      v-if="hasError" 
      class="image-error"
      :style="errorStyle"
    >
      <slot name="error">
        <span class="error-text">{{ errorText }}</span>
      </slot>
    </div>
    
    <!-- Caption -->
    <figcaption v-if="caption" class="image-caption">
      {{ caption }}
    </figcaption>
  </figure>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
  // Source URL
  src: {
    type: String,
    required: true
  },
  // Alternative text
  alt: {
    type: String,
    required: true
  },
  // Srcset for responsive images
  srcset: {
    type: String,
    default: ''
  },
  // WebP srcset
  webpSrcset: {
    type: String,
    default: ''
  },
  // AVIF srcset
  avifSrcset: {
    type: String,
    default: ''
  },
  // Sizes attribute
  sizes: {
    type: String,
    default: ''
  },
  // Intrinsic width
  width: {
    type: [Number, String],
    default: null
  },
  // Intrinsic height
  height: {
    type: [Number, String],
    default: null
  },
  // Loading behavior
  loading: {
    type: String,
    default: 'lazy',
    validator: (v) => ['lazy', 'eager'].includes(v)
  },
  // Decoding hint
  decoding: {
    type: String,
    default: 'async',
    validator: (v) => ['sync', 'async', 'auto'].includes(v)
  },
  // Fetch priority
  fetchpriority: {
    type: String,
    default: 'auto',
    validator: (v) => ['high', 'low', 'auto'].includes(v)
  },
  // Object fit
  objectFit: {
    type: String,
    default: 'cover'
  },
  // Object position
  objectPosition: {
    type: String,
    default: 'center'
  },
  // Aspect ratio
  aspectRatio: {
    type: String,
    default: ''
  },
  // Border radius
  rounded: {
    type: [Boolean, String],
    default: false
  },
  // Show loading placeholder
  showPlaceholder: {
    type: Boolean,
    default: true
  },
  // Placeholder background
  placeholderBg: {
    type: String,
    default: '#f0f0f0'
  },
  // Error text
  errorText: {
    type: String,
    default: 'Image failed to load'
  },
  // Fallback image
  fallbackSrc: {
    type: String,
    default: ''
  },
  // Caption
  caption: {
    type: String,
    default: ''
  },
  // Container class
  containerClass: {
    type: String,
    default: 'responsive-image'
  },
  // Image class
  imageClass: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['load', 'error']);

const imageRef = ref(null);
const isLoaded = ref(false);
const hasError = ref(false);
const currentSrc = ref(props.src);

// Computed properties
const showPicture = computed(() => {
  return props.srcset || props.webpSrcset || props.avifSrcset;
});

const mimeType = computed(() => {
  const ext = props.src.split('.').pop()?.toLowerCase();
  const mimeTypes = {
    jpg: 'image/jpeg',
    jpeg: 'image/jpeg',
    png: 'image/png',
    gif: 'image/gif',
    svg: 'image/svg+xml',
    webp: 'image/webp',
    avif: 'image/avif'
  };
  return mimeTypes[ext] || 'image/jpeg';
});

const computedSizes = computed(() => {
  if (props.sizes) return props.sizes;
  if (props.width) return `${props.width}px`;
  return '100vw';
});

const imageStyle = computed(() => {
  const styles = {
    objectFit: props.objectFit,
    objectPosition: props.objectPosition
  };
  
  if (props.aspectRatio) {
    styles.aspectRatio = props.aspectRatio;
  }
  
  if (props.rounded === true) {
    styles.borderRadius = '0.5rem';
  } else if (typeof props.rounded === 'string') {
    styles.borderRadius = props.rounded;
  }
  
  return styles;
});

const placeholderStyle = computed(() => ({
  backgroundColor: props.placeholderBg,
  aspectRatio: props.aspectRatio || (props.width && props.height ? `${props.width}/${props.height}` : 'auto')
}));

const errorStyle = computed(() => ({
  aspectRatio: props.aspectRatio || (props.width && props.height ? `${props.width}/${props.height}` : 'auto')
}));

// Event handlers
const onLoad = () => {
  isLoaded.value = true;
  hasError.value = false;
  emit('load');
};

const onError = () => {
  if (props.fallbackSrc && currentSrc.value !== props.fallbackSrc) {
    currentSrc.value = props.fallbackSrc;
    return;
  }
  
  hasError.value = true;
  isLoaded.value = false;
  emit('error');
};

// Watch for src changes
watch(() => props.src, (newSrc) => {
  currentSrc.value = newSrc;
  isLoaded.value = false;
  hasError.value = false;
});

// Check if already loaded (cached)
onMounted(() => {
  if (imageRef.value?.complete && imageRef.value?.naturalHeight > 0) {
    isLoaded.value = true;
  }
});

// Generate srcset helper
const generateSrcset = (basePath, widths = [320, 640, 960, 1280, 1920]) => {
  const ext = basePath.split('.').pop();
  const base = basePath.slice(0, -(ext.length + 1));
  return widths.map(w => `${base}-${w}.${ext} ${w}w`).join(', ');
};

defineExpose({ generateSrcset });
</script>

<style scoped>
.responsive-image {
  position: relative;
  display: inline-block;
  margin: 0;
  overflow: hidden;
}

.responsive-image img {
  display: block;
  max-width: 100%;
  height: auto;
  transition: opacity 0.3s ease;
}

.responsive-image.loading img {
  opacity: 0;
}

.image-placeholder {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.placeholder-shimmer {
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    #f0f0f0 25%,
    #e0e0e0 50%,
    #f0f0f0 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.image-error {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f8f8;
  color: #666;
}

.error-text {
  font-size: 0.875rem;
  text-align: center;
  padding: 1rem;
}

.image-caption {
  margin-top: 0.5rem;
  font-size: 0.875rem;
  color: #666;
  text-align: center;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .image-caption {
    font-size: 0.75rem;
  }
}
</style>
