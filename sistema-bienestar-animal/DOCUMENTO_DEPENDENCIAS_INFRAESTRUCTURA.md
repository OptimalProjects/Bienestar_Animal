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
MFA_CODE_EXPIRATION=5
MFA_MAX_ATTEMPTS=3
```

#### Parametros de MFA

| Parametro | Valor | Descripcion |
|-----------|-------|-------------|
| **Tipo de codigo** | Numerico | 6 digitos |
| **Transporte** | Email | Via SMTP |
| **Expiracion** | 5 minutos | TTL en Redis |
| **Intentos maximos** | 3 | Por sesion de login |
| **Hash del codigo** | BCrypt | Almacenado hasheado en Redis |

#### Uso de Redis para MFA

| Funcionalidad | Key Pattern | TTL |
|---------------|-------------|-----|
| Codigos MFA | `mfa_code:{user_id}` | 5 minutos |
| Intentos MFA | `mfa_attempts:{user_id}` | 15 minutos |
| Verificacion MFA | `mfa_verified:{user_id}` | 1 hora |
| Rate Limiting Login | `rate_limit:login:{ip}` | 1 minuto |

#### Plantilla de Email MFA

```
Asunto: Codigo de Verificacion - Sistema Bienestar Animal

Contenido:
--------------------------------------------------
Su codigo de verificacion es: {CODIGO_6_DIGITOS}

Este codigo expira en 5 minutos.

Si usted no solicito este codigo, ignore este mensaje.

Sistema de Bienestar Animal
Alcaldia de Santiago de Cali
--------------------------------------------------
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

### 9.5 Rate Limiting

| Endpoint | Limite | Ventana | Descripcion |
|----------|--------|---------|-------------|
| `/auth/login` | 5 intentos | 1 minuto | Proteccion contra fuerza bruta |
| `/auth/mfa/verify` | 3 intentos | 1 minuto | Proteccion codigo MFA |
| `/auth/refresh` | 10 solicitudes | 1 minuto | Renovacion de tokens |
| Otros endpoints | 60 solicitudes | 1 minuto | Rate limit general |

### 9.6 Configuracion CORS

