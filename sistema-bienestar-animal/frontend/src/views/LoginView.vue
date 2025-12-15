<!-- src/views/LoginView.vue -->
<!-- Página de inicio de sesión - Solo SSO con SCI -->
<template>
  <div class="login-view">
    <div class="login-container">
      <!-- Panel izquierdo - Información -->
      <div class="login-info">
        <div class="login-info-content">
          <!-- Logo -->
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

      <!-- Panel derecho - SSO Login -->
      <div class="login-form-panel">
        <div class="login-form-container">
          <!-- Títulos -->
          <h2 class="login-title h3-tipografia-govco govcolor-blue-dark">
            Acceso al Sistema
          </h2>
          <p class="login-subtitle text2-tipografia-govco">
            Para ingresar al sistema, utiliza tu cuenta institucional de la Alcaldía.
          </p>

          <!-- Información SSO -->
          <div class="sso-info">
            <div class="sso-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#3366CC" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </div>
            <p class="sso-description">
              La autenticación se realiza a través del <strong>Sistema Central de Interoperabilidad (SCI)</strong>
              usando tu cuenta de Directorio Activo de la Alcaldía.
            </p>
          </div>

          <!-- Botón SSO -->
          <button
            @click="handleSsoLogin"
            class="btn-govco btn-govco-primary btn-block btn-sso"
            :disabled="isLoading"
          >
            <span v-if="!isLoading" class="btn-content">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
              </svg>
              Ingresar con cuenta institucional
            </span>
            <span v-else class="btn-content">
              <span class="spinner"></span>
              Conectando con SCI...
            </span>
          </button>

          <!-- Error general -->
          <p v-if="errorMessage" class="error-message-govco mt-2" style="text-align: center;">
            {{ errorMessage }}
          </p>

          <!-- Nota de ayuda -->
          <div class="help-note">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>
              Si tienes problemas para acceder, contacta a la mesa de ayuda de TI.
            </span>
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
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const isLoading = ref(false);
const errorMessage = ref('');

async function handleSsoLogin() {
  errorMessage.value = '';
  isLoading.value = true;

  try {
    // Redirigir al SCI para autenticación
    await authStore.redirectToSso();
  } catch (error) {
    errorMessage.value = authStore.error || 'No se pudo conectar con el sistema de autenticación. Intenta de nuevo.';
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

.login-logo img {
  height: 48px;
  width: auto;
  max-width: 100%;
}

.login-title {
  text-align: center;
  color: #004884;
  margin: 0 0 0.5rem 0;
}

.login-subtitle {
  text-align: center;
  color: #666;
  margin: 0 0 2rem 0;
  line-height: 1.5;
}

/* SSO Info */
.sso-info {
  text-align: center;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.sso-icon {
  margin-bottom: 1rem;
}

.sso-description {
  color: #4B4B4B;
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0;
}

/* Botón SSO */
.btn-sso {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 16px 24px;
  font-size: 1rem;
  font-weight: 600;
}

.btn-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-block {
  width: 100%;
}

.btn-govco-primary {
  background: #3366CC;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-govco-primary:hover:not(:disabled) {
  background: #004884;
}

.btn-govco-primary:disabled {
  background: #a0a0a0;
  cursor: not-allowed;
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

/* Nota de ayuda */
.help-note {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-top: 1.5rem;
  padding: 1rem;
  background: #FFF8E6;
  border: 1px solid #FFD700;
  border-radius: 4px;
  font-size: 0.85rem;
  color: #664D03;
}

.help-note svg {
  flex-shrink: 0;
  margin-top: 2px;
}

/* Error */
.error-message-govco {
  display: block;
  margin-top: 1rem;
  font-size: 0.9rem;
  color: #A80521;
}

.mt-2 {
  margin-top: 1rem;
}

/* Back link */
.back-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 2rem;
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
}
</style>
