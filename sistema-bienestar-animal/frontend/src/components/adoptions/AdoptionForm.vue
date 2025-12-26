<!-- src/components/adoptions/AdoptionForm.vue -->
<template>
  <div class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-container">
      <header class="modal-header">
        <div>
          <h2 class="h4-tipografia-govco govcolor-blue-dark">
            Solicitud de Adopcion
          </h2>
          <p class="text2-tipografia-govco" v-if="animal">
            Animal: <strong>{{ animal.nombre || animal.name || animal.codigo_unico }}</strong>
          </p>
        </div>
        <button
          type="button"
          class="close-btn"
          aria-label="Cerrar"
          @click="$emit('close')"
        >
          &times;
        </button>
      </header>

      <form class="modal-body" @submit.prevent="onSubmit">
        <!-- Seccion: Datos Personales -->
        <fieldset class="form-section">
          <legend class="h5-tipografia-govco">Datos Personales del Adoptante</legend>

          <div class="form-grid">
            <!-- Nombres -->
            <div class="entradas-de-texto-govco">
              <label for="nombres">Nombres<span class="required">*</span></label>
              <input
                id="nombres"
                v-model="form.nombres"
                type="text"
                maxlength="100"
                required
                placeholder="Ingrese sus nombres"
              />
            </div>

            <!-- Apellidos -->
            <div class="entradas-de-texto-govco">
              <label for="apellidos">Apellidos<span class="required">*</span></label>
              <input
                id="apellidos"
                v-model="form.apellidos"
                type="text"
                maxlength="100"
                required
                placeholder="Ingrese sus apellidos"
              />
            </div>

            <!-- Tipo de documento -->
            <div class="entradas-de-texto-govco">
              <label for="tipo_documento">Tipo de documento<span class="required">*</span></label>
              <select
                id="tipo_documento"
                v-model="form.tipo_documento"
                class="input-govco"
                required
              >
                <option value="">Seleccione...</option>
                <option value="CC">Cedula de Ciudadania</option>
                <option value="CE">Cedula de Extranjeria</option>
                <option value="TI">Tarjeta de Identidad</option>
                <option value="PA">Pasaporte</option>
                <option value="PEP">Permiso Especial de Permanencia</option>
              </select>
            </div>

            <!-- Numero de documento -->
            <div class="entradas-de-texto-govco">
              <label for="numero_documento">Numero de documento<span class="required">*</span></label>
              <input
                id="numero_documento"
                v-model="form.numero_documento"
                type="text"
                maxlength="20"
                required
                placeholder="Ej: 1234567890"
              />
            </div>

            <!-- Fecha de nacimiento -->
            <div class="entradas-de-texto-govco">
              <label for="fecha_nacimiento">Fecha de nacimiento<span class="required">*</span></label>
              <input
                id="fecha_nacimiento"
                v-model="form.fecha_nacimiento"
                type="date"
                :max="maxFechaNacimiento"
                required
              />
            </div>

            <!-- Telefono -->
            <div class="entradas-de-texto-govco">
              <label for="telefono">Telefono de contacto<span class="required">*</span></label>
              <input
                id="telefono"
                v-model="form.telefono"
                type="tel"
                maxlength="20"
                required
                placeholder="Ej: 3001234567"
              />
            </div>

            <!-- Email -->
            <div class="entradas-de-texto-govco full-width">
              <label for="email">Correo electronico<span class="required">*</span></label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                maxlength="100"
                required
                placeholder="correo@ejemplo.com"
              />
            </div>
          </div>
        </fieldset>

        <!-- Seccion: Informacion de Vivienda -->
        <fieldset class="form-section">
          <legend class="h5-tipografia-govco">Informacion de Vivienda</legend>

          <div class="form-grid">
            <!-- Direccion -->
            <div class="entradas-de-texto-govco full-width">
              <label for="direccion">Direccion de residencia<span class="required">*</span></label>
              <input
                id="direccion"
                v-model="form.direccion"
                type="text"
                maxlength="300"
                required
                placeholder="Calle/Carrera, numero, barrio, ciudad"
              />
            </div>

            <!-- Tipo de vivienda -->
            <div class="entradas-de-texto-govco">
              <label for="tipo_vivienda">Tipo de vivienda<span class="required">*</span></label>
              <select
                id="tipo_vivienda"
                v-model="form.tipo_vivienda"
                class="input-govco"
                required
              >
                <option value="">Seleccione...</option>
                <option value="casa">Casa</option>
                <option value="apartamento">Apartamento</option>
                <option value="finca">Finca</option>
                <option value="otro">Otro</option>
              </select>
            </div>

            <!-- Tiene patio -->
            <div class="entradas-de-texto-govco">
              <label for="tiene_patio">Tiene patio o zona verde?</label>
              <select
                id="tiene_patio"
                v-model="form.tiene_patio"
                class="input-govco"
              >
                <option value="false">No</option>
                <option value="true">Si</option>
              </select>
            </div>

            <!-- Numero de personas en el hogar -->
            <div class="entradas-de-texto-govco">
              <label for="num_personas_hogar">Personas en el hogar</label>
              <input
                id="num_personas_hogar"
                v-model.number="form.num_personas_hogar"
                type="number"
                min="1"
                max="20"
                placeholder="Ej: 4"
              />
            </div>
          </div>
        </fieldset>

        <!-- Seccion: Experiencia con Animales -->
        <fieldset class="form-section">
          <legend class="h5-tipografia-govco">Experiencia y Motivacion</legend>

          <div class="form-grid">
            <!-- Experiencia con animales -->
            <div class="entradas-de-texto-govco full-width">
              <label for="experiencia_animales">Experiencia previa con animales</label>
              <textarea
                id="experiencia_animales"
                v-model="form.experiencia_animales"
                rows="3"
                maxlength="1000"
                class="textarea-govco"
                placeholder="Describa su experiencia previa con mascotas (tipos de animales, tiempo que los tuvo, etc.)"
              ></textarea>
            </div>

            <!-- Motivo de adopcion -->
            <div class="entradas-de-texto-govco full-width">
              <label for="motivo_adopcion">Por que desea adoptar?<span class="required">*</span></label>
              <textarea
                id="motivo_adopcion"
                v-model="form.motivo_adopcion"
                rows="3"
                maxlength="1000"
                class="textarea-govco"
                required
                placeholder="Explique sus razones para querer adoptar a este animal"
              ></textarea>
            </div>

            <!-- Descripcion del hogar -->
            <div class="entradas-de-texto-govco full-width">
              <label for="descripcion_hogar">Descripcion de su hogar</label>
              <textarea
                id="descripcion_hogar"
                v-model="form.descripcion_hogar"
                rows="2"
                maxlength="500"
                class="textarea-govco"
                placeholder="Describa las condiciones de su hogar para recibir al animal"
              ></textarea>
            </div>
          </div>
        </fieldset>

        <!-- Seccion: Documentos Adjuntos -->
        <fieldset class="form-section">
          <legend class="h5-tipografia-govco">Documentos Requeridos</legend>

          <div class="form-grid">
            <!-- Copia de cedula -->
            <div class="file-upload-field">
              <label for="copia_cedula">
                Copia del documento de identidad<span class="required">*</span>
              </label>
              <div class="file-input-wrapper">
                <input
                  id="copia_cedula"
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png"
                  required
                  @change="handleFileChange('copia_cedula', $event)"
                />
                <div class="file-info" v-if="archivos.copia_cedula">
                  <span class="file-name">{{ archivos.copia_cedula.name }}</span>
                  <button type="button" class="remove-file" @click="removeFile('copia_cedula')">
                    &times;
                  </button>
                </div>
              </div>
              <small class="file-hint">Formatos: PDF, JPG, PNG. Max: 5MB</small>
            </div>

            <!-- Comprobante de domicilio -->
            <div class="file-upload-field">
              <label for="comprobante_domicilio">
                Comprobante de domicilio<span class="required">*</span>
              </label>
              <div class="file-input-wrapper">
                <input
                  id="comprobante_domicilio"
                  type="file"
                  accept=".pdf,.jpg,.jpeg,.png"
                  required
                  @change="handleFileChange('comprobante_domicilio', $event)"
                />
                <div class="file-info" v-if="archivos.comprobante_domicilio">
                  <span class="file-name">{{ archivos.comprobante_domicilio.name }}</span>
                  <button type="button" class="remove-file" @click="removeFile('comprobante_domicilio')">
                    &times;
                  </button>
                </div>
              </div>
              <small class="file-hint">Recibo de servicios publicos o extracto bancario (max 3 meses)</small>
            </div>
          </div>
        </fieldset>

        <!-- Seccion: Compromisos -->
        <fieldset class="form-section">
          <legend class="h5-tipografia-govco">Compromisos</legend>

          <div class="checkbox-group">
            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.acepta_visita_seguimiento"
              />
              <span>Acepto recibir visitas de seguimiento posteriores a la adopcion</span>
            </label>

            <label class="checkbox-label">
              <input
                type="checkbox"
                v-model="form.acceptTerms"
                required
              />
              <span>
                Confirmo que la informacion suministrada es veridica y me comprometo a
                brindar cuidado responsable al animal, incluyendo alimentacion adecuada,
                atencion veterinaria y un ambiente seguro.
                <span class="required">*</span>
              </span>
            </label>
          </div>
        </fieldset>

        <!-- Mensaje de error -->
        <div v-if="errorMessage" class="alert-govco alert-govco-danger">
          {{ errorMessage }}
        </div>

        <!-- Acciones -->
        <div class="modal-actions">
          <button
            type="button"
            class="btn-govco btn-govco-outline"
            @click="$emit('close')"
            :disabled="loading"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="btn-govco btn-govco-primary"
            :disabled="loading || !isFormValid"
          >
            <span v-if="!loading">Enviar Solicitud</span>
            <span v-else>Enviando...</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { submitAdoptionRequest } from '@/services/adoptionService';

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

