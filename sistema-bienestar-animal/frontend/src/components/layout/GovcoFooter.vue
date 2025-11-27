<!-- src/components/layout/GovcoFooter.vue -->
<!-- Footer oficial GOV.CO -->
<template>
  <footer class="govco-footer">
    <!-- Sección principal del footer -->
    <div class="govco-footer-main">
      <div class="container-fluid">
        <div class="footer-grid">
          <!-- Columna de la entidad -->
          <div class="footer-column">
            <div class="footer-entity">
              <img
                src="https://cdn.www.gov.co/layout/v4/images/logo-gov-co-blanco.svg"
                alt="Logo GOV.CO"
                class="footer-govco-logo"
              />
              <div class="footer-entity-info">
                <h4 class="footer-entity-name">Alcaldía Municipal</h4>
                <p class="footer-entity-subtitle">Sistema de Bienestar Animal</p>
              </div>
            </div>
            <p class="footer-description">
              Plataforma integral para la gestión, protección y bienestar de los animales
              en nuestro municipio. Comprometidos con la tenencia responsable y la
              protección animal.
            </p>
          </div>

          <!-- Columna de enlaces rápidos -->
          <div class="footer-column">
            <h5 class="footer-title">Enlaces Rápidos</h5>
            <ul class="footer-links">
              <li>
                <router-link to="/">Inicio</router-link>
              </li>
              <li>
                <router-link to="/denuncias">Reportar Denuncia</router-link>
              </li>
              <li>
                <router-link to="/adopciones">Adoptar una Mascota</router-link>
              </li>
              <li>
                <a href="#" @click.prevent>Preguntas Frecuentes</a>
              </li>
              <li>
                <a href="#" @click.prevent>Normatividad</a>
              </li>
            </ul>
          </div>

          <!-- Columna de contacto -->
          <div class="footer-column">
            <h5 class="footer-title">Contacto</h5>
            <ul class="footer-contact">
              <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <span>Dirección: Calle Principal #123, Centro</span>
              </li>
              <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                <span>Teléfono: (604) 123-4567</span>
              </li>
              <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <span>Email: bienestaranimal@alcaldia.gov.co</span>
              </li>
              <li>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>Horario: Lunes a Viernes 8:00 - 17:00</span>
              </li>
            </ul>
          </div>

          <!-- Columna de redes sociales -->
          <div class="footer-column">
            <h5 class="footer-title">Redes Sociales</h5>
            <div class="footer-social">
              <a href="#" class="social-link" title="Facebook" aria-label="Facebook">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                </svg>
              </a>
              <a href="#" class="social-link" title="Twitter/X" aria-label="Twitter">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/>
                </svg>
              </a>
              <a href="#" class="social-link" title="Instagram" aria-label="Instagram">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                  <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                  <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                </svg>
              </a>
              <a href="#" class="social-link" title="YouTube" aria-label="YouTube">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/>
                  <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="#004884"/>
                </svg>
              </a>
            </div>

            <div class="footer-apps">
              <p class="footer-apps-title">Descarga nuestra App</p>
              <div class="app-badges">
                <a href="#" class="app-badge" title="Descargar en App Store">
                  <img src="https://cdn.www.gov.co/layout/v4/images/app-store-badge.svg" alt="App Store" />
                </a>
                <a href="#" class="app-badge" title="Descargar en Google Play">
                  <img src="https://cdn.www.gov.co/layout/v4/images/google-play-badge.svg" alt="Google Play" />
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Barra inferior de copyright -->
    <div class="govco-footer-bottom">
      <div class="container-fluid">
        <div class="footer-bottom-content">
          <div class="footer-copyright">
            <p>&copy; {{ currentYear }} Alcaldía Municipal. Todos los derechos reservados.</p>
            <p class="footer-legal">
              <a href="#" @click.prevent>Política de privacidad</a>
              <span class="separator">|</span>
              <a href="#" @click.prevent>Términos y condiciones</a>
              <span class="separator">|</span>
              <a href="#" @click.prevent>Mapa del sitio</a>
            </p>
          </div>
          <div class="footer-govco-badge">
            <a href="https://www.gov.co/" target="_blank" rel="noopener" title="Portal GOV.CO">
              <img
                src="https://cdn.www.gov.co/layout/v4/images/logo-gov-co.svg"
                alt="GOV.CO"
                class="govco-badge-img"
              />
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';

const currentYear = computed(() => new Date().getFullYear());
</script>

<style scoped>
/* Footer principal */
.govco-footer {
  background-color: #004884;
  color: white;
  margin-top: auto;
}

.govco-footer-main {
  padding: 48px 0 32px;
}

.footer-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 32px;
}

/* Columnas del footer */
.footer-column {
  display: flex;
  flex-direction: column;
}

.footer-entity {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.footer-govco-logo {
  height: 40px;
  width: auto;
}

.footer-entity-name {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
}

.footer-entity-subtitle {
  margin: 0;
  font-size: 0.85rem;
  opacity: 0.85;
}

.footer-description {
  font-size: 0.9rem;
  line-height: 1.6;
  opacity: 0.9;
  margin: 0;
}

/* Títulos de sección */
.footer-title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 16px 0;
  padding-bottom: 8px;
  border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

/* Enlaces del footer */
.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: 10px;
}

.footer-links a {
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s, padding-left 0.2s;
  display: inline-block;
}

.footer-links a:hover {
  color: white;
  padding-left: 4px;
}

/* Contacto */
.footer-contact {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-contact li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 12px;
  font-size: 0.9rem;
}

.footer-contact svg {
  flex-shrink: 0;
  margin-top: 2px;
  opacity: 0.85;
}

.footer-contact span {
  opacity: 0.9;
}

/* Redes sociales */
.footer-social {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
}

.social-link {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  transition: background-color 0.2s, transform 0.2s;
}

.social-link:hover {
  background-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

/* Apps */
.footer-apps-title {
  font-size: 0.85rem;
  margin: 0 0 12px 0;
  opacity: 0.9;
}

.app-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.app-badge img {
  height: 36px;
  width: auto;
  opacity: 0.9;
  transition: opacity 0.2s;
}

.app-badge:hover img {
  opacity: 1;
}

/* Barra inferior */
.govco-footer-bottom {
  background-color: #003366;
  padding: 16px 0;
}

.footer-bottom-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.footer-copyright {
  font-size: 0.85rem;
}

.footer-copyright p {
  margin: 0 0 4px 0;
}

.footer-legal {
  opacity: 0.85;
}

.footer-legal a {
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  transition: color 0.2s;
}

.footer-legal a:hover {
  color: white;
  text-decoration: underline;
}

.footer-legal .separator {
  margin: 0 8px;
  opacity: 0.5;
}

.govco-badge-img {
  height: 32px;
  width: auto;
}

/* Container */
.container-fluid {
  width: 100%;
  padding-right: 15px;
  padding-left: 15px;
  margin-right: auto;
  margin-left: auto;
  max-width: 1400px;
}

/* Responsive */
@media (max-width: 991.98px) {
  .footer-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 575.98px) {
  .footer-grid {
    grid-template-columns: 1fr;
  }

  .govco-footer-main {
    padding: 32px 0 24px;
  }

  .footer-bottom-content {
    flex-direction: column;
    text-align: center;
  }

  .footer-legal .separator {
    display: none;
  }

  .footer-legal a {
    display: block;
    margin: 4px 0;
  }

  .footer-social {
    justify-content: center;
  }

  .app-badges {
    justify-content: center;
  }
}
</style>
