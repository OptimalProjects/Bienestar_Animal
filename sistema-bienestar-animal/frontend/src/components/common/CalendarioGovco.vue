<template>
  <div>
    <label :for="inputId" class="label-desplegable-govco">
      {{ label }}<span v-if="required" aria-required="true">*</span>
    </label>
    <div 
      :id="id" 
      class="desplegable-govco desplegable-calendar-govco" 
      :style="{ width: width, height: height }" 
      data-type="calendar"
    >
      <div class="date desplegable-selected-option">
        <input 
          class="browser-default" 
          type="text" 
          :id="inputId" 
          aria-autocomplete="off" 
          :days="viewDays"
          :min-day="minDay"
          :max-day="maxDay"
          :disabled="disabled"
          v-model="internalValue"
          @change="handleChange"
          @input="handleInput"
        >
      </div>
    </div>
    <span 
      v-if="alertText" 
      class="alert-desplegable-govco" 
      :id="alertId"
      :class="{ 'error-desplegable-govco': error }"
    >
      {{ alertText }}
    </span>
  </div>
</template>

<script>
export default {
  name: 'CalendarioGovco',
  
  props: {
    id: {
      type: String,
      required: true
    },
    inputId: {
      type: String,
      required: true
    },
    modelValue: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      default: 'Etiqueta'
    },
    viewDays: {
      type: String,
      default: 'true',
      validator: (value) => ['true', 'false'].includes(value)
    },
    minDay: {
      type: String,
      default: ''
    },
    maxDay: {
      type: String,
      default: ''
    },
    alertText: {
      type: String,
      default: ''
    },
    alertId: {
      type: String,
      default: 'alert-id'
    },
    required: {
      type: Boolean,
      default: false
    },
    disabled: {
      type: Boolean,
      default: false
    },
    error: {
      type: Boolean,
      default: false
    },
    width: {
      type: String,
      default: '280px'
    },
    height: {
      type: String,
      default: '40px'
    }
  },
  
  emits: ['update:modelValue', 'change'],
  
  data() {
    return {
      internalValue: this.modelValue
    };
  },
  
  watch: {
    modelValue(newVal) {
      this.internalValue = newVal;
    }
  },
  
  methods: {
    handleChange() {
      this.$emit('update:modelValue', this.internalValue);
      this.$emit('change', this.internalValue);
    },
    
    handleInput() {
      this.$emit('update:modelValue', this.internalValue);
    }
  },
  
  mounted() {
    this.$nextTick(() => {
      // Inicializar el calendario después de que el componente se monte
      if (window.initDropDownCalendar) {
        window.initDropDownCalendar();
      } else if (window.initDropDown) {
        window.initDropDown();
      }
    });
  }
};
</script>

<style scoped>
/* Estilos específicos del componente si necesitas */
</style>