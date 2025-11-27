<!-- src/views/HomeView.vue -->
<!-- Página de inicio con diseño GOV.CO -->
<template>
  <div class="home-view">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-overlay"></div>
      <div class="hero-content container-govco">
        <h1 class="hero-title">Sistema de Bienestar Animal</h1>
        <p class="hero-subtitle">
          Protegiendo y cuidando a nuestros compañeros animales.
          Juntos por una convivencia responsable.
        </p>
        <div class="hero-actions">
          <router-link to="/denuncias" class="btn-govco btn-govco-primary btn-lg">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
              <line x1="12" y1="9" x2="12" y2="13"></line>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            Reportar Denuncia
          </router-link>
          <router-link to="/adopciones" class="btn-govco btn-govco-outline-white btn-lg">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
            </svg>
            Adoptar una Mascota
          </router-link>
        </div>
      </div>
    </section>

    <!-- Servicios principales -->
    <section class="services-section">
      <div class="container-govco">
        <h2 class="section-title h2-tipografia-govco govcolor-blue-dark">Nuestros Servicios</h2>
        <p class="section-subtitle text2-tipografia-govco">
          Trabajamos por el bienestar de todos los animales en nuestro municipio
        </p>

        <div class="services-grid">
          <!-- Servicio 1: Denuncias -->
          <div class="service-card card-govco">
            <div class="service-icon service-icon-danger">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
            </div>
            <h3 class="service-title h5-tipografia-govco">Denuncias de Maltrato</h3>
            <p class="service-description text2-tipografia-govco">
              Reporta casos de maltrato animal de forma anónima. Nuestro equipo actuará de inmediato.
            </p>
            <router-link to="/denuncias" class="service-link">
              Reportar ahora
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </router-link>
          </div>

          <!-- Servicio 2: Adopciones -->
          <div class="service-card card-govco">
            <div class="service-icon service-icon-success">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
            </div>
            <h3 class="service-title h5-tipografia-govco">Adopciones Responsables</h3>
            <p class="service-description text2-tipografia-govco">
              Encuentra tu compañero ideal. Tenemos animales rescatados esperando un hogar.
            </p>
            <router-link to="/adopciones" class="service-link">
              Ver animales
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </router-link>
          </div>

          <!-- Servicio 3: Atención Veterinaria -->
          <div class="service-card card-govco">
            <div class="service-icon service-icon-primary">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                <path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08v0c.82.82 2.13.85 3 .07l2.07-1.9a2.82 2.82 0 0 1 3.79 0l2.96 2.66"></path>
              </svg>
            </div>
            <h3 class="service-title h5-tipografia-govco">Atención Veterinaria</h3>
            <p class="service-description text2-tipografia-govco">
              Servicios veterinarios gratuitos para animales rescatados y de familias vulnerables.
            </p>
            <a href="#" class="service-link" @click.prevent>
              Más información
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </a>
          </div>

          <!-- Servicio 4: Consulta de Estado -->
          <div class="service-card card-govco">
            <div class="service-icon service-icon-warning">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
            </div>
            <h3 class="service-title h5-tipografia-govco">Consultar Estado</h3>
            <p class="service-description text2-tipografia-govco">
              Consulta el estado de tu denuncia o solicitud con tu número de radicado.
            </p>
            <router-link to="/denuncias" class="service-link">
              Consultar
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Estadísticas -->
    <section class="stats-section">
      <div class="container-govco">
        <div class="stats-grid">
          <div class="stat-item">
            <span class="stat-number">{{ stats.rescued }}</span>
            <span class="stat-label">Animales Rescatados</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">{{ stats.adopted }}</span>
            <span class="stat-label">Adopciones Exitosas</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">{{ stats.sterilized }}</span>
            <span class="stat-label">Esterilizaciones</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">{{ stats.complaints }}</span>
            <span class="stat-label">Denuncias Atendidas</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
      <div class="container-govco">
        <div class="cta-content">
          <h2 class="cta-title">¿Eres funcionario o veterinario?</h2>
          <p class="cta-description">
            Accede al sistema de gestión para registrar animales, gestionar denuncias
            y administrar el programa de bienestar animal.
          </p>
          <router-link to="/login" class="btn-govco btn-govco-primary btn-lg">
            Iniciar Sesión
          </router-link>
        </div>
      </div>
    </section>

    <!-- Información de contacto -->
    <section class="contact-section">
      <div class="container-govco">
        <h2 class="section-title h2-tipografia-govco govcolor-blue-dark">Líneas de Atención</h2>

        <div class="contact-grid">
          <div class="contact-card">
            <div class="contact-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
            </div>
            <h4 class="contact-title">Línea de Emergencias</h4>
            <p class="contact-info">(604) 123-4567</p>
            <p class="contact-hours">24 horas, 7 días</p>
          </div>

          <div class="contact-card">
            <div class="contact-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
            </div>
            <h4 class="contact-title">Correo Electrónico</h4>
            <p class="contact-info">bienestaranimal@alcaldia.gov.co</p>
            <p class="contact-hours">Respuesta en 24-48 horas</p>
          </div>

          <div class="contact-card">
            <div class="contact-icon">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
            </div>
            <h4 class="contact-title">Atención Presencial</h4>
            <p class="contact-info">Calle Principal #123</p>
            <p class="contact-hours">Lunes a Viernes 8:00 - 17:00</p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Estadísticas (conectar con API real)
