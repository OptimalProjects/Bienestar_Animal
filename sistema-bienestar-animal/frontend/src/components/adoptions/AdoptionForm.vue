<!-- src/components/adoptions/AdoptionForm.vue -->
<template>
  <div class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-container">
      <header class="modal-header">
        <h2 class="h4-tipografia-govco">
          Solicitud de adopción para {{ animal?.name }}
        </h2>
        <button
          type="button"
          class="close-btn"
          aria-label="Cerrar"
          @click="$emit('close')"
        >
          ✕
        </button>
      </header>

      <form class="modal-body" @submit.prevent="onSubmit">
        <div class="form-grid">
          <div class="entradas-de-texto-govco">
            <label for="fullName">Nombre completo<span>*</span></label>
            <input
              id="fullName"
              v-model="form.fullName"
              type="text"
              required
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="idNumber">Documento de identidad<span>*</span></label>
            <input
              id="idNumber"
              v-model="form.idNumber"
              type="text"
              required
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="phone">Teléfono de contacto<span>*</span></label>
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              required
            />
          </div>

          <div class="entradas-de-texto-govco">
            <label for="email">Correo electrónico<span>*</span></label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
            />
          </div>

          <div class="entradas-de-texto-govco full-width">
            <label for="address">Dirección de residencia<span>*</span></label>
            <input
              id="address"
              v-model="form.address"
              type="text"
              required
            />
          </div>

          <DesplegableGovco
            id="housingType-dropdown"
            v-model="form.housingType"
            label="Tipo de vivienda"
            :options="housingOptions"
            placeholder="Selecciona una opción"
            :required="true"
            width="100%"
          />

          <DesplegableGovco
            id="otherPets-dropdown"
            v-model="form.otherPets"
            label="¿Tienes otras mascotas?"
            :options="petsOptions"
            placeholder="Selecciona una opción"
            width="100%"
          />

          <div class="entradas-de-texto-govco full-width">
            <label for="motivation">¿Por qué quieres adoptar?<span>*</span></label>
            <textarea
              id="motivation"
              v-model="form.motivation"
              rows="4"
              required
              class="textarea-full"
            />
          </div>
        </div>

        <div class="terms">
          <label>
            <input
              type="checkbox"
              v-model="form.acceptTerms"
              required
            />
            Confirmo que la información suministrada es verídica y acepto el
            proceso de adopción responsable.
          </label>
        </div>

        <div class="modal-actions">
          <ButtonGovco
            type="button"
            variant="outline"
            label="Cancelar"
            width="auto"
            @click="$emit('close')"
          />
          <ButtonGovco
            type="submit"
            variant="fill"
            :disabled="loading"
            width="auto"
          >
            <span v-if="!loading">Enviar solicitud</span>
            <span v-else>Enviando...</span>
          </ButtonGovco>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue';
import DesplegableGovco from '../common/DesplegableGovco.vue';
import ButtonGovco from '../common/ButtonGovCo.vue';

const props = defineProps({
  animal: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close', 'submitted']);

const form = reactive({
  fullName: '',
  idNumber: '',
  phone: '',
  email: '',
  address: '',
  housingType: '',
  otherPets: '',
  motivation: '',
  acceptTerms: false,
});

// Opciones para los dropdowns
const housingOptions = [
  { value: 'casa-propia', text: 'Casa propia' },
  { value: 'arriendo', text: 'Arriendo' },
  { value: 'apartamento', text: 'Apartamento' },
  { value: 'finca', text: 'Finca' }
];

const petsOptions = [
  { value: 'no', text: 'No' },
  { value: 'si', text: 'Sí' }
];

// Si quieres precargar nombre/email del SSO del ciudadano, aquí sería
watch(
  () => props.animal,
  () => {
    // podrías resetear campos específicos si cambia el animal
  }
);

function onSubmit() {
  if (!form.acceptTerms) return;

  emit('submitted', { ...form });
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
}

.modal-container {
  background: #ffffff;
  border-radius: 12px;
  max-width: 800px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 16px 20px;
  border-bottom: 1px solid #dde3ea;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 16px 20px 20px;
  overflow-y: auto;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px 16px;
}

.full-width {
  grid-column: 1 / 3;
}

.terms {
  margin-top: 16px;
}

.modal-actions {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.close-btn {
  border: none;
  background: none;
  cursor: pointer;
  font-size: 1.5rem;
  color: #666;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
}

.close-btn:hover {
  color: #333;
}

/* Estilos para inputs y textarea */
.entradas-de-texto-govco input,
.entradas-de-texto-govco textarea {
  width: 100%;
  box-sizing: border-box;
}

.textarea-full {
  min-height: 80px;
  resize: vertical;
  padding: 12px;
  border: 1px solid #dde3ea;
  border-radius: 4px;
  font-family: inherit;
  font-size: 1rem;
}

.textarea-full:focus {
  outline: none;
  border-color: #004884;
  box-shadow: 0 0 0 3px rgba(0, 72, 132, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .full-width {
    grid-column: 1;
  }
}
</style>