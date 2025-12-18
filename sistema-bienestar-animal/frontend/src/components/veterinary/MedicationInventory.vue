<template>
  <section class="inventory-module">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Inventario de Medicamentos</h2>
      <p class="text2-tipografia-govco">
        Registre ingresos, controle existencias y vigile vencimientos.
      </p>
    </div>

    <!-- Registro de ingreso -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">
        Registro de ingreso de medicamentos
      </h3>

      <form @submit.prevent="registerEntry">
        <div class="form-grid">
          <div class="entradas-de-texto-govco">
            <label for="medName">
              Nombre del medicamento<span aria-required="true">*</span>
            </label>
            <input
              id="medName"
              v-model="entryForm.name"
              type="text"
              placeholder="Amoxicilina 500mg, Meloxicam, etc."
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="lotNumber">
              Número de lote<span aria-required="true">*</span>
            </label>
            <input id="lotNumber" v-model="entryForm.lot" type="text" />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="expiry">
              Fecha de vencimiento<span aria-required="true">*</span>
            </label>
            <input id="expiry" v-model="entryForm.expiryDate" type="date" />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="quantity">
              Cantidad de ingreso<span aria-required="true">*</span>
            </label>
            <input
              id="quantity"
              v-model.number="entryForm.quantity"
              type="number"
              min="1"
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="minStock">
              Stock mínimo recomendado<span aria-required="true">*</span>
            </label>
            <input
              id="minStock"
              v-model.number="entryForm.minStock"
              type="number"
              min="1"
            />
          </div>
        </div>

        <div class="form-actions">
          <button
            type="button"
            class="govco-btn govco-bg-concrete"
            @click="resetEntryForm"
          >
            Limpiar
          </button>
          <button type="submit" class="govco-btn govco-bg-elf-green">
            Registrar ingreso
          </button>
        </div>
      </form>
    </div>

    <!-- Alertas -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">
        Alertas de inventario
      </h3>

      <div class="alerts-grid">
        <div class="alert-card" v-if="lowStock.length">
          <h4>Stock bajo</h4>
          <ul>
            <li v-for="med in lowStock" :key="med.id">
              {{ med.name }} - Stock actual: {{ med.stock }}
              (mínimo: {{ med.minStock }})
            </li>
          </ul>
        </div>

        <div class="alert-card" v-if="nearExpiry.length">
          <h4>Próximos a vencer (30 días)</h4>
          <ul>
            <li v-for="med in nearExpiry" :key="med.id">
              {{ med.name }} - Vence: {{ med.expiryDate }}
            </li>
          </ul>
        </div>

        <p v-if="!lowStock.length && !nearExpiry.length" class="no-alerts">
          No hay alertas activas de inventario.
        </p>
      </div>
    </div>

    <!-- Tabla de inventario -->
    <div class="form-section">
      <h3 class="h5-tipografia-govco section-title">
        Inventario actual
      </h3>

      <div class="table-wrapper">
        <table class="govco-table">
          <thead>
            <tr>
              <th>Medicamento</th>
              <th>Lote</th>
              <th>Vencimiento</th>
              <th>Stock actual</th>
              <th>Stock mínimo</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="med in medications" :key="med.id">
              <td>{{ med.name }}</td>
              <td>{{ med.lot }}</td>
              <td>{{ med.expiryDate }}</td>
              <td>{{ med.stock }}</td>
              <td>{{ med.minStock }}</td>
            </tr>
            <tr v-if="!medications.length">
              <td colspan="5">No hay medicamentos registrados.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useVeterinaryStore } from '@/stores/veterinary';

const veterinaryStore = useVeterinaryStore();

const loading = ref(true);
const medications = ref([]);

const entryForm = reactive({
  name: '',
  lot: '',
  expiryDate: '',
  quantity: null,
  minStock: 5
});