**Archivo:** `config/cors.php`

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',      // Desarrollo
        'http://127.0.0.1:5173',      // Desarrollo alt
        'https://bienestaranimal.cali.gov.co',  // Produccion
    ],
    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Accept',
        'Origin',
        'X-Trace-ID',
    ],
    'supports_credentials' => true,
];
```

### 9.7 Headers de Seguridad Adicionales (Produccion)

```nginx
# Configurar en Nginx para produccion
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" always;
```

### 9.8 Auditoria de Seguridad

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

### 9.9 Roles y Permisos del Sistema

#### Matriz de Roles

| Codigo | Nombre | MFA Obligatorio | Modulo | Nivel de Acceso |
|--------|--------|-----------------|--------|-----------------|
| `ADMIN` | Administrador del Sistema | Si | general | Total |
| `DIRECTOR` | Director del Programa | Si | general | Alto |
| `COORDINADOR` | Coordinador de Operaciones | No | general | Medio-Alto |
| `VETERINARIO` | Medico Veterinario | No | veterinaria | Medio |
| `AUXILIAR_VET` | Auxiliar Veterinario | No | veterinaria | Bajo |
| `OPERADOR` | Operador de Rescate | No | denuncias | Medio |
| `EVALUADOR` | Evaluador de Adopciones | No | adopciones | Medio |

#### Permisos por Modulo - Autenticacion

| Permiso | Descripcion | ADMIN | DIRECTOR | COORD | VET | AUX | OP | EVAL |
|---------|-------------|-------|----------|-------|-----|-----|-----|------|
| `auth.login` | Iniciar sesion | Si | Si | Si | Si | Si | Si | Si |
| `users.manage` | Gestionar usuarios | Si | Si | No | No | No | No | No |
| `roles.manage` | Gestionar roles | Si | No | No | No | No | No | No |

#### Permisos por Modulo - Animales

| Permiso | Descripcion | ADMIN | DIRECTOR | COORD | VET | AUX | OP | EVAL |
|---------|-------------|-------|----------|-------|-----|-----|-----|------|
| `animales.ver` | Ver animales | Si | Si | Si | Si | Si | Si | Si |
| `animales.crear` | Registrar animales | Si | Si | Si | Si | Si | Si | No |
| `animales.editar` | Editar animales | Si | Si | Si | Si | Si | Si | No |
| `animales.eliminar` | Eliminar animales | Si | Si | Si | No | No | No | No |

#### Permisos por Modulo - Veterinaria

| Permiso | Descripcion | ADMIN | DIRECTOR | COORD | VET | AUX | OP | EVAL |
|---------|-------------|-------|----------|-------|-----|-----|-----|------|
| `veterinaria.ver` | Ver registros veterinarios | Si | Si | Si | Si | Si | No | No |
| `veterinaria.crear` | Crear consultas/vacunas | Si | Si | No | Si | Si | No | No |
| `veterinaria.editar` | Editar registros | Si | Si | No | Si | No | No | No |

#### Permisos por Modulo - Adopciones

| Permiso | Descripcion | ADMIN | DIRECTOR | COORD | VET | AUX | OP | EVAL |
|---------|-------------|-------|----------|-------|-----|-----|-----|------|
| `adopciones.ver` | Ver adopciones | Si | Si | Si | No | No | No | Si |
| `adopciones.evaluar` | Evaluar solicitudes | Si | Si | No | No | No | No | Si |
| `adopciones.aprobar` | Aprobar adopciones | Si | Si | No | No | No | No | No |

#### Permisos por Modulo - Denuncias

| Permiso | Descripcion | ADMIN | DIRECTOR | COORD | VET | AUX | OP | EVAL |
|---------|-------------|-------|----------|-------|-----|-----|-----|------|
| `denuncias.ver` | Ver denuncias | Si | Si | Si | No | No | Si | No |
| `denuncias.asignar` | Asignar denuncias | Si | Si | Si | No | No | No | No |
| `denuncias.gestionar` | Gestionar denuncias | Si | Si | Si | No | No | Si | No |

#### Jerarquia de Permisos

```
ADMIN (Administrador)
├── * (Todos los permisos)
│
DIRECTOR
├── usuarios.*
├── reportes.*
├── animales.*
├── denuncias.*
├── adopciones.*
│
COORDINADOR
├── animales.*
├── denuncias.ver
├── denuncias.asignar
├── adopciones.ver
│
VETERINARIO
├── animales.ver
├── animales.editar
├── veterinaria.*
│
AUXILIAR_VET
├── animales.ver
├── veterinaria.ver
├── veterinaria.registrar
│
OPERADOR
├── animales.ver
├── animales.crear
├── denuncias.*
│
EVALUADOR
├── animales.ver
├── adopciones.*
```

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
MFA_CODE_EXPIRATION=5
MFA_MAX_ATTEMPTS=3

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

### 18.3 Checklist Detallado de Solicitudes

#### Alta Prioridad (Requerido para Funcionamiento)

| # | Solicitud | Area Responsable | Estado | Fecha Solicitud | Fecha Entrega |
|---|-----------|------------------|--------|-----------------|---------------|
| 1 | Servidor de produccion con Ubuntu 22.04 LTS | Direccion TIC / Infraestructura | ⬜ Pendiente | | |
| 2 | Servidor MySQL con base de datos `bienestar_animal` | Direccion TIC / DBA | ⬜ Pendiente | | |
| 3 | Servidor Redis para cache y sesiones MFA | Direccion TIC / Infraestructura | ⬜ Pendiente | | |
| 4 | Dominio/subdominio bienestaranimal.cali.gov.co | Direccion TIC / Comunicaciones | ⬜ Pendiente | | |
| 5 | Certificado SSL/TLS para dominio de produccion | Direccion TIC / Seguridad | ⬜ Pendiente | | |
| 6 | Credenciales SMTP institucional para envio de emails y MFA | Direccion TIC / Comunicaciones | ⬜ Pendiente | | |
| 7 | Apertura de puertos: 80, 443 (entrada), 3306, 6379, 587 (interno) | Direccion TIC / Redes/Seguridad | ⬜ Pendiente | | |

#### Media Prioridad (Integracion SCI)

| # | Solicitud | Area Responsable | Estado | Fecha Solicitud | Fecha Entrega |
|---|-----------|------------------|--------|-----------------|---------------|
| 8 | Registro de aplicacion en SCI (Client ID y Secret) | Alcaldia Digital / Ecosistema Digital | ⬜ Pendiente | | |
| 9 | Documentacion de endpoints SCI Auth y RBAC | Alcaldia Digital / Ecosistema Digital | ⬜ Pendiente | | |
| 10 | Inclusion de IP/Dominio en whitelist de SCI | Alcaldia Digital / Ecosistema Digital | ⬜ Pendiente | | |
| 11 | Acceso a ambiente de staging de SCI para pruebas | Alcaldia Digital / Ecosistema Digital | ⬜ Pendiente | | |
| 12 | Definicion de roles y permisos del sistema en SCI RBAC | Alcaldia Digital / Ecosistema Digital | ⬜ Pendiente | | |

#### Baja Prioridad (Mejoras Futuras)

| # | Solicitud | Area Responsable | Estado | Fecha Solicitud | Fecha Entrega |
|---|-----------|------------------|--------|-----------------|---------------|
| 13 | Cuenta AWS para almacenamiento S3 (opcional) | Direccion TIC | ⬜ Pendiente | | |
| 14 | Integracion con LDAP/Active Directory institucional | Direccion TIC Corporativo | ⬜ Pendiente | | |
| 15 | Servicio de notificaciones SMS (alternativa MFA) | Comunicaciones | ⬜ Pendiente | | |
| 16 | Integracion con sistema de logs centralizado (ELK/Splunk) | Direccion TIC / Infraestructura | ⬜ Pendiente | | |

#### Formato de Solicitud Sugerido

```
SOLICITUD DE INFRAESTRUCTURA/SERVICIO

