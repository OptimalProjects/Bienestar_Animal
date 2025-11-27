<template>
  <div>
    <label :for="id" class="label-desplegable-govco">
      {{ label }}<span v-if="required" aria-required="true">*</span>
    </label>
    <div 
      class="desplegable-govco" 
      :style="{ width: width, height: height }" 
      :id="id" 
      data-type="basic"
    >
      <select 
        :aria-invalid="ariaInvalid" 
        :aria-describedby="ariaDescribedby"
        :disabled="disabled"
        v-model="internalValue"
        @change="handleChange"
      >
        <option disabled value="">{{ placeholder }}</option>
        <option 
          v-for="option in options" 
          :key="option.value" 
          :value="option.value"
        >
          {{ option.text }}
        </option>
      </select>
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
  name: 'DesplegableGovco',
  
  props: {
    id: {
      type: String,
      required: true
    },
    modelValue: {
      type: [String, Number],
      default: ''
    },
    label: {
      type: String,
      default: 'Etiqueta'
    },
    options: {
      type: Array,
      default: () => []
    },
    placeholder: {
      type: String,
      default: 'Escoger'
    },
    alertText: {
      type: String,
      default: ''
    },
    alertId: {
      type: String,
      default: 'alert-id'
    },
    ariaInvalid: {
      type: String,
      default: 'false'
    },
    ariaDescribedby: {
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
      default: '255px'
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
      this.$nextTick(() => {
        this.syncDropdownDisplay();
      });
    }
  },
  
  methods: {
    handleChange() {
      this.$emit('update:modelValue', this.internalValue);
      this.$emit('change', this.internalValue);
    },
    
    syncDropdownDisplay() {
      // Sincronizar el valor con el botón visual del dropdown
      const button = document.querySelector(`#${this.id} .desplegable-selected-option`);
      if (button && this.internalValue) {
        const selectedOption = this.options.find(opt => opt.value === this.internalValue);
        if (selectedOption) {
          button.innerHTML = selectedOption.text;
        }
      }
    }
  },
  
  mounted() {
    this.$nextTick(() => {
      if (window.createList) {
        window.createList(this.id);
      }
      
      // Sincronizar valor inicial si existe
      setTimeout(() => {
        this.syncDropdownDisplay();
      }, 100);
    });
  }
};
</script>

<style scoped>
/* Estilos específicos del componente si necesitas */
</style>