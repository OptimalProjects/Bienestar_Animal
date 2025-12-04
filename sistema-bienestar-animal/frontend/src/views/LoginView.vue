<!-- src/views/LoginView.vue -->
<!-- Página de "inicio de sesión" pero usando selección de rol de prueba -->
<template>
  <div class="login-view">
    <div class="login-container">
      <!-- Panel izquierdo - Información -->
      <div class="login-info">
        <div class="login-info-content">
          <h1 class="login-info-title">Sistema de Bienestar Animal</h1>
          <p class="login-info-description">
            Plataforma de gestión integral para el cuidado y protección
            de los animales en nuestro municipio.
          </p>

          <div class="login-info-features">
            <div class="feature-item">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
              <span>Gestión de denuncias y rescates</span>
            </div>
            <div class="feature-item">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
              <span>Atención veterinaria integral</span>
            </div>
            <div class="feature-item">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
              <span>Proceso de adopciones responsables</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Panel derecho - Formulario -->
      <div class="login-form-panel">
        <div class="login-form-container">
          <!-- Logo -->
          <div class="login-logo">
            <img
              src="https://cdn.www.gov.co/layout/v4/images/logo-gov-co.svg"
              alt="GOV.CO"
            />
          </div>

          <!-- Títulos -->
          <h2 class="login-title h3-tipografia-govco govcolor-blue-dark">
            Ingresar al sistema
          </h2>
          <p class="login-subtitle text2-tipografia-govco">
            Para esta versión demo, selecciona un rol para simular el acceso
            con SSO y permisos RBAC.
          </p>

          <!-- Formulario de "login" por rol -->
          <form @submit.prevent="handleLogin" class="login-form">
            <div class="form-group-govco">
              <label for="role" class="label-govco">
                Selecciona tu rol
                <span aria-required="true">*</span>
              </label>
              <select
                id="role"
                v-model="selectedRole"
                class="input-govco"
              >
                <option
                  v-for="opt in roleOptions"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
              <span class="help-text-govco">
                Este selector reemplaza temporalmente el SSO (HU-024 / HU-025) para pruebas de interfaz.
              </span>
            </div>

            <!-- Botón -->
            <button
              type="submit"
              class="btn-govco btn-govco-primary"
              :disabled="isLoading"
            >
              <span v-if="!isLoading">Ingresar</span>
              <span v-else>Ingresando...</span>
            </button>

            <!-- Error -->
            <p v-if="errorMessage" class="error-message-govco mt-2">
              {{ errorMessage }}
            </p>
          </form>

          <!-- Volver al inicio -->
          <router-link to="/" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="19" y1="12" x2="5" y2="12"></line>
              <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Volver al inicio
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useRole, ROLES } from '../composables/useRol.js';

const router = useRouter();
const { setRole } = useRole();

const selectedRole = ref(ROLES.CIUDADANO);
const isLoading = ref(false);
const errorMessage = ref('');

const roleOptions = [
  { value: ROLES.CIUDADANO, label: 'Ciudadano (portal público)' },
  { value: ROLES.OPERADOR_RESCATE, label: 'Operador de Rescate' },
  { value: ROLES.MEDICO_VETERINARIO, label: 'Médico Veterinario' },
  { value: ROLES.COORDINADOR_ADOPCIONES, label: 'Coordinador de Adopciones' },
  { value: ROLES.ADMIN_SISTEMA, label: 'Administrador del Sistema' },
  { value: ROLES.DIRECTOR, label: 'Director' },
];

async function handleLogin() {
  errorMessage.value = '';

  if (!selectedRole.value) {
    errorMessage.value = 'Selecciona un rol para continuar.';
    return;
  }

  try {
    isLoading.value = true;

    // Simulación de delay de login
    await new Promise(resolve => setTimeout(resolve, 400));

    setRole(selectedRole.value);

    // Redirección según rol:
    if (selectedRole.value === ROLES.CIUDADANO) {
      router.push('/adopciones');
    } else {
      router.push('/dashboard');
    }
  } catch (error) {
    errorMessage.value = 'Error al iniciar sesión de prueba.';
  } finally {
    isLoading.value = false;
  }
}
</script>


<style scoped>
.login-view {
  min-height: calc(100vh - 150px);
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f7fb;
  padding: 40px 20px;
}

.login-container {
  display: flex;
  max-width: 1000px;
  width: 100%;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
}

/* Panel de información */
.login-info {
  flex: 1;
  background: linear-gradient(135deg, #004884 0%, #3366CC 100%);
  color: white;
  padding: 60px 40px;
  display: flex;
  align-items: center;
}

.login-info-title {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 1rem 0;
}

.login-info-description {
  font-size: 1rem;
  opacity: 0.9;
  margin: 0 0 2rem 0;
  line-height: 1.6;
}

.login-info-features {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 0.95rem;
}

.feature-item svg {
  flex-shrink: 0;
  color: #FFD700;
}

/* Panel de formulario */
.login-form-panel {
  flex: 1;
  padding: 60px 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.login-form-container {
  width: 100%;
  max-width: 360px;
}

.login-logo {
  text-align: center;
  margin-bottom: 2rem;
}

.govco-logo {
  height: 40px;
  width: auto;
}

.login-title {
  text-align: center;
  color: #004884;
  margin: 0 0 0.5rem 0;
}

.login-subtitle {
  text-align: center;
  margin: 0 0 2rem 0;
}

/* Formulario */
.login-form {
  margin-bottom: 1.5rem;
}

.form-group-govco {
  margin-bottom: 1.25rem;
}

.label-govco {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.label-govco span[aria-required="true"] {
  color: #A80521;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 12px;
  color: #999;
  pointer-events: none;
}

.input-with-icon {
  padding-left: 44px !important;
}

.input-govco {
  width: 100%;
  padding: 12px 16px;
  font-size: 1rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.input-govco:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.15);
}

.input-govco.is-invalid {
  border-color: #A80521;
}

.password-toggle {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  padding: 4px;
  cursor: pointer;
  color: #666;
}

.password-toggle:hover {
  color: #3366CC;
}

.error-message-govco {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #A80521;
}

/* Opciones del formulario */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 8px;
}

.checkbox-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-wrapper input {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #3366CC;
}

.checkbox-label {
  font-size: 0.9rem;
  color: #4B4B4B;
}

.forgot-password {
  font-size: 0.9rem;
  color: #3366CC;
  text-decoration: none;
}

.forgot-password:hover {
  text-decoration: underline;
}

/* Botón de login */
.btn-block {
  width: 100%;
  padding: 14px 24px;
}

.spinner {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Alerta */
.alert-govco {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 1.5rem;
}

/* Footer */
.login-footer {
  text-align: center;
  padding-top: 1.5rem;
  border-top: 1px solid #e0e0e0;
}

.login-footer a {
  color: #3366CC;
  text-decoration: none;
  font-weight: 600;
}

.login-footer a:hover {
  text-decoration: underline;
}

.back-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 1.5rem;
  color: #666;
  text-decoration: none;
  font-size: 0.9rem;
}

.back-link:hover {
  color: #3366CC;
}

/* Responsive */
@media (max-width: 767.98px) {
  .login-container {
    flex-direction: column;
  }

  .login-info {
    padding: 40px 30px;
  }

  .login-info-title {
    font-size: 1.5rem;
  }

  .login-form-panel {
    padding: 40px 30px;
  }

  .form-options {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
