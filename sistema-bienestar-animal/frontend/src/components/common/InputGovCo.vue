<template>
  <div 
    class="entradas-de-texto-govco" 
    :class="{ 'disabled-govco': disabled, 'error-govco': error, 'success-govco': success }"
  >
    <label :for="id">
      {{ label }}<span v-if="required" aria-required="true">*</span>
    </label>
    
    <div class="container-input-texto-govco">
      <input
        :type="type"
        :id="id"
        :value="modelValue"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        :placeholder="placeholder"
        :disabled="disabled"
        :aria-describedby="helpText || alertText ? `${id}-help` : undefined"
        :aria-invalid="error ? 'true' : 'false'"
        :class="{ 'error': error, 'success': success }"
        :step="step"
        :min="min"
        :max="max"
        ref="inputRef"
      />
    </div>

    <span 
      v-if="alertText && error" 
      :id="`${id}-help`" 
      class="alert-entradas-de-texto-govco"
    >
      {{ alertText }}
    </span>

    <span 
      v-if="helpText && !error" 
      :id="`${id}-help`" 
      class="info-entradas-de-texto-govco"
    >
      {{ helpText }}
    </span>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) => ['text', 'number', 'email', 'tel', 'password', 'url'].includes(value)
  },
  placeholder: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  error: {
    type: Boolean,
    default: false
  },
  success: {
    type: Boolean,
    default: false
  },
  alertText: {
    type: String,
    default: ''
  },
  helpText: {
    type: String,
    default: ''
  },
  step: {
    type: String,
    default: undefined
  },
  min: {
    type: [String, Number],
    default: undefined
  },
  max: {
    type: [String, Number],
    default: undefined
  }
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const inputRef = ref(null);

function handleInput(event) {
  let value = event.target.value;
  
  // Para inputs de tipo number, convertir a número si es necesario
  if (props.type === 'number' && value !== '') {
    value = parseFloat(value);
  }
  
  emit('update:modelValue', value);
}

function handleBlur(event) {
  emit('blur', event.target.value);
}

function handleFocus(event) {
  emit('focus', event.target.value);
}

// Función para obtener el valor actual del input
function getValue() {
  return inputRef.value?.value || props.modelValue;
}

// Exponer la función getValue para que los padres puedan acceder
defineExpose({
  getValue,
  inputRef
});

// Asegurar que el input siempre esté disponible y no bloqueado
onMounted(() => {
  nextTick(() => {
    if (inputRef.value) {
      inputRef.value.disabled = props.disabled;
    }
  });
});

// Vigilar cambios en la prop disabled
watch(() => props.disabled, (newValue) => {
  if (inputRef.value) {
    inputRef.value.disabled = newValue;
  }
});

// Vigilar cambios en modelValue para mantener sincronizado
watch(() => props.modelValue, (newValue) => {
  if (inputRef.value && inputRef.value.value !== String(newValue)) {
    inputRef.value.value = newValue;
  }
});
</script>

<style scoped>
.entradas-de-texto-govco {
  padding: 1rem 0;
  font-size: 16px;
  font-family: WorkSans-Regular, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  width: 100%;
}

.entradas-de-texto-govco label {
  color: #4B4B4B;
  display: block;
  font-size: 16px;
  margin-bottom: 0.625rem;
  line-height: 1.375rem;
  font-weight: 500;
}

.entradas-de-texto-govco label span[aria-required="true"] {
  color: #d32f2f;
  margin-left: 0.25rem;
}

.container-input-texto-govco {
  position: relative;
  width: 100%;
}

.entradas-de-texto-govco input {
  outline: none;
  background-color: #FFFFFF;
  border: 0.094rem solid #737373;
  border-radius: 0.313rem;
  width: 100%;
  padding: 0.5rem;
  color: #4B4B4B;
  font-size: 16px;
  margin-bottom: 0.5rem;
  display: inline-block;
  height: auto;
  box-sizing: border-box;
  line-height: 1.375rem;
  font-family: inherit;
}

.entradas-de-texto-govco input::placeholder {
  color: #737373;
}

.entradas-de-texto-govco input:not(.success):not(.error):focus {
  box-shadow: 0 0.25rem 0 rgba(51, 102, 204, 0.14);
  border: 0.094rem solid #3366CC;
}

.entradas-de-texto-govco input:disabled {
  background-color: #F2F2F2;
  border: 0.094rem solid #BABABA;
  cursor: not-allowed;
}

.entradas-de-texto-govco input:disabled::placeholder,
.entradas-de-texto-govco.disabled-govco label,
.entradas-de-texto-govco.disabled-govco span {
  color: #BABABA;
}

.entradas-de-texto-govco input.error {
  border-color: #A80521;
  background-color: #FDEAED;
}

.entradas-de-texto-govco input.error:focus {
  box-shadow: 0 0.25rem 0 rgba(168, 5, 33, 0.14);
  border-color: #A80521;
}

.entradas-de-texto-govco input.success {
  border-color: #069169;
  background-color: #E5F4F0;
}

.entradas-de-texto-govco input.success:focus {
  box-shadow: 0 0.25rem 0 rgba(6, 145, 105, 0.14);
  border-color: #069169;
}

.alert-entradas-de-texto-govco {
  display: block;
  color: #A80521;
  font-size: 0.875rem;
  margin-top: -0.25rem;
  margin-bottom: 0.5rem;
  line-height: 1.25rem;
}

.info-entradas-de-texto-govco {
  display: block;
  color: #737373;
  font-size: 0.875rem;
  margin-top: -0.25rem;
  margin-bottom: 0.5rem;
  line-height: 1.25rem;
}

/* Asegurar que el input siempre sea clickeable */
.entradas-de-texto-govco input:not(:disabled) {
  pointer-events: auto !important;
  cursor: text !important;
}
</style>