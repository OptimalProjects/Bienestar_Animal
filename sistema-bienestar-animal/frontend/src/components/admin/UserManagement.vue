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
          @keyup.enter="fetchUsers"
        />
        <span class="search-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
        </span>
      </div>
      <div class="filter-group">
        <select v-model="filterRoleId" class="filter-select" @change="fetchUsers">
          <option value="">Todos los roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.nombre }}
          </option>
        </select>
      </div>
      <div class="filter-group">
        <select v-model="filterStatus" class="filter-select" @change="fetchUsers">
          <option value="">Todos los estados</option>
          <option value="true">Activo</option>
          <option value="false">Inactivo</option>
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
        <span class="stat-value">{{ stats.activos }}</span>
        <span class="stat-label">Activos</span>
      </div>
      <div class="stat-card inactive">
        <span class="stat-value">{{ stats.inactivos }}</span>
        <span class="stat-label">Inactivos</span>
      </div>
      <div class="stat-card mfa">
        <span class="stat-value">{{ stats.conMfa }}</span>
        <span class="stat-label">Con MFA</span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Cargando usuarios...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="error-container">
      <p>{{ error }}</p>
      <button type="button" class="btn-retry" @click="fetchUsers">Reintentar</button>
    </div>

    <!-- Tabla de Usuarios -->
    <div v-else class="users-table-container">
      <table class="users-table">
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>MFA</th>
            <th>Ultimo Acceso</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td class="user-cell">
              <div class="user-avatar">{{ getInitials(user.nombre_completo || `${user.nombres} ${user.apellidos}`) }}</div>
              <div class="user-info">
                <span class="user-name">{{ user.nombre_completo || `${user.nombres} ${user.apellidos}` }}</span>
                <span class="user-email">{{ user.email }}</span>
              </div>
            </td>
            <td>
              <span class="role-badge" :class="getRoleClass(user.roles?.[0]?.codigo)">
                {{ user.roles?.[0]?.nombre || 'Sin rol' }}
              </span>
            </td>
            <td>
              <span class="status-badge" :class="user.activo ? 'activo' : 'inactivo'">
                {{ user.activo ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td>
              <span class="mfa-badge" :class="{ enabled: user.mfa_enabled }">
                {{ user.mfa_enabled ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td>{{ formatDate(user.ultimo_acceso) }}</td>
            <td class="actions-cell">
              <button
                type="button"
                class="action-btn view"
                @click="viewUser(user)"
                title="Ver detalle"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
              <button
                type="button"
                class="action-btn edit"
                @click="openModal('edit', user)"
                title="Editar"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </button>
              <button
                type="button"
                class="action-btn toggle"
                @click="toggleUserStatus(user)"
                :title="user.activo ? 'Desactivar' : 'Activar'"
              >
                <svg v-if="user.activo" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>
                </svg>
                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
              </button>
              <button
                type="button"
                class="action-btn reset"
                @click="openPasswordModal(user)"
                title="Cambiar contrasena"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="users.length === 0 && !loading" class="no-results">
        <p>No se encontraron usuarios con los filtros aplicados</p>
      </div>
    </div>

    <!-- Paginacion -->
    <div v-if="pagination.lastPage > 1" class="pagination">
      <button
        type="button"
        @click="goToPage(pagination.currentPage - 1)"
        :disabled="pagination.currentPage === 1"
        class="page-btn"
      >
        Anterior
      </button>
      <span class="page-info">
        Pagina {{ pagination.currentPage }} de {{ pagination.lastPage }}
        ({{ pagination.total }} usuarios)
      </span>
      <button
        type="button"
        @click="goToPage(pagination.currentPage + 1)"
        :disabled="pagination.currentPage === pagination.lastPage"
        class="page-btn"
      >
        Siguiente
      </button>
    </div>

    <!-- Seccion de Roles -->
    <div class="roles-section">
      <div class="roles-header">
        <h3 class="h5-tipografia-govco">Roles del Sistema</h3>
      </div>
      <div v-if="loadingRoles" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Cargando roles...</p>
      </div>
      <div v-else class="roles-grid">
        <div v-for="role in roles" :key="role.id" class="role-card">
          <div class="role-header-card">
            <span class="role-name">{{ role.nombre }}</span>
            <span v-if="role.requiere_mfa" class="mfa-required-tag">MFA</span>
          </div>
          <p class="role-description">{{ role.descripcion }}</p>
          <div class="role-permissions">
            <span
              v-for="perm in role.permisos?.slice(0, 4)"
              :key="perm.id"
              class="permission-tag"
            >
              {{ perm.recurso }}.{{ perm.accion }}
            </span>
            <span v-if="(role.permisos?.length || 0) > 4" class="more-permissions">
              +{{ role.permisos.length - 4 }} mas
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Crear/Editar Usuario -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ modalMode === 'create' ? 'Nuevo Usuario' : 'Editar Usuario' }}</h3>
          <button type="button" class="close-btn" @click="closeModal">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveUser">
            <div class="form-row">
              <div class="form-group">
                <label>Nombres *</label>
                <input
                  type="text"
                  v-model="userForm.nombres"
                  required
                  class="form-input"
                  placeholder="Ej: Carlos Alberto"
                />
              </div>
              <div class="form-group">
                <label>Apellidos *</label>
                <input
                  type="text"
                  v-model="userForm.apellidos"
                  required
                  class="form-input"
                  placeholder="Ej: Ramirez Lopez"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Tipo Documento *</label>
                <select v-model="userForm.tipo_documento" required class="form-select">
                  <option value="CC">Cedula de Ciudadania</option>
                  <option value="CE">Cedula de Extranjeria</option>
                  <option value="TI">Tarjeta de Identidad</option>
                  <option value="PP">Pasaporte</option>
                </select>
              </div>
              <div class="form-group">
                <label>Numero de Documento *</label>
                <input
                  type="text"
                  v-model="userForm.documento_identidad"
                  required
                  class="form-input"
                  placeholder="Ej: 1234567890"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Email Institucional *</label>
                <input
                  type="email"
                  v-model="userForm.email"
                  required
                  class="form-input"
                  placeholder="usuario@bienestaranimal.gov.co"
                />
              </div>
              <div class="form-group">
                <label>Telefono</label>
                <input
                  type="tel"
                  v-model="userForm.telefono"
                  class="form-input"
                  placeholder="Ej: 3001234567"
                />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Rol *</label>
                <select v-model="userForm.rol_id" required class="form-select">
                  <option value="">Seleccione un rol</option>
                  <option v-for="role in roles" :key="role.id" :value="role.id">
                    {{ role.nombre }}
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>Area</label>
                <select v-model="userForm.area" class="form-select">
                  <option value="">Seleccione</option>
                  <option value="Direccion General">Direccion General</option>
                  <option value="Operaciones">Operaciones</option>
                  <option value="Area Veterinaria">Area Veterinaria</option>
                  <option value="Area de Adopciones">Area de Adopciones</option>
                  <option value="Atencion Denuncias">Atencion Denuncias</option>
                </select>
              </div>
            </div>

            <!-- Password solo para creacion -->
            <div v-if="modalMode === 'create'" class="form-row">
              <div class="form-group">
                <label>Contrasena *</label>
                <input
                  type="password"
                  v-model="userForm.password"
                  required
                  class="form-input"
                  placeholder="Min. 8 caracteres, mayusculas, numeros"
                  minlength="8"
                />
              </div>
              <div class="form-group">
                <label>Confirmar Contrasena *</label>
                <input
                  type="password"
                  v-model="userForm.password_confirmation"
                  required
                  class="form-input"
                  placeholder="Repita la contrasena"
                  minlength="8"
                />
              </div>
            </div>

            <!-- Configuracion MFA -->
            <div class="mfa-section">
              <h4>Autenticacion Multi-Factor (MFA)</h4>
              <label class="checkbox-label">
                <input
                  type="checkbox"
                  v-model="userForm.mfa_enabled"
                  :disabled="selectedRoleRequiresMfa"
                />
                Habilitar MFA para este usuario
              </label>
              <p v-if="selectedRoleRequiresMfa" class="mfa-note">
                * Obligatorio para el rol seleccionado
              </p>
            </div>

            <!-- Mensaje de error del formulario -->
            <div v-if="formError" class="form-error">
              {{ formError }}
            </div>

            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="closeModal" :disabled="saving">
                Cancelar
              </button>
              <button type="submit" class="btn-save" :disabled="saving">
                <span v-if="saving" class="btn-loading"></span>
                {{ saving ? 'Guardando...' : (modalMode === 'create' ? 'Crear Usuario' : 'Guardar Cambios') }}
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
          <button type="button" class="close-btn" @click="closeViewModal">&times;</button>
        </div>
        <div class="modal-body">
          <div v-if="loadingDetail" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Cargando detalle...</p>
          </div>
          <template v-else-if="selectedUser">
            <div class="user-detail-header">
              <div class="user-avatar-large">{{ getInitials(selectedUser.nombre_completo || `${selectedUser.nombres} ${selectedUser.apellidos}`) }}</div>
              <div class="user-detail-info">
                <h4>{{ selectedUser.nombre_completo || `${selectedUser.nombres} ${selectedUser.apellidos}` }}</h4>
                <p>{{ selectedUser.email }}</p>
                <span class="status-badge" :class="selectedUser.activo ? 'activo' : 'inactivo'">
                  {{ selectedUser.activo ? 'Activo' : 'Inactivo' }}
                </span>
              </div>
            </div>

            <div class="detail-grid">
              <div class="detail-item">
                <span class="detail-label">Username</span>
                <span class="detail-value">{{ selectedUser.username || '-' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Rol</span>
                <span class="detail-value">
                  <span class="role-badge" :class="getRoleClass(selectedUser.roles?.[0]?.codigo)">
                    {{ selectedUser.roles?.[0]?.nombre || 'Sin rol' }}
                  </span>
                </span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Autenticacion</span>
                <span class="detail-value">{{ selectedUser.origen_autenticacion || 'local' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">MFA</span>
                <span class="detail-value">{{ selectedUser.mfa_enabled ? 'Habilitado' : 'No habilitado' }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Ultimo Acceso</span>
                <span class="detail-value">{{ formatDate(selectedUser.ultimo_acceso) }}</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Fecha Creacion</span>
                <span class="detail-value">{{ formatDate(selectedUser.created_at) }}</span>
              </div>
            </div>

            <!-- Permisos del rol -->
            <div v-if="selectedUser.roles?.[0]?.permisos?.length" class="permissions-section">
              <h4>Permisos del Rol</h4>
              <div class="permissions-list">
                <span
                  v-for="perm in selectedUser.roles[0].permisos"
                  :key="perm.id"
                  class="permission-tag"
                >
                  {{ perm.recurso }}.{{ perm.accion }}
                </span>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Modal Cambiar Contrasena -->
    <div v-if="showPasswordModal" class="modal-overlay" @click.self="closePasswordModal">
      <div class="modal-content password-modal">
        <div class="modal-header">
          <h3>Cambiar Contrasena</h3>
          <button type="button" class="close-btn" @click="closePasswordModal">&times;</button>
        </div>
        <div class="modal-body">
          <p class="password-user-info">
            Usuario: <strong>{{ passwordUser?.nombre_completo || `${passwordUser?.nombres} ${passwordUser?.apellidos}` }}</strong>
          </p>
          <form @submit.prevent="changePassword">
            <div class="form-group">
              <label>Nueva Contrasena *</label>
              <input
                type="password"
                v-model="passwordForm.password"
                required
                class="form-input"
                placeholder="Min. 8 caracteres, mayusculas, numeros"
                minlength="8"
              />
            </div>
            <div class="form-group" style="margin-top: 1rem;">
              <label>Confirmar Contrasena *</label>
              <input
                type="password"
                v-model="passwordForm.password_confirmation"
                required
                class="form-input"
                placeholder="Repita la contrasena"
                minlength="8"
              />
            </div>

            <div v-if="passwordError" class="form-error" style="margin-top: 1rem;">
              {{ passwordError }}
            </div>

            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="closePasswordModal" :disabled="savingPassword">
                Cancelar
              </button>
              <button type="submit" class="btn-save" :disabled="savingPassword">
                {{ savingPassword ? 'Guardando...' : 'Cambiar Contrasena' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Toast de notificacion -->
    <Transition name="toast">
      <div v-if="toast.show" class="toast" :class="toast.type">
        {{ toast.message }}
      </div>
    </Transition>
  </section>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { userService } from '@/services/userService.js';

// --- Estado ---
const loading = ref(false);
const loadingRoles = ref(false);
const loadingDetail = ref(false);
const saving = ref(false);
const savingPassword = ref(false);
const error = ref(null);
const formError = ref(null);
const passwordError = ref(null);

const searchQuery = ref('');
const filterRoleId = ref('');
const filterStatus = ref('');

const users = ref([]);
const roles = ref([]);
const pagination = ref({
  currentPage: 1,
  lastPage: 1,
  perPage: 15,
  total: 0,
});

const stats = ref({
  total: 0,
  activos: 0,
  inactivos: 0,
  conMfa: 0,
});

// Modales
const showModal = ref(false);
const showViewModal = ref(false);
const showPasswordModal = ref(false);
const modalMode = ref('create');
const selectedUser = ref(null);
const passwordUser = ref(null);
let searchTimeout = null;

// Formulario de usuario
const userForm = ref(getEmptyForm());

// Formulario de contrasena
const passwordForm = ref({
  password: '',
  password_confirmation: '',
});

// Toast
const toast = ref({ show: false, message: '', type: 'success' });

// --- Computed ---
const selectedRoleRequiresMfa = computed(() => {
  if (!userForm.value.rol_id) return false;
  const role = roles.value.find(r => r.id === userForm.value.rol_id);
  return role?.requiere_mfa || false;
});

// --- Funciones de datos ---
function getEmptyForm() {
  return {
    nombres: '',
    apellidos: '',
    tipo_documento: 'CC',
    documento_identidad: '',
    email: '',
    telefono: '',
    rol_id: '',
    area: '',
    mfa_enabled: false,
    password: '',
    password_confirmation: '',
  };
}

async function fetchUsers() {
  loading.value = true;
  error.value = null;
  try {
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
    };
    if (searchQuery.value) params.busqueda = searchQuery.value;
    if (filterRoleId.value) params.rol_id = filterRoleId.value;
    if (filterStatus.value !== '') params.activo = filterStatus.value;

    const response = await userService.getUsers(params);
    const paginated = response.data;

    users.value = paginated.data || [];
    pagination.value = {
      currentPage: paginated.current_page,
      lastPage: paginated.last_page,
      perPage: paginated.per_page,
      total: paginated.total,
    };

    computeStats();
  } catch (err) {
    console.error('Error al cargar usuarios:', err);
    error.value = err.response?.data?.message || 'Error al cargar la lista de usuarios';
  } finally {
    loading.value = false;
  }
}

async function fetchRoles() {
  loadingRoles.value = true;
  try {
    const response = await userService.getRoles();
    roles.value = response.data || [];
  } catch (err) {
    console.error('Error al cargar roles:', err);
  } finally {
    loadingRoles.value = false;
  }
}

function computeStats() {
  const all = users.value;
  stats.value = {
    total: pagination.value.total,
    activos: all.filter(u => u.activo).length,
    inactivos: all.filter(u => !u.activo).length,
    conMfa: all.filter(u => u.mfa_enabled).length,
  };
}

function goToPage(page) {
  if (page < 1 || page > pagination.value.lastPage) return;
  pagination.value.currentPage = page;
  fetchUsers();
}

// --- Acciones CRUD ---
async function saveUser() {
  formError.value = null;

  if (modalMode.value === 'create') {
    if (userForm.value.password !== userForm.value.password_confirmation) {
      formError.value = 'Las contrasenas no coinciden';
      return;
    }
  }

  // Forzar MFA si el rol lo requiere
  if (selectedRoleRequiresMfa.value) {
    userForm.value.mfa_enabled = true;
  }

  saving.value = true;
  try {
    if (modalMode.value === 'create') {
      await userService.createUser({
        nombres: userForm.value.nombres,
        apellidos: userForm.value.apellidos,
        tipo_documento: userForm.value.tipo_documento,
        documento_identidad: userForm.value.documento_identidad,
        email: userForm.value.email,
        telefono: userForm.value.telefono || null,
        rol_id: userForm.value.rol_id,
        area: userForm.value.area || null,
        password: userForm.value.password,
        mfa_enabled: userForm.value.mfa_enabled,
      });
      showToast('Usuario creado exitosamente', 'success');
    } else {
      await userService.updateUser(userForm.value.id, {
        nombres: userForm.value.nombres,
        apellidos: userForm.value.apellidos,
        email: userForm.value.email,
        telefono: userForm.value.telefono || null,
        rol_id: userForm.value.rol_id,
        area: userForm.value.area || null,
        activo: userForm.value.activo,
      });
      showToast('Usuario actualizado exitosamente', 'success');
    }
    closeModal();
    await fetchUsers();
  } catch (err) {
    const data = err.response?.data;
    if (data?.errors) {
      const messages = Object.values(data.errors).flat();
      formError.value = messages.join('. ');
    } else {
      formError.value = data?.message || 'Error al guardar el usuario';
    }
  } finally {
    saving.value = false;
  }
}

async function toggleUserStatus(user) {
  const action = user.activo ? 'desactivar' : 'activar';
  const name = user.nombre_completo || `${user.nombres} ${user.apellidos}`;
  if (!confirm(`¿Desea ${action} al usuario ${name}?`)) return;

  try {
    await userService.toggleActive(user.id);
    showToast(`Usuario ${action === 'activar' ? 'activado' : 'desactivado'} exitosamente`, 'success');
    await fetchUsers();
  } catch (err) {
    showToast(err.response?.data?.message || 'Error al cambiar estado del usuario', 'error');
  }
}

async function changePassword() {
  passwordError.value = null;

  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    passwordError.value = 'Las contrasenas no coinciden';
    return;
  }

  savingPassword.value = true;
  try {
    await userService.changePassword(passwordUser.value.id, {
      password: passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation,
    });
    showToast('Contrasena actualizada exitosamente', 'success');
    closePasswordModal();
  } catch (err) {
    const data = err.response?.data;
    if (data?.errors) {
      const messages = Object.values(data.errors).flat();
      passwordError.value = messages.join('. ');
    } else {
      passwordError.value = data?.message || 'Error al cambiar la contrasena';
    }
  } finally {
    savingPassword.value = false;
  }
}

// --- Modales ---
function openModal(mode, user = null) {
  modalMode.value = mode;
  formError.value = null;
  if (mode === 'edit' && user) {
    userForm.value = {
      id: user.id,
      nombres: user.nombres,
      apellidos: user.apellidos,
      tipo_documento: user.tipo_documento || 'CC',
      documento_identidad: user.documento_identidad || '',
      email: user.email,
      telefono: user.telefono || '',
      rol_id: user.roles?.[0]?.id || '',
      area: user.area || '',
      mfa_enabled: user.mfa_enabled || false,
      activo: user.activo,
      password: '',
      password_confirmation: '',
    };
  } else {
    userForm.value = getEmptyForm();
  }
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  formError.value = null;
}

async function viewUser(user) {
  showViewModal.value = true;
  loadingDetail.value = true;
  try {
    const response = await userService.getUser(user.id);
    selectedUser.value = response.data;
  } catch (err) {
    selectedUser.value = user;
  } finally {
    loadingDetail.value = false;
  }
}

function closeViewModal() {
  showViewModal.value = false;
  selectedUser.value = null;
}

function openPasswordModal(user) {
  passwordUser.value = user;
  passwordForm.value = { password: '', password_confirmation: '' };
  passwordError.value = null;
  showPasswordModal.value = true;
}

function closePasswordModal() {
  showPasswordModal.value = false;
  passwordUser.value = null;
  passwordError.value = null;
}

// --- Utilidades ---
function getInitials(name) {
  if (!name) return '??';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('es-CO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

function getRoleClass(codigo) {
  if (!codigo) return '';
  const map = {
    ADMIN: 'role-admin',
    DIRECTOR: 'role-director',
    VETERINARIO: 'role-vet',
    AUXILIAR_VET: 'role-vet',
    COORDINADOR: 'role-coordinador',
    OPERADOR: 'role-operador',
    EVALUADOR: 'role-evaluador',
  };
  return map[codigo] || '';
}

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type };
  setTimeout(() => {
    toast.value.show = false;
  }, 3500);
}

// --- Watchers ---
watch(searchQuery, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    pagination.value.currentPage = 1;
    fetchUsers();
  }, 400);
});

// Forzar MFA cuando el rol lo requiere
watch(() => userForm.value.rol_id, (newRolId) => {
  if (!newRolId) return;
  const role = roles.value.find(r => r.id === newRolId);
  if (role?.requiere_mfa) {
    userForm.value.mfa_enabled = true;
  }
});

// --- Inicializacion ---
onMounted(async () => {
  await fetchRoles();
  await fetchUsers();
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

.search-input:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.15);
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid #D0D0D0;
  border-radius: 4px;
  font-size: 0.95rem;
  min-width: 160px;
}

.filter-select:focus {
  outline: none;
  border-color: #3366CC;
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.15);
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

.stat-card.active { border-color: #069169; }
.stat-card.inactive { border-color: #FFAB00; }
.stat-card.mfa { border-color: #3366CC; }

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

/* Loading */
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 3rem;
  color: #666;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #E8F0FE;
  border-top-color: #3366CC;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error */
.error-container {
  text-align: center;
  padding: 2rem;
  background: #FFF5F5;
  border: 1px solid #FFCDD2;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  color: #A80521;
}

.btn-retry {
  margin-top: 1rem;
  padding: 0.5rem 1.5rem;
  background: #A80521;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.btn-retry:hover {
  background: #8b0419;
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
  background: #E8F0FE;
  font-weight: 600;
  color: #004884;
  white-space: nowrap;
}

.users-table tbody tr:hover {
  background: #f5f7fb;
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
  flex-shrink: 0;
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
  white-space: nowrap;
}

.role-admin { background: #E8F0FE; color: #1565C0; }
.role-director { background: #F3E5F5; color: #7B1FA2; }
.role-vet { background: #E8F5E9; color: #2E7D32; }
.role-coordinador { background: #FFF3E0; color: #EF6C00; }
.role-operador { background: #E0F7FA; color: #00838F; }
.role-evaluador { background: #F3E5F5; color: #6A1B9A; }

.status-badge {
  padding: 0.3rem 0.6rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
}

.status-badge.activo { background: #E8F5E9; color: #2E7D32; }
.status-badge.inactivo { background: #FFF8E1; color: #F57F17; }

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
  color: #555;
}

.action-btn.view { background: #E8F0FE; color: #1565C0; }
.action-btn.edit { background: #FFF3E0; color: #EF6C00; }
.action-btn.toggle { background: #F3E5F5; color: #7B1FA2; }
.action-btn.reset { background: #E0F7FA; color: #00838F; }

.action-btn:hover {
  transform: scale(1.1);
  opacity: 0.85;
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
  font-weight: 500;
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

.roles-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.role-card {
  border: 1px solid #E0E0E0;
  border-radius: 8px;
  padding: 1rem;
  transition: box-shadow 0.2s;
}

.role-card:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.role-header-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.role-name {
  font-weight: 600;
  color: #004884;
}

.mfa-required-tag {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.15rem 0.4rem;
  background: #E8F0FE;
  color: #1565C0;
  border-radius: 4px;
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
  padding: 0.2rem 0.5rem;
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

.modal-content.password-modal {
  max-width: 450px;
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
  color: #666;
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
  box-shadow: 0 0 0 2px rgba(51, 102, 204, 0.15);
}

.form-error {
  padding: 0.75rem 1rem;
  background: #FFF5F5;
  border: 1px solid #FFCDD2;
  border-radius: 4px;
  color: #A80521;
  font-size: 0.9rem;
  margin-top: 1rem;
}

.mfa-section {
  margin-top: 1.5rem;
  padding: 1rem;
  background: #f5f7fb;
  border-radius: 8px;
}

.mfa-section h4 {
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
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-save:hover:not(:disabled) {
  background: #003366;
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-loading {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
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
  flex-shrink: 0;
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

.permissions-section {
  background: #f5f7fb;
  border-radius: 8px;
  padding: 1rem;
}

.permissions-section h4 {
  margin: 0 0 0.75rem 0;
  color: #004884;
  font-size: 0.95rem;
}

.permissions-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
}

.password-user-info {
  margin: 0 0 1.5rem 0;
  color: #333;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  z-index: 2000;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.toast.success {
  background: #069169;
}

.toast.error {
  background: #A80521;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(20px);
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
