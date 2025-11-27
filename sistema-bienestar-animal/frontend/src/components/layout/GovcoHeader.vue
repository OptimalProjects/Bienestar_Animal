<!-- src/components/layout/GovcoHeader.vue -->
<!-- Header oficial GOV.CO con barra de accesibilidad y navegación -->
<template>
  <header class="govco-header">
    <!-- Barra superior de accesibilidad GOV.CO -->
    <div class="govco-accessibility-bar">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-auto">
            <a href="https://www.gov.co/" target="_blank" rel="noopener" class="govco-logo-link">
              <img
                src="https://cdn.www.gov.co/layout/v4/images/logo-gov-co.svg"
                alt="Logo GOV.CO"
                class="govco-logo"
              />
            </a>
          </div>
          <div class="col d-none d-md-block">
            <span class="govco-slogan">Portal del Estado Colombiano</span>
          </div>
          <div class="col-auto">
            <div class="govco-accessibility-controls">
              <button
                type="button"
                class="govco-btn-accessibility"
                @click="decreaseFont"
                title="Disminuir tamaño de letra"
                aria-label="Disminuir tamaño de letra"
              >
                A-
              </button>
              <button
                type="button"
                class="govco-btn-accessibility"
                @click="increaseFont"
                title="Aumentar tamaño de letra"
                aria-label="Aumentar tamaño de letra"
              >
                A+
              </button>
              <button
                type="button"
                class="govco-btn-accessibility govco-btn-contrast"
                @click="toggleContrast"
                :class="{ active: highContrast }"
                title="Alto contraste"
                aria-label="Alternar alto contraste"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/>
                  <path d="M12 2a10 10 0 0 1 0 20V2z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Barra principal de navegación -->
    <nav class="govco-main-nav">
      <div class="container-fluid">
        <div class="row align-items-center">
          <!-- Logo de la entidad -->
          <div class="col-auto">
            <router-link to="/" class="govco-entity-brand">
              <img
                src="https://cdn.www.gov.co/layout/v4/images/logo-alcaldia.svg"
                alt="Logo Alcaldía"
                class="govco-entity-logo"
                @error="handleLogoError"
              />
              <div class="govco-entity-info">
                <span class="govco-entity-name">Alcaldía Municipal</span>
                <span class="govco-entity-subtitle">Sistema de Bienestar Animal</span>
              </div>
            </router-link>
          </div>

          <!-- Botón hamburguesa para móvil -->
          <div class="col d-lg-none text-end">
            <button
              type="button"
              class="govco-hamburger"
              @click="toggleMenu"
              :aria-expanded="menuOpen"
              aria-label="Menú de navegación"
            >
              <span class="govco-hamburger-line"></span>
              <span class="govco-hamburger-line"></span>
              <span class="govco-hamburger-line"></span>
            </button>
          </div>

          <!-- Menú de navegación -->
          <div class="col-lg govco-nav-wrapper" :class="{ 'show': menuOpen }">
            <ul class="govco-nav-menu">
              <li class="govco-nav-item">
                <router-link to="/" class="govco-nav-link" @click="closeMenu">
                  Inicio
                </router-link>
              </li>
              <li class="govco-nav-item">
                <router-link to="/denuncias" class="govco-nav-link" @click="closeMenu">
                  Denuncias
                </router-link>
              </li>
              <li class="govco-nav-item">
                <router-link to="/adopciones" class="govco-nav-link" @click="closeMenu">
                  Adopciones
                </router-link>
              </li>
              <li v-if="isAuthenticated" class="govco-nav-item">
                <router-link to="/animales" class="govco-nav-link" @click="closeMenu">
                  Animales
                </router-link>
              </li>
              <li v-if="isAuthenticated" class="govco-nav-item">
                <router-link to="/veterinaria" class="govco-nav-link" @click="closeMenu">
                  Veterinaria
                </router-link>
              </li>
              <li v-if="isAdmin" class="govco-nav-item">
                <router-link to="/administracion" class="govco-nav-link" @click="closeMenu">
                  Administración
                </router-link>
              </li>
            </ul>

            <!-- Acciones de usuario -->
            <div class="govco-nav-actions">
              <template v-if="!isAuthenticated">
                <router-link to="/login" class="govco-btn govco-btn-outline" @click="closeMenu">
                  Iniciar Sesión
                </router-link>
              </template>
              <template v-else>
                <div class="govco-user-menu" @click="toggleUserMenu">
                  <div class="govco-user-avatar">
                    {{ userInitials }}
                  </div>
                  <span class="govco-user-name d-none d-xl-inline">{{ userName }}</span>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                  </svg>
                </div>

                <div v-if="userMenuOpen" class="govco-dropdown-menu">
                  <router-link to="/dashboard" class="govco-dropdown-item" @click="closeAllMenus">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="3" width="7" height="7"></rect>
                      <rect x="14" y="3" width="7" height="7"></rect>
                      <rect x="14" y="14" width="7" height="7"></rect>
                      <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Panel Principal
                  </router-link>
                  <a href="#" class="govco-dropdown-item" @click.prevent="logout">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                      <polyline points="16 17 21 12 16 7"></polyline>
                      <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Cerrar Sesión
                  </a>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

