<!-- src/views/LoginView.vue -->
<!-- Página de "inicio de sesión" pero usando selección de rol de prueba -->
<template>
  <div class="login-view">
    <div class="login-container">
      <!-- Panel izquierdo - Información -->
      <div class="login-info">
        <div class="login-info-content">
         <!-- Logo          -->
          <div class="login-logo">
            <img
              src="https://cdn.www.gov.co/assets/images/logo.svg"
              alt="GOV.CO"
            />
          </div>
 
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
          <!-- Logo 
          <div class="login-logo">
            <img
              src="https://cdn.www.gov.co/assets/images/logo.svg"
              alt="GOV.CO"
            />
          </div>
          -->

          <!-- Títulos -->
          <h2 class="login-title h3-tipografia-govco govcolor-blue-dark">
            Ingresar al sistema
          </h2>
          <p class="login-subtitle text2-tipografia-govco">
            Ingresa tus credenciales para acceder al sistema.
          </p>

          <!-- Formulario de login -->
          <form v-if="!showMfaForm" @submit.prevent="handleLogin" class="login-form">
            <!-- Correo electrónico -->
            <div class="form-group-govco">
              <label for="email" class="label-govco">
                Correo electrónico
                <span aria-required="true">*</span>
              </label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                  </svg>
                </span>
                <input
                  type="email"
                  id="email"
                  v-model="email"
                  class="input-govco input-with-icon"
                  placeholder="ejemplo@correo.com"
                  required
                  :class="{ 'is-invalid': emailError }"
                />
              </div>
              <span v-if="emailError" class="error-message-govco">{{ emailError }}</span>
            </div>

            <!-- Contraseña -->
            <div class="form-group-govco">
              <label for="password" class="label-govco">
                Contraseña
                <span aria-required="true">*</span>
              </label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                  </svg>
                </span>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  v-model="password"
                  class="input-govco input-with-icon"
                  placeholder="••••••••"
                  required
                  :class="{ 'is-invalid': passwordError }"
                />
                <button
                  type="button"
                  class="password-toggle"
                  @click="showPassword = !showPassword"
                  tabindex="-1"
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
              <span v-if="passwordError" class="error-message-govco">{{ passwordError }}</span>
            </div>

            <!-- Opciones adicionales -->
            <div class="form-options">
              <div class="checkbox-wrapper">
                <input
                  type="checkbox"
                  id="remember"
                  v-model="rememberMe"
                />
                <label for="remember" class="checkbox-label">Recordarme</label>
              </div>
              <a href="#" class="forgot-password" @click.prevent="handleForgotPassword">
                ¿Olvidaste tu contraseña?
              </a>
            </div>

            <!-- Botón -->
            <button
              type="submit"
              class="btn-govco btn-govco-primary btn-block"
              :disabled="isLoading"
            >
              <span v-if="!isLoading">Ingresar</span>
              <span v-else>
                <span class="spinner"></span>
                Ingresando...
              </span>
            </button>

            <!-- Error general -->
            <p v-if="errorMessage" class="error-message-govco mt-2" style="text-align: center;">
              {{ errorMessage }}
            </p>
          </form>

          <!-- Formulario MFA -->
          <form v-else @submit.prevent="handleMfaVerify" class="login-form">
            <div class="mfa-info">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#3366CC" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <h3>Verificación de seguridad</h3>
              <p>Hemos enviado un código de 6 dígitos a tu correo electrónico.</p>
            </div>

            <div class="form-group-govco">
              <label for="mfaCode" class="label-govco">
                Código de verificación
                <span aria-required="true">*</span>
              </label>
              <input
                type="text"
                id="mfaCode"
                v-model="mfaCode"
                class="input-govco mfa-input"
                placeholder="000000"
                maxlength="6"
                pattern="[0-9]*"
                inputmode="numeric"
                autocomplete="one-time-code"
              />
            </div>

            <!-- Botones MFA -->
            <button
              type="submit"
              class="btn-govco btn-govco-primary btn-block"
              :disabled="isLoading"
            >
              <span v-if="!isLoading">Verificar código</span>
              <span v-else>
                <span class="spinner"></span>
                Verificando...
              </span>
            </button>

            <button
              type="button"
              class="btn-govco btn-govco-secondary btn-block mt-2"
              @click="cancelMfa"
              :disabled="isLoading"
            >
              Cancelar
            </button>

            <!-- Error general -->
            <p v-if="errorMessage" class="error-message-govco mt-2" style="text-align: center;">
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
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