const stats = ref({
  rescued: 0,
  adopted: 0,
  sterilized: 0,
  complaints: 0
});

// Animación de contadores
function animateCounter(target, end, duration = 2000) {
  let start = 0;
  const increment = end / (duration / 16);

  const timer = setInterval(() => {
    start += increment;
    if (start >= end) {
      target.value = end;
      clearInterval(timer);
    } else {
      target.value = Math.floor(start);
    }
  }, 16);
}

onMounted(() => {
  // Simular carga de estadísticas
  setTimeout(() => {
    stats.value = {
      rescued: 1250,
      adopted: 890,
      sterilized: 2340,
      complaints: 456
    };
  }, 500);
});
</script>

<style scoped>
/* Hero Section */
.hero-section {
  position: relative;
  min-height: 500px;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #004884 0%, #3366CC 100%);
  color: white;
  overflow: hidden;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="2"/></svg>') repeat;
  background-size: 100px 100px;
  opacity: 0.5;
}

.hero-content {
  position: relative;
  z-index: 1;
  text-align: center;
  padding: 60px 20px;
}

.hero-title {
  font-size: 3rem;
  font-weight: 700;
  margin: 0 0 1rem 0;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.hero-subtitle {
  font-size: 1.25rem;
  max-width: 600px;
  margin: 0 auto 2rem;
  opacity: 0.95;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-lg {
  padding: 14px 32px;
  font-size: 1.1rem;
}

.btn-govco-outline-white {
  background: transparent;
  border: 2px solid white;
  color: white;
}

.btn-govco-outline-white:hover {
  background: white;
  color: #004884;
}

/* Services Section */
.services-section {
  padding: 80px 0;
  background: #f5f7fb;
}

.section-title {
  text-align: center;
  margin-bottom: 0.5rem;
}

.section-subtitle {
  text-align: center;
  margin-bottom: 3rem;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}

.service-card {
  padding: 2rem;
  text-align: center;
  transition: transform 0.3s, box-shadow 0.3s;
}

.service-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.service-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
}

.service-icon-primary {
  background: #E8F0FE;
  color: #3366CC;
}

.service-icon-success {
  background: #E8F5E9;
  color: #069169;
}

.service-icon-danger {
  background: #FFEBEE;
  color: #A80521;
}

.service-icon-warning {
  background: #FFF8E1;
  color: #F57C00;
}

.service-title {
  margin: 0 0 0.75rem 0;
  color: #004884;
}

.service-description {
  margin: 0 0 1rem 0;
  min-height: 60px;
}

.service-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #3366CC;
  font-weight: 600;
  text-decoration: none;
  transition: gap 0.2s;
}

.service-link:hover {
  gap: 12px;
}

/* Stats Section */
.stats-section {
  padding: 60px 0;
  background: linear-gradient(135deg, #004884 0%, #3366CC 100%);
  color: white;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 40px;
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 3rem;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 1rem;
  opacity: 0.9;
}

/* CTA Section */
.cta-section {
  padding: 60px 0;
  background: #E8F0FE;
}

.cta-content {
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
}

.cta-title {
  font-size: 2rem;
  font-weight: 700;
  color: #004884;
  margin: 0 0 1rem 0;
}

.cta-description {
  color: #4B4B4B;
  margin: 0 0 2rem 0;
}

/* Contact Section */
.contact-section {
  padding: 80px 0;
  background: white;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 32px;
}

.contact-card {
  text-align: center;
  padding: 2rem;
  border-radius: 8px;
  background: #f5f7fb;
}

.contact-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #E8F0FE;
  color: #3366CC;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}

.contact-title {
  color: #004884;
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
}

.contact-info {
  color: #333;
  font-weight: 600;
  margin: 0 0 0.25rem 0;
}

.contact-hours {
  color: #666;
  font-size: 0.9rem;
  margin: 0;
}

/* Container */
.container-govco {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* Responsive */
@media (max-width: 991.98px) {
  .hero-title {
    font-size: 2.5rem;
  }

  .services-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
  }

  .contact-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 575.98px) {
  .hero-section {
    min-height: 400px;
  }

  .hero-title {
    font-size: 2rem;
  }

  .hero-subtitle {
    font-size: 1rem;
  }

  .hero-actions {
    flex-direction: column;
    align-items: center;
  }

  .btn-lg {
    width: 100%;
    max-width: 280px;
  }

  .services-grid,
  .stats-grid,
  .contact-grid {
    grid-template-columns: 1fr;
  }

  .stat-number {
    font-size: 2.5rem;
  }

  .services-section,
  .contact-section {
    padding: 60px 0;
  }
}
</style>
