<!-- src/views/LoginView.vue -->
<!-- Página de inicio de sesión con diseño GOV.CO -->
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
              <span>Registro y seguimiento de animales</span>
            </div>
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
              class="govco-logo"
            />
          </div>

          <h2 class="login-title h3-tipografia-govco">Iniciar Sesión</h2>
          <p class="login-subtitle text2-tipografia-govco">
            Ingresa tus credenciales para acceder al sistema
          </p>

          <!-- Alerta de error -->
          <div v-if="errorMessage" class="alert-govco alert-govco-danger">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="15" y1="9" x2="9" y2="15"></line>
              <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <span>{{ errorMessage }}</span>
          </div>

          <!-- Formulario de login -->
          <form @submit.prevent="handleLogin" class="login-form">
            <!-- Email -->
            <div class="form-group-govco">
              <label for="email" class="label-govco">
                Correo electrónico
                <span aria-required="true">*</span>
              </label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="input-govco input-with-icon"
                  :class="{ 'is-invalid': errors.email }"
                  placeholder="correo@ejemplo.gov.co"
                  required
                  autocomplete="email"
                />
              </div>
              <span v-if="errors.email" class="error-message-govco">{{ errors.email }}</span>
            </div>

            <!-- Password -->
            <div class="form-group-govco">
              <label for="password" class="label-govco">
                Contraseña
                <span aria-required="true">*</span>
              </label>
              <div class="input-wrapper">
                <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="input-govco input-with-icon"
                  :class="{ 'is-invalid': errors.password }"
                  placeholder="Ingresa tu contraseña"
                  required
                  autocomplete="current-password"
                />
                <button
                  type="button"
                  class="password-toggle"
                  @click="showPassword = !showPassword"
                  :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                >
                  <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                  </svg>
                </button>
              </div>
              <span v-if="errors.password" class="error-message-govco">{{ errors.password }}</span>
            </div>

            <!-- Recordar y olvidé contraseña -->
            <div class="form-options">
              <label class="checkbox-wrapper">
                <input type="checkbox" v-model="form.remember" />
                <span class="checkbox-label">Recordar sesión</span>
              </label>
              <a href="#" class="forgot-password" @click.prevent>¿Olvidaste tu contraseña?</a>
            </div>

            <!-- Botón de login -->
            <button
              type="submit"
              class="btn-govco btn-govco-primary btn-block"
              :disabled="isLoading"
            >
              <span v-if="isLoading" class="spinner"></span>
              <span v-else>Iniciar Sesión</span>
            </button>
          </form>

          <!-- Enlace de registro -->
          <div class="login-footer">
            <p class="text2-tipografia-govco">
              ¿No tienes cuenta?
              <a href="#" @click.prevent>Solicita acceso aquí</a>
            </p>
          </div>

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
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// Estado del formulario
const form = reactive({
  email: '',
  password: '',
  remember: false
});

const errors = reactive({
  email: '',
  password: ''
});

const showPassword = ref(false);
const isLoading = ref(false);
const errorMessage = ref('');

// Validación
function validate() {
  errors.email = '';
  errors.password = '';

  if (!form.email) {
    errors.email = 'El correo electrónico es requerido';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Ingresa un correo electrónico válido';
  }

  if (!form.password) {
    errors.password = 'La contraseña es requerida';
  } else if (form.password.length < 6) {
    errors.password = 'La contraseña debe tener al menos 6 caracteres';
  }

  return !errors.email && !errors.password;
}

// Login
async function handleLogin() {
  errorMessage.value = '';

  if (!validate()) {
    return;
  }

  isLoading.value = true;

  try {
    // Simular llamada a API
    await new Promise(resolve => setTimeout(resolve, 1500));

    // Credenciales de prueba
    if (form.email === 'admin@alcaldia.gov.co' && form.password === 'admin123') {
      // Login exitoso
      router.push('/dashboard');
    } else {
      errorMessage.value = 'Credenciales inválidas. Verifica tu correo y contraseña.';
    }
  } catch (error) {
    errorMessage.value = 'Error al iniciar sesión. Intenta nuevamente.';
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
