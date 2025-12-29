<template>
  <div class="sso-callback">
    <div class="card">
      <!-- Estado: Cargando -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <h2>Autenticando...</h2>
        <p class="status">Validando credenciales con SSO</p>
      </div>

      <!-- Estado: Error -->
      <div v-else-if="error" class="error-state">
        <div class="error-icon">⚠️</div>
        <h2>Error de Autenticación</h2>
        <p class="error">{{ errorMessage }}</p>
        <button @click="redirectToLogin" class="btn-primary">
          Volver al Login
        </button>
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

const loading = ref(true);
const error = ref(false);
const errorMessage = ref('');

// Configuración: Elige el método que prefieras
const USE_DIRECT_CALLBACK = true; // true = método directo, false = método en dos pasos

onMounted(async () => {
  await handleSSOCallback();
});

/**
 * Maneja el callback SSO
 */
async function handleSSOCallback() {
  try {
    // Obtener el jwt_token de los parámetros de la URL
    // Nota: Joel especificó que el parámetro es "jwt_token"
    const jwtToken = route.query.jwt_token || route.query.jwt;

    if (!jwtToken) {
      throw new Error('No se recibió el token de autenticación (jwt_token)');
    }

    console.log('🔐 Token SSO recibido, validando...');

    // Método 1: Callback directo (RECOMENDADO según especificación)
    if (USE_DIRECT_CALLBACK) {
      await loginDirecto(jwtToken);
    } 
    // Método 2: Validar + Login en dos pasos (como tus compañeros)
    else {
      await loginEnDosPasos(jwtToken);
    }

  } catch (err) {
    console.error('❌ Error en SSO callback:', err);
    error.value = true;
    errorMessage.value = err.response?.data?.message || err.message || 'Error al autenticar';
    loading.value = false;
  }
}

/**
 * MÉTODO 1: Login directo (un solo endpoint)
 * Más simple y directo
 */
async function loginDirecto(jwtToken) {
  console.log('📍 Usando método directo');
  
  const result = await authStore.loginWithSSO(jwtToken);

  if (result.success) {
    console.log('✅ Login SSO exitoso');
    
    // Pequeño delay para mostrar el spinner
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Redirigir al dashboard
    router.push({ name: 'dashboard' });
  } else {
    throw new Error('Error al procesar la autenticación');
  }
}

/**
 * MÉTODO 2: Login en dos pasos (validar + login)
 * Similar al método de tus compañeros
 */
async function loginEnDosPasos(jwtToken) {
  console.log('📍 Usando método en dos pasos');

  // Paso 1: Validar el token
  const validationResult = await authStore.validateSSOToken(jwtToken);

  if (!validationResult.valid) {
    throw new Error('Token SSO inválido o expirado');
  }

  console.log('✅ Token validado:', validationResult.user_data);

  // Paso 2: Hacer login con el token validado
  const loginResult = await authStore.loginWithSSOHeader(jwtToken);

  if (loginResult.success) {
    console.log('✅ Login completado');
    
    await new Promise(resolve => setTimeout(resolve, 500));
    router.push({ name: 'dashboard' });
  } else {
    throw new Error('Error al completar el login');
  }
}

/**
 * Redirige al login en caso de error
 */
function redirectToLogin() {
  router.push({ name: 'login' });
}
</script>

<style scoped>
.sso-callback {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.card {
  background: white;
  border-radius: 12px;
  padding: 48px 40px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  max-width: 420px;
  width: 100%;
  text-align: center;
}

.loading-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-state h2 {
  margin: 0;
  font-size: 24px;
  color: #333;
}

.status {
  margin: 0;
  font-size: 14px;
  color: #64748b;
}

.error-state {
  gap: 20px;
}

.error-icon {
  font-size: 48px;
}

.error-state h2 {
  margin: 0;
  font-size: 24px;
  color: #e53e3e;
}

.error {
  margin: 0;
  font-size: 14px;
  color: #d32f2f;
  line-height: 1.5;
}

.btn-primary {
  padding: 12px 32px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: #5568d3;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}
</style>