import { ref, computed } from 'vue';

/**
 * Composable para manejo de carga de archivos.
 * Provee validación, progreso, previews y estado de upload.
 *
 * @param {Object} options - Opciones de configuración
 * @param {number} options.maxSizeMB - Tamaño máximo por archivo en MB (default: 2)
 * @param {number} options.maxFiles - Número máximo de archivos (default: 1)
 * @param {string[]} options.allowedTypes - MIME types permitidos (default: todos)
 */
export function useFileUpload(options = {}) {
  const {
    maxSizeMB = 2,
    maxFiles = 1,
    allowedTypes = [],
  } = options;

  const files = ref([]);
  const previews = ref([]);
  const uploading = ref(false);
  const progress = ref(0);
  const error = ref('');

  const maxBytes = maxSizeMB * 1024 * 1024;

  const hasFiles = computed(() => files.value.length > 0);
  const fileCount = computed(() => files.value.length);

  /**
   * Valida un archivo contra las restricciones configuradas.
   * @param {File} file
   * @returns {string|null} Mensaje de error o null si es válido
   */
  function validateFile(file) {
    if (file.size > maxBytes) {
      return `"${file.name}" excede el tamaño máximo de ${maxSizeMB} MB.`;
    }

    if (allowedTypes.length > 0) {
      const isAllowed = allowedTypes.some((type) => {
        if (type.endsWith('/*')) {
          return file.type.startsWith(type.replace('/*', '/'));
        }
        return file.type === type;
      });
      if (!isAllowed) {
        return `"${file.name}" no es un tipo de archivo permitido.`;
      }
    }

    return null;
  }

  /**
   * Genera una URL de preview para un archivo de imagen.
   * @param {File} file
   * @returns {string|null} URL de preview o null si no es imagen
   */
  function generatePreview(file) {
    if (!file.type.startsWith('image/')) {
      return null;
    }
    return URL.createObjectURL(file);
  }

  /**
   * Agrega archivos validándolos primero.
   * @param {FileList|File[]} newFiles
   * @returns {boolean} true si se agregaron correctamente
   */
  function addFiles(newFiles) {
    error.value = '';
    const fileArray = Array.from(newFiles);

    // Validar cada archivo
    for (const file of fileArray) {
      const validationError = validateFile(file);
      if (validationError) {
        error.value = validationError;
        return false;
      }
    }

    // Validar cantidad total
    const total = files.value.length + fileArray.length;
    if (total > maxFiles) {
      error.value = `Solo puede adjuntar máximo ${maxFiles} archivo(s).`;
      return false;
    }

    // Agregar archivos y generar previews
    for (const file of fileArray) {
      files.value.push(file);
      previews.value.push(generatePreview(file));
    }

    return true;
  }

  /**
   * Reemplaza todos los archivos con uno nuevo (modo single).
   * @param {File} file
   * @returns {boolean}
   */
  function setFile(file) {
    clear();
    return addFiles([file]);
  }

  /**
   * Elimina un archivo por índice.
   * @param {number} index
   */
  function removeFile(index) {
    error.value = '';
    if (previews.value[index]) {
      URL.revokeObjectURL(previews.value[index]);
    }
    files.value.splice(index, 1);
    previews.value.splice(index, 1);
  }

  /**
   * Limpia todos los archivos y previews.
   */
  function clear() {
    previews.value.forEach((url) => {
      if (url) URL.revokeObjectURL(url);
    });
    files.value = [];
    previews.value = [];
    error.value = '';
    progress.value = 0;
  }

  /**
   * Agrega los archivos a un FormData existente.
   * @param {FormData} formData
   * @param {string} fieldName - Nombre del campo
   * @param {boolean} multiple - Si hay múltiples archivos, usar fieldName[]
   */
  function appendToFormData(formData, fieldName, multiple = false) {
    if (files.value.length === 0) return;

    if (multiple) {
      files.value.forEach((file) => {
        formData.append(`${fieldName}[]`, file);
      });
    } else {
      formData.append(fieldName, files.value[0]);
    }
  }

  /**
   * Marca estado de uploading con progreso.
   * @param {number} pct - Porcentaje de progreso (0-100)
   */
  function setProgress(pct) {
    progress.value = Math.min(100, Math.max(0, pct));
  }

  function startUpload() {
    uploading.value = true;
    progress.value = 0;
    error.value = '';
  }

  function finishUpload() {
    uploading.value = false;
    progress.value = 100;
  }

  function failUpload(msg) {
    uploading.value = false;
    error.value = msg || 'Error al subir los archivos.';
  }

  return {
    files,
    previews,
    uploading,
    progress,
    error,
    hasFiles,
    fileCount,
    addFiles,
    setFile,
    removeFile,
    clear,
    appendToFormData,
    setProgress,
    startUpload,
    finishUpload,
    failUpload,
    validateFile,
  };
}

export default useFileUpload;
