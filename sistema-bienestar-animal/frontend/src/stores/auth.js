/**
 * Auth Store
 * Estado de autenticacion con Pinia
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api, { setAuthToken } from '@/services/api';

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null);
  const token = ref(localStorage.getItem('auth_token') || null);
  const permisos = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const requiresMfa = ref(false);
  const mfaUserId = ref(null);

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const userRole = computed(() => user.value?.rol || null);
  const userName = computed(() => {
    if (!user.value) return '';
    return `${user.value.nombres} ${user.value.apellidos}`;
  });

  // Actions
  async function login(email, password) {
    loading.value = true;
    error.value = null;
    requiresMfa.value = false;

    try {
      const response = await api.post('/auth/login', { email, password });
      const data = response.data.data;

      if (data.requiere_mfa) {
        requiresMfa.value = true;
        mfaUserId.value = data.usuario_id;
        return { requiresMfa: true };
      }

      // Login exitoso
      console.log('🔐 Login exitoso - Datos:', data);

      setToken(data.access_token);
      user.value = data.usuario;
      permisos.value = data.permisos || [];
      localStorage.setItem('user', JSON.stringify(data.usuario));

      // Sincronizar rol con useRol composable
      const rolCode = data.usuario.rol_codigo?.toLowerCase() || '';
      console.log('🎭 Rol código del backend:', data.usuario.rol_codigo, '→ lowercase:', rolCode);

      const rolMap = {
        'admin': 'admin_sistema',
        'administrador': 'admin_sistema',
        'director': 'director',
        'operador': 'operador_rescate',
        'veterinario': 'medico_veterinario',
        'coordinador': 'coordinador_adopciones',
      };
      const mappedRole = rolMap[rolCode] || 'ciudadano';
      console.log('🗺️ Rol mapeado:', mappedRole);

      localStorage.setItem('sba-role', mappedRole);
      console.log('💾 Guardado sba-role:', localStorage.getItem('sba-role'));

      return { success: true };
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al iniciar sesion';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function verifyMfa(codigo) {
    loading.value = true;
    error.value = null;

    try {
      const response = await api.post('/auth/mfa/verify', {
        usuario_id: mfaUserId.value,
        codigo,
      });
      const data = response.data.data;

      // Login exitoso
      setToken(data.access_token);
      user.value = data.usuario;
      permisos.value = data.permisos || [];
      localStorage.setItem('user', JSON.stringify(data.usuario));
      requiresMfa.value = false;
      mfaUserId.value = null;

      // Sincronizar rol con useRol composable
      const rolCode = data.usuario.rol_codigo?.toLowerCase() || '';
      const rolMap = {
        'admin': 'admin_sistema',
        'administrador': 'admin_sistema',
        'director': 'director',
        'operador': 'operador_rescate',
        'veterinario': 'medico_veterinario',
        'coordinador': 'coordinador_adopciones',
      };
      const mappedRole = rolMap[rolCode] || 'ciudadano';
      localStorage.setItem('sba-role', mappedRole);

      return { success: true };
    } catch (err) {
      error.value = err.response?.data?.message || 'Codigo MFA invalido';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function logout() {
    try {
      await api.post('/auth/logout');
    } catch (err) {
      // Ignorar errores de logout
    } finally {
      clearAuth();
    }
  }

  async function refreshToken() {
    try {
      const response = await api.post('/auth/refresh');
      const data = response.data.data;
      setToken(data.access_token);
      return true;
    } catch (err) {
      clearAuth();
      return false;
    }
  }

  async function fetchUser() {
    if (!token.value) return null;

    try {
      const response = await api.get('/auth/me');
      const data = response.data.data;
      user.value = data.usuario;
      permisos.value = data.permisos || [];
      localStorage.setItem('user', JSON.stringify(data.usuario));
      return data.usuario;
    } catch (err) {
      clearAuth();
      return null;
    }
  }

  function setToken(newToken) {
    token.value = newToken;
    setAuthToken(newToken);
  }

  function clearAuth() {
    token.value = null;
    user.value = null;
    permisos.value = [];
    requiresMfa.value = false;
    mfaUserId.value = null;
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    localStorage.removeItem('sba-role');
    setAuthToken(null);
  }

  function hasPermission(permiso) {
    if (!permisos.value) return false;
    // Admin tiene todos los permisos
    if (userRole.value?.toLowerCase() === 'administrador') return true;
    return permisos.value.includes(permiso);
  }

  function hasRole(rol) {
    if (!userRole.value) return false;
    if (Array.isArray(rol)) {
      return rol.some(r => r.toLowerCase() === userRole.value.toLowerCase());
    }
    return rol.toLowerCase() === userRole.value.toLowerCase();
  }

  // Inicializar usuario desde localStorage
  function initAuth() {
    const storedUser = localStorage.getItem('user');
    if (storedUser && token.value) {
      try {
        user.value = JSON.parse(storedUser);
        // Refrescar datos del usuario
        fetchUser();
      } catch {
        clearAuth();
      }
    }
  }

  return {
    // State
    user,
    token,
    permisos,
    loading,
    error,
    requiresMfa,
    // Getters
    isAuthenticated,
    userRole,
    userName,
    // Actions
    login,
    verifyMfa,
    logout,
    refreshToken,
    fetchUser,
    clearAuth,
    hasPermission,
    hasRole,
    initAuth,
  };
});

export default useAuthStore;
