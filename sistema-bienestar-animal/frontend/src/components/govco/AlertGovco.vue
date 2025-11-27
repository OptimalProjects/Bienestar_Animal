<!-- src/components/govco/AlertGovco.vue -->
<!-- Componente Alert reutilizable con estilos GOV.CO -->
<template>
  <div v-if="visible" :class="alertClasses" role="alert">
    <span v-if="showIcon" class="alert-icon" v-html="iconSvg"></span>
    <div class="alert-content">
      <strong v-if="title" class="alert-title">{{ title }}</strong>
      <slot>{{ message }}</slot>
    </div>
    <button
      v-if="dismissible"
      type="button"
      class="alert-close"
      @click="dismiss"
      aria-label="Cerrar alerta"
    >
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'info',
    validator: (value) => ['info', 'success', 'warning', 'danger'].includes(value)
  },
  title: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: ''
  },
  dismissible: {
    type: Boolean,
    default: false
  },
  showIcon: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['dismiss']);

const visible = ref(true);

const alertClasses = computed(() => {
  return [
    'alert-govco-component',
    `alert-govco-${props.variant}`
  ];
});

const iconSvg = computed(() => {
  const icons = {
    info: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="12" y1="16" x2="12" y2="12"></line>
      <line x1="12" y1="8" x2="12.01" y2="8"></line>
    </svg>`,
    success: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
      <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>`,
    warning: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
      <line x1="12" y1="9" x2="12" y2="13"></line>
      <line x1="12" y1="17" x2="12.01" y2="17"></line>
    </svg>`,
    danger: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="15" y1="9" x2="9" y2="15"></line>
      <line x1="9" y1="9" x2="15" y2="15"></line>
    </svg>`
  };
  return icons[props.variant];
});

function dismiss() {
  visible.value = false;
  emit('dismiss');
}
</script>

<style scoped>
.alert-govco-component {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px 20px;
  border-radius: 4px;
  border-left: 4px solid;
}

/* Variantes */
.alert-govco-info {
  background-color: #E8F0FE;
  border-color: #3366CC;
  color: #004884;
}

.alert-govco-success {
  background-color: #E8F5E9;
  border-color: #069169;
  color: #057a58;
}

.alert-govco-warning {
  background-color: #FFF8E1;
  border-color: #FFAB00;
  color: #856404;
}

.alert-govco-danger {
  background-color: #FFEBEE;
  border-color: #A80521;
  color: #8a041b;
}

/* Icon */
.alert-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Content */
.alert-content {
  flex: 1;
}

.alert-title {
  display: block;
  margin-bottom: 4px;
}

/* Close button */
.alert-close {
  flex-shrink: 0;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  opacity: 0.6;
  transition: opacity 0.2s;
  color: inherit;
}

.alert-close:hover {
  opacity: 1;
}
</style>
