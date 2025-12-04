<template>
  <button
    :type="type"
    class="btn-govco"
    :class="variantClass"
    :style="buttonStyle"
    :disabled="disabled"
    @click="handleClick"
  >
    <slot>{{ label }}</slot>
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  },
  variant: {
    type: String,
    default: 'fill',
    validator: (value) => ['fill', 'outline', 'fill-secondary', 'outline-secondary'].includes(value)
  },
  label: {
    type: String,
    default: 'Botón'
  },
  width: {
    type: String,
    default: '165px'
  },
  height: {
    type: String,
    default: '42px'
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['click']);

const variantClass = computed(() => {
  const variantMap = {
    'fill': 'fill-btn-govco',
    'outline': 'outline-btn-govco',
    'fill-secondary': 'fill-secundary-btn-govco',
    'outline-secondary': 'outline-secundary-btn-govco'
  };
  return variantMap[props.variant] || 'fill-btn-govco';
});

const buttonStyle = computed(() => ({
  width: props.width,
  height: props.height
}));

const handleClick = (event) => {
  if (!props.disabled) {
    emit('click', event);
  }
};
</script>

<style scoped>
/* Fuente iconografica */
@font-face {
  font-family: "govco-font";
  src: url("../assets/icons/fonts/govco-font-icons.ttf") format("truetype");
  font-weight: normal;
  font-style: normal;
}

/* WorkSans-Medium */
@font-face {
  font-family: 'WorkSans-Medium';
  src: url('../assets/fonts/Work_Sans/static/WorkSans-Medium.ttf');
}

/* WorkSans-Bold */
@font-face {
  font-family: 'WorkSans-Bold';
  src: url('../assets/fonts/Work_Sans/static/WorkSans-Bold.ttf');
}

html {
  font-size: 100%; /* 100% = 16px */
}

.btn-govco {
  border-radius: 1.563rem;
  font-family: WorkSans-Medium, sans-serif;
  font-size: 16px;
  line-height: 0.563rem;
  padding: 0.75rem 1rem;
  border-width: 0.125rem;
  border-style: solid;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-govco.fill-btn-govco:hover,
.btn-govco.fill-btn-govco:focus-visible,
.btn-govco.outline-btn-govco:hover,
.btn-govco.outline-btn-govco:focus-visible,
.btn-govco.fill-secundary-btn-govco:hover,
.btn-govco.fill-secundary-btn-govco:focus-visible,
.btn-govco.outline-secundary-btn-govco:hover,
.btn-govco.outline-secundary-btn-govco:focus-visible {
  background-color: #004884;
  border-color: #004884;
  color: #FFFFFF;
}

.btn-govco.fill-btn-govco:focus-visible,
.btn-govco.outline-btn-govco:focus-visible,
.btn-govco.fill-secundary-btn-govco:focus-visible,
.btn-govco.outline-secundary-btn-govco:focus-visible {
  outline: max(0.125rem, 0.125rem) solid #000000;
  outline-offset: max(0.125rem, 0.188rem);
}

.btn-govco.fill-btn-govco,
.btn-govco.outline-btn-govco {
  border-color: #3366cc;
}

.btn-govco.fill-btn-govco,
.btn-govco.outline-secundary-btn-govco {
  background-color: #3366CC;
  color: #FFFFFF;
}

.btn-govco.outline-btn-govco,
.btn-govco.fill-secundary-btn-govco,
.btn-govco.outline-secundary-btn-govco:hover,
.btn-govco.outline-secundary-btn-govco:focus-visible {
  background-color: #FFFFFF;
  color: #3366CC;
}

.btn-govco.fill-secundary-btn-govco:focus-visible,
.btn-govco.outline-secundary-btn-govco:focus-visible {
  outline-color: #FFFFFF;
}

.btn-govco:disabled {
  background-color: #737373;
  border-color: #737373;
  color: #FFFFFF;
  pointer-events: none;
  cursor: not-allowed;
}

.btn-govco.fill-secundary-btn-govco:hover,
.btn-govco.fill-secundary-btn-govco:focus-visible,
.btn-govco.fill-secundary-btn-govco:disabled,
.btn-govco.fill-secundary-btn-govco,
.btn-govco.outline-secundary-btn-govco:hover,
.btn-govco.outline-secundary-btn-govco:focus-visible,
.btn-govco.outline-secundary-btn-govco:disabled,
.btn-govco.outline-secundary-btn-govco {
  border-color: #FFFFFF;
}
</style>