<template>
  <div class="file-uploader entradas-de-texto-govco">
    <label :for="inputId">
      {{ label }}
      <span v-if="required" aria-required="true">*</span>
    </label>

    <div class="file-input-wrapper">
      <input
        :id="inputId"
        ref="fileInput"
        type="file"
        :accept="accept"
        :multiple="multiple"
        @change="onFilesSelected"
      />
      <button
        type="button"
        class="govco-btn-small govco-bg-elf-green"
        @click="triggerInput"
      >
        Seleccionar archivos
      </button>
    </div>

    <p v-if="helpText" class="info-entradas-de-texto-govco">{{ helpText }}</p>

    <ul v-if="localFiles.length" class="file-list">
      <li v-for="(file, index) in localFiles" :key="index">
        {{ file.name }} ({{ formatSize(file.size) }})
        <button
          type="button"
          class="link-remove"
          @click="removeFile(index)"
        >
          Quitar
        </button>
      </li>
    </ul>

    <span v-if="error" class="alert-desplegable-govco">{{ error }}</span>
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
  }
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const localFiles = ref([...props.modelValue]);
const error = ref('');

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
  fileInput.value?.click();
}

function onFilesSelected(event) {
  error.value = '';
  const files = Array.from(event.target.files || []);

  if (!files.length) return;

  const maxBytes = props.maxSizeMB * 1024 * 1024;
  const allFiles = props.multiple
    ? [...localFiles.value, ...files]
    : files.slice(0, props.maxFiles);

  if (allFiles.length > props.maxFiles) {
    error.value = `Solo puede adjuntar máximo ${props.maxFiles} archivo(s).`;
    return;
  }

  const oversized = allFiles.find(f => f.size > maxBytes);
  if (oversized) {
    error.value = `El archivo "${oversized.name}" supera el tamaño máximo de ${props.maxSizeMB}MB.`;
    return;
  }

  localFiles.value = allFiles;
  emit('update:modelValue', localFiles.value);
}

function removeFile(index) {
  localFiles.value.splice(index, 1);
  emit('update:modelValue', localFiles.value);
}

function formatSize(bytes) {
  if (bytes < 1024 * 1024) {
    return `${Math.round(bytes / 1024)} KB`;
  }
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
</script>

<style scoped>
.file-input-wrapper {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 0.5rem;
}

.file-input-wrapper input[type="file"] {
  display: none;
}

.file-list {
  margin-top: 0.75rem;
  padding-left: 1.25rem;
  font-size: 0.9rem;
}

.file-list li {
  margin-bottom: 0.25rem;
}

.link-remove {
  background: none;
  border: none;
  color: #b00020;
  cursor: pointer;
  font-size: 0.85rem;
  margin-left: 0.5rem;
}

.govco-btn-small {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  color: #fff;
  font-weight: 600;
}

.govco-bg-elf-green {
  background-color: #069169;
}
</style>