Proyecto: Sistema de Bienestar Animal
Modulo: [Especificar modulo]
Solicitante: [Nombre del PM/Lider Tecnico]
Fecha: [Fecha de solicitud]

DESCRIPCION DEL REQUERIMIENTO:
[Descripcion detallada de lo que se necesita]

JUSTIFICACION:
[Por que es necesario para el funcionamiento del sistema]

ESPECIFICACIONES TECNICAS:
- [Especificacion 1]
- [Especificacion 2]
- [...]

AMBIENTE REQUERIDO:
☐ Desarrollo
☐ Staging
☐ Produccion

PRIORIDAD:
☐ Alta (Bloquea el proyecto)
☐ Media (Necesario para integracion)
☐ Baja (Mejora futura)

FECHA ESPERADA DE ENTREGA: [Fecha]

CONTACTO TECNICO: [Email/Telefono]
```

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

## ANEXO B: FLUJOS DE AUTENTICACION

### B.1 Diagrama de Secuencia - Login sin MFA

```
Usuario          Frontend           Backend            MySQL           Redis
   │                │                  │                 │               │
   │ Ingresa email  │                  │                 │               │
   │ y password     │                  │                 │               │
   │───────────────>│                  │                 │               │
   │                │ POST /auth/login │                 │               │
   │                │─────────────────>│                 │               │
   │                │                  │ SELECT usuario  │               │
   │                │                  │────────────────>│               │
   │                │                  │    usuario      │               │
   │                │                  │<────────────────│               │
   │                │                  │                 │               │
   │                │                  │ Verificar BCrypt│               │
   │                │                  │ (password_hash) │               │
   │                │                  │                 │               │
   │                │                  │ Generar token   │               │
   │                │                  │ Sanctum         │               │
   │                │                  │                 │               │
   │                │  {token, user}   │                 │               │
   │                │<─────────────────│                 │               │
   │                │                  │                 │               │
   │                │ Guardar en       │                 │               │
   │                │ localStorage     │                 │               │
   │                │                  │                 │               │
   │  Redirigir a   │                  │                 │               │
   │  Dashboard     │                  │                 │               │
   │<───────────────│                  │                 │               │
