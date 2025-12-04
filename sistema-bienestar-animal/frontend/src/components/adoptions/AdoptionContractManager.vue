<!-- src/components/adoptions/AdoptionContractManager.vue -->
<template>
  <section class="contracts-container">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Contratos de adopción</h2>
      <p class="text2-tipografia-govco">
        Gestiona los contratos digitales de adopción ya aprobados.
      </p>
    </div>

    <div v-if="loading" class="govco-card">
      Cargando solicitudes aprobadas...
    </div>

    <div v-else-if="!requests.length" class="govco-card">
      No hay solicitudes aprobadas pendientes de contrato.
    </div>

    <table v-else class="govco-table">
      <thead>
        <tr>
          <th>Adoptante</th>
          <th>Animal</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="req in requests" :key="req.id">
          <td>
            <strong>{{ req.applicant.name }}</strong><br />
            <span class="small">{{ req.applicant.idNumber }}</span>
          </td>
          <td>
            {{ req.animal.name }}<br />
            <span class="small">{{ req.animal.species }}</span>
          </td>
          <td>
            <span v-if="req.contractSigned" class="badge badge-success">
              Firmado
            </span>
            <span v-else-if="req.contractUrl" class="badge badge-warning">
              Generado pendiente de firma
            </span>
            <span v-else class="badge badge-secondary">
              Sin contrato
            </span>
          </td>
          <td class="actions-col">
            <button
              v-if="!req.contractUrl"
              type="button"
              class="govco-btn govco-btn-small govco-btn-primary"
              @click="onGenerate(req)"
            >
              Generar contrato
            </button>

            <a
              v-if="req.contractUrl"
              :href="req.contractUrl"
              target="_blank"
              class="govco-btn govco-btn-small govco-btn-secondary"
            >
              Ver PDF
            </a>

            <button
              v-if="req.contractUrl && !req.contractSigned"
              type="button"
              class="govco-btn govco-btn-small govco-btn-success"
              @click="onSign(req)"
            >
              Registrar firma
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';

/**
 * DATA FALSA
 */
const mockRequests = [
  {
    id: 2,
    applicant: {
      name: 'Carlos Pérez',
      idNumber: '987654321',
    },
    animal: {
      name: 'Michi',
      species: 'Gato',
    },
    contractUrl: null,
    contractId: null,
    contractSigned: false,
  },
  {
    id: 3,
    applicant: {
      name: 'Laura Ramírez',
      idNumber: '1122334455',
    },
    animal: {
      name: 'Rocky',
      species: 'Perro',
    },
    contractUrl: 'https://ejemplo.gov.co/contratos/rocky.pdf',
    contractId: 'C-0003',
    contractSigned: false,
  },
  {
    id: 4,
    applicant: {
      name: 'Andrea López',
      idNumber: '5566778899',
    },
    animal: {
      name: 'Nala',
      species: 'Perro',
    },
    contractUrl: 'https://ejemplo.gov.co/contratos/nala.pdf',
    contractId: 'C-0004',
    contractSigned: true,
  },
];

const requests = ref([]);
const loading = ref(false);

async function loadData() {
  loading.value = true;
  try {
    requests.value = mockRequests;
  } finally {
    loading.value = false;
  }
}

async function onGenerate(req) {
  // Simulamos generación
  req.contractUrl = `https://ejemplo.gov.co/contratos/mock-${req.id}.pdf`;
  req.contractId = `C-MOCK-${req.id}`;
  alert('Contrato generado (mock) y almacenado en el gestor documental.');
}

async function onSign(req) {
  req.contractSigned = true;
  alert(
    'Firma registrada (mock). Aquí en real se programarían seguimientos y se cambiaría estado del animal a "adoptado".'
  );
}

onMounted(loadData);
</script>


<style scoped>
.contracts-container {
  background: #f5f7fb;
  padding: 16px 20px;
  border-radius: 8px;
}

.govco-card {
  background: #ffffff;
  padding: 16px;
  border-radius: 8px;
}

.small {
  font-size: 0.8rem;
  color: #555;
}

.badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.75rem;
}

.badge-success {
  background: #198754;
  color: #fff;
}

.badge-warning {
  background: #ffc107;
  color: #000;
}

.badge-secondary {
  background: #6c757d;
  color: #fff;
}

.actions-col {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
</style>
