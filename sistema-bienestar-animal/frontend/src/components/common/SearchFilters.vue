<!-- SearchFilters.vue - COMPONENTE CORREGIDO -->
<template>
  <div class="form-section">
    <h3 class="h5-tipografia-govco section-title">Criterios de búsqueda</h3>
    
    <div class="form-grid">
      <div class="entradas-de-texto-govco">
        <label for="searchMicrochip">Microchip</label>
        <input type="text" id="searchMicrochip" v-model="localFilters.microchip" placeholder="MC123456789" />
      </div>

      <div class="input-like-govco">
        <label for="searchSpecies" class="label-desplegable-govco">Especie</label>
        <div class="desplegable-govco" data-type="basic" id="species-search-dropdown">
          <select id="searchSpecies" v-model="localFilters.species" @change="onDropdownChange">
            <option value="">Todas</option>
            <option value="perro">Perro</option>
            <option value="gato">Gato</option>
            <option value="otro">Otro</option>
          </select>
        </div>
      </div>

      <div class="entradas-de-texto-govco">
        <label for="searchBreed">Raza</label>
        <input type="text" id="searchBreed" v-model="localFilters.breed" placeholder="Criollo, Mestizo" />
      </div>

      <div class="entradas-de-texto-govco">
        <label for="searchColor">Color</label>
        <input type="text" id="searchColor" v-model="localFilters.color" placeholder="Café, Blanco" />
      </div>

      <div class="input-like-govco">
        <label for="searchSex" class="label-desplegable-govco">Sexo</label>
        <div class="desplegable-govco" data-type="basic" id="sex-search-dropdown">
          <select id="searchSex" v-model="localFilters.sex" @change="onDropdownChange">
            <option value="">Todos</option>
            <option value="macho">Macho</option>
            <option value="hembra">Hembra</option>
            <option value="indeterminado">Indeterminado</option>
          </select>
        </div>
      </div>

      <div class="input-like-govco">
        <label for="searchStatus" class="label-desplegable-govco">Estado actual</label>
        <div class="desplegable-govco" data-type="basic" id="status-search-dropdown">
          <select id="searchStatus" v-model="localFilters.status" @change="onDropdownChange">
            <option value="">Todos</option>
            <option value="en_calle">En calle</option>
            <option value="refugio">En refugio</option>
            <option value="adoptado">Adoptado</option>
            <option value="fallecido">Fallecido</option>
          </select>
        </div>
      </div>

      <div class="entradas-de-texto-govco full-width">
        <label for="searchLocation">Ubicación aproximada</label>
        <input type="text" id="searchLocation" v-model="localFilters.location" placeholder="Ej: Comuna 15, Barrio El Refugio" />
        <span class="info-entradas-de-texto-govco">Ingrese zona, barrio o comuna</span>
      </div>

      <div class="input-like-govco">
        <div class="date-field-container neut-date-container">
          <label for="searchDateFrom" class="label-desplegable-govco">Fecha rescate desde</label>
          <div class="desplegable-govco desplegable-calendar-govco" data-type="calendar">
            <div class="date desplegable-selected-option">
              <input 
                class="browser-default" 
                type="text" 
                id="searchDateFrom" 
                v-model="localFilters.dateFrom" 
                @change="onDateChange"
                @blur="onDateChange"
                aria-autocomplete="off" 
                days="true" 
                placeholder="DD/MM/AAAA" 
              />
            </div>
          </div>
        </div>
      </div>

      <div class="input-like-govco">
        <div class="date-field-container neut-date-container">
          <label for="searchDateTo" class="label-desplegable-govco">Fecha rescate hasta</label>
          <div class="desplegable-govco desplegable-calendar-govco" data-type="calendar">
            <div class="date desplegable-selected-option">
              <input 
                class="browser-default" 
                type="text" 
                id="searchDateTo" 
                v-model="localFilters.dateTo" 
                @change="onDateChange"
                @blur="onDateChange"
                placeholder="DD/MM/AAAA" 
              />
            </div>
          </div>
        </div>
      </div>

      <div class="checkbox-govco">
        <input type="checkbox" id="searchNeutered" v-model="localFilters.onlyNeutered" />
        <label for="searchNeutered">Solo animales esterilizados</label>
      </div>
    </div>

    <div class="form-actions">
      <button type="button" @click="$emit('clear')" class="govco-btn govco-bg-concrete">Limpiar filtros</button>
      <button type="button" @click="handleSearch" class="govco-btn govco-bg-elf-green">Buscar</button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, nextTick } from 'vue';