```

### B.2 Diagrama de Secuencia - Login con MFA

```
Usuario          Frontend           Backend            MySQL           Redis         Email
   │                │                  │                 │               │              │
   │ Ingresa email  │                  │                 │               │              │
   │ y password     │                  │                 │               │              │
   │───────────────>│                  │                 │               │              │
   │                │ POST /auth/login │                 │               │              │
   │                │─────────────────>│                 │               │              │
   │                │                  │ SELECT usuario  │               │              │
   │                │                  │────────────────>│               │              │
   │                │                  │    usuario      │               │              │
   │                │                  │<────────────────│               │              │
   │                │                  │                 │               │              │
   │                │                  │ Verificar BCrypt│               │              │
   │                │                  │                 │               │              │
   │                │                  │ ¿Rol requiere   │               │              │
   │                │                  │  MFA? (ADMIN)   │               │              │
   │                │                  │      SI         │               │              │
   │                │                  │                 │               │              │
   │                │                  │ Generar codigo  │               │              │
   │                │                  │ 6 digitos       │               │              │
   │                │                  │                 │               │              │
   │                │                  │ SET mfa_code:id │               │              │
   │                │                  │ TTL=5min        │               │              │
   │                │                  │─────────────────────────────────>│              │
   │                │                  │                 │               │              │
   │                │                  │ Enviar email    │               │              │
   │                │                  │ con codigo      │               │              │
   │                │                  │─────────────────────────────────────────────────>│
   │                │                  │                 │               │              │
   │                │ {requiere_mfa:   │                 │               │              │
   │                │  true, user_id}  │                 │               │              │
   │                │<─────────────────│                 │               │              │
   │                │                  │                 │               │              │
   │ Mostrar form   │                  │                 │               │              │
   │ MFA            │                  │                 │               │              │
   │<───────────────│                  │                 │               │              │
   │                │                  │                 │               │              │
   │ Ingresa codigo │                  │                 │               │              │
   │ 6 digitos      │                  │                 │               │              │
   │───────────────>│                  │                 │               │              │
   │                │POST /auth/mfa/   │                 │               │              │
   │                │verify            │                 │               │              │
   │                │─────────────────>│                 │               │              │
   │                │                  │ GET mfa_code:id │               │              │
   │                │                  │─────────────────────────────────>│              │
   │                │                  │    codigo       │               │              │
   │                │                  │<─────────────────────────────────│              │
   │                │                  │                 │               │              │
   │                │                  │ Verificar codigo│               │              │
   │                │                  │                 │               │              │
   │                │                  │ Generar token   │               │              │
   │                │                  │                 │               │              │
   │                │  {token, user}   │                 │               │              │
   │                │<─────────────────│                 │               │              │
   │                │                  │                 │               │              │
   │  Redirigir a   │                  │                 │               │              │
   │  Dashboard     │                  │                 │               │              │
   │<───────────────│                  │                 │               │              │
```

### B.3 Flujo de Renovacion de Token

```
Frontend                    Backend                    MySQL
    │                          │                         │
    │ (Token proximo a expirar)│                         │
    │                          │                         │
    │ POST /auth/refresh       │                         │
    │ Authorization: Bearer... │                         │
    │─────────────────────────>│                         │
    │                          │                         │
    │                          │ Verificar token actual  │
    │                          │                         │
    │                          │ Revocar token anterior  │
    │                          │─────────────────────────>│
    │                          │                         │
    │                          │ Crear nuevo token       │
    │                          │─────────────────────────>│
    │                          │                         │
    │     {nuevo_token}        │                         │
    │<─────────────────────────│                         │
    │                          │                         │
    │ Actualizar localStorage  │                         │
    │                          │                         │
