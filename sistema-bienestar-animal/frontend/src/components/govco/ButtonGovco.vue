<!-- src/components/govco/ButtonGovco.vue -->
<!-- Componente de botón reutilizable con estilos GOV.CO -->
<template>
  <component
    :is="tag"
    :type="tag === 'button' ? type : undefined"
    :to="to"
    :href="href"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
  >
    <span v-if="loading" class="btn-spinner"></span>
    <span v-if="icon && iconPosition === 'left'" class="btn-icon" v-html="icon"></span>
    <slot>{{ label }}</slot>
    <span v-if="icon && iconPosition === 'right'" class="btn-icon" v-html="icon"></span>
  </component>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'danger', 'warning', 'outline', 'ghost'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  type: {
    type: String,
    default: 'button'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  block: {
    type: Boolean,
    default: false
  },
  icon: {
    type: String,
    default: ''
  },
  iconPosition: {
    type: String,
    default: 'left',
    validator: (value) => ['left', 'right'].includes(value)
  },
  to: {
    type: [String, Object],
    default: null
  },
  href: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['click']);

// Determinar el tipo de elemento
const tag = computed(() => {
  if (props.to) return 'router-link';
  if (props.href) return 'a';
  return 'button';
});

// Clases del botón
const buttonClasses = computed(() => {
  return [
    'btn-govco',
    `btn-govco-${props.variant}`,
    `btn-govco-${props.size}`,
    {
      'btn-govco-block': props.block,
      'btn-govco-loading': props.loading,
      'btn-govco-disabled': props.disabled
    }
  ];
});

function handleClick(event) {
  if (!props.disabled && !props.loading) {
    emit('click', event);
  }
}
</script>

<style scoped>
.btn-govco {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-family: 'Work Sans', sans-serif;
  font-weight: 600;
  text-decoration: none;
  border: 2px solid transparent;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

/* Tamaños */
.btn-govco-sm {
  padding: 6px 16px;
  font-size: 0.85rem;
}

.btn-govco-md {
  padding: 10px 24px;
  font-size: 1rem;
}

.btn-govco-lg {
  padding: 14px 32px;
  font-size: 1.1rem;
}

/* Variantes */
.btn-govco-primary {
  background-color: #3366CC;
  border-color: #3366CC;
  color: white;
}

.btn-govco-primary:hover:not(:disabled) {
  background-color: #004884;
  border-color: #004884;
}

.btn-govco-secondary {
  background-color: transparent;
  border-color: #3366CC;
  color: #3366CC;
}

.btn-govco-secondary:hover:not(:disabled) {
  background-color: #3366CC;
  color: white;
}

.btn-govco-success {
  background-color: #069169;
  border-color: #069169;
  color: white;
}

.btn-govco-success:hover:not(:disabled) {
  background-color: #057a58;
  border-color: #057a58;
}

.btn-govco-danger {
  background-color: #A80521;
  border-color: #A80521;
  color: white;
}

.btn-govco-danger:hover:not(:disabled) {
  background-color: #8a041b;
  border-color: #8a041b;
}

.btn-govco-warning {
  background-color: #FFAB00;
  border-color: #FFAB00;
  color: #333;
}

.btn-govco-warning:hover:not(:disabled) {
  background-color: #E09900;
  border-color: #E09900;
}

.btn-govco-outline {
  background-color: transparent;
  border-color: #D0D0D0;
  color: #4B4B4B;
}

.btn-govco-outline:hover:not(:disabled) {
  background-color: #f5f5f5;
  border-color: #3366CC;
  color: #3366CC;
}

.btn-govco-ghost {
  background-color: transparent;
  border-color: transparent;
  color: #3366CC;
}

.btn-govco-ghost:hover:not(:disabled) {
  background-color: #E8F0FE;
}

/* Estados */
.btn-govco:disabled,
.btn-govco-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-govco-block {
  display: flex;
  width: 100%;
}

/* Loading */
.btn-govco-loading {
  cursor: wait;
}

.btn-spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid currentColor;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Icon */
.btn-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon :deep(svg) {
  width: 1em;
  height: 1em;
}

/* Focus */
.btn-govco:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.25);
}

.btn-govco:focus:not(:focus-visible) {
  box-shadow: none;
}

.btn-govco:focus-visible {
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.25);
}
</style>
