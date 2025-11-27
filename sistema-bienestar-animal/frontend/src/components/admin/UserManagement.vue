<!-- src/components/admin/UserManagement.vue -->
<!-- HU-022: Gestionar Usuarios y Roles del Sistema -->
<template>
  <section class="user-management">
    <div class="management-header">
      <div class="header-left">
        <h2 class="h3-tipografia-govco">Gestion de Usuarios</h2>
        <p class="text2-tipografia-govco">
          Administre usuarios, roles y permisos del sistema
        </p>
      </div>
      <div class="header-right">
        <button type="button" class="btn-add-user" @click="openModal('create')">
          + Nuevo Usuario
        </button>
      </div>
    </div>

    <!-- Filtros y Busqueda -->
    <div class="filters-bar">
      <div class="search-box">
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Buscar por nombre, email o documento..."
          class="search-input"
        />
        <span class="search-icon">=</span>
      </div>
      <div class="filter-group">
        <select v-model="filterRole" class="filter-select">
          <option value="">Todos los roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.name }}
          </option>
        </select>
      </div>
      <div class="filter-group">
        <select v-model="filterStatus" class="filter-select">
          <option value="">Todos los estados</option>
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
          <option value="bloqueado">Bloqueado</option>
        </select>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards">
      <div class="stat-card">
        <span class="stat-value">{{ stats.total }}</span>
        <span class="stat-label">Total Usuarios</span>
      </div>
      <div class="stat-card active">
        <span class="stat-value">{{ stats.active }}</span>
        <span class="stat-label">Activos</span>
      </div>
      <div class="stat-card inactive">
        <span class="stat-value">{{ stats.inactive }}</span>
        <span class="stat-label">Inactivos</span>
      </div>
      <div class="stat-card blocked">
        <span class="stat-value">{{ stats.blocked }}</span>
        <span class="stat-label">Bloqueados</span>
      </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="users-table-container">
      <table class="users-table">
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Documento</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>MFA</th>
            <th>Ultimo Acceso</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in filteredUsers" :key="user.id">
            <td class="user-cell">
              <div class="user-avatar">{{ getInitials(user.nombre) }}</div>
              <div class="user-info">
                <span class="user-name">{{ user.nombre }}</span>
                <span class="user-email">{{ user.email }}</span>
              </div>
            </td>
            <td>{{ user.documento }}</td>
            <td>
              <span class="role-badge" :class="getRoleClass(user.rol)">
                {{ user.rolNombre }}
              </span>
            </td>
            <td>
              <span class="status-badge" :class="user.estado">
                {{ capitalize(user.estado) }}
              </span>
            </td>
            <td>
              <span class="mfa-badge" :class="{ enabled: user.mfaEnabled }">
                {{ user.mfaEnabled ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td>{{ formatDate(user.ultimoAcceso) }}</td>
            <td class="actions-cell">
              <button type="button" class="action-btn view" @click="viewUser(user)" title="Ver detalle">
                =A
              </button>
              <button type="button" class="action-btn edit" @click="openModal('edit', user)" title="Editar">
                
              </button>
              <button
                type="button"
                class="action-btn toggle"
                @click="toggleUserStatus(user)"
                :title="user.estado === 'activo' ? 'Desactivar' : 'Activar'"
              >
                {{ user.estado === 'activo' ? '=' : '=' }}
              </button>
              <button type="button" class="action-btn reset" @click="resetPassword(user)" title="Resetear contrasena">
                =
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="filteredUsers.length === 0" class="no-results">
        <p>No se encontraron usuarios con los filtros aplicados</p>
      </div>
    </div>

    <!-- Paginacion -->
    <div class="pagination">
      <button
        type="button"
        @click="currentPage--"
        :disabled="currentPage === 1"
        class="page-btn"
      >
        Anterior
      </button>
      <span class="page-info">
        Pagina {{ currentPage }} de {{ totalPages }}
      </span>
      <button
        type="button"
        @click="currentPage++"
        :disabled="currentPage === totalPages"
        class="page-btn"
      >
        Siguiente
      </button>
    </div>

    <!-- Seccion de Roles -->
    <div class="roles-section">
      <div class="roles-header">
        <h3 class="h5-tipografia-govco">Gestion de Roles</h3>
        <button type="button" class="btn-add-role" @click="openRoleModal">
          + Nuevo Rol
        </button>
      </div>
      <div class="roles-grid">
        <div v-for="role in roles" :key="role.id" class="role-card">
          <div class="role-header">
            <span class="role-name">{{ role.name }}</span>
            <span class="role-count">{{ role.userCount }} usuarios</span>
          </div>
          <p class="role-description">{{ role.description }}</p>
          <div class="role-permissions">
            <span
              v-for="perm in role.permissions.slice(0, 3)"
              :key="perm"
              class="permission-tag"
            >
              {{ perm }}
            </span>
            <span v-if="role.permissions.length > 3" class="more-permissions">
              +{{ role.permissions.length - 3 }} mas
            </span>
          </div>
          <div class="role-actions">
            <button type="button" class="role-btn" @click="editRole(role)">Editar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Usuario -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ modalMode === 'create' ? 'Nuevo Usuario' : 'Editar Usuario' }}</h3>
          <button type="button" class="close-btn" @click="closeModal">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveUser">
            <div class="form-row">
              <div class="form-group">
                <label>Nombre Completo *</label>
                <input
                  type="text"
                  v-model="userForm.nombre"
                  required
                  class="form-input"
                />
              </div>
              <div class="form-group">
                <label>Tipo Documento *</label>
                <select v-model="userForm.tipoDocumento" required class="form-select">
                  <option value="CC">Cedula de Ciudadania</option>
                  <option value="CE">Cedula de Extranjeria</option>
                  <option value="PA">Pasaporte</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Numero de Documento *</label>
                <input
                  type="text"
                  v-model="userForm.documento"
                  required
                  class="form-input"
                />
              </div>
              <div class="form-group">
                <label>Email Institucional *</label>
                <input
                  type="email"
                  v-model="userForm.email"
                  required
                  class="form-input"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Telefono</label>
                <input
                  type="tel"
                  v-model="userForm.telefono"
                  class="form-input"
                />
              </div>
              <div class="form-group">
                <label>Rol *</label>
                <select v-model="userForm.rol" required class="form-select">
                  <option value="">Seleccione un rol</option>
                  <option v-for="role in roles" :key="role.id" :value="role.id">
                    {{ role.name }}
                  </option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Dependencia</label>
                <select v-model="userForm.dependencia" class="form-select">
                  <option value="">Seleccione</option>
                  <option value="direccion">Direccion General</option>
                  <option value="operaciones">Operaciones</option>
                  <option value="veterinaria">Area Veterinaria</option>
                  <option value="adopciones">Area de Adopciones</option>
                  <option value="denuncias">Atencion Denuncias</option>
                </select>
              </div>
              <div class="form-group">
                <label>Estado</label>
                <select v-model="userForm.estado" class="form-select">
                  <option value="activo">Activo</option>
                  <option value="inactivo">Inactivo</option>
                </select>
              </div>
            </div>

            <!-- Configuracion MFA -->
            <div class="mfa-section">
              <h4>Autenticacion Multi-Factor (MFA)</h4>
              <label class="checkbox-label">
                <input type="checkbox" v-model="userForm.mfaEnabled" />
                Habilitar MFA para este usuario
              </label>
              <p class="mfa-note">
                * Obligatorio para usuarios con rol de Administrador
              </p>
            </div>

            <!-- SSO Info -->
            <div class="sso-section">
              <h4>Integracion SSO</h4>
              <p class="sso-info">
                Este usuario sera sincronizado con sci-auth y sci-rbac para autenticacion unica (SSO).
              </p>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="closeModal">
                Cancelar
              </button>
              <button type="submit" class="btn-save">
                {{ modalMode === 'create' ? 'Crear Usuario' : 'Guardar Cambios' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Ver Usuario -->
    <div v-if="showViewModal" class="modal-overlay" @click.self="closeViewModal">
      <div class="modal-content view-modal">
        <div class="modal-header">
          <h3>Detalle de Usuario</h3>
          <button type="button" class="close-btn" @click="closeViewModal">×</button>
        </div>
        <div class="modal-body">
          <div class="user-detail-header">
            <div class="user-avatar-large">{{ getInitials(selectedUser?.nombre) }}</div>
            <div class="user-detail-info">
              <h4>{{ selectedUser?.nombre }}</h4>
              <p>{{ selectedUser?.email }}</p>
              <span class="status-badge" :class="selectedUser?.estado">
                {{ capitalize(selectedUser?.estado) }}
              </span>
            </div>
          </div>

          <div class="detail-grid">
            <div class="detail-item">
              <span class="detail-label">Documento</span>
              <span class="detail-value">{{ selectedUser?.tipoDocumento }} {{ selectedUser?.documento }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Rol</span>
              <span class="detail-value">{{ selectedUser?.rolNombre }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Dependencia</span>
              <span class="detail-value">{{ selectedUser?.dependencia || 'Sin asignar' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Telefono</span>
              <span class="detail-value">{{ selectedUser?.telefono || 'No registrado' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">MFA</span>
              <span class="detail-value">{{ selectedUser?.mfaEnabled ? 'Habilitado' : 'No habilitado' }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Ultimo Acceso</span>
              <span class="detail-value">{{ formatDate(selectedUser?.ultimoAcceso) }}</span>
            </div>
            <div class="detail-item">
              <span class="detail-label">Fecha Creacion</span>
              <span class="detail-value">{{ formatDate(selectedUser?.fechaCreacion) }}</span>
            </div>
          </div>

          <div class="activity-section">
            <h4>Actividad Reciente</h4>
            <div class="activity-list">
              <div v-for="(activity, index) in userActivities" :key="index" class="activity-item">
                <span class="activity-icon">{{ activity.icon }}</span>
                <span class="activity-text">{{ activity.text }}</span>
                <span class="activity-time">{{ activity.time }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const searchQuery = ref('');
const filterRole = ref('');
const filterStatus = ref('');
const currentPage = ref(1);
const pageSize = 10;

const showModal = ref(false);
const showViewModal = ref(false);
const modalMode = ref('create');
const selectedUser = ref(null);

// Stats
const stats = ref({
  total: 45,
  active: 38,
  inactive: 5,
  blocked: 2
});

// Roles disponibles
const roles = ref([
  {
    id: 'admin',
    name: 'Administrador',
    description: 'Acceso completo al sistema',
    userCount: 3,
    permissions: ['usuarios', 'reportes', 'configuracion', 'auditoria']
  },
  {
    id: 'director',
    name: 'Director',
    description: 'Visualizacion de KPIs y reportes',
    userCount: 2,
    permissions: ['dashboard', 'reportes', 'estadisticas']
  },
  {
    id: 'veterinario',
    name: 'Veterinario',
    description: 'Gestion de salud animal',
    userCount: 12,
    permissions: ['animales', 'vacunacion', 'historias_clinicas']
  },
  {
    id: 'inspector',
    name: 'Inspector',
    description: 'Atencion de denuncias y rescates',
    userCount: 15,
    permissions: ['denuncias', 'rescates', 'operativos']
  },
  {
    id: 'adoptante',
    name: 'Gestor de Adopciones',
    description: 'Proceso de adopciones',
    userCount: 8,
    permissions: ['adopciones', 'seguimiento', 'animales_lectura']
  },
  {
    id: 'consulta',
    name: 'Solo Consulta',
    description: 'Visualizacion sin edicion',
    userCount: 5,
    permissions: ['lectura_general']
  }
]);

// Usuarios
const users = ref([
  {
    id: 1,
    nombre: 'Carlos Alberto Ramirez',
    email: 'caramirez@gov.co',
    tipoDocumento: 'CC',
    documento: '79.852.147',
    telefono: '3001234567',
    rol: 'admin',
    rolNombre: 'Administrador',
    estado: 'activo',
    mfaEnabled: true,
    dependencia: 'direccion',
    ultimoAcceso: '2024-11-27T10:30:00',
    fechaCreacion: '2023-01-15'
  },
  {
    id: 2,
    nombre: 'Maria Elena Lopez',
    email: 'melopez@gov.co',
    tipoDocumento: 'CC',
    documento: '52.741.963',
    telefono: '3109876543',
    rol: 'director',
    rolNombre: 'Director',
    estado: 'activo',
    mfaEnabled: true,
    dependencia: 'direccion',
    ultimoAcceso: '2024-11-27T08:15:00',
    fechaCreacion: '2023-02-20'
  },
  {
    id: 3,
    nombre: 'Juan David Martinez',
    email: 'jdmartinez@gov.co',
    tipoDocumento: 'CC',
    documento: '80.123.456',
    telefono: '3205551234',
    rol: 'veterinario',
    rolNombre: 'Veterinario',
    estado: 'activo',
    mfaEnabled: false,
    dependencia: 'veterinaria',
    ultimoAcceso: '2024-11-26T16:45:00',
    fechaCreacion: '2023-05-10'
  },
  {
    id: 4,
    nombre: 'Ana Patricia Gonzalez',
    email: 'apgonzalez@gov.co',
    tipoDocumento: 'CC',
    documento: '51.987.654',
    telefono: '3157894561',
    rol: 'inspector',
    rolNombre: 'Inspector',
    estado: 'activo',
    mfaEnabled: false,
    dependencia: 'denuncias',
    ultimoAcceso: '2024-11-27T09:00:00',
    fechaCreacion: '2023-06-05'
  },
  {
    id: 5,
    nombre: 'Roberto Carlos Vega',
    email: 'rcvega@gov.co',
    tipoDocumento: 'CC',
    documento: '79.456.123',
    telefono: '3001112233',
    rol: 'adoptante',
    rolNombre: 'Gestor de Adopciones',
    estado: 'inactivo',
    mfaEnabled: false,
    dependencia: 'adopciones',
    ultimoAcceso: '2024-11-15T14:20:00',
    fechaCreacion: '2023-08-12'
  },
  {
    id: 6,
    nombre: 'Sandra Milena Torres',
    email: 'smtorres@gov.co',
    tipoDocumento: 'CC',
    documento: '52.369.147',
    telefono: '3208887766',
    rol: 'inspector',
    rolNombre: 'Inspector',
    estado: 'bloqueado',
    mfaEnabled: false,
    dependencia: 'operaciones',
    ultimoAcceso: '2024-10-20T11:30:00',
    fechaCreacion: '2023-04-18'
  }
]);

// Form
const userForm = ref({
  nombre: '',
  tipoDocumento: 'CC',
  documento: '',
  email: '',
  telefono: '',
  rol: '',
  dependencia: '',
  estado: 'activo',
  mfaEnabled: false
});

// Actividades del usuario (mock)
const userActivities = ref([
  { icon: '=Ý', text: 'Actualizo registro de animal AN-1234', time: 'Hace 2 horas' },
  { icon: '', text: 'Completo adopcion AD-5678', time: 'Hace 5 horas' },
  { icon: '=', text: 'Inicio sesion desde 192.168.1.50', time: 'Ayer 16:30' },
  { icon: '=Ê', text: 'Genero reporte de adopciones', time: 'Hace 2 dias' }
]);

// Computed
const filteredUsers = computed(() => {
  let result = users.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(u =>
      u.nombre.toLowerCase().includes(query) ||
      u.email.toLowerCase().includes(query) ||
      u.documento.includes(query)
    );
  }

  if (filterRole.value) {
    result = result.filter(u => u.rol === filterRole.value);
  }

  if (filterStatus.value) {
    result = result.filter(u => u.estado === filterStatus.value);
  }

  return result;
});

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(filteredUsers.value.length / pageSize));
});

// Methods
function getInitials(name) {
  if (!name) return '??';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
}

function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function getRoleClass(rol) {
  const classes = {
    admin: 'role-admin',
    director: 'role-director',
    veterinario: 'role-vet',
    inspector: 'role-inspector',
    adoptante: 'role-adoptante',
    consulta: 'role-consulta'
  };
  return classes[rol] || '';
}

function openModal(mode, user = null) {
  modalMode.value = mode;
  if (mode === 'edit' && user) {
    userForm.value = { ...user };
  } else {
    userForm.value = {
      nombre: '',
      tipoDocumento: 'CC',
      documento: '',
      email: '',
      telefono: '',
      rol: '',
      dependencia: '',
      estado: 'activo',
      mfaEnabled: false
    };
  }
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

function saveUser() {
  // Validar MFA obligatorio para admin
  if (userForm.value.rol === 'admin' && !userForm.value.mfaEnabled) {
    alert('El MFA es obligatorio para usuarios con rol de Administrador');
    return;
  }

  if (modalMode.value === 'create') {
    const newUser = {
      ...userForm.value,
      id: users.value.length + 1,
      rolNombre: roles.value.find(r => r.id === userForm.value.rol)?.name,
      ultimoAcceso: null,
      fechaCreacion: new Date().toISOString()
    };
    users.value.push(newUser);
    alert('Usuario creado exitosamente');
  } else {
    const index = users.value.findIndex(u => u.id === userForm.value.id);
    if (index !== -1) {
      users.value[index] = {
        ...userForm.value,
        rolNombre: roles.value.find(r => r.id === userForm.value.rol)?.name
      };
    }
    alert('Usuario actualizado exitosamente');
  }
  closeModal();
}

function viewUser(user) {
  selectedUser.value = user;
  showViewModal.value = true;
}

function closeViewModal() {
  showViewModal.value = false;
  selectedUser.value = null;
}

function toggleUserStatus(user) {
  const newStatus = user.estado === 'activo' ? 'inactivo' : 'activo';
  if (confirm(`Cambiar estado de ${user.nombre} a ${newStatus}?`)) {
    user.estado = newStatus;
  }
}

function resetPassword(user) {
  if (confirm(`Enviar enlace de restablecimiento de contrasena a ${user.email}?`)) {
    alert(`Enlace enviado a ${user.email}`);
  }
}

function openRoleModal() {
  alert('Funcionalidad de crear rol (Integracion con sci-rbac)');
}

function editRole(role) {
  alert(`Editar rol: ${role.name} (Integracion con sci-rbac)`);
}

// Watch para MFA obligatorio
watch(() => userForm.value.rol, (newRol) => {
  if (newRol === 'admin') {
    userForm.value.mfaEnabled = true;
  }
});
</script>

<style scoped>
.user-management {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.5rem;
}

.management-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.management-header h2 {
  margin: 0 0 0.25rem 0;
  color: #004884;
}

.management-header p {
  margin: 0;
  color: #666;
}

.btn-add-user {
  padding: 0.75rem 1.5rem;
  background: #004884;
  color: white;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-add-user:hover {
  background: #003366;
}

/* Filters */
.filters-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.95rem;
  box-sizing: border-box;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.95rem;
  min-width: 160px;
}

/* Stats Cards */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 8px;
  padding: 1rem 1.25rem;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  border-left: 4px solid #004884;
}

.stat-card.active { border-color: #068460; }
.stat-card.inactive { border-color: #FFAB00; }
.stat-card.blocked { border-color: #A80521; }

.stat-value {
  display: block;
  font-size: 1.75rem;
  font-weight: bold;
  color: #004884;
}

.stat-label {
  font-size: 0.85rem;
  color: #666;
}

/* Users Table */
.users-table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
  overflow-x: auto;
  margin-bottom: 1rem;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th,
.users-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #E0E0E0;
}

.users-table th {
  background: #f5f7fb;
  font-weight: 600;
  color: #004884;
  white-space: nowrap;
}

.users-table tbody tr:hover {
  background: #f9f9f9;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #3366CC;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-weight: 600;
  color: #333;
}

.user-email {
  font-size: 0.85rem;
  color: #666;
}

/* Badges */
.role-badge {
  padding: 0.3rem 0.6rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.role-admin { background: #E8F0FE; color: #1565C0; }
.role-director { background: #F3E5F5; color: #7B1FA2; }
.role-vet { background: #E8F5E9; color: #2E7D32; }
.role-inspector { background: #FFF3E0; color: #EF6C00; }
.role-adoptante { background: #E0F7FA; color: #00838F; }
.role-consulta { background: #ECEFF1; color: #546E7A; }

.status-badge {
  padding: 0.3rem 0.6rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.status-badge.activo { background: #E8F5E9; color: #2E7D32; }
.status-badge.inactivo { background: #FFF8E1; color: #F57F17; }
.status-badge.bloqueado { background: #FFEBEE; color: #C62828; }

.mfa-badge {
  padding: 0.3rem 0.6rem;
  border-radius: 12px;
  font-size: 0.8rem;
  background: #ECEFF1;
  color: #546E7A;
}

.mfa-badge.enabled {
  background: #E8F5E9;
  color: #2E7D32;
}

/* Actions */
.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn.view { background: #E8F0FE; }
.action-btn.edit { background: #FFF3E0; }
.action-btn.toggle { background: #F3E5F5; }
.action-btn.reset { background: #E0F7FA; }

.action-btn:hover {
  transform: scale(1.1);
}

.no-results {
  padding: 2rem;
  text-align: center;
  color: #666;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
}

.page-btn {
  padding: 0.5rem 1rem;
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  background: #3366CC;
  color: white;
}

.page-btn:disabled {
  background: #f0f0f0;
  color: #999;
  border-color: #ddd;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.9rem;
  color: #666;
}

/* Roles Section */
.roles-section {
  margin-top: 2rem;
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.roles-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.roles-header h3 {
  margin: 0;
  color: #004884;
}

.btn-add-role {
  padding: 0.5rem 1rem;
  background: #E8F0FE;
  color: #3366CC;
  border: 1px solid #3366CC;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-add-role:hover {
  background: #3366CC;
  color: white;
}

.roles-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.role-card {
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  padding: 1rem;
}

.role-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.role-name {
  font-weight: 600;
  color: #004884;
}

.role-count {
  font-size: 0.8rem;
  color: #666;
  background: #f5f5f5;
  padding: 0.2rem 0.5rem;
  border-radius: 10px;
}

.role-description {
  font-size: 0.85rem;
  color: #666;
  margin: 0 0 0.75rem 0;
}

.role-permissions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  margin-bottom: 0.75rem;
}

.permission-tag {
  padding: 0.2rem 0.5rem;
  background: #E8F0FE;
  color: #3366CC;
  border-radius: 4px;
  font-size: 0.75rem;
}

.more-permissions {
  font-size: 0.75rem;
  color: #666;
}

.role-actions {
  text-align: right;
}

.role-btn {
  padding: 0.4rem 0.8rem;
  background: white;
  color: #3366CC;
  border: 1px solid #3366CC;
  border-radius: 4px;
  font-size: 0.85rem;
  cursor: pointer;
}

.role-btn:hover {
  background: #3366CC;
  color: white;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content.view-modal {
  max-width: 600px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #E0E0E0;
}

.modal-header h3 {
  margin: 0;
  color: #004884;
}

.close-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: #f5f5f5;
  border-radius: 50%;
  font-size: 1.25rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  background: #e0e0e0;
}

.modal-body {
  padding: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
  font-size: 0.9rem;
}

.form-input,
.form-select {
  padding: 0.65rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.95rem;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.1);
}

.mfa-section,
.sso-section {
  margin-top: 1.5rem;
  padding: 1rem;
  background: #f5f7fb;
  border-radius: 8px;
}

.mfa-section h4,
.sso-section h4 {
  margin: 0 0 0.75rem 0;
  color: #004884;
  font-size: 0.95rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.mfa-note {
  margin: 0.5rem 0 0 0;
  font-size: 0.8rem;
  color: #A80521;
}

.sso-info {
  margin: 0;
  font-size: 0.9rem;
  color: #666;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #E0E0E0;
}

.btn-cancel {
  padding: 0.75rem 1.5rem;
  background: white;
  color: #666;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  cursor: pointer;
}

.btn-cancel:hover {
  background: #f5f5f5;
}

.btn-save {
  padding: 0.75rem 1.5rem;
  background: #004884;
  color: white;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
}

.btn-save:hover {
  background: #003366;
}

/* View Modal */
.user-detail-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #E0E0E0;
}

.user-avatar-large {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #3366CC;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.5rem;
}

.user-detail-info h4 {
  margin: 0 0 0.25rem 0;
  color: #004884;
}

.user-detail-info p {
  margin: 0 0 0.5rem 0;
  color: #666;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
}

.detail-label {
  font-size: 0.8rem;
  color: #666;
  margin-bottom: 0.25rem;
}

.detail-value {
  font-weight: 500;
  color: #333;
}

.activity-section {
  background: #f5f7fb;
  border-radius: 8px;
  padding: 1rem;
}

.activity-section h4 {
  margin: 0 0 1rem 0;
  color: #004884;
  font-size: 0.95rem;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  background: white;
  border-radius: 4px;
}

.activity-icon {
  font-size: 1.25rem;
}

.activity-text {
  flex: 1;
  font-size: 0.9rem;
}

.activity-time {
  font-size: 0.8rem;
  color: #999;
}

/* Responsive */
@media (max-width: 1200px) {
  .roles-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 992px) {
  .stats-cards {
    grid-template-columns: repeat(2, 1fr);
  }

  .roles-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .user-management {
    padding: 1rem;
  }

  .management-header {
    flex-direction: column;
  }

  .filters-bar {
    flex-direction: column;
  }

  .stats-cards {
    grid-template-columns: repeat(2, 1fr);
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .modal-content {
    width: 95%;
  }
}
</style>
