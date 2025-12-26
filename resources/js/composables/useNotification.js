import { ref } from 'vue';

const notification = ref({
  show: false,
  type: 'success',
  title: '',
  message: '',
  timeout: 4000
});

export function useNotification() {
  const showNotification = ({ type = 'success', title = '', message, timeout = 4000 }) => {
    notification.value = {
      show: true,
      type,
      title,
      message,
      timeout
    };
  };

  const success = (message, title = 'Success') => {
    showNotification({ type: 'success', title, message });
  };

  const error = (message, title = 'Error') => {
    showNotification({ type: 'error', title, message });
  };

  const warning = (message, title = 'Warning') => {
    showNotification({ type: 'warning', title, message });
  };

  const info = (message, title = 'Info') => {
    showNotification({ type: 'info', title, message });
  };

  return {
    notification,
    showNotification,
    success,
    error,
    warning,
    info
  };
}
