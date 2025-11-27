<template>
  <div>
    <label :for="selectId" class="label-desplegable-govco">
      {{ label }}<span v-if="required" aria-required="true">*</span>
    </label>
    <div 
      class="desplegable-govco" 
      :style="{ width: width, height: height }" 
      :id="id" 
      data-type="basic"
    >
      <select 
        :id="selectId"
        :aria-invalid="ariaInvalid" 
        :aria-describedby="ariaDescribedby"
        :disabled="disabled"
        v-model="internalValue"
        @change="handleChange"
        @input="handleInput"
        ref="selectElement"
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
      internalValue: this.modelValue,
      isInitialized: false,
      observers: []
    };
  },
  
  computed: {
    selectId() {
      return `${this.id}-select`;
    }
  },
  
  watch: {
    modelValue(newVal) {
      console.log(`[${this.id}] modelValue prop changed to:`, newVal);
      if (this.internalValue !== newVal) {
        this.internalValue = newVal;
        this.$nextTick(() => {
          this.syncFromSelect();
          this.syncDropdownDisplay();
        });
      }
    },
    
    internalValue(newVal, oldVal) {
      console.log(`[${this.id}] internalValue changed from "${oldVal}" to "${newVal}"`);
    }
  },
  
  methods: {
    handleChange(event) {
      const value = event.target.value;
      console.log(`[${this.id}] handleChange - value:`, value);
      
      this.updateValue(value);
    },
    
    handleInput(event) {
      const value = event.target.value;
      console.log(`[${this.id}] handleInput - value:`, value);
      
      this.updateValue(value);
    },
    
    updateValue(value) {
      // Convertir a número si es necesario
      let processedValue = value;
      if (typeof this.modelValue === 'number' && value !== '') {
        processedValue = Number(value);
      }
      
      console.log(`[${this.id}] updateValue - processed:`, processedValue, typeof processedValue);
      
      // Actualizar valor interno
      this.internalValue = processedValue;
      
      // Emitir eventos
      this.$emit('update:modelValue', processedValue);
      this.$emit('change', processedValue);
      
      // Sincronizar display
      this.$nextTick(() => {
        this.syncDropdownDisplay();
      });
    },
    
    syncFromSelect() {
      // Leer el valor directamente del select nativo
      if (this.$refs.selectElement) {
        const value = this.$refs.selectElement.value;
        if (value && value !== this.internalValue) {
          console.log(`[${this.id}] syncFromSelect - found value:`, value);
          this.updateValue(value);
        }
      }
    },
    
    syncDropdownDisplay() {
      const button = document.querySelector(`#${this.id} .desplegable-selected-option`);
      
      if (button && this.internalValue !== '' && this.internalValue !== null) {
        const selectedOption = this.options.find(opt => {
          // Comparación flexible para números y strings
          return opt.value == this.internalValue;
        });
        
        if (selectedOption) {
          button.innerHTML = selectedOption.text;
          console.log(`[${this.id}] Display synced to:`, selectedOption.text);
        }
      }
    },
    
    setupMutationObserver() {
      // Observar cambios en el select para capturar actualizaciones de GOV.CO
      if (this.$refs.selectElement) {
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
              console.log(`[${this.id}] MutationObserver detected value change`);
              this.syncFromSelect();
            }
          });
        });
        
        observer.observe(this.$refs.selectElement, {
          attributes: true,
          attributeFilter: ['value']
        });
        
        this.observers.push(observer);
      }
    },
    
    setupClickListener() {
      // Escuchar clics en las opciones del dropdown de GOV.CO
      const dropdownItems = document.querySelector(`#${this.id} .desplegable-items`);
      
      if (dropdownItems) {
        const handleItemClick = (e) => {
          const item = e.target.closest('.desplegable-item');
          if (item) {
            setTimeout(() => {
              console.log(`[${this.id}] Dropdown item clicked, syncing...`);
              this.syncFromSelect();
            }, 100);
          }
        };
        
        dropdownItems.addEventListener('click', handleItemClick);
        
        // Guardar para cleanup
        this.observers.push({
          disconnect: () => dropdownItems.removeEventListener('click', handleItemClick)
        });
      }
    },
    
    setupPeriodicSync() {
      // Como último recurso, verificar periódicamente
      const intervalId = setInterval(() => {
        if (this.$refs.selectElement) {
          const currentValue = this.$refs.selectElement.value;
          if (currentValue && currentValue !== '' && currentValue != this.internalValue) {
            console.log(`[${this.id}] Periodic sync found different value:`, currentValue);
            this.updateValue(currentValue);
          }
        }
      }, 500);
      
      this.observers.push({
        disconnect: () => clearInterval(intervalId)
      });
    },
    
    initializeGovcoDropdown() {
      if (this.isInitialized) return;
      
      this.$nextTick(() => {
        // Inicializar GOV.CO
        if (window.createList) {
          window.createList(this.id);
        } else if (window.GOVCo?.init) {
          const dropdownElement = document.getElementById(this.id);
          if (dropdownElement) {
            window.GOVCo.init(dropdownElement.parentElement);
          }
        }
        
        this.isInitialized = true;
        
        // Configurar listeners y observers
        setTimeout(() => {
          this.setupMutationObserver();
          this.setupClickListener();
          this.setupPeriodicSync();
          
          // Sincronizar valor inicial
          if (this.internalValue) {
            this.syncDropdownDisplay();
          }
        }, 200);
      });
    },
    
    // Método público para obtener el valor actual
    getValue() {
      if (this.$refs.selectElement) {
        let value = this.$refs.selectElement.value;
        if (typeof this.modelValue === 'number' && value !== '') {
          value = Number(value);
        }
        console.log(`[${this.id}] getValue() returning:`, value, typeof value);
        return value;
      }
      return this.internalValue;
    }
  },
  
  mounted() {
    console.log(`[${this.id}] Component mounted with modelValue:`, this.modelValue);
    this.initializeGovcoDropdown();
  },
  
  beforeUnmount() {
    // Limpiar observers
    this.observers.forEach(observer => {
      if (observer && observer.disconnect) {
        observer.disconnect();
      }
    });
    this.observers = [];
  }
};
</script>

<style scoped>
.desplegable-govco {
  position: relative;
}

.desplegable-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23333' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 12px 8px;
  padding-right: 2.5rem;
}

.desplegable-govco select:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.alert-desplegable-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.error-desplegable-govco {
  color: #b00020;
}

.label-desplegable-govco {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.label-desplegable-govco span[aria-required="true"] {
  color: #d32f2f;
  margin-left: 0.25rem;
}
</style>