import CalendarioGovco from '../common/CalendarioGovco.vue'; 

const props = defineProps({
  modelValue: Object
});

const emit = defineEmits(['update:modelValue', 'search', 'clear']);

const localFilters = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
});

onMounted(() => {
  initializeGovcoComponents();
  setupDropdownListeners();
});

function initializeGovcoComponents() {
  if (typeof window === 'undefined' || !window.GOVCo) return;
  
  nextTick(() => {
    // Inicializar dropdowns básicos
    const dropdowns = document.querySelectorAll('.desplegable-govco[data-type="basic"]');
    dropdowns.forEach(dropdown => {
      if (window.GOVCo?.init) {
        window.GOVCo.init(dropdown.parentElement);
      }
    });

    // Inicializar calendarios
    const calendars = document.querySelectorAll('[data-type="calendar"]');
    calendars.forEach(cal => {
      if (window.GOVCo?.init) {
        window.GOVCo.init(cal.parentElement);
      }
    });
    
    // Sincronizar valores después de inicializar
    setTimeout(() => {
      syncDropdownValues();
    }, 200);
  });
}

function setupDropdownListeners() {
  // Escuchar cambios en los selects nativos
  const selects = ['searchSpecies', 'searchSex', 'searchStatus'];
  
  selects.forEach(id => {
    const select = document.getElementById(id);
    if (select) {
      select.addEventListener('change', (e) => {
        const field = id.replace('search', '').toLowerCase();
        const fieldMap = {
          'species': 'species',
          'sex': 'sex',
          'status': 'status'
        };
        
        if (fieldMap[field]) {
          localFilters.value[fieldMap[field]] = e.target.value;
        }
      });
    }
  });
  
  // Escuchar cambios en los inputs de fecha
  const dateInputs = ['searchDateFrom', 'searchDateTo'];
  dateInputs.forEach(id => {
    const input = document.getElementById(id);
    if (input) {
      // Escuchar múltiples eventos para capturar cambios del calendario GOV.CO
      ['change', 'blur', 'input'].forEach(eventType => {
        input.addEventListener(eventType, (e) => {
          syncDateValues();
        });
      });
    }
  });
  
  // También escuchar eventos personalizados de GOV.CO si existen
  document.addEventListener('govco:dropdown:change', (e) => {
    syncDropdownValues();
  });
  
  // Escuchar eventos del calendario GOV.CO
  document.addEventListener('govco:calendar:change', (e) => {
    syncDateValues();
  });
}

function syncDateValues() {
  // Sincronizar valores de las fechas con el modelo
  const dateFrom = document.getElementById('searchDateFrom');
  const dateTo = document.getElementById('searchDateTo');
  
  if (dateFrom) localFilters.value.dateFrom = dateFrom.value;
  if (dateTo) localFilters.value.dateTo = dateTo.value;
  
  console.log('Fechas sincronizadas:', {
    dateFrom: localFilters.value.dateFrom,
    dateTo: localFilters.value.dateTo
  });
}

function syncDropdownValues() {
  // Sincronizar valores de los dropdowns con el modelo
  const species = document.getElementById('searchSpecies');
  const sex = document.getElementById('searchSex');
  const status = document.getElementById('searchStatus');
  
  if (species) localFilters.value.species = species.value;
  if (sex) localFilters.value.sex = sex.value;
  if (status) localFilters.value.status = status.value;
}

function onDropdownChange(e) {
  // Asegurar que el cambio se refleje inmediatamente
  console.log('Dropdown changed:', e.target.id, e.target.value);
  syncDropdownValues();
}

function onDateChange(e) {
  // Asegurar que el cambio de fecha se refleje inmediatamente
  console.log('Date changed:', e.target.id, e.target.value);
  syncDateValues();
}

