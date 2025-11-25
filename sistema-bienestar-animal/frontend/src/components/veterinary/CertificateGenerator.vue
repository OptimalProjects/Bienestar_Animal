<template>
  <section class="certificate-generator">
    <div class="form-header">
      <h2 class="h2-tipografia-govco">Generación de certificados</h2>
      <p class="text2-tipografia-govco">
        Genere certificados de vacunación, esterilización o salud general.
      </p>
    </div>

    <form @submit.prevent="generateCertificate">
      <div class="form-section">
        <h3 class="h5-tipografia-govco section-title">Datos del certificado</h3>

        <div class="form-grid">
          <!-- Animal -->
          <div class="input-like-govco">
            <label for="animal">
              Animal<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select
                id="animal"
                v-model="form.animalId"
                class="browser-default"
              >
                <option value="" disabled>Seleccione un animal</option>
                <option
                  v-for="animal in animals"
                  :key="animal.id"
                  :value="animal.id"
                >
                  {{ animal.name }} ({{ animal.microchip }})
                </option>
              </select>
            </div>
          </div>

          <!-- Tipo certificado -->
          <div class="input-like-govco">
            <label for="type">
              Tipo de certificado<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select
                id="type"
                v-model="form.type"
                class="browser-default"
              >
                <option value="" disabled>Seleccione tipo</option>
                <option value="vaccination">Vacunación</option>
                <option value="sterilization">Esterilización</option>
                <option value="health">Salud general</option>
              </select>
            </div>
          </div>

          <!-- Veterinario -->
          <div class="input-like-govco">
            <label for="veterinarian">
              Veterinario responsable<span aria-required="true">*</span>
            </label>
            <div class="desplegable-govco" data-type="basic">
              <select
                id="veterinarian"
                v-model="form.veterinarianId"
                class="browser-default"
              >
                <option value="" disabled>Seleccione veterinario</option>
                <option
                  v-for="vet in veterinarians"
                  :key="vet.id"
                  :value="vet.id"
                >
                  {{ vet.name }} ({{ vet.license }})
                </option>
              </select>
            </div>
          </div>

          <!-- Observaciones -->
          <div class="entradas-de-texto-govco full-width">
            <label for="notes">Observaciones adicionales</label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              placeholder="Información adicional que desea incluir en el certificado."
            />
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button
          type="button"
          class="govco-btn govco-bg-concrete"
          @click="resetForm"
        >
          Limpiar
        </button>
        <button type="submit" class="govco-btn govco-bg-elf-green">
          Generar certificado PDF
        </button>
      </div>
    </form>
  </section>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';

const animals = ref([
  { id: 1, name: 'Firulais', microchip: 'MC123456789' },
  { id: 2, name: 'Michi', microchip: 'MC987654321' }
]);

const veterinarians = ref([
  { id: 1, name: 'Dr. Juan Pérez', license: '12345' },
  { id: 2, name: 'Dra. María López', license: '67890' }
]);

const form = reactive({
  animalId: '',
  type: '',
  veterinarianId: '',
  notes: ''
});

function resetForm() {
  Object.assign(form, {
    animalId: '',
    type: '',
    veterinarianId: '',
    notes: ''
  });
}

async function generateCertificate() {
  if (!form.animalId || !form.type || !form.veterinarianId) {
    alert('Debe completar los campos obligatorios');
    return;
  }

  try {
    const payload = { ...form };
    console.log('Generando certificado:', payload);

    // TODO: Llamar al backend para generar el PDF
    // const pdfBlob = await generateCertificateApi(payload);
    // descargar o abrir en nueva pestaña

    alert('Certificado generado (simulado). Integrar generación de PDF en backend.');
  } catch (e) {
    console.error('Error generando certificado:', e);
    alert('Error al generar el certificado');
  }
}

onMounted(() => {
  if (window.GOVCo?.init) {
    const dropdowns = document.querySelectorAll('.desplegable-govco');
    dropdowns.forEach(dd => {
      window.GOVCo.init(dd.parentElement);
    });
  }
});
</script>

<style scoped>
.certificate-generator {
  max-width: 900px;
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
.full-width {
  grid-column: 1 / 3;
}
.entradas-de-texto-govco input,
.entradas-de-texto-govco textarea,
.desplegable-govco select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d0d0d0;
  border-radius: 4px;
  font-size: 1rem;
}
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
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
@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .full-width {
    grid-column: 1 / 2;
  }
}
</style>
