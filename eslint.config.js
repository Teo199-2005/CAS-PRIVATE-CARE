import pluginVue from 'eslint-plugin-vue'
import pluginVueA11y from 'eslint-plugin-vuejs-accessibility'

export default [
  // Vue recommended rules
  ...pluginVue.configs['flat/recommended'],
  
  // Vue accessibility rules
  ...pluginVueA11y.configs['flat/recommended'],
  
  {
    files: ['resources/js/**/*.vue', 'resources/js/**/*.js'],
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: 'module',
      globals: {
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        fetch: 'readonly',
        localStorage: 'readonly',
        sessionStorage: 'readonly',
        setTimeout: 'readonly',
        setInterval: 'readonly',
        clearTimeout: 'readonly',
        clearInterval: 'readonly',
        FormData: 'readonly',
        URLSearchParams: 'readonly',
        Blob: 'readonly',
        File: 'readonly',
        FileReader: 'readonly',
        URL: 'readonly',
        navigator: 'readonly',
        requestAnimationFrame: 'readonly',
        cancelAnimationFrame: 'readonly',
        IntersectionObserver: 'readonly',
        ResizeObserver: 'readonly',
        MutationObserver: 'readonly',
        CustomEvent: 'readonly',
        Event: 'readonly',
        HTMLElement: 'readonly',
        Element: 'readonly',
        Node: 'readonly',
        NodeList: 'readonly',
        DOMParser: 'readonly',
        XMLSerializer: 'readonly',
        AbortController: 'readonly',
        Headers: 'readonly',
        Request: 'readonly',
        Response: 'readonly',
        performance: 'readonly',
        PerformanceObserver: 'readonly',
        btoa: 'readonly',
        atob: 'readonly',
        crypto: 'readonly',
        Stripe: 'readonly',
      }
    },
    rules: {
      // ==========================================
      // Vue Rules - Relaxed for existing codebase
      // ==========================================
      'vue/html-indent': 'off', // Allow existing 4-space indentation
      'vue/max-attributes-per-line': 'off', // Allow inline attributes
      'vue/singleline-html-element-content-newline': 'off',
      'vue/multiline-html-element-content-newline': 'off',
      'vue/html-self-closing': 'off', // Allow self-closing HTML elements
      'vue/multi-word-component-names': 'off', // Allow single-word component names
      'vue/no-v-html': 'warn', // Warn about v-html (XSS risk)
      'vue/require-default-prop': 'off', // Don't require defaults for all props
      'vue/require-prop-types': 'warn',
      'vue/no-unused-vars': 'warn',
      'vue/no-mutating-props': 'error',
      'vue/no-side-effects-in-computed-properties': 'error',
      'vue/return-in-computed-property': 'error',
      
      // ==========================================
      // Accessibility Rules - Critical for WCAG 2.1
      // ==========================================
      
      // Form accessibility
      'vuejs-accessibility/form-control-has-label': 'error',
      'vuejs-accessibility/label-has-for': ['error', {
        required: {
          some: ['nesting', 'id']
        }
      }],
      
      // Interactive elements
      'vuejs-accessibility/click-events-have-key-events': 'warn',
      'vuejs-accessibility/mouse-events-have-key-events': 'warn',
      'vuejs-accessibility/interactive-supports-focus': 'warn',
      
      // ARIA
      'vuejs-accessibility/aria-props': 'error',
      'vuejs-accessibility/aria-role': 'error',
      'vuejs-accessibility/aria-unsupported-elements': 'error',
      
      // Media
      'vuejs-accessibility/alt-text': 'error',
      'vuejs-accessibility/media-has-caption': 'warn',
      
      // Anchors
      'vuejs-accessibility/anchor-has-content': 'error',
      'vuejs-accessibility/no-redundant-roles': 'warn',
      
      // Focus
      'vuejs-accessibility/no-autofocus': 'warn',
      'vuejs-accessibility/tabindex-no-positive': 'error',
      
      // Headings
      'vuejs-accessibility/heading-has-content': 'error',
      
      // Distracting elements
      'vuejs-accessibility/no-distracting-elements': 'error',
      
      // Access keys (can cause conflicts)
      'vuejs-accessibility/no-access-key': 'warn',
    }
  },
  
  // Ignore patterns
  {
    ignores: [
      'node_modules/**',
      'vendor/**',
      'public/build/**',
      'storage/**',
      'bootstrap/cache/**',
      '*.min.js',
    ]
  }
]