// Antes de buscar, asegurar que los valores estén sincronizados
function handleSearch() {
  syncDropdownValues();
  syncDateValues();
  
  // Log para debug
  console.log('Valores al buscar:', {
    species: localFilters.value.species,
    sex: localFilters.value.sex,
    status: localFilters.value.status,
    microchip: localFilters.value.microchip,
    breed: localFilters.value.breed,
    color: localFilters.value.color,
    location: localFilters.value.location,
    dateFrom: localFilters.value.dateFrom,
    dateTo: localFilters.value.dateTo,
    onlyNeutered: localFilters.value.onlyNeutered
  });
  
  emit('search');
}

// Exponer las funciones para que puedan ser llamadas externamente si es necesario
defineExpose({
  syncDropdownValues,
  syncDateValues
});
</script>

<style scoped>
.form-section {
  background: white;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.section-title {
  margin: 0;
  padding: 1rem 1.5rem;
  background: #E8F0FE;
  color: #3366CC;
  font-weight: 600;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  column-gap: 2rem;
  row-gap: 1.5rem;
  padding: 1.5rem;
}

.full-width {
  grid-column: 1 / 4;
}

.entradas-de-texto-govco input,
.desplegable-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  height: 44px;
  box-sizing: border-box;
}

.input-like-govco {
  display: flex;
  flex-direction: column;
  width: 100%;
  margin: 18px 0  ;
}

.input-like-govco label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.info-entradas-de-texto-govco {
  display: block;
  color: #666;
  font-size: 0.85rem;
  margin-top: 0.25rem;
}

.checkbox-govco {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: #f5f7fb;
  border-radius: 6px;
}

.checkbox-govco input {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 1px solid #E0E0E0;
}

.govco-btn {
  padding: 0.75rem 2rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
  transition: all 0.3s;
}

.govco-btn:hover {
  transform: translateY(-2px);
  opacity: 0.9;
}

.govco-bg-concrete {
  background-color: #737373;
}

.govco-bg-elf-green {
  background-color: #069169;
}

.desplegable-govco {
  position: relative;
  width: 100%;
}

.desplegable-govco select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%23333' d='M6 8L0 0h12z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  background-size: 12px 8px;
  padding-right: 2.5rem;
}

:deep(.desplegable-govco .desplegable-items),
:deep(.desplegable-govco.desplegable-calendar-govco .desplegable-calendar-control) { 
  z-index: 1500 !important;
}

:deep(.desplegable-govco.desplegable-calendar-govco .desplegable-calendar-control) {
  width: 100% !important;
  max-width: 100% !important;
  max-height: 668.8px !important;
  overflow-y: auto !important;
  box-sizing: border-box !important;
  padding: 0 !important;
}

:deep(.desplegable-calendar-govco .desplegable-calendar-control .header) { 
  width: 100% !important; 
  box-sizing: border-box !important;
}

:deep(.desplegable-calendar-govco .desplegable-calendar-control table#miCalendarioGrid.dates) {
  width: 100% !important;
  table-layout: fixed !important;
  box-sizing: border-box !important;
  padding: 0 !important;
  margin: 0 !important;  
  margin-left: -4.8px !important;
}

:deep(.desplegable-calendar-govco .desplegable-calendar-control table td) { 
  box-sizing: border-box !important; 
  width: calc(100% / 7) !important;
}

.date-field-container {  
  width: 100%;
}

.date-field-container :deep(.date.desplegable-selected-option) {
  padding: 7px 40px 7px 16px !important; 
  box-sizing: border-box !important;
}

.date-field-container :deep(.date.desplegable-selected-option input) {
  width: 100% !important;
  padding-right: 30px !important;
  box-sizing: border-box !important;
}

.neut-date-container .desplegable-govco {
  margin-top: 0.7rem; 
}

@media (max-width: 992px) {
  .form-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .full-width {
    grid-column: 1 / 3;
  }
}

@media (max-width: 576px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .full-width {
    grid-column: 1 / 2;
  }
  .form-actions {
    flex-direction: column;
  }
  .govco-btn {
    width: 100%;
  }
}
</style>