// Estado del menú
const menuOpen = ref(false);
const userMenuOpen = ref(false);
const highContrast = ref(false);

// Simulación de autenticación (conectar con store real)
const isAuthenticated = ref(true); // Cambiar a false para probar modo público
const userName = ref('Dr. Juan Pérez');
const userRole = ref('admin'); // 'citizen', 'operator', 'director', 'admin'

const userInitials = computed(() => {
  return userName.value
    .split(' ')
    .map(n => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase();
});

const isAdmin = computed(() => {
  return ['director', 'admin'].includes(userRole.value);
});

// Funciones de menú
function toggleMenu() {
  menuOpen.value = !menuOpen.value;
  if (menuOpen.value) {
    userMenuOpen.value = false;
  }
}

function closeMenu() {
  menuOpen.value = false;
}

function toggleUserMenu() {
  userMenuOpen.value = !userMenuOpen.value;
}

function closeAllMenus() {
  menuOpen.value = false;
  userMenuOpen.value = false;
}

// Funciones de accesibilidad
function increaseFont() {
  const html = document.documentElement;
  const currentSize = parseFloat(getComputedStyle(html).fontSize);
  if (currentSize < 24) {
    html.style.fontSize = `${currentSize + 2}px`;
  }
}

function decreaseFont() {
  const html = document.documentElement;
  const currentSize = parseFloat(getComputedStyle(html).fontSize);
  if (currentSize > 12) {
    html.style.fontSize = `${currentSize - 2}px`;
  }
}

function toggleContrast() {
  highContrast.value = !highContrast.value;
  document.body.classList.toggle('high-contrast', highContrast.value);
}

function logout() {
  // Aquí iría la lógica de logout
  isAuthenticated.value = false;
  closeAllMenus();
  // router.push('/');
}

function handleLogoError(e) {
  // Usar un logo de respaldo si falla la carga
  e.target.style.display = 'none';
}

// Cerrar menús al hacer clic fuera
function handleClickOutside(event) {
  if (!event.target.closest('.govco-user-menu') && !event.target.closest('.govco-dropdown-menu')) {
    userMenuOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Barra de accesibilidad GOV.CO */
.govco-accessibility-bar {
  background-color: #3366CC;
  color: white;
  padding: 8px 0;
  font-size: 0.875rem;
}

.govco-logo {
  height: 28px;
  width: auto;
}

.govco-logo-link {
  display: flex;
  align-items: center;
}

.govco-slogan {
  font-size: 0.8rem;
  opacity: 0.9;
}

.govco-accessibility-controls {
  display: flex;
  gap: 8px;
}

.govco-btn-accessibility {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 4px 10px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 600;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.govco-btn-accessibility:hover,
.govco-btn-accessibility.active {
  background: rgba(255, 255, 255, 0.25);
}

/* Navegación principal */
.govco-main-nav {
  background-color: #004884;
  padding: 0;
  position: relative;
}

.govco-entity-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: white;
  padding: 12px 0;
}

.govco-entity-logo {
  height: 50px;
  width: auto;
}

.govco-entity-info {
  display: flex;
  flex-direction: column;
}

.govco-entity-name {
  font-size: 1rem;
  font-weight: 600;
  line-height: 1.2;
}

.govco-entity-subtitle {
  font-size: 0.75rem;
  opacity: 0.85;
}

/* Botón hamburguesa */
.govco-hamburger {
  background: transparent;
  border: none;
  padding: 10px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.govco-hamburger-line {
  display: block;
  width: 24px;
  height: 2px;
  background-color: white;
  transition: transform 0.3s;
}

/* Menú de navegación */
.govco-nav-wrapper {
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.govco-nav-menu {
  display: flex;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: 4px;
}

.govco-nav-item {
  position: relative;
}

.govco-nav-link {
  display: block;
  padding: 16px 16px;
  color: white;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: background-color 0.2s;
  white-space: nowrap;
}

.govco-nav-link:hover,
.govco-nav-link.router-link-active {
  background-color: rgba(255, 255, 255, 0.1);
}

.govco-nav-link.router-link-exact-active {
  background-color: rgba(255, 255, 255, 0.15);
  border-bottom: 3px solid #FFD700;
}

/* Acciones de navegación */
.govco-nav-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-left: 16px;
  padding-left: 16px;
  border-left: 1px solid rgba(255, 255, 255, 0.2);
}

.govco-btn {
  padding: 8px 20px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.875rem;
  text-decoration: none;
  transition: all 0.2s;
  cursor: pointer;
}

.govco-btn-outline {
  background: transparent;
  border: 2px solid white;
  color: white;
}

.govco-btn-outline:hover {
  background: white;
  color: #004884;
}

/* Menú de usuario */
.govco-user-menu {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 20px;
  cursor: pointer;
  transition: background-color 0.2s;
  color: white;
}

.govco-user-menu:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.govco-user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: #3366CC;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.8rem;
}

.govco-user-name {
  font-size: 0.875rem;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Dropdown de usuario */
.govco-dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  min-width: 200px;
  z-index: 1000;
  overflow: hidden;
}

.govco-dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  color: #333;
  text-decoration: none;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.govco-dropdown-item:hover {
  background-color: #f5f5f5;
}

.govco-dropdown-item svg {
  color: #666;
}

/* Responsive */
@media (max-width: 991.98px) {
  .govco-nav-wrapper {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background-color: #004884;
    flex-direction: column;
    align-items: stretch;
    padding: 16px;
    display: none;
    z-index: 999;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .govco-nav-wrapper.show {
    display: flex;
  }

  .govco-nav-menu {
    flex-direction: column;
    gap: 0;
  }

  .govco-nav-link {
    padding: 12px 16px;
    border-radius: 4px;
  }

  .govco-nav-actions {
    margin-left: 0;
    padding-left: 0;
    border-left: none;
    padding-top: 16px;
    margin-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    flex-direction: column;
    align-items: stretch;
  }

  .govco-btn-outline {
    text-align: center;
  }

  .govco-user-menu {
    justify-content: center;
  }

  .govco-dropdown-menu {
    position: static;
    margin-top: 8px;
    box-shadow: none;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.1);
  }

  .govco-dropdown-item {
    color: white;
  }

  .govco-dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
  }
}

/* Alto contraste */
:global(body.high-contrast) .govco-accessibility-bar {
  background-color: #000;
}

:global(body.high-contrast) .govco-main-nav {
  background-color: #000;
}

:global(body.high-contrast) .govco-nav-link.router-link-exact-active {
  border-bottom-color: #FFFF00;
}

/* Utilidades de grid */
.container-fluid {
  width: 100%;
  padding-right: 15px;
  padding-left: 15px;
  margin-right: auto;
  margin-left: auto;
  max-width: 1400px;
}

.row {
  display: flex;
  flex-wrap: wrap;
  margin-right: -15px;
  margin-left: -15px;
}

.align-items-center {
  align-items: center;
}

.col {
  flex: 1 0 0%;
  padding-right: 15px;
  padding-left: 15px;
}

.col-auto {
  flex: 0 0 auto;
  width: auto;
  padding-right: 15px;
  padding-left: 15px;
}

.col-lg {
  flex: 1 0 0%;
  padding-right: 15px;
  padding-left: 15px;
}

.text-end {
  text-align: right;
}

.d-none {
  display: none !important;
}

.d-md-block {
  display: none !important;
}

.d-lg-none {
  display: block !important;
}

.d-xl-inline {
  display: none !important;
}

@media (min-width: 768px) {
  .d-md-block {
    display: block !important;
  }
}

@media (min-width: 992px) {
  .d-lg-none {
    display: none !important;
  }
}

@media (min-width: 1200px) {
  .d-xl-inline {
    display: inline !important;
  }
}
</style>