// Estado del formulario
const form = reactive({
  // Datos personales
  nombres: '',
  apellidos: '',
  tipo_documento: '',
  numero_documento: '',
  fecha_nacimiento: '',
  telefono: '',
  email: '',
  // Vivienda
  direccion: '',
  tipo_vivienda: '',
  tiene_patio: 'false',
  num_personas_hogar: null,
  // Experiencia
  experiencia_animales: '',
  motivo_adopcion: '',
  descripcion_hogar: '',
  // Compromisos
  acepta_visita_seguimiento: true,
  acceptTerms: false,
});

// Archivos
const archivos = reactive({
  copia_cedula: null,
  comprobante_domicilio: null,
});

// Estado de carga local y errores
const localLoading = ref(false);
const errorMessage = ref('');

// Fecha maxima de nacimiento (18 anos atras)
const maxFechaNacimiento = computed(() => {
  const date = new Date();
  date.setFullYear(date.getFullYear() - 18);
  return date.toISOString().split('T')[0];
});

// Validacion del formulario
const isFormValid = computed(() => {
  return (
    form.nombres &&
    form.apellidos &&
    form.tipo_documento &&
    form.numero_documento &&
    form.fecha_nacimiento &&
    form.telefono &&
    form.email &&
    form.direccion &&
    form.tipo_vivienda &&
    form.motivo_adopcion &&
    archivos.copia_cedula &&
    archivos.comprobante_domicilio &&
    form.acceptTerms
  );
});

