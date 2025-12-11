/**
 * Composable useToast
 * Proporciona acceso al sistema de notificaciones toast
 */

import { getCurrentInstance } from 'vue';

export function useToast() {
  const instance = getCurrentInstance();
  const toast = instance?.appContext.config.globalProperties.$toast;

  // Fallback si el toast no esta disponible
  if (!toast) {
    console.warn('Toast component not found. Using console fallback.');
    return {
      success: (title, message) => {
        console.log('%c SUCCESS ', 'background: #069169; color: white; padding: 2px 6px; border-radius: 3px;', title, message || '');
      },
      error: (title, message) => {
        console.error('%c ERROR ', 'background: #A80521; color: white; padding: 2px 6px; border-radius: 3px;', title, message || '');
      },
      warning: (title, message) => {
        console.warn('%c WARNING ', 'background: #FFAB00; color: black; padding: 2px 6px; border-radius: 3px;', title, message || '');
      },
      info: (title, message) => {
        console.info('%c INFO ', 'background: #3366CC; color: white; padding: 2px 6px; border-radius: 3px;', title, message || '');
      },
    };
  }

  return {
    success: (title, message, duration) => {
      toast.success(title, message, duration);
    },
    error: (title, message, duration) => {
      toast.error(title, message, duration);
    },
    warning: (title, message, duration) => {
      toast.warning(title, message, duration);
    },
    info: (title, message, duration) => {
      toast.info(title, message, duration);
    },
  };
}

export default useToast;