```

---

## ANEXO C: DETALLE DE ENDPOINTS DE AUTENTICACION

### C.1 POST /api/v1/auth/login

**Descripcion:** Inicio de sesion de usuario

**Request:**
```json
{
    "email": "usuario@cali.gov.co",
    "password": "contrasena123"
}
```

**Response Exitoso (sin MFA):**
```json
{
    "success": true,
    "data": {
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer",
        "expires_in": 3600,
        "user": {
            "id": "uuid",
            "username": "usuario",
            "email": "usuario@cali.gov.co",
            "nombres": "Juan",
            "apellidos": "Perez",
            "rol": "OPERADOR"
        },
        "permisos": ["animales.ver", "animales.crear", "denuncias.*"]
    }
}
```

**Response (requiere MFA):**
```json
{
    "success": true,
    "data": {
        "requiere_mfa": true,
        "usuario_id": "uuid",
        "mensaje": "Se ha enviado un codigo de verificacion a su correo"
    }
}
```

### C.2 POST /api/v1/auth/mfa/verify

**Descripcion:** Verificacion de codigo MFA

**Request:**
```json
{
    "usuario_id": "uuid",
    "codigo": "123456"
}
```

**Response Exitoso:**
```json
{
    "success": true,
    "data": {
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxx",
        "token_type": "Bearer",
        "expires_in": 3600,
        "user": {
            "id": "uuid",
            "username": "admin",
            "email": "admin@bienestaranimal.gov.co",
            "nombres": "Administrador",
            "apellidos": "Sistema",
            "rol": "ADMIN"
        },
        "permisos": ["*"]
    }
}
```

### C.3 GET /api/v1/auth/me

**Descripcion:** Obtener informacion del usuario autenticado

**Headers:**
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "username": "admin",
        "email": "admin@bienestaranimal.gov.co",
        "nombres": "Administrador",
        "apellidos": "Sistema",
        "rol": {
            "codigo": "ADMIN",
            "nombre": "Administrador"
        },
        "permisos": ["*"],
        "ultimo_acceso": "2025-12-29T10:30:00Z"
    }
}
```

### C.4 POST /api/v1/auth/refresh

**Descripcion:** Renovar token de acceso

**Headers:**
```
Authorization: Bearer 1|token_actual
```

**Response:**
```json
{
    "success": true,
    "data": {
        "token": "2|nuevo_token_xxxxxxxxx",
        "token_type": "Bearer",
        "expires_in": 3600
    }
}
```

### C.5 POST /api/v1/auth/logout

**Descripcion:** Cerrar sesion (revocar token)

**Headers:**
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Response:**
```json
{
    "success": true,
    "message": "Sesion cerrada exitosamente"
}
```

### C.6 Codigos de Error de Autenticacion

| Codigo HTTP | Codigo Interno | Descripcion |
|-------------|----------------|-------------|
| 401 | `AUTH_INVALID_CREDENTIALS` | Credenciales invalidas (email/password) |
| 401 | `AUTH_TOKEN_EXPIRED` | Token expirado |
| 401 | `AUTH_TOKEN_INVALID` | Token invalido o mal formado |
| 403 | `AUTH_MFA_REQUIRED` | Requiere verificacion MFA |
| 403 | `AUTH_MFA_INVALID` | Codigo MFA invalido |
| 403 | `AUTH_MFA_EXPIRED` | Codigo MFA expirado (>5 min) |
| 403 | `AUTH_MFA_MAX_ATTEMPTS` | Maximo de intentos MFA alcanzado |
| 403 | `AUTH_ROLE_DENIED` | Rol no autorizado para la operacion |
| 403 | `AUTH_PERMISSION_DENIED` | Permiso denegado |
| 403 | `AUTH_USER_INACTIVE` | Usuario desactivado |
| 422 | `VALIDATION_ERROR` | Error de validacion en campos |
| 429 | `RATE_LIMIT_EXCEEDED` | Limite de solicitudes excedido |

**Ejemplo de Response de Error:**
```json
{
    "success": false,
    "error": {
        "code": "AUTH_INVALID_CREDENTIALS",
        "message": "Las credenciales proporcionadas son incorrectas",
        "details": null
    }
}
```

