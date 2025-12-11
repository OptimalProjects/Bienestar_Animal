/**
 * Composable useConfirm
 * Proporciona acceso al dialogo de confirmacion
 */

import { getCurrentInstance } from 'vue';

export function useConfirm() {
  const instance = getCurrentInstance();
  const confirm = instance?.appContext.config.globalProperties.$confirm;

  // Fallback si el confirm no esta disponible
  if (!confirm) {
    console.warn('ConfirmDialog component not found. Using window.confirm fallback.');
    return async (options) => {
      const message = options.message || options.title || '¿Esta seguro?';
      return window.confirm(message);
    };
  }

  return (options) => confirm.show(options);
}

export default useConfirm;
