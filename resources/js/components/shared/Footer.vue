<template>
  <footer class="dashboard-footer">
    <div class="footer-content">
      <div class="footer-right">
        <span class="copyright">© 2024 CAS Private Care LLC - All rights reserved</span>
        <span class="separator">•</span>
        <span class="version">v1.2.0</span>
      </div>
    </div>
  </footer>
</template>

<script setup>
// No props or logic needed for this minimalist footer
</script>

<style scoped>
.dashboard-footer {
  background: #ffffff;
  border-top: 1px solid #f0f0f0;
  padding: 16px 32px;
  margin-top: auto;
}

.footer-content {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  max-width: 100%;
}

.footer-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.separator {
  font-size: 0.875rem;
  color: #ccc;
  font-weight: 500;
}

.copyright {
  font-size: 0.875rem;
  color: #666;
  font-weight: 500;
}

.version {
  font-size: 0.75rem;
  color: #999;
  font-weight: 500;
  padding: 4px 8px;
  background: #f8f9fa;
  border-radius: 6px;
}
</style>