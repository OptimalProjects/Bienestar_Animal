<template>
  <div class="ui-field" :style="{ width }">
    <label :for="id" class="ui-label">
      {{ label }}<span v-if="required" class="ui-required">*</span>
    </label>

    <div class="ui-control ui-select" :class="{ 'has-error': error, 'is-disabled': disabled }">
      <select
        class="ui-select__native"
        :id="id"
        :disabled="disabled"
        :value="modelValue"
        @change="onChange"
      >
        <option disabled value="">{{ placeholder }}</option>
        <option v-for="opt in options" :key="opt.value" :value="opt.value">
          {{ opt.text }}
        </option>
      </select>

      <span class="ui-select__chevron" aria-hidden="true">
        <!-- Chevron down -->
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
          <path d="M7 10l5 5 5-5" stroke="#004884" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </span>
    </div>

    <p v-if="helpText" class="ui-help">{{ helpText }}</p>
    <p v-if="alertText" class="ui-alert" :class="{ 'is-error': error }">{{ alertText }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  id: { type: String, required: true },
  modelValue: { type: [String, Number], default: '' },

  label: { type: String, required: true },
  placeholder: { type: String, default: 'Seleccione una opción' },
  options: { type: Array, default: () => [] }, // [{ value, text }]

  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },

  width: { type: String, default: '100%' },

  helpText: { type: String, default: '' },
  alertText: { type: String, default: '' },
  error: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'change'])

function onChange(e) {
  const v = e.target.value
  emit('update:modelValue', v)
  emit('change', v)
}
</script>

<style scoped>
.ui-field {
  font-family: "Work Sans", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
}

.ui-label {
  display: inline-block;
  margin: 0 0 6px;
  font-size: 13px;
  font-weight: 600;
  color: #333;
}

.ui-required { color: #b00020; margin-left: 4px; }

.ui-control {
  position: relative;
  border: 1px solid #D0D0D0;
  border-radius: 6px;
  background: #FFFFFF;
  transition: box-shadow .15s ease, border-color .15s ease;
}

.ui-control:focus-within {
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, .22);
}

.ui-control.has-error {
  border-color: #d32f2f;
  box-shadow: 0 0 0 3px rgba(211, 47, 47, .14);
}

.ui-control.is-disabled { opacity: .7; }

.ui-select__native {
  width: 100%;
  height: 40px;
  border: none;
  outline: none;
  padding: 0 38px 0 12px;
  font-size: 14px;
  color: #333;
  background: transparent;
  appearance: none;
}

.ui-select__chevron {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
}

.ui-help {
  margin: 6px 0 0;
  font-size: 12px;
  color: #666;
}

.ui-alert {
  margin: 6px 0 0;
  font-size: 12px;
  color: #4B4B4B;
}
.ui-alert.is-error { color: #b00020; }
</style>
