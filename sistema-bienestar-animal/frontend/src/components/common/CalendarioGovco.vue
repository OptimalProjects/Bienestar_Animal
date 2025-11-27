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
          ref="inputElement"
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
          @blur="handleBlur"
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
      internalValue: this.modelValue,
      isInitialized: false,
      observers: []
    };
  },
  
  watch: {
    modelValue(newVal) {
      console.log(`[${this.inputId}] modelValue prop changed to:`, newVal);
      if (this.internalValue !== newVal) {
        this.internalValue = newVal;
        this.$nextTick(() => {
          this.syncFromInput();
        });
      }
    },
    
    internalValue(newVal, oldVal) {
      console.log(`[${this.inputId}] internalValue changed from "${oldVal}" to "${newVal}"`);
    }
  },
  
  methods: {
    handleChange(event) {
      const value = event.target.value;
      console.log(`[${this.inputId}] handleChange - value:`, value);
      this.updateValue(value);
    },
    
    handleInput(event) {
      const value = event.target.value;
      console.log(`[${this.inputId}] handleInput - value:`, value);
      this.updateValue(value);
    },
    
    handleBlur(event) {
      const value = event.target.value;
      console.log(`[${this.inputId}] handleBlur - value:`, value);
      this.updateValue(value);
    },
    
    updateValue(value) {
      console.log(`[${this.inputId}] updateValue called with:`, value);
      
      // Actualizar valor interno
      this.internalValue = value;
      
      // Emitir eventos
      this.$emit('update:modelValue', value);
      this.$emit('change', value);
    },
    
    syncFromInput() {
      // Leer el valor directamente del input nativo
      if (this.$refs.inputElement) {
        const value = this.$refs.inputElement.value;
        if (value && value !== this.internalValue) {
          console.log(`[${this.inputId}] syncFromInput - found value:`, value);
          this.updateValue(value);
        }
      }
    },
    
    setupMutationObserver() {
      // Observar cambios en el input para capturar actualizaciones de GOV.CO
      if (this.$refs.inputElement) {
        const observer = new MutationObserver((mutations) => {
          mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
              console.log(`[${this.inputId}] MutationObserver detected value change`);
              this.syncFromInput();
            }
          });
        });
        
        observer.observe(this.$refs.inputElement, {
          attributes: true,
          attributeFilter: ['value']
        });
        
        this.observers.push(observer);
      }
    },
    
    setupCalendarListener() {
      // Escuchar clics en las celdas del calendario de GOV.CO
      const calendar = document.querySelector(`#${this.id} .desplegable-calendar-control`);
      
      if (calendar) {
        const handleCalendarClick = (e) => {
          const dateCell = e.target.closest('td[data-day]');
          if (dateCell) {
            setTimeout(() => {
              console.log(`[${this.inputId}] Calendar cell clicked, syncing...`);
              this.syncFromInput();
            }, 150);
          }
        };
        
        calendar.addEventListener('click', handleCalendarClick);
        
        // Guardar para cleanup
        this.observers.push({
          disconnect: () => calendar.removeEventListener('click', handleCalendarClick)
        });
      }
    },
    
    setupPeriodicSync() {
      // Verificar periódicamente si el valor cambió
      const intervalId = setInterval(() => {
        if (this.$refs.inputElement) {
          const currentValue = this.$refs.inputElement.value;
          if (currentValue && currentValue !== '' && currentValue !== this.internalValue) {
            console.log(`[${this.inputId}] Periodic sync found different value:`, currentValue);
            this.updateValue(currentValue);
          }
        }
      }, 500);
      
      this.observers.push({
        disconnect: () => clearInterval(intervalId)
      });
    },
    
    // Método público para obtener el valor actual
    getValue() {
      if (this.$refs.inputElement) {
        const value = this.$refs.inputElement.value;
        console.log(`[${this.inputId}] getValue() returning:`, value);
        return value;
      }
      return this.internalValue;
    },
    
    initializeCalendar() {
      if (this.isInitialized) return;
      
      this.$nextTick(() => {
        // Inicializar el calendario GOV.CO
        if (window.initDropDownCalendar) {
          window.initDropDownCalendar();
          console.log(`[${this.inputId}] Calendar initialized via initDropDownCalendar`);
        } else if (window.initDropDown) {
          window.initDropDown();
          console.log(`[${this.inputId}] Calendar initialized via initDropDown`);
        } else if (window.GOVCo?.init) {
          const calendarElement = document.getElementById(this.id);
          if (calendarElement) {
            window.GOVCo.init(calendarElement.parentElement);
            console.log(`[${this.inputId}] Calendar initialized via GOVCo.init`);
          }
        }
        
        this.isInitialized = true;
        
        // Configurar listeners y observers
        setTimeout(() => {
          this.setupMutationObserver();
          this.setupCalendarListener();
          this.setupPeriodicSync();
          
          // Sincronizar valor inicial
          if (this.internalValue) {
            console.log(`[${this.inputId}] Setting initial value:`, this.internalValue);
          }
        }, 300);
      });
    }
  },
  
  mounted() {
    console.log(`[${this.inputId}] Component mounted with modelValue:`, this.modelValue);
    this.initializeCalendar();
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

.alert-desplegable-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.5rem;
}

.error-desplegable-govco {
  color: #b00020;
}

.date.desplegable-selected-option input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

.date.desplegable-selected-option input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}
</style>