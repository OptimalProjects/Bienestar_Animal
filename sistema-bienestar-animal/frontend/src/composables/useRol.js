// src/composables/useRole.js
import { ref } from 'vue';

export const ROLES = {
  CIUDADANO: 'ciudadano',
  OPERADOR_RESCATE: 'operador_rescate',
  MEDICO_VETERINARIO: 'medico_veterinario',
  COORDINADOR_ADOPCIONES: 'coordinador_adopciones',
  ADMIN_SISTEMA: 'admin_sistema',
  DIRECTOR: 'director',
};

// Rol inicial: lo que haya guardado o ciudadano por defecto
const storedRole = localStorage.getItem('sba-role') || ROLES.CIUDADANO;
const role = ref(storedRole);

export function useRole() {
  function setRole(newRole) {
    role.value = newRole;
    localStorage.setItem('sba-role', newRole);
  }

  function hasRole(required) {
    if (!required || !required.length) return true;
    return required.includes(role.value);
  }

  const isInternalRole = () => role.value !== ROLES.CIUDADANO;

  return {
    role,
    setRole,
    hasRole,
    isInternalRole,
  };
}

// Para usar desde el router (fuera de <script setup>)
export function getCurrentRole() {
  return role.value;
}