// Manejo de archivos
function handleFileChange(field, event) {
  const file = event.target.files[0];
  if (file) {
    // Validar tamano (5MB max)
    if (file.size > 5 * 1024 * 1024) {
      errorMessage.value = `El archivo ${field === 'copia_cedula' ? 'de cedula' : 'de domicilio'} excede el tamano maximo de 5MB`;
      event.target.value = '';
      return;
    }
    archivos[field] = file;
    errorMessage.value = '';
  }
}

function removeFile(field) {
  archivos[field] = null;
  const input = document.getElementById(field);
  if (input) input.value = '';
}

// Envio del formulario
async function onSubmit() {
  if (!isFormValid.value) return;

  errorMessage.value = '';
  localLoading.value = true;

  try {
    // Preparar datos del adoptante
    const adoptanteData = {
      nombres: form.nombres,
      apellidos: form.apellidos,
      tipo_documento: form.tipo_documento,
      numero_documento: form.numero_documento,
      fecha_nacimiento: form.fecha_nacimiento,
      telefono: form.telefono,
      email: form.email,
      direccion: form.direccion,
      tipo_vivienda: form.tipo_vivienda,
      tiene_patio: form.tiene_patio === 'true',
      experiencia_animales: form.experiencia_animales || null,
      num_personas_hogar: form.num_personas_hogar || null,
    };

    // Datos de la solicitud
    const solicitudData = {
      motivo_adopcion: form.motivo_adopcion,
      descripcion_hogar: form.descripcion_hogar || null,
      acepta_visita_seguimiento: form.acepta_visita_seguimiento,
    };

    // Archivos
    const archivosData = {
      copia_cedula: archivos.copia_cedula,
      comprobante_domicilio: archivos.comprobante_domicilio,
    };

    // Enviar solicitud
    const response = await submitAdoptionRequest(
      adoptanteData,
      props.animal?.id,
      solicitudData,
      archivosData
    );

    // Emitir evento de exito
    emit('submitted', response);

  } catch (error) {
    console.error('Error al enviar solicitud:', error);

    if (error.response?.data?.errors) {
      // Errores de validacion
      const errors = error.response.data.errors;
      const firstError = Object.values(errors)[0];
      errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Error al enviar la solicitud. Por favor intente nuevamente.';
    }
  } finally {
    localLoading.value = false;
  }
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 20px;
}