// Cargar medicamentos desde el store/API
async function loadMedications() {
  loading.value = true;
  try {
    await veterinaryStore.fetchMedicamentos();
    medications.value = veterinaryStore.medicamentos.map(med => ({
      id: med.id,
      name: med.nombre || med.name,
      lot: med.lote || med.lot || 'N/A',
      expiryDate: med.fecha_vencimiento || med.expiryDate || 'N/A',
      stock: med.stock_actual || med.stock || 0,
      minStock: med.stock_minimo || med.minStock || 5
    }));

    // Cargar alertas de stock bajo
    await veterinaryStore.fetchAlertasStockBajo();
  } catch (error) {
    console.error('Error cargando medicamentos:', error);
  } finally {
    loading.value = false;
  }
}

function resetEntryForm() {
  Object.assign(entryForm, {
    name: '',
    lot: '',
    expiryDate: '',
    quantity: null,
    minStock: 5
  });
}

async function registerEntry() {
  if (!entryForm.name || !entryForm.lot || !entryForm.expiryDate || !entryForm.quantity) {
    alert('Todos los campos de ingreso son obligatorios');
    return;
  }

  try {
    // Buscar si ya existe por nombre + lote (localmente primero)
    const existing = medications.value.find(
      m => m.name === entryForm.name && m.lot === entryForm.lot
    );

    if (existing) {
      existing.stock += entryForm.quantity;
      existing.expiryDate = entryForm.expiryDate;
      existing.minStock = entryForm.minStock;
    } else {
      medications.value.push({
        id: Date.now(),
        name: entryForm.name,
        lot: entryForm.lot,
        expiryDate: entryForm.expiryDate,
        stock: entryForm.quantity,
        minStock: entryForm.minStock
      });
    }

    // TODO: Implementar endpoint en backend para registrar ingreso de inventario
    // await veterinaryService.registrarIngresoInventario({
    //   nombre: entryForm.name,
    //   lote: entryForm.lot,
    //   fecha_vencimiento: entryForm.expiryDate,
    //   cantidad: entryForm.quantity,
    //   stock_minimo: entryForm.minStock
    // });

    alert('Ingreso registrado en el inventario');
    resetEntryForm();
  } catch (error) {
    console.error('Error registrando ingreso:', error);
    alert('Error al registrar el ingreso');
  }
}

const lowStock = computed(() =>
  medications.value.filter(m => m.stock <= m.minStock)
);

const nearExpiry = computed(() => {
  const today = new Date();
  const limit = new Date();
  limit.setDate(today.getDate() + 30);

  return medications.value.filter(m => {
    if (!m.expiryDate || m.expiryDate === 'N/A') return false;
    const exp = new Date(m.expiryDate);
    return exp >= today && exp <= limit;
  });
});

onMounted(async () => {
  await loadMedications();
  console.log('Inventario de medicamentos cargado');
});
</script>

<style scoped>
.inventory-module {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
  background: #f5f7fb;
}
.form-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 3px solid #3366cc;
}
.form-section {
  background: #fff;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  overflow: visible;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
.section-title {
  margin: 0;
  padding: 1rem 1.5rem;
  background: #e8f0fe;
  color: #3366cc;
  font-weight: 600;
}
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  column-gap: 2rem;
  row-gap: 1.5rem;
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
}
.govco-bg-concrete {
  background-color: #737373;
}
.govco-bg-elf-green {
  background-color: #069169;
}
.alerts-grid {
  padding: 1.5rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1rem;
}
.alert-card {
  background: #fff7e6;
  border: 1px solid #ffd699;
  border-radius: 6px;
  padding: 1rem;
}
.alert-card h4 {
  margin-top: 0;
}
.no-alerts {
  padding: 1.5rem;
}
.table-wrapper {
  padding: 1.5rem;
  overflow-x: auto;
}
.govco-table {
  width: 100%;
  border-collapse: collapse;
}
.govco-table th,
.govco-table td {
  border: 1px solid #e0e0e0;
  padding: 0.75rem;
  text-align: left;
}
.govco-table th {
  background: #f2f2f2;
}
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
