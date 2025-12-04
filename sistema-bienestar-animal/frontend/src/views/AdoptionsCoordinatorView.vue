<!-- src/views/AdoptionsCoordinatorView.vue -->
<template>
  <section class="adoptions-coordinator-view">
    <header class="adoptions-header">
      <h1 class="h2-tipografia-govco govcolor-blue-dark">
        Gestión de adopciones
      </h1>
      <p class="text2-tipografia-govco">
        Desde este módulo puedes gestionar solicitudes, contratos, seguimientos
        y devoluciones de adopciones.
      </p>
    </header>

    <!-- Tabs -->
    <nav class="adoptions-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="['govco-btn-secondary', { active: currentTab === tab.key }]"
        type="button"
        @click="currentTab = tab.key"
      >
        {{ tab.label }}
      </button>
    </nav>

    <!-- Contenido -->
    <div class="adoptions-content">
      <!-- 5.1 Gestión de solicitudes HU-011 -->
      <AdoptionRequestsManager
        v-if="currentTab === 'requests'"
      />

      <!-- 5.2 Contratos HU-012 -->
      <AdoptionContractManager
        v-if="currentTab === 'contracts'"
      />

      <!-- 5.3 Seguimientos HU-013 -->
      <PostAdoptionFollowUps
        v-if="currentTab === 'followups'"
      />

      <!-- 5.4 Devoluciones HU-014 -->
      <AdoptionReturnsManager
        v-if="currentTab === 'returns'"
      />
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import AdoptionRequestsManager from '../components/adoptions/AdoptionRequestsManager.vue';
import AdoptionContractManager from '../components/adoptions/AdoptionContractManager.vue';
import PostAdoptionFollowUps from '../components/adoptions/PostAdoptionFollowUps.vue';
import AdoptionReturnsManager from '../components/adoptions/AdoptionReturnsManager.vue';

const tabs = [
  { key: 'requests', label: 'Solicitudes' },
  { key: 'contracts', label: 'Contratos' },
  { key: 'followups', label: 'Seguimientos' },
  { key: 'returns', label: 'Devoluciones' },
];

const currentTab = ref('requests');
</script>

<style scoped>
.adoptions-coordinator-view {
  background: #f5f7fb;
  padding: 24px;
}

.adoptions-header {
  margin-bottom: 16px;
}

.adoptions-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.adoptions-tabs .govco-btn-secondary {
  border-radius: 999px;
  padding-inline: 16px;
}

.adoptions-tabs .active {
  background: #004884;
  color: #ffffff;
}

.adoptions-content {
  background: #ffffff;
  border-radius: 8px;
  padding: 16px 20px;
}
</style>
