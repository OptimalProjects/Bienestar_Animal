<!-- AnimalDetail.vue - Con colores Gov.co oficiales -->
<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header govco-bg-blue-light">
        <h3 class="h4-tipografia-govco govcolor-blue-dark">
          {{ isEditing ? 'Editar animal' : 'Detalles del animal' }}
        </h3>
        <button @click="handleClose" class="modal-close">×</button>
      </div>

      <!-- MODO VISTA -->
      <div v-if="!isEditing" class="modal-body">
        <div class="animal-details-grid">
          <div class="detail-image">
            <img :src="displayPhoto" :alt="`Foto de ${displayId}`" />
          </div>

          <div class="detail-section govco-bg-hawkes-blue">
            <h4 class="h6-tipografia-govco section-subtitle govcolor-marine">Identificación</h4>
            <dl class="detail-list">
              <dt>Código/Microchip:</dt>
              <dd>{{ displayId }}</dd>
              <dt>Nombre:</dt>
              <dd>{{ animal.nombre || 'Sin nombre' }}</dd>
              <dt>Especie:</dt>
              <dd>{{ displayEspecie }}</dd>
              <dt>Raza:</dt>
              <dd>{{ animal.raza || animal.breed || 'No especificada' }}</dd>
              <dt>Color:</dt>
              <dd>{{ animal.color || 'No especificado' }}</dd>
              <dt>Sexo:</dt>
              <dd>{{ displaySexo }}</dd>
              <dt>Edad estimada:</dt>
              <dd>{{ displayEdad }}</dd>
              <dt>Tamaño:</dt>
              <dd>{{ displayTamanio }}</dd>
              <dt>Esterilización:</dt>
              <dd>{{ (animal.esterilizacion ?? animal.esterilizado ?? animal.neutered) ? 'Sí' : 'No' }}</dd>
            </dl>
          </div>

          <div class="detail-section govco-bg-hawkes-blue">
            <h4 class="h6-tipografia-govco section-subtitle govcolor-marine">Estado y Ubicación</h4>
            <dl class="detail-list">
              <dt>Estado actual:</dt>
              <dd>
                <span class="status-badge" :class="`status-${animal.estado || animal.status}`">
                  {{ getStatusLabel(animal.estado || animal.status) }}
                </span>
              </dd>
              <dt>Estado de salud:</dt>
              <dd>{{ displayEstadoSalud }}</dd>
              <dt>Fecha de rescate:</dt>
              <dd>{{ displayFechaRescate }}</dd>
              <dt>Ubicación de rescate:</dt>
              <dd>{{ animal.ubicacion_rescate || 'No registrada' }}</dd>
              <dt>Observaciones:</dt>
              <dd>{{ animal.observaciones || animal.senias_particulares || 'Sin observaciones' }}</dd>
            </dl>
          </div>

          <div v-if="animal.esterilizacion ?? animal.esterilizado ?? animal.neutered" class="detail-section govco-bg-hawkes-blue full-width">
            <h4 class="h6-tipografia-govco section-subtitle govcolor-elf-green">Esterilización ✓</h4>
            <dl class="detail-list">
              <dt>Fecha:</dt>
              <dd>{{ animal.fecha_esterilizacion || animal.neuteringDate || 'No registrada' }}</dd>
              <dt>Veterinario:</dt>
              <dd>{{ animal.veterinario_esterilizacion || animal.neuteringVet || 'No registrado' }}</dd>
            </dl>
          </div>
        </div>
      </div>

      <!-- MODO EDICIÓN -->
      <div v-else class="modal-body">
        <form @submit.prevent="handleSave" class="edit-form">
          <div class="form-grid">
            <!-- Nombre -->
            <div class="form-group">
              <label for="edit-nombre">Nombre</label>
              <input type="text" id="edit-nombre" v-model="editForm.nombre" placeholder="Nombre del animal" />
            </div>

            <!-- Especie -->
            <div class="form-group">
              <label for="edit-especie">Especie*</label>
              <select id="edit-especie" v-model="editForm.especie" required>
                <option value="">Seleccione</option>
                <option value="canino">Canino</option>
                <option value="felino">Felino</option>
                <option value="equino">Equino</option>
                <option value="otro">Otro</option>
              </select>
            </div>

            <!-- Raza -->
            <div class="form-group">
              <label for="edit-raza">Raza</label>
              <input type="text" id="edit-raza" v-model="editForm.raza" placeholder="Raza del animal" />
            </div>

            <!-- Sexo -->
            <div class="form-group">
              <label for="edit-sexo">Sexo</label>
              <select id="edit-sexo" v-model="editForm.sexo">
                <option value="">Seleccione</option>
                <option value="macho">Macho</option>
                <option value="hembra">Hembra</option>
                <option value="desconocido">Desconocido</option>
              </select>
            </div>

            <!-- Color -->
            <div class="form-group">
              <label for="edit-color">Color</label>
              <input type="text" id="edit-color" v-model="editForm.color" placeholder="Color del pelaje" />
            </div>

            <!-- Tamaño -->
            <div class="form-group">
              <label for="edit-tamanio">Tamaño</label>
              <select id="edit-tamanio" v-model="editForm.tamanio">
                <option value="">Seleccione</option>
                <option value="pequenio">Pequeño</option>
                <option value="mediano">Mediano</option>
                <option value="grande">Grande</option>
                <option value="muy_grande">Muy grande</option>
              </select>
            </div>

            <!-- Edad aproximada -->
            <div class="form-group">
              <label for="edit-edad">Edad aproximada (meses)</label>
              <input type="number" id="edit-edad" v-model.number="editForm.edad_aproximada" min="0" placeholder="Ej: 24" />
            </div>

            <!-- Peso -->
            <div class="form-group">
              <label for="edit-peso">Peso (kg)</label>
              <input type="number" id="edit-peso" v-model.number="editForm.peso_actual" min="0" step="0.1" placeholder="Ej: 15.5" />
            </div>

            <!-- Estado -->
            <div class="form-group">
              <label for="edit-estado">Estado*</label>
              <select id="edit-estado" v-model="editForm.estado" required>
                <option value="">Seleccione</option>
                <option value="en_calle">En calle</option>
                <option value="en_refugio">En refugio</option>
                <option value="en_adopcion">En adopción</option>
                <option value="adoptado">Adoptado</option>
                <option value="en_tratamiento">En tratamiento</option>
                <option value="fallecido">Fallecido</option>
              </select>
            </div>

            <!-- Estado de salud -->
            <div class="form-group">
              <label for="edit-estado-salud">Estado de salud</label>
              <select id="edit-estado-salud" v-model="editForm.estado_salud">
                <option value="">Seleccione</option>
                <option value="critico">Crítico</option>
                <option value="grave">Grave</option>
                <option value="estable">Estable</option>
                <option value="bueno">Bueno</option>
                <option value="excelente">Excelente</option>
              </select>
            </div>

            <!-- Observaciones -->
            <div class="form-group full-width">
              <label for="edit-observaciones">Observaciones</label>
              <textarea id="edit-observaciones" v-model="editForm.observaciones" rows="3" placeholder="Señas particulares, observaciones..."></textarea>
            </div>
          </div>

          <div v-if="editError" class="error-message">{{ editError }}</div>
        </form>
      </div>

      <div class="modal-footer">
        <button @click="handleClose" class="govco-btn govco-bg-dim-gray" :disabled="isSaving">
          {{ isEditing ? 'Cancelar' : 'Cerrar' }}
        </button>
        <button v-if="!isEditing" @click="startEditing" class="govco-btn govco-bg-elf-green">
          Editar información
        </button>
        <button v-else @click="handleSave" class="govco-btn govco-bg-elf-green" :disabled="isSaving">
          {{ isSaving ? 'Guardando...' : 'Guardar cambios' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useAnimalsStore } from '@/stores/animals';

const props = defineProps({
  animal: Object
});

const emit = defineEmits(['close', 'updated']);

const animalsStore = useAnimalsStore();
const isEditing = ref(false);
const isSaving = ref(false);
const editError = ref('');
const editForm = ref({});

// ---- Helpers
function resolveMediaUrl(input) {
  if (!input) return '/placeholder-animal.jpg';
  const s = String(input);
  if (/^(https?:)?\/\//i.test(s) || s.startsWith('data:') || s.startsWith('blob:')) return s;
  if (s.includes('/storage/')) {
    return s.startsWith('http') ? s : `${window.location.origin}${s.startsWith('/') ? '' : '/'}${s}`;
  }
  const clean = s.replace(/^\/+/, '');
  return `${window.location.origin}/storage/${clean}`;
}

// Computed para mostrar datos con fallbacks español/inglés
const displayPhoto = computed(() => {
  const raw = props.animal?.foto_url || props.animal?.url_foto_principal || props.animal?.foto_principal || props.animal?.photoUrl;
  return resolveMediaUrl(raw);
});

const displayId = computed(() =>
  props.animal?.codigo_unico || props.animal?.numero_chip || props.animal?.microchip || 'Sin ID'
);

const displayEspecie = computed(() => {
  const especieRaw = props.animal?.especie || props.animal?.species || '';
  const especie = especieRaw.toString().toLowerCase();
  const labels = {
    perro: 'Perro',
    canino: 'Perro',
    gato: 'Gato',
    felino: 'Gato',
    equino: 'Equino',
    otro: 'Otro'
  };
  return labels[especie] || especieRaw || 'No especificada';
});

const displaySexo = computed(() => {
  const sexo = props.animal?.sexo || props.animal?.sex || '';
  const labels = { macho: 'Macho', hembra: 'Hembra', desconocido: 'Desconocido' };
  return labels[sexo?.toLowerCase()] || sexo || 'No especificado';
});

const displayEdad = computed(() => {
  if (props.animal?.edad_formateada) return props.animal.edad_formateada;
  const edadMeses = props.animal?.edad_aproximada || props.animal?.estimatedAge;
  if (!edadMeses) return 'Desconocida';
  const anios = Math.floor(edadMeses / 12);
  const meses = edadMeses % 12;
  const partes = [];
  if (anios > 0) partes.push(`${anios} año${anios > 1 ? 's' : ''}`);
  if (meses > 0) partes.push(`${meses} mes${meses > 1 ? 'es' : ''}`);
  return partes.join(' y ') || 'Desconocida';
});

const displayTamanio = computed(() => {
  const tamanio = props.animal?.tamanio || '';
  const labels = { pequenio: 'Pequeño', mediano: 'Mediano', grande: 'Grande', muy_grande: 'Muy grande' };
  return labels[tamanio] || tamanio || 'No especificado';
});

const displayEstadoSalud = computed(() => {
  const estado = props.animal?.estado_salud || props.animal?.healthCondition || '';
  const labels = { critico: 'Crítico', grave: 'Grave', estable: 'Estable', bueno: 'Bueno', excelente: 'Excelente' };
  return labels[estado?.toLowerCase()] || estado || 'No especificado';
});

const displayFechaRescate = computed(() => {
  const fecha = props.animal?.fecha_rescate || props.animal?.rescueDate;
  if (!fecha) return 'No registrada';
  try {
    const date = new Date(fecha);
    if (isNaN(date.getTime())) return fecha;
    return date.toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' });
  } catch {
    return fecha;
  }
});

function getStatusLabel(status) {
  const labels = {
    en_calle: 'En calle',
    en_refugio: 'En refugio',
    refugio: 'En refugio',
    en_adopcion: 'En adopción',
    adoptado: 'Adoptado',
    en_tratamiento: 'En tratamiento',
    fallecido: 'Fallecido'
  };
  return labels[status] || status || 'Sin estado';
}

function startEditing() {
  // Copiar datos actuales al formulario de edición
  editForm.value = {
    nombre: props.animal?.nombre || '',
    especie: props.animal?.especie || '',
    raza: props.animal?.raza || '',
    sexo: props.animal?.sexo || '',
    color: props.animal?.color || '',
    tamanio: props.animal?.tamanio || '',
    edad_aproximada: props.animal?.edad_aproximada || null,
    peso_actual: props.animal?.peso_actual || null,
    estado: props.animal?.estado || '',
    estado_salud: props.animal?.estado_salud || '',
    observaciones: props.animal?.observaciones || props.animal?.senias_particulares || ''
  };
  editError.value = '';
  isEditing.value = true;
}

async function handleSave() {
  if (!editForm.value.especie || !editForm.value.estado) {
    editError.value = 'Especie y estado son campos requeridos';
    return;
  }

  isSaving.value = true;
  editError.value = '';

  try {
    console.log('📝 Actualizando animal:', props.animal.id, editForm.value);

    const response = await animalsStore.updateAnimal(props.animal.id, editForm.value);

    console.log('✅ Animal actualizado:', response);

    if (window.$toast) {
      window.$toast.success(
        '¡Información actualizada!',
        `Los datos de ${editForm.value.nombre || 'el animal'} han sido actualizados correctamente.`
      );
    } else {
      alert('✅ Información actualizada exitosamente');
    }

    emit('updated', response);
    isEditing.value = false;

  } catch (error) {
    console.error('❌ Error al actualizar:', error);

    let errorMessage = 'No se pudo actualizar la información. Intente nuevamente.';
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const firstError = Object.values(error.response.data.errors)[0];
      errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
    }

    editError.value = errorMessage;

    if (window.$toast) {
      window.$toast.error('Error al actualizar', errorMessage);
    }
  } finally {
    isSaving.value = false;
  }
}

function handleClose() {
  if (isEditing.value) {
    isEditing.value = false;
    editError.value = '';
  } else {
    emit('close');
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #3366cc; /* govcolor-marine */
}

.modal-header h3 {
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 2rem;
  cursor: pointer;
  color: #737373; /* govcolor-dim-gray */
  width: 40px;
  height: 40px;
  border-radius: 50%;
  transition: all 0.2s;
}

.modal-close:hover {
  background: rgba(0,0,0,0.1);
  color: #4B4B4B; /* govcolor-tundora */
}

.modal-body {
  padding: 1.5rem;
}

.animal-details-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
}

.detail-image {
  grid-column: 1 / 3;
  width: 100%;
  height: 300px;
  border-radius: 8px;
  overflow: hidden;
  background: linear-gradient(135deg, #3366cc 0%, #004884 100%);
  /* govco-bg-marine to govco-bg-blue-dark */
}

.detail-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.detail-section {
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #c9e2ff; /* govco-bg-blue-light */
}

.full-width {
  grid-column: 1 / 3;
}

.section-subtitle {
  margin: 0 0 1rem 0;
  font-weight: 600;
}

.detail-list {
  margin: 0;
}

.detail-list dt {
  font-weight: 600;
  color: #4B4B4B; /* govcolor-tundora */
  margin-top: 0.75rem;
}

.detail-list dd {
  margin: 0.25rem 0 0 0;
  color: #737373; /* govcolor-dim-gray */
}

.status-badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  color: white;
}

/* Colores Gov.co para estados */
.status-en_calle {
  background-color: #A80521; /* govco-bg-shiraz - Rojo para urgencia */
}

.status-refugio {
  background-color: #FFAB00; /* govco-bg-gold - Amarillo para en proceso */
}

.status-en_refugio {
  background-color: #FFAB00;
}

.status-en_adopcion {
  background-color: #3366cc;
}

.status-en_tratamiento {
  background-color: #FFAB00;
}

.status-adoptado {
  background-color: #068460; /* govco-bg-elf-green - Verde para éxito */
}

.status-fallecido {
  background-color: #4B4B4B; /* govco-bg-tundora - Gris oscuro */
}

.certificate-link {
  text-decoration: none;
  font-weight: 600;
}

.certificate-link:hover {
  text-decoration: underline;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 2px solid #c9e2ff; /* govco-bg-blue-light */
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

.govco-bg-dim-gray {
  background-color: #737373;
}

.govco-bg-elf-green {
  background-color: #068460;
}

/* Clases de color Gov.co */
.govco-bg-blue-light {
  background-color: #c9e2ff;
}

.govco-bg-hawkes-blue {
  background-color: #F6F8F9;
}

.govcolor-marine {
  color: #3366cc;
}

.govcolor-blue-dark {
  color: #004884;
}

.govcolor-elf-green {
  color: #068460;
}

.govcolor-dim-gray {
  color: #737373;
}

.govcolor-tundora {
  color: #4B4B4B;
}

/* Estilos del formulario de edición */
.edit-form {
  padding: 0;
}

.edit-form .form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}

.edit-form .form-group {
  display: flex;
  flex-direction: column;
}

.edit-form .form-group.full-width {
  grid-column: 1 / 3;
}

.edit-form label {
  font-weight: 600;
  color: #4B4B4B;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.edit-form input,
.edit-form select,
.edit-form textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 1rem;
  line-height: 1.5;
  box-sizing: border-box;
  background: white;
}

.edit-form input:focus,
.edit-form select:focus,
.edit-form textarea:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.edit-form select {
  height: 44px;
}

.edit-form textarea {
  resize: vertical;
  min-height: 80px;
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: #FFEBEE;
  border-left: 4px solid #A80521;
  color: #A80521;
  border-radius: 4px;
  font-size: 0.9rem;
}

@media (max-width: 992px) {
  .animal-details-grid {
    grid-template-columns: 1fr;
  }
  .detail-image,
  .full-width {
    grid-column: 1;
  }
  .edit-form .form-grid {
    grid-template-columns: 1fr;
  }
  .edit-form .form-group.full-width {
    grid-column: 1;
  }
}

@media (max-width: 576px) {
  .modal-footer {
    flex-direction: column;
  }
  .govco-btn {
    width: 100%;
  }
}
</style>