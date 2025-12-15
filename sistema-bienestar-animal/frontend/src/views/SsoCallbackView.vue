<!-- src/views/SsoCallbackView.vue -->
<!-- Página que procesa el callback del SSO (SCI) -->
<template>
  <div class="sso-callback-view">
    <div class="sso-callback-container">
      <!-- Loading state -->
      <div v-if="isProcessing" class="sso-status">
        <div class="spinner-large"></div>
        <h2>Verificando autenticación...</h2>
        <p>Por favor espera mientras validamos tu sesión con el SCI.</p>
      </div>

      <!-- Error state -->
      <div v-else-if="errorMessage" class="sso-status sso-error">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#A80521" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="15" y1="9" x2="9" y2="15"></line>
          <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        <h2>Error de autenticación</h2>
        <p>{{ errorMessage }}</p>
        <button @click="goToLogin" class="btn-govco btn-govco-primary">
          Volver a intentar
        </button>
      </div>

      <!-- Success state (brief, before redirect) -->
      <div v-else-if="isSuccess" class="sso-status sso-success">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#069169" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <h2>¡Bienvenido!</h2>
        <p>Redirigiendo al sistema...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const isProcessing = ref(true);
const isSuccess = ref(false);
const errorMessage = ref('');

onMounted(async () => {
  await processCallback();
});

async function processCallback() {
  try {
    // Obtener el token del query parameter o del hash (diferentes SSO usan diferentes métodos)
    let token = route.query.token;

    // Si no está en query, buscar en hash (formato: #token=xxx o #access_token=xxx)
    if (!token && route.hash) {
      const hashParams = new URLSearchParams(route.hash.substring(1));
      token = hashParams.get('token') || hashParams.get('access_token');
    }

    // También verificar si viene en el header (algunos SSO lo pasan así)
    if (!token) {
      // Intentar obtener de la URL completa
      const urlParams = new URLSearchParams(window.location.search);
      token = urlParams.get('token');
    }

    if (!token) {
      throw new Error('No se recibió el token de autenticación del SCI');
    }

    console.log('🔑 Token SSO recibido, procesando...');

    // Procesar el token con el backend
    const result = await authStore.handleSsoCallback(token);

    if (result.success) {
      isSuccess.value = true;
      isProcessing.value = false;

      // Pequeña pausa para mostrar mensaje de éxito
      await new Promise(resolve => setTimeout(resolve, 1000));

      // Redirigir según el rol
      redirectByRole();
    }
  } catch (error) {
    console.error('❌ Error procesando callback SSO:', error);
    errorMessage.value = error.message || 'Error al procesar la autenticación';
    isProcessing.value = false;
  }
}

function redirectByRole() {
  const userRole = authStore.userRole?.toLowerCase();
  console.log('🚀 redirectByRole - userRole:', userRole);

  // Roles que van al dashboard administrativo
  const adminRoles = ['administrador', 'admin', 'director', 'veterinario', 'operador', 'coordinador'];
  const isAdmin = adminRoles.some(r => userRole?.includes(r));

  if (isAdmin) {
    router.push('/dashboard');
  } else {
    router.push('/');
  }
}

function goToLogin() {
  router.push('/login');
}
</script>

<style scoped>
.sso-callback-view {
  min-height: calc(100vh - 150px);
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f7fb;
  padding: 40px 20px;
}

.sso-callback-container {
  background: white;
  border-radius: 12px;
  padding: 60px 40px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  width: 100%;
  text-align: center;
}

.sso-status {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.sso-status h2 {
  color: #004884;
  margin: 0;
  font-size: 1.5rem;
}

.sso-status p {
  color: #666;
  margin: 0;
  font-size: 1rem;
}

.sso-error h2 {
  color: #A80521;
}

.sso-success h2 {
  color: #069169;
}

.spinner-large {
  width: 64px;
  height: 64px;
  border: 4px solid rgba(51, 102, 204, 0.2);
  border-top-color: #3366CC;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.btn-govco {
  margin-top: 16px;
  padding: 12px 32px;
  font-size: 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-govco-primary {
  background: #3366CC;
  color: white;
}

.btn-govco-primary:hover {
  background: #004884;
}
</style>
