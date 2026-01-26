<template>
  <section class="inventory-module2">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Inventario de Medicamentos</h2>
      <p class="text2-tipografia-govco">
        Registre ingresos, controle existencias y vigile vencimientos.
      </p>
    </div>

    <!-- TABS NAVIGATION -->
    <div class="tabs-container">
      <button
        @click="activeTab = 'registro'"
        :class="['tab-btn', { active: activeTab === 'registro' }]"
      >
        📥 Registro de Ingreso
      </button>
      <button
        @click="activeTab = 'alertas'"
        :class="['tab-btn', { active: activeTab === 'alertas' }]"
      >
        ⚠️ Alertas ({{ lowStock.length + nearExpiry.length }})
      </button>
      <button
        @click="activeTab = 'inventario'"
        :class="['tab-btn', { active: activeTab === 'inventario' }]"
      >
        📊 Inventario ({{ medications.length }})
      </button>
    </div>

    <!-- TAB 1: REGISTRO -->
    <div v-show="activeTab === 'registro'" class="tab-content">
      <div class="form-section">
        <div v-if="successMessage" class="alert-success">
          ✅ {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="alert-error">
          ❌ {{ errorMessage }}
        </div>

        <form @submit.prevent="registerEntry">
          <div class="form-grid">
            <div class="entradas-de-texto-govco">
              <label for="medName">Nombre del medicamento<span aria-required="true">*</span></label>
              <input
                id="medName"
                v-model="entryForm.name"
                type="text"
                placeholder="Amoxicilina 500mg, Meloxicam, etc."
              />
            </div>

            <div class="entradas-de-texto-govco">
              <label for="category">Categoría<span aria-required="true">*</span></label>
              <select id="category" v-model="entryForm.categoria">
                <option value="">Seleccionar categoría</option>
                <option value="Medicamento">Medicamento</option>
                <option value="Insumo">Insumo</option>
                <option value="Suplemento">Suplemento</option>
              </select>
            </div>

            <div class="entradas-de-texto-govco">
              <label for="lotNumber">Número de lote<span aria-required="true">*</span></label>
              <input id="lotNumber" v-model="entryForm.lot" type="text" />
            </div>

            <div class="entradas-de-texto-govco">
              <label for="unitMedida">Unidad de medida<span aria-required="true">*</span></label>
              <select id="unitMedida" v-model="entryForm.unidad_medida">
                <option value="">Seleccionar unidad</option>
                <option value="unidades">Unidades</option>
                <option value="ml">Mililitros (ml)</option>
                <option value="mg">Miligramos (mg)</option>
                <option value="gramos">Gramos (g)</option>
                <option value="tabletas">Tabletas</option>
              </select>
            </div>

            <div class="entradas-de-texto-govco">
              <label for="expiry">Fecha de vencimiento<span aria-required="true">*</span></label>
              <input id="expiry" v-model="entryForm.expiryDate" type="date" />
            </div>

            <div class="entradas-de-texto-govco">
              <label for="quantity">Cantidad de ingreso<span aria-required="true">*</span></label>
              <input
                id="quantity"
                v-model.number="entryForm.quantity"
                type="number"
                min="1"
              />
            </div>

            <div class="entradas-de-texto-govco">
              <label for="minStock">Stock mínimo<span aria-required="true">*</span></label>
              <input
                id="minStock"
                v-model.number="entryForm.minStock"
                type="number"
                min="1"
              />
            </div>

            <div class="entradas-de-texto-govco">
              <label for="provider">Proveedor</label>
              <input id="provider" v-model="entryForm.proveedor" type="text" />
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="govco-btn govco-bg-concrete" @click="resetEntryForm">
              Limpiar
            </button>
            <button type="submit" class="govco-btn govco-bg-elf-green" :disabled="loading">
              {{ loading ? 'Registrando...' : 'Registrar ingreso' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- TAB 2: ALERTAS -->
    <div v-show="activeTab === 'alertas'" class="tab-content">
      <div class="form-section">
        <div class="alerts-grid">
          <div class="alert-card stock-bajo" v-if="lowStock.length">
            <h4>📉 Stock bajo ({{ lowStock.length }})</h4>
            <ul>
              <li v-for="med in lowStock" :key="med.id">
                <strong>{{ med.name }}</strong>
                <br />
                <small>Stock: {{ med.stock }} / Mínimo: {{ med.minStock }}</small>
              </li>
            </ul>
          </div>

          <div class="alert-card vencimiento" v-if="nearExpiry.length">
            <h4>📅 Próximos a vencer ({{ nearExpiry.length }})</h4>
            <ul>
              <li v-for="med in nearExpiry" :key="med.id">
                <strong>{{ med.name }}</strong>
                <br />
                <small>Vence: {{ formatDate(med.expiryDate) }}</small>
              </li>
            </ul>
          </div>

          <p v-if="!lowStock.length && !nearExpiry.length" class="no-alerts">
            ✅ No hay alertas activas. Inventario en buena condición.
          </p>
        </div>
      </div>
    </div>

    <!-- TAB 3: INVENTARIO COMPLETO -->
    <div v-show="activeTab === 'inventario'" class="tab-content">
      <div class="form-section">
        <!-- Filtros -->
        <div class="filters-bar">
          <div class="filter-group">
            <input
              v-model="filters.search"
              type="text"
              placeholder="🔍 Buscar por nombre o código..."
              class="filter-input"
            />
          </div>

          <div class="filter-group">
            <select v-model="filters.categoria" class="filter-select">
              <option value="">Todas las categorías</option>
              <option value="Medicamento">Medicamento</option>
              <option value="Insumo">Insumo</option>
              <option value="Suplemento">Suplemento</option>
            </select>
          </div>

          <div class="filter-group">
            <select v-model="filters.stockStatus" class="filter-select">
              <option value="">Todos los stocks</option>
              <option value="bajo">Stock bajo</option>
              <option value="normal">Stock normal</option>
              <option value="vencido">Vencidos</option>
            </select>
          </div>

          <button @click="resetFilters" class="govco-btn govco-btn-small govco-bg-concrete">
            Limpiar filtros
          </button>
        </div>

        <!-- Tabla -->
        <div class="table-wrapper">
          <table class="govco-table">
            <thead>
              <tr>
                <th>Acciones</th>
                <th>Medicamento</th>
                <th>Categoría</th>
                <th>Código</th>
                <th>Lote</th>
                <th>Vencimiento</th>
                <th>Stock actual</th>
                <th>Stock mínimo</th>
                <th>Proveedor</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="med in filteredMedications" :key="med.id" :class="getMedicationClass(med)">
                <td class="actions-cell">
                  <button
                    @click="editMedication(med)"
                    class="action-btn edit-btn"
                    title="Editar"
                  >
                    ✏️
                  </button>
                  <button
                    @click="deleteMedication(med.id)"
                    class="action-btn delete-btn"
                    title="Eliminar"
                  >
                    🗑️
                  </button>
                </td>
                <td>{{ med.name }}</td>
                <td>{{ med.categoria || 'N/A' }}</td>
                <td><code>{{ med.codigo }}</code></td>
                <td>{{ med.lot }}</td>
                <td>{{ formatDate(med.expiryDate) }}</td>
                <td>
                  <strong :style="{ color: med.stock <= med.minStock ? '#d32f2f' : '#388e3c' }">
                    {{ med.stock }}
                  </strong>
                </td>
                <td>{{ med.minStock }}</td>
                <td>{{ med.proveedor || 'N/A' }}</td>
              </tr>
              <tr v-if="!filteredMedications.length">
                <td colspan="9" class="text-center">No hay medicamentos que coincidan con los filtros.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal de edición -->
    <div v-if="showEditModal" class="modal-overlay" @click="closeEditModal">
      <div class="modal-content" @click.stop>
        <h3>Editar medicamento</h3>

        <form @submit.prevent="saveEdit">
          <div class="form-grid">
            <div class="entradas-de-texto-govco">
              <label>Nombre</label>
              <input v-model="editingMed.name" type="text" />
            </div>
            <div class="entradas-de-texto-govco">
              <label>Stock actual</label>
              <input v-model.number="editingMed.stock" type="number" />
            </div>
            <div class="entradas-de-texto-govco">
              <label>Stock mínimo</label>
              <input v-model.number="editingMed.minStock" type="number" />
            </div>
            <div class="entradas-de-texto-govco">
              <label>Vencimiento</label>
              <input v-model="editingMed.expiryDate" type="date" />
            </div>
            <div class="entradas-de-texto-govco">
              <label>Proveedor</label>
              <input v-model="editingMed.proveedor" type="text" />
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="govco-btn govco-bg-concrete" @click="closeEditModal">
              Cancelar
            </button>
            <button type="submit" class="govco-btn govco-bg-elf-green">
              Guardar cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import inventoryService from '@/services/inventoryService';

const activeTab = ref('inventario');
const loading = ref(true);
const medications = ref([]);
const successMessage = ref('');
const errorMessage = ref('');
const showEditModal = ref(false);
const editingMed = ref(null);

const entryForm = reactive({
  name: '',
  categoria: '',
  lot: '',
  unidad_medida: '',
  expiryDate: '',
  quantity: null,
  minStock: 5,
  proveedor: ''
});

const filters = reactive({
  search: '',
  categoria: '',
  stockStatus: ''
});

async function loadMedications() {
  loading.value = true;
  try {
    const data = await inventoryService.getInsumos({ per_page: 100 });

    if (Array.isArray(data) && data.length > 0) {
      medications.value = data.map(med => ({
        id: med.id,
        codigo: med.codigo,
        name: med.nombre || med.name,
        categoria: med.categoria || 'N/A',
        lot: med.lote || med.lot || 'N/A',
        expiryDate: med.fecha_vencimiento || med.expiryDate || 'N/A',
        stock: parseFloat(med.stock_actual || med.cantidad_actual || 0),
        minStock: parseFloat(med.stock_minimo || med.cantidad_minima || 5),
        proveedor: med.proveedor || '',
        unidad_medida: med.unidad_medida || 'unidades'
      }));
    }
  } catch (error) {
    console.error('❌ Error cargando medicamentos:', error);
    errorMessage.value = 'Error al cargar los medicamentos';
  } finally {
    loading.value = false;
  }
}

function resetEntryForm() {
  Object.assign(entryForm, {
    name: '',
    categoria: '',
    lot: '',
    unidad_medida: '',
    expiryDate: '',
    quantity: null,
    minStock: 5,
    proveedor: ''
  });
  errorMessage.value = '';
  successMessage.value = '';
}

async function registerEntry() {
  if (!entryForm.name || !entryForm.lot || !entryForm.expiryDate || !entryForm.quantity) {
    errorMessage.value = 'Todos los campos marcados con * son obligatorios';
    return;
  }

  loading.value = true;
  try {
    const newMed = await inventoryService.crearInventario({
      codigo: `MED-${Date.now()}`,
      nombre: entryForm.name,
      categoria: entryForm.categoria || 'Medicamento',
      tipo: entryForm.categoria || 'Medicamento',
      unidad_medida: entryForm.unidad_medida || 'unidades',
      cantidad_actual: entryForm.quantity,
      cantidad_minima: entryForm.minStock,
      fecha_vencimiento: entryForm.expiryDate,
      proveedor: entryForm.proveedor
    });

    medications.value.push({
      id: newMed.id,
      codigo: newMed.codigo,
      name: newMed.nombre,
      categoria: newMed.categoria,
      lot: entryForm.lot,
      expiryDate: entryForm.expiryDate,
      stock: entryForm.quantity,
      minStock: entryForm.minStock,
      proveedor: entryForm.proveedor,
      unidad_medida: entryForm.unidad_medida
    });

    successMessage.value = 'Medicamento registrado exitosamente ✅';
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);

    resetEntryForm();
    activeTab.value = 'inventario';
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Error al registrar el medicamento';
  } finally {
    loading.value = false;
  }
}

function editMedication(med) {
  editingMed.value = { ...med };
  showEditModal.value = true;
}

async function saveEdit() {
  if (!editingMed.value) return;

  loading.value = true;
  try {
    await inventoryService.actualizarInventario(editingMed.value.id, {
      nombre: editingMed.value.name,
      cantidad_actual: editingMed.value.stock,
      cantidad_minima: editingMed.value.minStock,
      fecha_vencimiento: editingMed.value.expiryDate,
      proveedor: editingMed.value.proveedor
    });

    const index = medications.value.findIndex(m => m.id === editingMed.value.id);
    if (index !== -1) {
      medications.value[index] = { ...editingMed.value };
    }

    successMessage.value = 'Medicamento actualizado exitosamente ✅';
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);

    closeEditModal();
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Error al actualizar el medicamento';
  } finally {
    loading.value = false;
  }
}

function closeEditModal() {
  showEditModal.value = false;
  editingMed.value = null;
}

async function deleteMedication(id) {
  if (!confirm('¿Está seguro de que desea eliminar este medicamento?')) return;

  loading.value = true;
  try {
    await inventoryService.eliminarInventario(id);
    medications.value = medications.value.filter(m => m.id !== id);

    successMessage.value = 'Medicamento eliminado exitosamente ✅';
    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Error al eliminar el medicamento';
  } finally {
    loading.value = false;
  }
}

function resetFilters() {
  filters.search = '';
  filters.categoria = '';
  filters.stockStatus = '';
}

function formatDate(date) {
  if (!date || date === 'N/A') return 'N/A';
  const d = new Date(date);
  return d.toLocaleDateString('es-ES');
}

function getMedicationClass(med) {
  if (med.stock === 0) return 'status-vencido';
  if (med.stock <= med.minStock) return 'status-bajo';
  if (med.expiryDate !== 'N/A') {
    const expDate = new Date(med.expiryDate);
    const today = new Date();
    if (expDate < today) return 'status-vencido';
  }
  return '';
}

const filteredMedications = computed(() => {
  return medications.value.filter(med => {
    if (filters.search) {
      const search = filters.search.toLowerCase();
      if (
        !med.name.toLowerCase().includes(search) &&
        !med.codigo.toLowerCase().includes(search)
      ) {
        return false;
      }
    }

    if (filters.categoria && med.categoria !== filters.categoria) {
      return false;
    }

    if (filters.stockStatus) {
      if (filters.stockStatus === 'bajo' && med.stock > med.minStock) return false;
      if (filters.stockStatus === 'normal' && med.stock <= med.minStock) return false;
      if (filters.stockStatus === 'vencido') {
        if (med.expiryDate === 'N/A') return false;
        const expDate = new Date(med.expiryDate);
        if (expDate >= new Date()) return false;
      }
    }

    return true;
  });
});

const lowStock = computed(() => medications.value.filter(m => m.stock <= m.minStock));

const nearExpiry = computed(() => {
  const today = new Date();
  const limit = new Date();
  limit.setDate(today.getDate() + 30);

  return medications.value.filter(m => {
    if (!m.expiryDate || m.expiryDate === 'N/A') return false;
    const exp = new Date(m.expiryDate);
    return exp >= today && exp <= limit && exp > today;
  });
});

onMounted(async () => {
  await loadMedications();
});
</script>

<style scoped>
.inventory-module2 {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}

.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366cc;
}

