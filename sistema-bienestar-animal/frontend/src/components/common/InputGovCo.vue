<template>
  <div class="ui-field" :style="{ width }">
    <label :for="id" class="ui-label">
      {{ label }}<span v-if="required" class="ui-required">*</span>
    </label>

    <div class="ui-control" :class="{ 'has-error': error }">
      <input
        class="ui-input"
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :step="step"
        :min="min"
        :max="max"
        @input="onInput"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
    </div>

    <p v-if="helpText" class="ui-help">{{ helpText }}</p>
    <p v-if="alertText" class="ui-alert" :class="{ 'is-error': error }">{{ alertText }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  id: { type: String, required: true },
  label: { type: String, required: true },

  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },

  step: { type: [String, Number], default: undefined },
  min: { type: [String, Number], default: undefined },
  max: { type: [String, Number], default: undefined },

  width: { type: String, default: '100%' },

  helpText: { type: String, default: '' },
  alertText: { type: String, default: '' },
  error: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus'])

function onInput(e) {
  let v = e.target.value
  if (props.type === 'number' && v !== '') v = Number(v)
  emit('update:modelValue', v)
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

.ui-input {
  width: 100%;
  height: 40px;
  border: none;
  outline: none;
  padding: 0 12px;
  font-size: 14px;
  color: #333;
  background: transparent;
}

.ui-input::placeholder { color: #737373; }

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
