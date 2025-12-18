<template>
  <button
    :type="type"
    class="ui-btn"
    :class="[variantClass, sizeClass, { 'is-block': block }]"
    :style="{ width: block ? '100%' : width }"
    :disabled="disabled || loading"
    @click="onClick"
  >
    <span v-if="loading" class="spinner" aria-hidden="true"></span>
    <slot>{{ label }}</slot>
  </button>

  <p v-if="helpText" class="ui-help">{{ helpText }}</p>
  <p v-if="alertText" class="ui-alert" :class="{ 'is-error': error }">{{ alertText }}</p>
</template>

<script setup>
const props = defineProps({
  label: { type: String, default: '' },
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' }, // primary | secondary | ghost | danger
  size: { type: String, default: 'md' }, // sm | md | lg
  disabled: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  block: { type: Boolean, default: false },
  width: { type: String, default: 'auto' },

  // Mensajería (opcional)
  helpText: { type: String, default: '' },
  alertText: { type: String, default: '' },
  error: { type: Boolean, default: false },
})

const emit = defineEmits(['click'])

const variantClass = (() => `ui-btn--${props.variant}`)()
const sizeClass = (() => `ui-btn--${props.size}`)()

function onClick(e) {
  if (props.disabled || props.loading) return
  emit('click', e)
}
</script>

<style scoped>
.ui-btn {
  font-family: "Work Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  border-radius: 6px;
  border: 1px solid transparent;
  padding: 10px 14px;
  font-weight: 600;
  line-height: 1;
  cursor: pointer;
  transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease, transform .02s ease;
  user-select: none;
}

.ui-btn:active { transform: translateY(1px); }
.ui-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }

.ui-btn:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, .25);
}

/* Variants (estilo similar GOV) */
.ui-btn--primary { background: #3366CC; color: #FFFFFF; border-color: #3366CC; }
.ui-btn--primary:hover:not(:disabled) { background: #2d5bb8; border-color: #2d5bb8; }

.ui-btn--secondary { background: #FFFFFF; color: #004884; border-color: #004884; }
.ui-btn--secondary:hover:not(:disabled) { background: #f5f7ff; }

.ui-btn--ghost { background: transparent; color: #004884; border-color: transparent; }
.ui-btn--ghost:hover:not(:disabled) { background: #f5f5f5; }

.ui-btn--danger { background: #d32f2f; color: #fff; border-color: #d32f2f; }
.ui-btn--danger:hover:not(:disabled) { background: #b92323; border-color: #b92323; }

.ui-btn--sm { padding: 8px 12px; font-size: 13px; }
.ui-btn--md { font-size: 14px; }
.ui-btn--lg { padding: 12px 16px; font-size: 15px; }

.is-block { display: inline-flex; justify-content: center; align-items: center; }

.spinner {
  width: 14px;
  height: 14px;
  margin-right: 8px;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,.55);
  border-top-color: rgba(255,255,255,1);
  animation: spin .9s linear infinite;
}
.ui-btn--secondary .spinner,
.ui-btn--ghost .spinner {
  border-color: rgba(0,72,132,.25);
  border-top-color: rgba(0,72,132,.9);
}

@keyframes spin { to { transform: rotate(360deg); } }

.ui-help {
  margin: 6px 0 0;
  font-size: 12px;
  color: #666;
  font-family: "Work Sans", system-ui, sans-serif;
}
.ui-alert {
  margin: 6px 0 0;
  font-size: 12px;
  color: #4B4B4B;
  font-family: "Work Sans", system-ui, sans-serif;
}
.ui-alert.is-error { color: #b00020; }
</style>