.modal-container {
  background: #ffffff;
  border-radius: 12px;
  max-width: 900px;
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: #f8fafc;
  border-radius: 12px 12px 0 0;
}

.modal-header h2 {
  margin: 0;
}

.modal-header p {
  margin: 4px 0 0;
  color: #64748b;
}

.close-btn {
  border: none;
  background: none;
  cursor: pointer;
  font-size: 1.75rem;
  color: #94a3b8;
  line-height: 1;
  padding: 0;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
}

.close-btn:hover {
  color: #475569;
  background: #e2e8f0;
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

/* Secciones del formulario */
.form-section {
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
}

.form-section legend {
  padding: 0 8px;
  margin-left: -8px;
  color: #004884;
  font-weight: 600;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.full-width {
  grid-column: 1 / -1;
}

/* Campos de texto */
.entradas-de-texto-govco {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.entradas-de-texto-govco label {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.required {
  color: #dc2626;
  margin-left: 2px;
}

.entradas-de-texto-govco input,
.entradas-de-texto-govco select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.entradas-de-texto-govco input:focus,
.entradas-de-texto-govco select:focus {
  outline: none;
  border-color: #004884;
  box-shadow: 0 0 0 3px rgba(0, 72, 132, 0.1);
}

.textarea-govco {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
  min-height: 80px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.textarea-govco:focus {
  outline: none;
  border-color: #004884;
  box-shadow: 0 0 0 3px rgba(0, 72, 132, 0.1);
}

/* Campos de archivo */
.file-upload-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.file-upload-field label {
  font-weight: 500;
  color: #374151;
  font-size: 0.875rem;
}

.file-input-wrapper {
  position: relative;
}

.file-input-wrapper input[type="file"] {
  width: 100%;
  padding: 10px;
  border: 2px dashed #d1d5db;
  border-radius: 6px;
  background: #f9fafb;
  cursor: pointer;
  transition: border-color 0.2s;
}

.file-input-wrapper input[type="file"]:hover {
  border-color: #004884;
}

.file-info {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 6px;
  padding: 8px 12px;
  background: #ecfdf5;
  border-radius: 4px;
  border: 1px solid #a7f3d0;
}

.file-name {
  font-size: 0.875rem;
  color: #065f46;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.remove-file {
  border: none;
  background: none;
  color: #dc2626;
  cursor: pointer;
  font-size: 1.25rem;
  padding: 0;
  line-height: 1;
}

.file-hint {
  color: #6b7280;
  font-size: 0.75rem;
}

/* Checkboxes */
.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  margin-top: 2px;
  cursor: pointer;
  accent-color: #004884;
}

.checkbox-label span {
  font-size: 0.9rem;
  color: #4b5563;
  line-height: 1.4;
}

/* Alertas */
.alert-govco-danger {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #991b1b;
  padding: 12px 16px;
  border-radius: 6px;
  margin-top: 16px;
  font-size: 0.9rem;
}

/* Acciones */
.modal-actions {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.btn-govco {
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-govco-primary {
  background: #004884;
  color: white;
  border: none;
}

.btn-govco-primary:hover:not(:disabled) {
  background: #003366;
}

.btn-govco-primary:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

.btn-govco-outline {
  background: transparent;
  color: #004884;
  border: 2px solid #004884;
}

.btn-govco-outline:hover:not(:disabled) {
  background: #f0f7ff;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-backdrop {
    padding: 10px;
  }

  .modal-container {
    max-height: 95vh;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .full-width {
    grid-column: 1;
  }

  .modal-actions {
    flex-direction: column;
  }

  .modal-actions button {
    width: 100%;
  }
}
</style>
