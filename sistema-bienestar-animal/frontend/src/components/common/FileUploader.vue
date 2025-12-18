<template>
  <div class="file-uploader">
    <label :for="inputId" class="file-uploader-label">
      {{ label }}
      <span v-if="required" class="required-asterisk">*</span>
    </label>

    <div class="file-input-wrapper">
      <input
        :id="inputId"
        ref="fileInput"
        type="file"
        :accept="accept"
        :multiple="multiple"
        @change="onFilesSelected"
        class="hidden-input"
      />
      <button
        type="button"
        class="select-file-btn"
        @click="triggerInput"
      >
        Seleccionar archivo{{ multiple ? 's' : '' }}
      </button>
      
      <span v-if="localFiles.length === 0" class="no-file-text">
        Sin archivo seleccionado
      </span>
    </div>

    <p v-if="helpText" class="help-text">{{ helpText }}</p>

    <!-- Lista de archivos seleccionados -->
    <ul v-if="localFiles.length > 0" class="file-list">
      <li v-for="(file, index) in localFiles" :key="index" class="file-item">
        <span class="file-info">
          <span class="file-name">{{ file.name }}</span>
          <span class="file-size">{{ formatSize(file.size) }}</span>
        </span>
        <button
          type="button"
          class="remove-btn"
          @click="removeFile(index)"
          title="Quitar archivo"
        >
          ✕
        </button>
      </li>
    </ul>

    <!-- Mensaje de error -->
    <span v-if="errorMessage" class="error-message">{{ errorMessage }}</span>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  accept: {
    type: String,
    default: ''
  },
  maxFiles: {
    type: Number,
    default: 1
  },
  maxSizeMB: {
    type: Number,
    default: 5
  },
  label: {
    type: String,
    default: 'Adjuntar archivos'
  },
  helpText: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const localFiles = ref([...props.modelValue]);
const errorMessage = ref('');

const inputId = computed(
  () => `file-uploader-${Math.random().toString(36).slice(2)}`
);

watch(
  () => props.modelValue,
  (newVal) => {
    if (Array.isArray(newVal)) {
      localFiles.value = [...newVal];
    }
  }
);

function triggerInput() {
  errorMessage.value = '';
  fileInput.value?.click();
}

function onFilesSelected(event) {
  errorMessage.value = '';
  const files = Array.from(event.target.files || []);

  if (!files.length) return;

  // Validar tamaño máximo
  const maxBytes = props.maxSizeMB * 1024 * 1024;
  
  // Validar cada archivo
  for (const file of files) {
    if (file.size > maxBytes) {
      errorMessage.value = `El archivo "${file.name}" supera el tamaño máximo de ${props.maxSizeMB}MB.`;
      // Limpiar el input
      if (fileInput.value) {
        fileInput.value.value = '';
      }
      return;
    }
  }

  // Determinar archivos finales según modo múltiple o único
  let allFiles;
  
  if (props.multiple) {
    // Modo múltiple: agregar a los existentes
    allFiles = [...localFiles.value, ...files];
  } else {
    // Modo único: reemplazar
    allFiles = [files[0]];
  }

  // Validar número máximo de archivos
  if (allFiles.length > props.maxFiles) {
    errorMessage.value = `Solo puede adjuntar máximo ${props.maxFiles} archivo(s).`;
    // Limpiar el input
    if (fileInput.value) {
      fileInput.value.value = '';
    }
    return;
  }

  // Actualizar archivos
  localFiles.value = allFiles;
  emit('update:modelValue', localFiles.value);

  // Limpiar el input para permitir seleccionar el mismo archivo de nuevo
  if (fileInput.value) {
    fileInput.value.value = '';
  }
}

function removeFile(index) {
  errorMessage.value = '';
  localFiles.value.splice(index, 1);
  emit('update:modelValue', localFiles.value);
}

function formatSize(bytes) {
  if (bytes < 1024) {
    return `${bytes} B`;
  }
  if (bytes < 1024 * 1024) {
    return `${Math.round(bytes / 1024)} KB`;
  }
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
</script>

<style scoped>
.file-uploader {
  width: 100%;
}

.file-uploader-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 14px;
}

.required-asterisk {
  color: #b00020;
  margin-left: 4px;
}

.file-input-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.hidden-input {
  display: none;
}

.select-file-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: #069169;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.select-file-btn:hover {
  background: #057a5a;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(6, 145, 105, 0.3);
}

.select-file-btn:active {
  transform: translateY(0);
}

.btn-icon {
  font-size: 1.1rem;
}

.no-file-text {
  color: #737373;
  font-size: 0.9rem;
  font-style: italic;
}

.help-text {
  margin: 0.5rem 0 0 0;
  font-size: 0.85rem;
  color: #637381;
}

.file-list {
  list-style: none;
  padding: 0;
  margin: 0.75rem 0 0 0;
}

.file-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f5f7fb;
  border: 1px solid #e6e9ef;
  border-radius: 6px;
  margin-bottom: 0.5rem;
  transition: all 0.2s;
}

.file-item:hover {
  background: #eef2f7;
  border-color: #d0d6e4;
}

.file-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.file-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 0;
}

.file-name {
  font-size: 0.9rem;
  font-weight: 500;
  color: #344054;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-size {
  font-size: 0.8rem;
  color: #637381;
}

.remove-btn {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  border: 1px solid #d0d6e4;
  border-radius: 4px;
  color: #b00020;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.2s;
}

.remove-btn:hover {
  background: #ffebee;
  border-color: #b00020;
  transform: scale(1.1);
}

.error-message {
  display: block;
  color: #b00020;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  font-weight: 500;
  padding: 0.5rem;
  background: #ffebee;
  border-left: 3px solid #b00020;
  border-radius: 4px;
}

@media (max-width: 576px) {
  .file-input-wrapper {
    flex-direction: column;
    align-items: flex-start;
  }

  .select-file-btn {
    width: 100%;
    justify-content: center;
  }

  .no-file-text {
    width: 100%;
    text-align: center;
  }
}
</style>