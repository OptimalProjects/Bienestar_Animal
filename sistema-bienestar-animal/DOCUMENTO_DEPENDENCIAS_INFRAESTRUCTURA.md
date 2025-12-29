# DOCUMENTO DE DEPENDENCIAS, APIs, SERVICIOS E INFRAESTRUCTURA
## Sistema de Bienestar Animal - Alcaldia de Santiago de Cali

**Version del Documento:** 1.0
**Fecha de Elaboracion:** 29 de Diciembre de 2025
**Aplicativo:** Sistema de Gestion de Bienestar Animal

---

## TABLA DE CONTENIDO

1. [Resumen Ejecutivo](#1-resumen-ejecutivo)
2. [Arquitectura General del Sistema](#2-arquitectura-general-del-sistema)
3. [Infraestructura de Servidores](#3-infraestructura-de-servidores)
4. [Base de Datos](#4-base-de-datos)
5. [Servicios de Cache y Sesiones](#5-servicios-de-cache-y-sesiones)
6. [APIs Externas e Integraciones](#6-apis-externas-e-integraciones)
7. [Servicios de Correo Electronico](#7-servicios-de-correo-electronico)
8. [Servicios de Almacenamiento](#8-servicios-de-almacenamiento)
9. [Servicios de Autenticacion y Seguridad](#9-servicios-de-autenticacion-y-seguridad)
10. [Servicios de Cola y Procesamiento Asincrono](#10-servicios-de-cola-y-procesamiento-asincrono)
11. [Contenedores e Infraestructura Docker](#11-contenedores-e-infraestructura-docker)
12. [Dependencias de Software](#12-dependencias-de-software)
13. [Puertos y Conectividad de Red](#13-puertos-y-conectividad-de-red)
14. [Variables de Entorno Requeridas](#14-variables-de-entorno-requeridas)
15. [Certificados y Dominios](#15-certificados-y-dominios)
16. [Recursos de Hardware Recomendados](#16-recursos-de-hardware-recomendados)
17. [Lista de Verificacion para Despliegue](#17-lista-de-verificacion-para-despliegue)
18. [Contactos y Responsables](#18-contactos-y-responsables)

---

## 1. RESUMEN EJECUTIVO

El Sistema de Bienestar Animal es una aplicacion web desarrollada para la Alcaldia de Santiago de Cali, destinada a gestionar los procesos de:

- Registro y seguimiento de animales en el refugio
- Atencion veterinaria y control de historiales clinicos
- Gestion de adopciones y seguimiento post-adopcion
- Recepcion y gestion de denuncias por maltrato animal
- Control de inventario de medicinas e insumos
- Generacion de reportes e indicadores

### Stack Tecnologico Principal

| Componente | Tecnologia | Version |
|------------|------------|---------|
| **Backend** | Laravel (PHP) | 11.31 |
| **Frontend** | Vue.js | 3.4.0 |
| **Base de Datos** | MySQL | 8.0 |
| **Cache/Sesiones** | Redis | 7 (Alpine) |
| **Servidor Web** | Nginx | Alpine |
| **Contenedores** | Docker + Docker Compose | 3.8 |
| **Runtime PHP** | PHP-FPM | 8.3 |
| **Runtime Node** | Node.js | 20 (Alpine) |

---

## 2. ARQUITECTURA GENERAL DEL SISTEMA

```
                            +------------------+
                            |    INTERNET      |
                            +--------+---------+
                                     |
                            +--------v---------+
                            |     NGINX        |
                            | (Reverse Proxy)  |
                            |   Puerto: 80/443 |
                            +--------+---------+
                                     |
              +----------------------+----------------------+
              |                                             |
    +---------v----------+                     +------------v---------+
    |    FRONTEND        |                     |      BACKEND         |
    |    (Vue.js)        |                     |    (Laravel/PHP)     |
    |  Puerto: 5173      |                     |   Puerto: 9000       |
    +--------------------+                     +----------+-----------+
                                                          |
                         +--------------------------------+--------------------------------+
                         |                                |                                |
              +----------v-----------+         +----------v-----------+        +----------v-----------+
              |       MySQL 8.0      |         |      Redis 7         |        |   Almacenamiento     |
              |   Puerto: 3306       |         |   Puerto: 6379       |        |      Local           |
              +----------------------+         +----------------------+        +----------------------+
```

### Flujo de Comunicacion

1. Las solicitudes HTTP/HTTPS llegan al servidor Nginx
2. Nginx actua como reverse proxy:
   - Rutas `/api/*` se redirigen al backend Laravel (PHP-FPM)
   - Rutas estaticas y SPA se sirven desde el frontend Vue.js
3. El backend se comunica con MySQL para persistencia y Redis para cache/sesiones
4. Los archivos (fotos, contratos PDF) se almacenan en el sistema de archivos local

---

## 3. INFRAESTRUCTURA DE SERVIDORES

### 3.1 Servidor de Aplicacion (Backend)

**Requerimiento:** Servidor con soporte para contenedores Docker

| Especificacion | Desarrollo | Produccion (Recomendado) |
|----------------|------------|--------------------------|
| **CPU** | 2 cores | 4+ cores |
| **RAM** | 4 GB | 8+ GB |
| **Almacenamiento** | 20 GB SSD | 100+ GB SSD |
| **Sistema Operativo** | Linux (Ubuntu 22.04+) | Linux (Ubuntu 22.04 LTS) |

### 3.2 Imagen Docker Backend (PHP 8.3-FPM)

**Archivo:** `docker/php/Dockerfile`

**Extensiones PHP Requeridas:**
| Extension | Proposito |
|-----------|-----------|
| `pdo_mysql` | Conexion a MySQL |
| `mysqli` | Funciones MySQL nativas |
| `mbstring` | Soporte multibyte (caracteres especiales) |
| `exif` | Metadatos de imagenes |
| `pcntl` | Control de procesos |
| `bcmath` | Calculos de precision |
| `gd` | Procesamiento de imagenes (fotos animales) |
| `zip` | Compresion de archivos |
| `opcache` | Cache de bytecode PHP |
| `intl` | Internacionalizacion |
| `redis` | Extension PECL para Redis |

**Dependencias del Sistema:**
- git
- curl
- libpng-dev
- libonig-dev
- libxml2-dev
- libicu-dev
- libzip-dev
- libfreetype6-dev
- libjpeg62-turbo-dev
- libwebp-dev
- libxpm-dev
- zip, unzip
- supervisor
- composer (ultima version)

### 3.3 Servidor Web Nginx

**Imagen:** `nginx:alpine`

**Configuracion Principal:**
- Tamano maximo de subida: 100MB
- Timeout FastCGI: 300 segundos
- Compresion Gzip habilitada
- Headers de seguridad (X-Frame-Options, X-Content-Type-Options, X-XSS-Protection)

**Headers de Seguridad Configurados:**
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

---

## 4. BASE DE DATOS

### 4.1 MySQL 8.0

**Tipo de Servicio:** Base de datos relacional principal

| Parametro | Valor Desarrollo | Valor Produccion |
|-----------|------------------|------------------|
| **Imagen Docker** | mysql:8.0 | mysql:8.0 |
| **Puerto** | 3306 | 3306 |
| **Base de Datos** | bienestar_animal | bienestar_animal |
| **Usuario** | bienestar_admin | (definir en produccion) |
| **Charset** | utf8mb4 | utf8mb4 |
| **Collation** | utf8mb4_unicode_ci | utf8mb4_unicode_ci |
| **Engine** | InnoDB | InnoDB |

**Credenciales de Desarrollo (NO usar en produccion):**
```
MYSQL_ROOT_PASSWORD=root_secure_2025
MYSQL_DATABASE=bienestar_animal
MYSQL_USER=bienestar_admin
MYSQL_PASSWORD=Ba_2025_Secure!
```

### 4.2 Esquema de Tablas Principales

El sistema utiliza **43 migraciones** que crean las siguientes tablas:

#### Modulo de Usuarios y Autenticacion
| Tabla | Descripcion |
|-------|-------------|
| `users` | Usuarios del sistema Laravel |
| `usuarios` | Usuarios del aplicativo (funcionarios) |
| `roles` | Roles del sistema (admin, director, veterinario, etc.) |
| `permisos` | Permisos granulares |
| `usuario_rol` | Relacion usuarios-roles |
| `rol_permiso` | Relacion roles-permisos |
| `personal_access_tokens` | Tokens de autenticacion Sanctum |
| `sessions` | Sesiones de usuario |

#### Modulo de Animales
| Tabla | Descripcion |
|-------|-------------|
| `animals` | Registro principal de animales |
| `historiales_clinicos` | Historial medico de cada animal |

#### Modulo Veterinario
| Tabla | Descripcion |
|-------|-------------|
| `veterinarios` | Personal veterinario |
| `consultas` | Consultas veterinarias realizadas |
| `examenes_laboratorio` | Examenes de laboratorio |
| `tipos_vacunas` | Catalogo de tipos de vacunas |
| `vacunas` | Vacunas aplicadas |
| `recordatorios_vacunas` | Recordatorios de proximas vacunas |
| `tratamientos` | Tratamientos medicos |
| `cirugias` | Procedimientos quirurgicos |
| `procedimientos` | Catalogo de procedimientos |
| `medicamentos` | Medicamentos administrados |
| `productos_farmaceuticos` | Catalogo de productos |

#### Modulo de Adopciones
| Tabla | Descripcion |
|-------|-------------|
| `adoptantes` | Personas que solicitan adopcion |
| `adopciones` | Procesos de adopcion |
| `visitas_domiciliarias` | Seguimiento post-adopcion |
| `devoluciones` | Registro de animales devueltos |

#### Modulo de Denuncias
| Tabla | Descripcion |
|-------|-------------|
| `denunciantes` | Personas que reportan maltrato |
| `denuncias` | Denuncias de maltrato animal |
| `rescates` | Operativos de rescate |

#### Modulo de Inventario
| Tabla | Descripcion |
|-------|-------------|
| `insumos` | Catalogo de insumos |
| `inventarios` | Movimientos de inventario |

#### Modulo de Reportes y Auditoria
| Tabla | Descripcion |
|-------|-------------|
| `indicadores` | Indicadores de gestion |
| `puntos_indicadores` | Valores de indicadores |
| `eventos_auditoria` | Registro de auditoria (retencion: 5 anios) |

#### Tablas del Sistema Laravel
| Tabla | Descripcion |
|-------|-------------|
| `cache` | Cache de base de datos (fallback) |
| `jobs` | Cola de trabajos |
| `failed_jobs` | Trabajos fallidos |
| `migrations` | Control de migraciones |

### 4.3 Configuraciones de Conexion

**Opciones PDO:**
```php
PDO::ATTR_EMULATE_PREPARES => true
PDO::ATTR_TIMEOUT => 30
```

**Healthcheck:**
```bash
mysqladmin ping -h localhost -u root -p${PASSWORD}
```

**Intervalo de verificacion:** 10 segundos
**Reintentos:** 5
**Tiempo de inicio:** 30 segundos

---

## 5. SERVICIOS DE CACHE Y SESIONES

### 5.1 Redis 7 (Alpine)

**Tipo de Servicio:** Cache en memoria, almacen de sesiones, broker de colas

| Parametro | Valor |
|-----------|-------|
| **Imagen Docker** | redis:7-alpine |
| **Puerto** | 6379 |
| **Memoria Maxima** | 256 MB |
| **Politica de Eviccion** | allkeys-lru |
| **Persistencia** | Activada (appendonly yes) |
| **Cliente PHP** | phpredis |

**Uso en el Sistema:**

| Funcion | Configuracion |
|---------|---------------|
| **Sesiones de Usuario** | SESSION_DRIVER=redis |
| **Cache de Datos** | CACHE_STORE=redis |
| **Cola de Trabajos** | QUEUE_CONNECTION=redis |
| **Prefix** | bienestar_ |

**Credenciales de Desarrollo:**
```
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null
```

**IMPORTANTE para Produccion:** Configurar `REDIS_PASSWORD` con una contrasena segura.

**Healthcheck:**
```bash
redis-cli ping
```

### 5.2 Configuracion de Cache Laravel

**Archivo:** `config/cache.php`

| Driver | Uso |
|--------|-----|
| redis | Principal (produccion y desarrollo) |
| database | Fallback |
| file | Desarrollo local sin Docker |

---

## 6. APIs EXTERNAS E INTEGRACIONES

### 6.1 SCI - Sistema Central de Interoperabilidad

**Estado Actual:** DESHABILITADO (SCI_ENABLED=false)
**Tipo:** REST API del Ecosistema Digital Municipal de Cali

El sistema esta preparado para integrarse con el Sistema Central de Interoperabilidad de la Alcaldia de Cali. Esta integracion permitira:

- Autenticacion centralizada de funcionarios
- Control de acceso basado en roles (RBAC) centralizado
- Envio de eventos de auditoria al sistema central
- Publicacion de indicadores de gestion

#### Endpoints Configurados

| Servicio | URL | Proposito | Estado |
|----------|-----|-----------|--------|
| **SCI Auth** | `https://sci.cali.gov.co/auth/api/v1` | Autenticacion centralizada OAuth2/OpenID | Pendiente |
| **SCI RBAC** | `https://sci.cali.gov.co/rbac/api/v1` | Control de roles y permisos | Pendiente |
| **SCI Audit** | `https://sci.cali.gov.co/audit/api/v1` | Envio de eventos de auditoria | Pendiente |
| **SCI Gateway** | `https://sci.cali.gov.co/gateway` | Gateway de servicios municipales | Pendiente |
| **SCI Indicators** | `https://sci.cali.gov.co/indicators/api/v1` | Publicacion de indicadores | Pendiente |

#### Credenciales Requeridas para Activacion

| Variable | Descripcion | Estado |
|----------|-------------|--------|
| `SCI_ENABLED` | Habilitar integracion | false |
| `SCI_CLIENT_ID` | Identificador del cliente | bienestar-animal |
| `SCI_CLIENT_SECRET` | Secreto del cliente | **PENDIENTE DE SOLICITAR** |

#### Acciones Requeridas para Integracion SCI

1. **Solicitar registro** del aplicativo en el Sistema Central de Interoperabilidad
2. **Obtener credenciales** (CLIENT_ID y CLIENT_SECRET) del equipo SCI
3. **Configurar URLs** de los endpoints segun ambiente (desarrollo/produccion)
4. **Definir roles y permisos** del sistema en el RBAC centralizado
5. **Mapear usuarios** existentes con el sistema de autenticacion centralizado
6. **Activar integracion** cambiando `SCI_ENABLED=true`

### 6.2 Leaflet Maps (OpenStreetMap)

**Libreria Frontend:** Leaflet v1.9.4

**Uso:** Visualizacion de mapas para:
- Ubicacion de denuncias
- Mapa de calor de incidentes
- Direcciones de adoptantes

**Proveedor de Tiles:** OpenStreetMap (gratuito, sin API key)

**URLs de Tiles (configuradas por defecto):**
```
https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png
```

**Requisitos:** Conexion a internet para cargar mapas

---

## 7. SERVICIOS DE CORREO ELECTRONICO

### 7.1 Configuracion Actual (Desarrollo)

**Estado:** Configurado en modo LOG (los emails se registran en logs, no se envian)

| Parametro | Valor Desarrollo |
|-----------|------------------|
| **MAIL_MAILER** | log |
| **MAIL_FROM_ADDRESS** | noreply@cali.gov.co |
| **MAIL_FROM_NAME** | Sistema Bienestar Animal |

### 7.2 Opciones para Produccion

El sistema soporta multiples proveedores de correo. Se debe seleccionar uno:

#### Opcion A: SMTP Institucional
```env
MAIL_MAILER=smtp
MAIL_HOST=(servidor SMTP de la Alcaldia)
MAIL_PORT=587
MAIL_USERNAME=(usuario)
MAIL_PASSWORD=(contrasena)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@cali.gov.co
```

**Solicitar:** Credenciales de servidor SMTP institucional

#### Opcion B: Amazon SES
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=(key)
AWS_SECRET_ACCESS_KEY=(secret)
AWS_DEFAULT_REGION=us-east-1
```

**Solicitar:** Cuenta AWS con SES configurado y dominio verificado

#### Opcion C: Postmark
```env
MAIL_MAILER=postmark
POSTMARK_TOKEN=(token)
```

**Solicitar:** Cuenta Postmark y token de API

#### Opcion D: Resend
```env
MAIL_MAILER=resend
RESEND_KEY=(key)
```

### 7.3 Notificaciones por Email del Sistema

El sistema enviara correos para:

| Evento | Destinatario |
|--------|--------------|
| Confirmacion de denuncia recibida | Denunciante |
| Actualizacion de estado de denuncia | Denunciante |
| Confirmacion de solicitud de adopcion | Adoptante |
| Aprobacion/Rechazo de adopcion | Adoptante |
| Recordatorios de vacunacion | Veterinarios |
| Alertas de stock bajo | Administradores |
| Reportes diarios | Directores |

---

## 8. SERVICIOS DE ALMACENAMIENTO

### 8.1 Almacenamiento Local (Desarrollo)

**Configuracion Actual:** Sistema de archivos local

**Directorio Base:** `storage/app/public/`

| Directorio | Contenido |
|------------|-----------|
| `animales/fotos/` | Fotografias principales de animales |
| `animales/galeria/` | Galerias de fotos adicionales |
| `contratos/` | Contratos de adopcion en PDF |
| `firmas/` | Firmas digitales de contratos |
| `reportes/` | Reportes generados |

**Tamano Maximo de Subida:** 100 MB (configurado en Nginx)

### 8.2 Almacenamiento en la Nube (Produccion - Opcional)

El sistema esta preparado para usar AWS S3:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=(key)
AWS_SECRET_ACCESS_KEY=(secret)
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=(nombre-del-bucket)
AWS_USE_PATH_STYLE_ENDPOINT=false
```

**Solicitar (si se requiere S3):**
- Cuenta AWS
- Bucket S3 creado
- Credenciales IAM con permisos de lectura/escritura

### 8.3 Link Simbolico de Storage

**Comando requerido despues del despliegue:**
```bash
php artisan storage:link
```

Esto crea un enlace simbolico de `public/storage` a `storage/app/public` para servir archivos publicos.

---

## 9. SERVICIOS DE AUTENTICACION Y SEGURIDAD

### 9.1 Laravel Sanctum (Autenticacion API)

**Tipo:** Token-based authentication (JWT-like)

| Parametro | Valor |
|-----------|-------|
| **Guard** | web |
| **Duracion Token** | 1440 minutos (24 horas) |
| **Tipo de Token** | Personal Access Token |

**Dominios Permitidos (CORS):**
```
localhost
localhost:5173
127.0.0.1
127.0.0.1:5173
```

**IMPORTANTE para Produccion:** Actualizar `SANCTUM_STATEFUL_DOMAINS` con el dominio real.

### 9.2 Autenticacion Multi-Factor (MFA)

**Estado:** Habilitado para roles especificos

**Roles que Requieren MFA:**
- admin
- director

**Configuracion:**
```env
MFA_REQUIRED_ROLES=admin,director
```

### 9.3 Encriptacion de Contrasenas

**Algoritmo:** Bcrypt
**Rounds:** 12

### 9.4 Rutas Publicas vs Protegidas

#### Rutas Publicas (Sin autenticacion)
| Metodo | Ruta | Descripcion |
|--------|------|-------------|
| GET | `/api/v1/health` | Health check del sistema |
| POST | `/api/v1/auth/login` | Inicio de sesion |
| POST | `/api/v1/auth/mfa/verify` | Verificacion MFA |
| GET | `/api/v1/animals/catalogo-adopcion` | Catalogo publico de animales |
| POST | `/api/v1/denuncias` | Crear denuncia (publico) |
| GET | `/api/v1/denuncias/consultar/{ticket}` | Consultar estado de denuncia |
| POST | `/api/v1/adopciones/solicitud` | Solicitar adopcion |
| GET | `/api/v1/adopciones/consulta-publica` | Consultar estado de adopcion |

#### Rutas Protegidas (Requieren Bearer Token)
Todas las demas rutas requieren autenticacion mediante token Bearer en el header:
```
Authorization: Bearer {token}
```

### 9.5 Auditoria de Seguridad

**Tabla:** `eventos_auditoria`
**Retencion:** 5 anios (AUDIT_RETENTION_YEARS=5)

**Eventos Auditados:**
- Login/Logout de usuarios
- Creacion/Modificacion de animales
- Aprobacion/Rechazo de adopciones
- Recepcion/Asignacion/Resolucion de denuncias
- Consultas veterinarias
- Vacunas aplicadas
- Cirugias realizadas
- Rescates registrados

---

## 10. SERVICIOS DE COLA Y PROCESAMIENTO ASINCRONO

### 10.1 Sistema de Colas (Laravel Queue)

**Driver:** Redis
**Conexion:** `QUEUE_CONNECTION=redis`

### 10.2 Jobs Programados

| Job | Archivo | Proposito |
|-----|---------|-----------|
| **SendAdoptionReminder** | `app/Jobs/SendAdoptionReminder.php` | Enviar recordatorios de seguimiento de adopciones |
| **ProcessDenunciaUrgency** | `app/Jobs/ProcessDenunciaUrgency.php` | Procesar urgencias y SLA de denuncias |
| **ProcessVaccineReminders** | `app/Jobs/ProcessVaccineReminders.php` | Enviar recordatorios de vacunacion |
| **CheckLowInventoryStock** | `app/Jobs/CheckLowInventoryStock.php` | Alertar sobre stock bajo de medicinas |
| **GenerateDailyReport** | `app/Jobs/GenerateDailyReport.php` | Generar reportes diarios automaticos |
| **CleanupOldAuditLogs** | `app/Jobs/CleanupOldAuditLogs.php` | Limpiar logs de auditoria antiguos |

### 10.3 SLA de Denuncias

| Prioridad | Tiempo Maximo (horas) | Variable |
|-----------|----------------------|----------|
| **Critica** | 4 horas | SLA_DENUNCIA_CRITICA=4 |
| **Alta** | 24 horas | SLA_DENUNCIA_ALTA=24 |
| **Media** | 72 horas | SLA_DENUNCIA_MEDIA=72 |

### 10.4 Comando para Procesar Colas

```bash
php artisan queue:work redis --tries=3
```

**En Docker:** Se ejecuta automaticamente via supervisor o como proceso separado.

---

## 11. CONTENEDORES E INFRAESTRUCTURA DOCKER

### 11.1 Docker Compose Stack

**Archivo:** `docker/docker-compose.yml`
**Version:** 3.8

### 11.2 Servicios Definidos

| Servicio | Imagen | Puerto Host | Puerto Contenedor | Proposito |
|----------|--------|-------------|-------------------|-----------|
| **mysql** | mysql:8.0 | 3306 | 3306 | Base de datos |
| **redis** | redis:7-alpine | 6379 | 6379 | Cache/Sesiones/Colas |
| **backend** | Custom (PHP 8.3-FPM) | - | 9000 | API Laravel |
| **frontend** | node:20-alpine | 5173 | 5173 | Vue.js Dev Server |
| **nginx** | nginx:alpine | 80, 443 | 80, 443 | Reverse Proxy |
| **phpmyadmin** | phpmyadmin:latest | 8080 | 80 | Gestion BD (solo dev) |
| **redis-commander** | rediscommander/redis-commander | 8081 | 8081 | Gestion Redis (solo dev) |

### 11.3 Volumenes Persistentes

| Volumen | Proposito |
|---------|-----------|
| `mysql_data` | Datos de MySQL (persistentes) |
| `redis_data` | Datos de Redis (persistentes) |
| `frontend_node_modules` | Dependencias NPM (evita reinstalar) |

### 11.4 Red Docker

**Nombre:** `bienestar_network`
**Driver:** bridge
**Subred:** 172.28.0.0/16

### 11.5 Dependencias entre Servicios

```
nginx
  └── depends_on: backend, frontend
      │
      ├── backend
      │   └── depends_on: mysql (healthy), redis (healthy)
      │
      └── frontend (independiente)

phpmyadmin
  └── depends_on: mysql (healthy)

redis-commander
  └── depends_on: redis (healthy)
```

### 11.6 Comandos Docker Utiles

```bash
# Iniciar todos los servicios
docker-compose up -d

# Iniciar con herramientas de desarrollo
docker-compose --profile dev up -d

# Ver logs
docker-compose logs -f backend

# Ejecutar comandos artisan
docker-compose exec backend php artisan migrate

# Reiniciar un servicio
docker-compose restart backend

# Detener todo
docker-compose down

# Detener y eliminar volumenes (CUIDADO: borra datos)
docker-compose down -v
```

---

## 12. DEPENDENCIAS DE SOFTWARE

### 12.1 Dependencias PHP (Backend)

**Archivo:** `backend/composer.json`

#### Produccion (require)

| Paquete | Version | Proposito |
|---------|---------|-----------|
| php | ^8.2 | Runtime PHP |
| laravel/framework | ^11.31 | Framework principal |
| laravel/sanctum | ^4.2 | Autenticacion API |
| laravel/tinker | ^2.9 | REPL para debugging |
| barryvdh/laravel-dompdf | ^3.1 | Generacion de PDF (contratos) |

#### Desarrollo (require-dev)

| Paquete | Version | Proposito |
|---------|---------|-----------|
| fakerphp/faker | ^1.23 | Datos de prueba |
| laravel/pail | ^1.1 | Logs en tiempo real |
| laravel/pint | ^1.13 | Formateo de codigo |
| laravel/sail | ^1.26 | Docker development |
| mockery/mockery | ^1.6 | Mocking en tests |
| nunomaduro/collision | ^8.1 | Error handling |
| phpunit/phpunit | ^11.0.1 | Testing |

### 12.2 Dependencias JavaScript (Frontend)

**Archivo:** `frontend/package.json`

#### Produccion (dependencies)

| Paquete | Version | Proposito |
|---------|---------|-----------|
| vue | ^3.4.0 | Framework UI |
| vue-router | ^4.3.0 | Enrutamiento SPA |
| pinia | ^3.0.4 | State management |
| axios | ^1.13.2 | Cliente HTTP |
| leaflet | ^1.9.4 | Mapas interactivos |
| jspdf | ^3.0.4 | Generacion PDF cliente |

#### Desarrollo (devDependencies)

| Paquete | Version | Proposito |
|---------|---------|-----------|
| vite | ^5.1.0 | Build tool |
| @vitejs/plugin-vue | ^5.0.0 | Plugin Vue para Vite |
| tailwindcss | ^3.4.1 | Framework CSS |
| postcss | ^8.4.38 | Procesador CSS |
| autoprefixer | ^10.4.19 | Prefijos CSS |
| vitest | ^4.0.15 | Testing |
| @vitest/coverage-v8 | ^4.0.15 | Cobertura de tests |
| @vue/test-utils | ^2.4.6 | Utilidades testing Vue |
| jsdom | ^27.3.0 | DOM virtual para tests |

---

## 13. PUERTOS Y CONECTIVIDAD DE RED

### 13.1 Puertos Requeridos

| Puerto | Protocolo | Servicio | Acceso |
|--------|-----------|----------|--------|
| **80** | HTTP | Nginx | Publico |
| **443** | HTTPS | Nginx | Publico |
| **3306** | TCP | MySQL | Interno (contenedores) |
| **6379** | TCP | Redis | Interno (contenedores) |
| **9000** | TCP | PHP-FPM | Interno (contenedores) |
| **5173** | TCP | Vite Dev Server | Desarrollo |

### 13.2 Puertos de Herramientas (Solo Desarrollo)

| Puerto | Servicio | Acceso |
|--------|----------|--------|
| **8080** | phpMyAdmin | Localhost |
| **8081** | Redis Commander | Localhost |

### 13.3 Comunicacion Externa Requerida

| Destino | Puerto | Proposito |
|---------|--------|-----------|
| `sci.cali.gov.co` | 443 | APIs SCI (cuando se active) |
| `tile.openstreetmap.org` | 443 | Tiles de mapas |
| Servidor SMTP | 587/465 | Envio de correos (produccion) |

### 13.4 Reglas de Firewall Recomendadas

**Entrada (Inbound):**
- Puerto 80 (HTTP) - Permitir desde cualquier origen
- Puerto 443 (HTTPS) - Permitir desde cualquier origen
- Puerto 22 (SSH) - Permitir desde IPs especificas (administracion)

**Salida (Outbound):**
- Puerto 443 - Permitir hacia sci.cali.gov.co
- Puerto 443 - Permitir hacia tile.openstreetmap.org
- Puerto SMTP - Permitir hacia servidor de correo

---

## 14. VARIABLES DE ENTORNO REQUERIDAS

### 14.1 Variables Obligatorias

```env
# ============================================
# APLICACION
# ============================================
APP_NAME="Sistema Bienestar Animal"
APP_ENV=production                    # local|production
APP_KEY=                              # Generar con: php artisan key:generate
APP_DEBUG=false                       # false en produccion
APP_TIMEZONE=America/Bogota
APP_URL=https://bienestaranimal.cali.gov.co  # URL de produccion
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_CO

# ============================================
# BASE DE DATOS
# ============================================
DB_CONNECTION=mysql
DB_HOST=mysql                         # Nombre del servicio Docker
DB_PORT=3306
DB_DATABASE=bienestar_animal
DB_USERNAME=bienestar_admin           # Cambiar en produccion
DB_PASSWORD=                          # Definir contrasena segura

# ============================================
# CACHE Y SESIONES
# ============================================
SESSION_DRIVER=redis
SESSION_LIFETIME=120
CACHE_STORE=redis
CACHE_PREFIX=bienestar_
QUEUE_CONNECTION=redis

# ============================================
# REDIS
# ============================================
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=                       # Definir en produccion

# ============================================
# CORREO ELECTRONICO
# ============================================
MAIL_MAILER=smtp                      # Cambiar segun proveedor
MAIL_HOST=                            # Servidor SMTP
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@cali.gov.co
MAIL_FROM_NAME="${APP_NAME}"

# ============================================
# ALMACENAMIENTO
# ============================================
FILESYSTEM_DISK=local                 # o 's3' para AWS

# ============================================
# AUTENTICACION
# ============================================
SANCTUM_STATEFUL_DOMAINS=bienestaranimal.cali.gov.co
BCRYPT_ROUNDS=12

# ============================================
# SEGURIDAD MFA
# ============================================
MFA_REQUIRED_ROLES=admin,director

# ============================================
# SLA DENUNCIAS
# ============================================
SLA_DENUNCIA_CRITICA=4
SLA_DENUNCIA_ALTA=24
SLA_DENUNCIA_MEDIA=72

# ============================================
# AUDITORIA
# ============================================
AUDIT_RETENTION_YEARS=5

# ============================================
# LOGGING
# ============================================
LOG_CHANNEL=stack
LOG_LEVEL=warning                     # debug|info|warning|error
```

### 14.2 Variables Opcionales (SCI)

```env
# ============================================
# SCI - SISTEMA CENTRAL INTEROPERABILIDAD
# ============================================
SCI_ENABLED=false                     # true cuando se active
SCI_AUTH_URL=https://sci.cali.gov.co/auth/api/v1
SCI_RBAC_URL=https://sci.cali.gov.co/rbac/api/v1
SCI_AUDIT_URL=https://sci.cali.gov.co/audit/api/v1
SCI_GATEWAY_URL=https://sci.cali.gov.co/gateway
SCI_INDICATORS_URL=https://sci.cali.gov.co/indicators/api/v1
SCI_CLIENT_ID=bienestar-animal
SCI_CLIENT_SECRET=                    # Solicitar a SCI
```

### 14.3 Variables Opcionales (AWS S3)

```env
# ============================================
# AWS S3 (si se usa almacenamiento en la nube)
# ============================================
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
```

### 14.4 Variables Frontend

**Archivo:** `frontend/.env`

```env
VITE_API_BASE_URL=https://bienestaranimal.cali.gov.co/api/v1
VITE_APP_NAME="Sistema Bienestar Animal"
VITE_APP_VERSION=1.0.0
```

---

## 15. CERTIFICADOS Y DOMINIOS

### 15.1 Dominio Requerido

**Dominio Sugerido:** `bienestaranimal.cali.gov.co`

**Acciones Requeridas:**
1. Solicitar subdominio al area de TI de la Alcaldia
2. Configurar registro DNS tipo A apuntando al servidor

### 15.2 Certificado SSL/TLS

**Requisito:** Certificado SSL valido para HTTPS

**Opciones:**
1. **Let's Encrypt** (gratuito, renovacion automatica)
2. **Certificado institucional** de la Alcaldia
3. **Certificado comercial** (DigiCert, Comodo, etc.)

**Ubicacion en Docker:**
```
docker/nginx/ssl/
  ├── certificate.crt
  └── private.key
```

### 15.3 Configuracion Nginx para SSL

```nginx
server {
    listen 443 ssl;
    server_name bienestaranimal.cali.gov.co;

    ssl_certificate /etc/nginx/ssl/certificate.crt;
    ssl_certificate_key /etc/nginx/ssl/private.key;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers off;

    # ... resto de configuracion
}
```

---

## 16. RECURSOS DE HARDWARE RECOMENDADOS

### 16.1 Ambiente de Desarrollo

| Recurso | Minimo | Recomendado |
|---------|--------|-------------|
| **CPU** | 2 cores | 4 cores |
| **RAM** | 4 GB | 8 GB |
| **Disco** | 20 GB SSD | 50 GB SSD |
| **Red** | 10 Mbps | 100 Mbps |

### 16.2 Ambiente de Produccion

| Recurso | Minimo | Recomendado | Alta Disponibilidad |
|---------|--------|-------------|---------------------|
| **CPU** | 4 cores | 8 cores | 16 cores |
| **RAM** | 8 GB | 16 GB | 32 GB |
| **Disco** | 100 GB SSD | 250 GB SSD | 500 GB SSD (RAID) |
| **Red** | 100 Mbps | 1 Gbps | 1 Gbps redundante |

### 16.3 Estimacion de Almacenamiento

| Concepto | Tamano Estimado (anual) |
|----------|------------------------|
| Base de datos MySQL | 5-10 GB |
| Fotos de animales | 20-50 GB |
| Contratos PDF | 2-5 GB |
| Logs del sistema | 5-10 GB |
| Redis (cache) | 256 MB (configurado) |
| **Total estimado** | **35-80 GB/anio** |

---

## 17. LISTA DE VERIFICACION PARA DESPLIEGUE

### 17.1 Pre-requisitos de Infraestructura

- [ ] Servidor con Ubuntu 22.04 LTS o similar
- [ ] Docker y Docker Compose instalados
- [ ] Acceso SSH al servidor
- [ ] Dominio configurado y apuntando al servidor
- [ ] Certificado SSL obtenido

### 17.2 Configuracion de Red

- [ ] Puerto 80 abierto (HTTP)
- [ ] Puerto 443 abierto (HTTPS)
- [ ] Puerto 22 abierto (SSH - solo IPs autorizadas)
- [ ] Firewall configurado

### 17.3 Servicios Externos

- [ ] Servidor SMTP configurado y credenciales obtenidas
- [ ] (Opcional) Credenciales AWS para S3
- [ ] (Opcional) Credenciales SCI para integracion municipal

### 17.4 Variables de Entorno

- [ ] `APP_KEY` generada
- [ ] `APP_URL` configurada con dominio de produccion
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Credenciales de base de datos seguras
- [ ] `REDIS_PASSWORD` configurada
- [ ] `SANCTUM_STATEFUL_DOMAINS` actualizado
- [ ] Credenciales de correo configuradas

### 17.5 Despliegue

- [ ] Clonar repositorio
- [ ] Copiar archivo `.env` de produccion
- [ ] Ejecutar `docker-compose up -d`
- [ ] Ejecutar migraciones: `docker-compose exec backend php artisan migrate`
- [ ] Crear link de storage: `docker-compose exec backend php artisan storage:link`
- [ ] Ejecutar seeders iniciales (usuarios, roles)
- [ ] Verificar health check: `GET /api/v1/health`

### 17.6 Post-despliegue

- [ ] Configurar respaldos automaticos de MySQL
- [ ] Configurar monitoreo del servidor
- [ ] Configurar rotacion de logs
- [ ] Documentar credenciales en lugar seguro
- [ ] Crear usuario administrador inicial
- [ ] Probar flujos criticos (login, denuncias, adopciones)

---

## 18. CONTACTOS Y RESPONSABLES

### 18.1 Equipo de Desarrollo

| Rol | Area | Responsabilidad |
|-----|------|-----------------|
| Lider Tecnico | Desarrollo | Arquitectura y decisiones tecnicas |
| Desarrollador Backend | Desarrollo | API Laravel, integraciones |
| Desarrollador Frontend | Desarrollo | Vue.js, UI/UX |
| DBA | Desarrollo | Base de datos, optimizacion |

### 18.2 Equipo de Infraestructura (Alcaldia)

| Servicio | Area a Contactar |
|----------|------------------|
| Servidores/Hosting | Direccion TIC |
| Dominio DNS | Direccion TIC |
| Certificados SSL | Direccion TIC |
| Correo SMTP | Direccion TIC |
| SCI - Interoperabilidad | Ecosistema Digital |

### 18.3 Solicitudes Pendientes

| Solicitud | Responsable | Estado | Prioridad |
|-----------|-------------|--------|-----------|
| Credenciales SCI (CLIENT_SECRET) | Ecosistema Digital | Pendiente | Alta |
| Servidor de produccion | Direccion TIC | Pendiente | Critica |
| Dominio bienestaranimal.cali.gov.co | Direccion TIC | Pendiente | Critica |
| Certificado SSL | Direccion TIC | Pendiente | Critica |
| Credenciales SMTP institucional | Direccion TIC | Pendiente | Alta |
| (Opcional) Cuenta AWS para S3 | Direccion TIC | Pendiente | Media |

---

## ANEXO A: DIAGRAMA DE ARQUITECTURA DETALLADO

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              INTERNET / USUARIOS                                 │
└─────────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                              BALANCEADOR / CDN                                   │
│                         (Opcional para alta disponibilidad)                      │
└─────────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                                                                                  │
│    ┌──────────────────────────────────────────────────────────────────────┐     │
│    │                         NGINX (Reverse Proxy)                         │     │
│    │                          Puerto: 80 / 443                             │     │
│    │  • SSL Termination                                                    │     │
│    │  • Compresion Gzip                                                    │     │
│    │  • Headers de Seguridad                                               │     │
│    │  • Cache de Assets Estaticos                                          │     │
│    └───────────────┬────────────────────────────────┬─────────────────────┘     │
│                    │                                │                            │
│                    ▼                                ▼                            │
│    ┌─────────────────────────────┐  ┌─────────────────────────────────────┐     │
│    │      FRONTEND (Vue.js)      │  │        BACKEND (Laravel)            │     │
│    │      node:20-alpine         │  │        php:8.3-fpm                  │     │
│    │      Puerto: 5173           │  │        Puerto: 9000                 │     │
│    │                             │  │                                     │     │
│    │  • Vue 3 + Composition API  │  │  • Laravel 11                       │     │
│    │  • Vue Router               │  │  • Sanctum Auth                     │     │
│    │  • Pinia State              │  │  • DomPDF                           │     │
│    │  • Axios HTTP               │  │  • Queue Workers                    │     │
│    │  • Leaflet Maps             │  │  • Events/Listeners                 │     │
│    │  • Tailwind CSS             │  │                                     │     │
│    └─────────────────────────────┘  └──────────────┬──────────────────────┘     │
│                                                     │                            │
│                    ┌────────────────────────────────┼────────────────────────┐   │
│                    │                                │                        │   │
│                    ▼                                ▼                        ▼   │
│    ┌─────────────────────────┐  ┌─────────────────────────┐  ┌─────────────────┐│
│    │     MySQL 8.0           │  │      Redis 7            │  │  File Storage   ││
│    │     Puerto: 3306        │  │      Puerto: 6379       │  │                 ││
│    │                         │  │                         │  │  • Fotos        ││
│    │  • 43 Tablas            │  │  • Sesiones             │  │  • Contratos    ││
│    │  • InnoDB               │  │  • Cache                │  │  • Reportes     ││
│    │  • UTF8MB4              │  │  • Colas                │  │  • Firmas       ││
│    │  • Volumen persistente  │  │  • 256MB Max            │  │                 ││
│    └─────────────────────────┘  └─────────────────────────┘  └─────────────────┘│
│                                                                                  │
│                              DOCKER NETWORK: bienestar_network                   │
│                                   172.28.0.0/16                                  │
└─────────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        ▼
┌─────────────────────────────────────────────────────────────────────────────────┐
│                           SERVICIOS EXTERNOS                                     │
│                                                                                  │
│    ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────────────────┐    │
│    │   SCI APIs      │  │   SMTP Server   │  │   OpenStreetMap Tiles       │    │
│    │   (Pendiente)   │  │   (Pendiente)   │  │   (Activo)                  │    │
│    │                 │  │                 │  │                             │    │
│    │  • Auth         │  │  • Notificaciones│  │  • Mapas de denuncias      │    │
│    │  • RBAC         │  │  • Alertas      │  │  • Ubicaciones              │    │
│    │  • Audit        │  │  • Reportes     │  │                             │    │
│    │  • Indicators   │  │                 │  │                             │    │
│    └─────────────────┘  └─────────────────┘  └─────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────────────────────┘
```

---

## ANEXO B: ENDPOINTS API COMPLETOS

### Rutas Publicas (8 endpoints)
```
GET     /api/v1/health
POST    /api/v1/auth/login
POST    /api/v1/auth/mfa/verify
GET     /api/v1/animals/catalogo-adopcion
POST    /api/v1/denuncias
GET     /api/v1/denuncias/consultar/{ticket}
POST    /api/v1/adopciones/solicitud
GET     /api/v1/adopciones/consulta-publica
POST    /api/v1/adopciones/{id}/contrato/firmar-publico
```

### Rutas Autenticacion (3 endpoints)
```
POST    /api/v1/auth/logout
POST    /api/v1/auth/refresh
GET     /api/v1/auth/me
```

### Modulo Animales (9 endpoints)
```
GET     /api/v1/animals/statistics
GET     /api/v1/animals
POST    /api/v1/animals
GET     /api/v1/animals/{id}
PUT     /api/v1/animals/{id}
DELETE  /api/v1/animals/{id}
GET     /api/v1/animals/{animalId}/historial-clinico
PUT     /api/v1/animals/{animalId}/historial-clinico
POST    /api/v1/animals/{animalId}/chip
GET     /api/v1/historial-clinico/buscar-chip/{chip}
```

### Modulo Veterinaria (18 endpoints)
```
GET     /api/v1/veterinarios
GET     /api/v1/consultas/estadisticas
GET     /api/v1/consultas/hoy
GET     /api/v1/consultas/pendientes
GET     /api/v1/consultas
POST    /api/v1/consultas
GET     /api/v1/consultas/{id}
GET     /api/v1/vacunas/tipos
GET     /api/v1/vacunas/veterinarios
GET     /api/v1/vacunas/proximas
GET     /api/v1/vacunas/animal/{animalId}
GET     /api/v1/vacunas
POST    /api/v1/vacunas
GET     /api/v1/vacunas/{id}
GET     /api/v1/cirugias/procedimientos
GET     /api/v1/cirugias/estadisticas
GET     /api/v1/cirugias/animal/{animalId}
GET     /api/v1/cirugias
POST    /api/v1/cirugias
GET     /api/v1/cirugias/{id}
PUT     /api/v1/cirugias/{id}
```

### Modulo Adopciones (20 endpoints)
```
GET     /api/v1/adopciones/estadisticas
GET     /api/v1/adopciones/pendientes
GET     /api/v1/adopciones/devoluciones
GET     /api/v1/adopciones/devoluciones/motivos
GET     /api/v1/adopciones/devoluciones/estadisticas
GET     /api/v1/adopciones/devoluciones/{devolucionId}
PUT     /api/v1/adopciones/devoluciones/{devolucionId}/revision
GET     /api/v1/adopciones
POST    /api/v1/adopciones
GET     /api/v1/adopciones/{id}
PUT     /api/v1/adopciones/{id}/evaluar
GET     /api/v1/adopciones/{id}/contrato
GET     /api/v1/adopciones/{id}/contrato/descargar
POST    /api/v1/adopciones/{id}/contrato/firmar
GET     /api/v1/adopciones/{id}/estado-contrato
POST    /api/v1/adopciones/{id}/devolucion
GET     /api/v1/visitas-seguimiento/pendientes
GET     /api/v1/visitas-seguimiento/requieren-visita
GET     /api/v1/visitas-seguimiento/adopcion/{adopcionId}
GET     /api/v1/visitas-seguimiento
POST    /api/v1/visitas-seguimiento
GET     /api/v1/visitas-seguimiento/{id}
POST    /api/v1/visitas-seguimiento/{id}/registrar
PUT     /api/v1/visitas-seguimiento/{id}/reprogramar
DELETE  /api/v1/visitas-seguimiento/{id}
```

### Modulo Denuncias (14 endpoints)
```
GET     /api/v1/denuncias/estadisticas
GET     /api/v1/denuncias/urgentes
GET     /api/v1/denuncias/mis-asignaciones
GET     /api/v1/denuncias/mapa-calor
GET     /api/v1/denuncias
GET     /api/v1/denuncias/{id}
PUT     /api/v1/denuncias/{id}/asignar
PUT     /api/v1/denuncias/{id}/estado
GET     /api/v1/rescates/estadisticas
GET     /api/v1/rescates
POST    /api/v1/rescates
GET     /api/v1/rescates/{id}
PUT     /api/v1/rescates/{id}
PUT     /api/v1/rescates/{id}/vincular-animal
```

### Modulo Administracion (21 endpoints)
```
GET     /api/v1/usuarios/roles
GET     /api/v1/usuarios
POST    /api/v1/usuarios
GET     /api/v1/usuarios/{id}
PUT     /api/v1/usuarios/{id}
PUT     /api/v1/usuarios/{id}/password
PUT     /api/v1/usuarios/{id}/toggle-activo
DELETE  /api/v1/usuarios/{id}
GET     /api/v1/inventario/estadisticas
GET     /api/v1/inventario/stock-bajo
GET     /api/v1/inventario/proximos-vencer
GET     /api/v1/inventario/vencidos
GET     /api/v1/inventario/verificar-stock
GET     /api/v1/inventario/insumos
GET     /api/v1/inventario
POST    /api/v1/inventario
PUT     /api/v1/inventario/{id}
POST    /api/v1/inventario/{id}/entrada
POST    /api/v1/inventario/{id}/salida
GET     /api/v1/reportes/dashboard
GET     /api/v1/reportes/indicadores
POST    /api/v1/reportes/indicadores/{indicadorId}/punto
GET     /api/v1/reportes/periodo
GET     /api/v1/reportes/exportar
```

**Total: ~95 endpoints**

---

## ANEXO C: GLOSARIO DE TERMINOS

| Termino | Definicion |
|---------|------------|
| **API** | Application Programming Interface - Interfaz de programacion |
| **CORS** | Cross-Origin Resource Sharing - Politica de seguridad del navegador |
| **Docker** | Plataforma de contenedores para despliegue de aplicaciones |
| **JWT** | JSON Web Token - Token de autenticacion |
| **MFA** | Multi-Factor Authentication - Autenticacion multifactor |
| **Nginx** | Servidor web y reverse proxy |
| **PHP-FPM** | FastCGI Process Manager para PHP |
| **Redis** | Base de datos en memoria para cache y sesiones |
| **Sanctum** | Sistema de autenticacion de Laravel para SPAs |
| **SCI** | Sistema Central de Interoperabilidad de la Alcaldia de Cali |
| **SLA** | Service Level Agreement - Acuerdo de nivel de servicio |
| **SPA** | Single Page Application - Aplicacion de pagina unica |
| **SSL/TLS** | Protocolos de seguridad para comunicacion HTTPS |

---

**Fin del Documento**

*Este documento debe actualizarse cada vez que se agreguen nuevas dependencias, integraciones o cambios en la infraestructura del sistema.*