**Ejemplo de Error de Validacion:**
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "Error de validacion",
        "details": {
            "email": ["El campo email es requerido"],
            "password": ["La contrasena debe tener al menos 6 caracteres"]
        }
    }
}
```

---

## ANEXO D: ENDPOINTS API COMPLETOS

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

## ANEXO E: USUARIOS DE PRUEBA

### E.1 Usuarios para Ambiente de Desarrollo/Staging

| Usuario | Email | Rol | MFA | Password (Desarrollo) |
|---------|-------|-----|-----|----------------------|
| admin | admin@bienestaranimal.gov.co | ADMIN | Si | Admin2025! |
| director | director@bienestaranimal.gov.co | DIRECTOR | Si | Director2025! |
| coordinador | coordinador@bienestaranimal.gov.co | COORDINADOR | No | Coord2025! |
| vet.garcia | ana.garcia@bienestaranimal.gov.co | VETERINARIO | No | Vet2025! |
| aux.lopez | maria.lopez@bienestaranimal.gov.co | AUXILIAR_VET | No | Aux2025! |
| op.torres | diego.torres@bienestaranimal.gov.co | OPERADOR | No | Op2025! |
| eval.ruiz | carlos.ruiz@bienestaranimal.gov.co | EVALUADOR | No | Eval2025! |

**IMPORTANTE:** Estos usuarios son solo para desarrollo y pruebas. En produccion, crear usuarios con credenciales seguras y unicas.

### E.2 Creacion de Usuario Administrador Inicial

```bash
# Ejecutar dentro del contenedor backend
docker-compose exec backend php artisan tinker

# En el REPL de Tinker:
$user = new \App\Models\User\Usuario();
$user->id = \Illuminate\Support\Str::uuid();
$user->username = 'admin';
$user->email = 'admin@bienestaranimal.gov.co';
$user->password_hash = \Illuminate\Support\Facades\Hash::make('NUEVA_PASSWORD_SEGURA');
$user->nombres = 'Administrador';
$user->apellidos = 'Sistema';
$user->activo = true;
$user->save();

# Asignar rol ADMIN
$rol = \App\Models\User\Rol::where('codigo', 'ADMIN')->first();
$user->roles()->attach($rol->id);
```

---

## ANEXO F: ARCHIVOS DE CONFIGURACION CLAVE

### F.1 Rutas de Archivos Importantes

| Archivo | Ruta | Proposito |
|---------|------|-----------|
| **LoginController** | `backend/app/Http/Controllers/Api/V1/Auth/LoginController.php` | Logica de autenticacion |
| **sanctum.php** | `backend/config/sanctum.php` | Configuracion de tokens Sanctum |
| **auth.php** | `backend/config/auth.php` | Guards y providers de autenticacion |
| **cors.php** | `backend/config/cors.php` | Politicas CORS |
| **database.php** | `backend/config/database.php` | Conexiones a base de datos |
| **mail.php** | `backend/config/mail.php` | Configuracion de email |
| **queue.php** | `backend/config/queue.php` | Configuracion de colas |
| **services.php** | `backend/config/services.php` | Servicios de terceros |
| **api.php** | `backend/routes/api.php` | Rutas de la API |
| **.env** | `backend/.env` | Variables de entorno backend |
| **auth.js** | `frontend/src/stores/auth.js` | Store de autenticacion Pinia |
| **api.js** | `frontend/src/services/api.js` | Cliente HTTP Axios |
| **LoginView.vue** | `frontend/src/views/LoginView.vue` | Componente de login |
| **useRol.js** | `frontend/src/composables/useRol.js` | Composable de permisos |
| **.env** | `frontend/.env` | Variables de entorno frontend |
| **docker-compose.yml** | `docker/docker-compose.yml` | Orquestacion de contenedores |
| **Dockerfile** | `docker/php/Dockerfile` | Imagen PHP personalizada |
| **default.conf** | `docker/nginx/default.conf` | Configuracion Nginx |

### F.2 Modelos de Base de Datos (Backend)

| Modelo | Ruta | Tabla |
|--------|------|-------|
| Usuario | `backend/app/Models/User/Usuario.php` | usuarios |
| Rol | `backend/app/Models/User/Rol.php` | roles |
| Permiso | `backend/app/Models/User/Permiso.php` | permisos |
| EventoAuditoria | `backend/app/Models/User/EventoAuditoria.php` | eventos_auditoria |
| Animal | `backend/app/Models/Animal/Animal.php` | animals |
| Adopcion | `backend/app/Models/Adoption/Adopcion.php` | adopciones |
| Denuncia | `backend/app/Models/Complaint/Denuncia.php` | denuncias |

---

## ANEXO G: COMANDOS UTILES

### G.1 Comandos de Laravel/Artisan

```bash
# Generar clave de aplicacion
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Revertir migraciones
php artisan migrate:rollback