.tabs-container {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  background: #fff;
  padding: 0.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  flex-wrap: wrap;
}

.tab-btn {
  flex: 1;
  min-width: 180px;
  padding: 0.75rem 1.5rem;
  border: 2px solid transparent;
  border-radius: 6px;
  background: #f0f0f0;
  color: #333;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-btn:hover {
  background: #e8e8e8;
}

.tab-btn.active {
  background: #3366cc;
  color: white;
  border-color: #3366cc;
}

.tab-content {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-section {
  background: #fff;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 0 1.5rem 1.5rem;
}

.govco-btn {
  padding: 0.75rem 2rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: #fff;
  transition: background-color 0.2s;
}

.govco-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.govco-btn-small {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.govco-bg-concrete {
  background-color: #737373;
}

.govco-bg-concrete:hover {
  background-color: #5a5a5a;
}

.govco-bg-elf-green {
  background-color: #069169;
}

.govco-bg-elf-green:hover {
  background-color: #047857;
}

.alert-success {
  background-color: #e8f5e9;
  color: #2e7d32;
  padding: 1rem;
  border-radius: 4px;
  border-left: 4px solid #2e7d32;
  margin: 1rem 1.5rem 0;
}

.alert-error {
  background-color: #ffebee;
  color: #c62828;
  padding: 1rem;
  border-radius: 4px;
  border-left: 4px solid #c62828;
  margin: 1rem 1.5rem 0;
}

.alerts-grid {
  padding: 1.5rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.alert-card {
  border-radius: 6px;
  padding: 1rem;
  border-left: 4px solid;
}

.alert-card.stock-bajo {
  background-color: #fff3e0;
  border-left-color: #f57c00;
}

.alert-card.vencimiento {
  background-color: #fce4ec;
  border-left-color: #e91e63;
}

.alert-card h4 {
  margin: 0 0 1rem 0;
  font-weight: 600;
}

.alert-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.alert-card li {
  padding: 0.5rem 0;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.alert-card li:last-child {
  border-bottom: none;
}

.no-alerts {
  padding: 1.5rem;
  text-align: center;
  color: #388e3c;
  font-weight: 500;
}

.filters-bar {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  flex-wrap: wrap;
  align-items: flex-end;
  border-bottom: 1px solid #e0e0e0;
}

.filter-group {
  flex: 1;
  min-width: 200px;
}

.filter-input,
.filter-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.875rem;
}

.table-wrapper {
  padding: 1.5rem;
  overflow-x: auto;
}

.govco-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

.govco-table th {
  background-color: #e8f0fe;
  color: #3366cc;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #3366cc;
}

.govco-table td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e0e0e0;
}

.govco-table tbody tr:hover {
  background-color: #f9f9f9;
}

.govco-table tbody tr.status-bajo {
  background-color: #fff3e0;
}

.govco-table tbody tr.status-vencido {
  background-color: #ffebee;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.action-btn:hover {
  background-color: #e0e0e0;
}

code {
  background-color: #f5f5f5;
  padding: 0.25rem 0.5rem;
  border-radius: 3px;
  font-family: monospace;
  font-size: 0.875rem;
}

.text-center {
  text-align: center;
  color: #999;
  font-style: italic;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h3 {
  margin-top: 0;
  color: #3366cc;
}

.entradas-de-texto-govco {
  display: flex;
  flex-direction: column;
}

.entradas-de-texto-govco label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #333;
}

.entradas-de-texto-govco input,
.entradas-de-texto-govco select {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.875rem;
  font-family: inherit;
}

.entradas-de-texto-govco input:focus,
.entradas-de-texto-govco select:focus {
  outline: none;
  border-color: #3366cc;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .tab-btn {
    min-width: 150px;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }

  .tabs-container {
    flex-direction: column;
  }

  .alerts-grid {
    grid-template-columns: 1fr;
  }

  .table-wrapper {
    font-size: 0.75rem;
    padding: 1rem;
  }

  .govco-table th,
  .govco-table td {
    padding: 0.5rem;
  }
}
</style>
