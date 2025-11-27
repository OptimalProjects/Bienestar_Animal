<!-- src/components/govco/CardGovco.vue -->
<!-- Componente Card reutilizable con estilos GOV.CO -->
<template>
  <div :class="cardClasses">
    <!-- Header -->
    <div v-if="$slots.header || title" class="card-govco-header" :class="headerClass">
      <slot name="header">
        <h3 v-if="title" :class="titleClass">{{ title }}</h3>
        <p v-if="subtitle" class="card-subtitle">{{ subtitle }}</p>
      </slot>
      <div v-if="$slots.actions" class="card-actions">
        <slot name="actions"></slot>
      </div>
    </div>

    <!-- Body -->
    <div v-if="$slots.default" class="card-govco-body" :class="{ 'no-padding': noPadding }">
      <slot></slot>
    </div>

    <!-- Footer -->
    <div v-if="$slots.footer" class="card-govco-footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'primary', 'success', 'warning', 'danger', 'info'].includes(value)
  },
  elevated: {
    type: Boolean,
    default: false
  },
  bordered: {
    type: Boolean,
    default: false
  },
  noPadding: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  }
});

const cardClasses = computed(() => {
  return [
    'card-govco-component',
    `card-govco-${props.variant}`,
    {
      'card-govco-elevated': props.elevated,
      'card-govco-bordered': props.bordered,
      'card-govco-clickable': props.clickable
    }
  ];
});

const headerClass = computed(() => {
  return `card-header-${props.variant}`;
});

const titleClass = computed(() => {
  return 'card-title h5-tipografia-govco';
});
</script>

<style scoped>
.card-govco-component {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Variantes de borde */
.card-govco-primary {
  border-top: 4px solid #3366CC;
}

.card-govco-success {
  border-top: 4px solid #069169;
}

.card-govco-warning {
  border-top: 4px solid #FFAB00;
}

.card-govco-danger {
  border-top: 4px solid #A80521;
}

.card-govco-info {
  border-top: 4px solid #17A2B8;
}

/* Modificadores */
.card-govco-elevated {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.card-govco-bordered {
  border: 1px solid #e0e0e0;
  box-shadow: none;
}

.card-govco-clickable {
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.card-govco-clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

/* Header */
.card-govco-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e0e0e0;
}

.card-header-default {
  background-color: #f5f7fb;
}

.card-header-primary {
  background-color: #E8F0FE;
}

.card-header-success {
  background-color: #E8F5E9;
}

.card-header-warning {
  background-color: #FFF8E1;
}

.card-header-danger {
  background-color: #FFEBEE;
}

.card-header-info {
  background-color: #E3F2FD;
}

.card-title {
  margin: 0;
  color: #004884;
}

.card-subtitle {
  margin: 4px 0 0 0;
  font-size: 0.9rem;
  color: #666;
}

.card-actions {
  display: flex;
  gap: 8px;
}

/* Body */
.card-govco-body {
  padding: 20px;
}

.card-govco-body.no-padding {
  padding: 0;
}

/* Footer */
.card-govco-footer {
  padding: 16px 20px;
  background-color: #f5f7fb;
  border-top: 1px solid #e0e0e0;
}
</style>