# Ejecutar seeders
php artisan db:seed

# Limpiar caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimizar para produccion
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verificar rutas de API
php artisan route:list --path=api/v1/auth

# Procesar cola de trabajos
php artisan queue:work redis --tries=3

# Ejecutar jobs programados
php artisan schedule:run

# Link de storage
php artisan storage:link
```

### G.2 Comandos de Docker

```bash
# Iniciar todos los servicios
docker-compose up -d

# Iniciar con perfil de desarrollo (incluye phpMyAdmin y Redis Commander)
docker-compose --profile dev up -d

# Ver logs de un servicio
docker-compose logs -f backend
docker-compose logs -f mysql

# Ejecutar comando en contenedor
docker-compose exec backend php artisan migrate
docker-compose exec backend composer install

# Reiniciar servicio
docker-compose restart backend

# Detener servicios
docker-compose down

# Detener y eliminar volumenes (CUIDADO: borra datos)
docker-compose down -v

# Reconstruir imagenes
docker-compose build --no-cache
```

### G.3 Comandos de Verificacion

```bash
# Probar conexion a Redis
docker-compose exec backend php artisan tinker
> Redis::ping()

# Probar conexion a MySQL
docker-compose exec backend php artisan tinker
> DB::connection()->getPdo()

# Verificar health check
curl http://localhost/api/v1/health

# Verificar version de PHP
docker-compose exec backend php -v

# Verificar extensiones PHP
docker-compose exec backend php -m
```

### G.4 Comandos de Backup

```bash
# Backup de base de datos MySQL
docker-compose exec mysql mysqldump -u bienestar_admin -p bienestar_animal > backup_$(date +%Y%m%d).sql

# Restaurar backup
docker-compose exec -T mysql mysql -u bienestar_admin -p bienestar_animal < backup.sql

# Backup de Redis
docker-compose exec redis redis-cli BGSAVE
```

---

## ANEXO H: GLOSARIO DE TERMINOS

| Termino | Definicion |
|---------|------------|
| **API** | Application Programming Interface - Interfaz de programacion |
| **Bearer Token** | Token de acceso enviado en header Authorization |
| **CORS** | Cross-Origin Resource Sharing - Politica de seguridad del navegador |
| **Docker** | Plataforma de contenedores para despliegue de aplicaciones |
| **JWT** | JSON Web Token - Token de autenticacion |
| **MFA** | Multi-Factor Authentication - Autenticacion multifactor |
| **Nginx** | Servidor web y reverse proxy |
| **OAuth2** | Protocolo de autorizacion estandar |
| **PHP-FPM** | FastCGI Process Manager para PHP |
| **Rate Limiting** | Control de frecuencia de solicitudes |
| **RBAC** | Role-Based Access Control - Control de acceso basado en roles |
| **Redis** | Base de datos en memoria para cache y sesiones |
| **Sanctum** | Sistema de autenticacion de Laravel para SPAs |
| **SCI** | Sistema Central de Interoperabilidad de la Alcaldia de Cali |
| **SLA** | Service Level Agreement - Acuerdo de nivel de servicio |
| **SPA** | Single Page Application - Aplicacion de pagina unica |
| **SSL/TLS** | Protocolos de seguridad para comunicacion HTTPS |
| **TTL** | Time To Live - Tiempo de vida de un dato en cache |

---

## Control de Versiones del Documento

| Version | Fecha | Autor | Cambios |
|---------|-------|-------|---------|
| 1.0 | 29/12/2025 | Equipo de Desarrollo | Version inicial |
| 1.1 | 29/12/2025 | Equipo de Desarrollo | Agregado detalle de autenticacion, MFA, roles y permisos |

---

**Fin del Documento**

*Este documento debe actualizarse cada vez que se agreguen nuevas dependencias, integraciones o cambios en la infraestructura del sistema.*
