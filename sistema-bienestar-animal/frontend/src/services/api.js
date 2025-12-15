/**
 * API Client Base
 * Configuracion centralizada de axios para todas las llamadas al backend
 */

import axios from 'axios';

// Crear instancia de axios con configuracion base
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Generar trace ID unico
const generateTraceId = () => {
  const date = new Date().toISOString().slice(0, 10).replace(/-/g, '');
  const random = Math.random().toString(36).substring(2, 10);
  return `${date}-${random}`;
};

// Interceptor de request
api.interceptors.request.use(
  (config) => {
    // Agregar token de autenticacion si existe
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    // Agregar trace ID para auditoria
    config.headers['X-Trace-ID'] = generateTraceId();

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor de response
api.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    const { response } = error;

    if (response) {
      switch (response.status) {
        case 401:
          // Token expirado o invalido
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          localStorage.removeItem('sba-role');
          // Redirigir a login (SSO) si no estamos ya ahi o en callback
          if (!window.location.pathname.includes('/login') &&
              !window.location.pathname.includes('/sso/callback')) {
            window.location.href = '/login';
          }
          break;

        case 403:
          // Sin permisos
          console.error('Acceso denegado:', response.data?.message);
          break;

        case 422:
          // Error de validacion
          console.error('Error de validacion:', response.data?.errors);
          break;

        case 500:
          // Error del servidor
          console.error('Error del servidor:', response.data?.message);
          break;
      }
    }

    return Promise.reject(error);
  }
);

// Metodos de utilidad
export const setAuthToken = (token) => {
  if (token) {
    localStorage.setItem('auth_token', token);
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    localStorage.removeItem('auth_token');
    delete api.defaults.headers.common['Authorization'];
  }
};

export const getAuthToken = () => {
  return localStorage.getItem('auth_token');
};

export const isAuthenticated = () => {
  return !!getAuthToken();
};

// Exportar instancia de axios configurada
export default api;