// Form fields
const email = ref('');
const password = ref('');
const rememberMe = ref(false);
const showPassword = ref(false);

// MFA fields
const showMfaForm = ref(false);
const mfaCode = ref('');
const mfaEmail = ref('');

// Loading and errors
const isLoading = ref(false);
const errorMessage = ref('');
const emailError = ref('');
const passwordError = ref('');

// Validación de email
function validateEmail(emailValue) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(emailValue);
}

// Limpiar errores
function clearErrors() {
  errorMessage.value = '';
  emailError.value = '';
  passwordError.value = '';
}

async function handleLogin() {
  clearErrors();

  let hasError = false;

  // Validar email
  if (!email.value.trim()) {
    emailError.value = 'El correo electrónico es obligatorio.';
    hasError = true;
  } else if (!validateEmail(email.value)) {
    emailError.value = 'Ingresa un correo electrónico válido.';
    hasError = true;
  }

  // Validar contraseña
  if (!password.value) {
    passwordError.value = 'La contraseña es obligatoria.';
    hasError = true;
  } else if (password.value.length < 6) {
    passwordError.value = 'La contraseña debe tener al menos 6 caracteres.';
    hasError = true;
  }

  if (hasError) {
    return;
  }

  try {
    isLoading.value = true;

    // Llamar al API real de autenticación
    const result = await authStore.login(email.value, password.value);

    if (result.requiresMfa) {
      // El usuario requiere verificación MFA
      showMfaForm.value = true;
      mfaEmail.value = email.value;
      return;
    }

    // Login exitoso - redirigir según rol
    redirectByRole();

  } catch (error) {
    // Mostrar mensaje de error del backend
    errorMessage.value = authStore.error || 'Credenciales incorrectas. Por favor, verifica tu correo y contraseña.';
  } finally {
    isLoading.value = false;
  }
}

async function handleMfaVerify() {
  if (!mfaCode.value || mfaCode.value.length !== 6) {
    errorMessage.value = 'Ingresa el código de 6 dígitos enviado a tu correo.';
    return;
  }

  try {
    isLoading.value = true;
    await authStore.verifyMfa(mfaCode.value);

    // Login exitoso después de MFA - redirigir según rol
    redirectByRole();

  } catch (error) {
    errorMessage.value = authStore.error || 'Código MFA inválido o expirado.';
  } finally {
    isLoading.value = false;
  }
}

function redirectByRole() {
  const userRole = authStore.userRole?.toLowerCase();
  console.log('🚀 redirectByRole - userRole:', userRole);
  console.log('🔑 localStorage sba-role:', localStorage.getItem('sba-role'));

  // Roles que van al dashboard administrativo
  const adminRoles = ['administrador', 'admin', 'director', 'veterinario', 'operador', 'coordinador'];
  const isAdmin = adminRoles.some(r => userRole?.includes(r));
  console.log('👤 ¿Es admin?:', isAdmin);

  if (isAdmin) {
    console.log('➡️ Redirigiendo a /dashboard...');
    router.push('/dashboard');
  } else {
    console.log('➡️ Redirigiendo a /adopciones...');
    router.push('/adopciones');
  }
}

function cancelMfa() {
  showMfaForm.value = false;
  mfaCode.value = '';
  mfaEmail.value = '';
  errorMessage.value = '';
}

function handleForgotPassword() {
  if (window.$toast) {
    window.$toast.info('Recuperar contrasena', 'Para recuperar tu contrasena, contacta al administrador del sistema.');
  } else {
    alert('Para recuperar tu contraseña, contacta al administrador del sistema.');
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

.login-logo img {
  height: 48px;
  width: auto;
  max-width: 100%;
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

.help-text-govco {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.85rem;
  color: #666;
  line-height: 1.4;
}

.mt-2 {
  margin-top: 1rem;
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

/* MFA Form */
.mfa-info {
  text-align: center;
  margin-bottom: 1.5rem;
}

.mfa-info svg {
  margin-bottom: 1rem;
}

.mfa-info h3 {
  color: #004884;
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
}

.mfa-info p {
  color: #666;
  margin: 0;
  font-size: 0.9rem;
}

.mfa-input {
  text-align: center;
  font-size: 1.5rem;
  letter-spacing: 0.5rem;
  font-weight: 600;
}

.btn-govco-secondary {
  background: white;
  color: #3366CC;
  border: 1px solid #3366CC;
}

.btn-govco-secondary:hover {
  background: #f5f7fb;
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
