<!-- src/components/adoptions/AdoptionReturnsManager.vue -->
<template>
  <section class="returns-container">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Gestión de devoluciones</h2>
      <p class="text2-tipografia-govco">
        Registra devoluciones de animales adoptados y programa revisión
        veterinaria obligatoria.
      </p>
    </div>

    <!-- Búsqueda -->
    <div class="govco-card search-card">
      <label for="search" class="entradas-de-texto-govco">
        <span>Buscar adopción por microchip, nombre del adoptante o documento</span>
        <input
          id="search"
          v-model="search"
          type="text"
          placeholder="Ej: MC12345, 1234567890, Juan Pérez"
          @keyup.enter="searchAdoptions"
        />
      </label>
      <button
        type="button"
        class="govco-btn govco-btn-primary govco-btn-small"
        @click="searchAdoptions"
      >
        Buscar
      </button>
    </div>

    <!-- Resultados -->
    <div v-if="loading" class="govco-card">
      Buscando adopciones activas...
    </div>

    <div v-else-if="!adoptions.length" class="govco-card">
      No hay adopciones activas encontradas.
    </div>

    <table v-else class="govco-table">
      <thead>
        <tr>
          <th>Animal</th>
          <th>Adoptante</th>
          <th>Fecha adopción</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="a in adoptions" :key="a.id">
          <td>
            {{ a.animal.name }}<br />
            <span class="small">{{ a.animal.microchip }}</span>
          </td>
          <td>
            {{ a.adopter.name }}<br />
            <span class="small">{{ a.adopter.idNumber }}</span>
          </td>
          <td>{{ formatDate(a.adoptionDate) }}</td>
          <td>
            <button
              type="button"
              class="govco-btn govco-btn-small govco-btn-danger"
              @click="selectAdoption(a)"
            >
              Gestionar devolución
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Formulario de devolución -->
    <div v-if="selectedAdoption" class="govco-card return-form">
      <h3 class="h4-tipografia-govco">
        Devolución de {{ selectedAdoption.animal.name }}
      </h3>
      <p class="text2-tipografia-govco">
        Completa la información de la devolución. El sistema actualizará el
        estado del animal, registrará el historial y agendará revisión
        veterinaria obligatoria.
      </p>

      <form @submit.prevent="submitReturn">
        <div class="entradas-de-texto-govco">
          <label for="reasonCategory">Motivo principal<span>*</span></label>
          <select
            id="reasonCategory"
            v-model="returnForm.reasonCategory"
            required
          >
            <option value="">Selecciona una opción</option>
            <option value="comportamiento">Comportamiento del animal</option>
            <option value="salud">Condición de salud</option>
            <option value="economico">Situación económica</option>
            <option value="familiar">Situación familiar</option>
            <option value="otro">Otro</option>
          </select>
        </div>

        <div class="entradas-de-texto-govco">
          <label for="reasonDetail">Detalle del motivo<span>*</span></label>
          <textarea
            id="reasonDetail"
            v-model="returnForm.reasonDetail"
            rows="3"
            required
          />
        </div>

        <div class="entradas-de-texto-govco">
          <label for="returnDate">Fecha de devolución<span>*</span></label>
          <input
            id="returnDate"
            v-model="returnForm.returnDate"
            type="date"
            required
          />
        </div>

        <div class="form-actions">
          <button
            type="submit"
            class="govco-btn govco-btn-primary"
          >
            Registrar devolución
          </button>
        </div>
      </form>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';

/**
 * DATA FALSA
 */
const mockAdoptions = [
  {
    id: 201,
    adoptionDate: '2024-12-20',
    animal: {
      id: 101,
      name: 'Luna',
      microchip: 'MC-0001',
    },
    adopter: {
      name: 'María González',
      idNumber: '123456789',
    },
  },
  {
    id: 202,
    adoptionDate: '2025-01-10',
    animal: {
      id: 103,
      name: 'Rocky',
      microchip: 'MC-0003',
    },
    adopter: {
      name: 'Laura Ramírez',
      idNumber: '1122334455',
    },
  },
];

const search = ref('');
const adoptions = ref([]);
const loading = ref(false);

const selectedAdoption = ref(null);

const returnForm = ref({
  reasonCategory: '',
  reasonDetail: '',
  returnDate: '',
});

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('es-CO');
}

async function searchAdoptions() {
  loading.value = true;
  try {
    const term = search.value.toLowerCase();
    if (!term) {
      adoptions.value = mockAdoptions;
    } else {
      adoptions.value = mockAdoptions.filter((a) => {
        const haystack = `${a.animal.microchip} ${a.animal.name} ${a.adopter.name} ${a.adopter.idNumber}`.toLowerCase();
        return haystack.includes(term);
      });
    }
  } finally {
    loading.value = false;
  }
}

function selectAdoption(a) {
  selectedAdoption.value = a;
  returnForm.value = {
    reasonCategory: '',
    reasonDetail: '',
    returnDate: new Date().toISOString().slice(0, 10),
  };
}

async function submitReturn() {
  if (!selectedAdoption.value) return;

  alert(
    `Devolución registrada (mock) para ${selectedAdoption.value.animal.name}.
Motivo: ${returnForm.value.reasonCategory}
Detalle: ${returnForm.value.reasonDetail}
Fecha: ${returnForm.value.returnDate}

En real: se actualiza el estado del animal a "disponible", se programa revisión veterinaria y se envía evento a sci-audit.`
  );

  // Simulamos que ya no está activa esta adopción
  adoptions.value = adoptions.value.filter(
    (a) => a.id !== selectedAdoption.value.id
  );
  selectedAdoption.value = null;
}
</script>


<style scoped>
.returns-container {
  background: #f5f7fb;
  padding: 16px 20px;
  border-radius: 8px;
}

.govco-card {
  background: #ffffff;
  padding: 16px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.search-card {
  display: flex;
  gap: 8px;
  align-items: flex-end;
}

.return-form {
  margin-top: 16px;
}

.small {
  font-size: 0.8rem;
}

.form-actions {
  margin-top: 12px;
  display: flex;
  justify-content: flex-end;
}
</style>
