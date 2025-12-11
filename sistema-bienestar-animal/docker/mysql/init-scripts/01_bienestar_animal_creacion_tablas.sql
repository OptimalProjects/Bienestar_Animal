-- ============================================
-- SISTEMA INTEGRAL DE GESTIÓN DE BIENESTAR ANIMAL
-- Base de Datos Completa
-- Versión: 1.0
-- Fecha: Noviembre 2025
-- ============================================

USE bienestar_animal;

-- Deshabilitar foreign key checks temporalmente
SET FOREIGN_KEY_CHECKS = 0;


-- Tabla: usuarios
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id CHAR(36) PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    origen_autenticacion VARCHAR(50) DEFAULT 'local',
    mfa_enabled BOOLEAN DEFAULT FALSE,
    mfa_secret VARCHAR(255) NULL,
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: roles
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id CHAR(36) PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    modulo VARCHAR(50) NULL,
    requiere_mfa BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: permisos
DROP TABLE IF EXISTS permisos;
CREATE TABLE permisos (
    id CHAR(36) PRIMARY KEY,
    recurso VARCHAR(100) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    descripcion TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_recurso_accion (recurso, accion),
    INDEX idx_recurso (recurso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla pivot: usuario_rol
DROP TABLE IF EXISTS usuario_rol;
CREATE TABLE usuario_rol (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL,
    rol_id CHAR(36) NOT NULL,
    asignado_por CHAR(36) NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME NULL,
    activo BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (asignado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_usuario_rol (usuario_id, rol_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_rol_id (rol_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla pivot: rol_permiso
DROP TABLE IF EXISTS rol_permiso;
CREATE TABLE rol_permiso (
    id CHAR(36) PRIMARY KEY,
    rol_id CHAR(36) NOT NULL,
    permiso_id CHAR(36) NOT NULL,
    asignado_por CHAR(36) NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE,
    FOREIGN KEY (asignado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_rol_permiso (rol_id, permiso_id),
    INDEX idx_rol_id (rol_id),
    INDEX idx_permiso_id (permiso_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: animals
DROP TABLE IF EXISTS animals;
CREATE TABLE animals (
    id CHAR(36) PRIMARY KEY,
    codigo_unico VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NULL,
    especie VARCHAR(50) NOT NULL,
    raza VARCHAR(100) NULL,
    sexo ENUM('macho', 'hembra', 'desconocido') NULL,
    edad_aproximada INT NULL COMMENT 'Edad en meses',
    peso_actual DECIMAL(6,2) NULL COMMENT 'Peso en kilogramos',
    color VARCHAR(100) NULL,
    tamanio ENUM('pequenio', 'mediano', 'grande', 'muy_grande') NULL,
    senias_particulares TEXT NULL,
    foto_principal VARCHAR(255) NULL,
    galeria_fotos JSON NULL,
    fecha_rescate DATE NULL,
    ubicacion_rescate VARCHAR(255) NULL,
    estado ENUM('en_calle', 'en_refugio', 'en_adopcion', 'adoptado', 'fallecido', 'en_tratamiento') NOT NULL DEFAULT 'en_calle',
    estado_salud ENUM('critico', 'grave', 'estable', 'bueno', 'excelente') DEFAULT 'estable',
    observaciones TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL,
    created_by CHAR(36) NULL,
    updated_by CHAR(36) NULL,
    
    FOREIGN KEY (created_by) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    INDEX idx_codigo_unico (codigo_unico),
    INDEX idx_especie (especie),
    INDEX idx_estado (estado),
    INDEX idx_estado_salud (estado_salud),
    INDEX idx_fecha_rescate (fecha_rescate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: historiales_clinicos
DROP TABLE IF EXISTS historiales_clinicos;
CREATE TABLE historiales_clinicos (
    id CHAR(36) PRIMARY KEY,
    animal_id CHAR(36) NOT NULL UNIQUE,
    fecha_apertura DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('activo', 'cerrado', 'archivado') DEFAULT 'activo',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    
    INDEX idx_animal_id (animal_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: veterinarios
DROP TABLE IF EXISTS veterinarios;
CREATE TABLE veterinarios (
    id CHAR(36) PRIMARY KEY,
    usuario_id CHAR(36) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    numero_tarjeta_profesional VARCHAR(50) NOT NULL UNIQUE,
    especialidad VARCHAR(100) NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_tarjeta_profesional (numero_tarjeta_profesional),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: consultas
DROP TABLE IF EXISTS consultas;
CREATE TABLE consultas (
    id CHAR(36) PRIMARY KEY,
    historial_clinico_id CHAR(36) NOT NULL,
    veterinario_id CHAR(36) NOT NULL,
    fecha_consulta DATETIME NOT NULL,
    motivo TEXT NULL,
    sintomas TEXT NULL,
    signos_vitales JSON NULL,
    temperatura DECIMAL(4,2) NULL COMMENT 'Temperatura en °C',
    frecuencia_cardiaca INT NULL COMMENT 'Latidos por minuto',
    frecuencia_respiratoria INT NULL COMMENT 'Respiraciones por minuto',
    peso DECIMAL(6,2) NULL COMMENT 'Peso en kg',
    diagnostico TEXT NULL,
    diagnostico_diferencial TEXT NULL,
    plan_diagnostico TEXT NULL,
    observaciones TEXT NULL,
    recomendaciones TEXT NULL,
    proxima_cita DATE NULL,
    tipo_consulta ENUM('inicial', 'control', 'emergencia', 'seguimiento') DEFAULT 'inicial',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (historial_clinico_id) REFERENCES historiales_clinicos(id) ON DELETE CASCADE,
    FOREIGN KEY (veterinario_id) REFERENCES veterinarios(id) ON DELETE RESTRICT,
    
    INDEX idx_historial_clinico_id (historial_clinico_id),
    INDEX idx_veterinario_id (veterinario_id),
    INDEX idx_fecha_consulta (fecha_consulta),
    INDEX idx_tipo_consulta (tipo_consulta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: examenes_laboratorio
DROP TABLE IF EXISTS examenes_laboratorio;
CREATE TABLE examenes_laboratorio (
    id CHAR(36) PRIMARY KEY,
    consulta_id CHAR(36) NOT NULL,
    tipo_examen VARCHAR(100) NOT NULL,
    fecha_solicitud DATE NOT NULL,
    fecha_resultado DATE NULL,
    laboratorio VARCHAR(150) NULL,
    resultados JSON NULL,
    interpretacion TEXT NULL,
    archivos_adjuntos JSON NULL,
    estado ENUM('solicitado', 'en_proceso', 'completado', 'cancelado') DEFAULT 'solicitado',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE CASCADE,
    
    INDEX idx_consulta_id (consulta_id),
    INDEX idx_tipo_examen (tipo_examen),
    INDEX idx_estado (estado),
    INDEX idx_fecha_solicitud (fecha_solicitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tipos_vacunas
DROP TABLE IF EXISTS tipos_vacunas;
CREATE TABLE tipos_vacunas (
    id CHAR(36) PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    especie_aplicable ENUM('perro', 'gato', 'ambos', 'otro') NOT NULL,
    edad_minima INT NULL COMMENT 'Edad mínima en meses',
    intervalo_dosis INT NULL COMMENT 'Días entre dosis',
    numero_dosis INT DEFAULT 1,
    es_obligatoria BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_especie_aplicable (especie_aplicable),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: vacunas
DROP TABLE IF EXISTS vacunas;
CREATE TABLE vacunas (
    id CHAR(36) PRIMARY KEY,
    historial_clinico_id CHAR(36) NOT NULL,
    tipo_vacuna_id CHAR(36) NOT NULL,
    fecha_aplicacion DATE NOT NULL,
    fecha_proxima_dosis DATE NULL,
    lote_vacuna VARCHAR(50) NULL,
    fabricante VARCHAR(100) NULL,
    veterinario_id CHAR(36) NOT NULL,
    observaciones TEXT NULL,
    reacciones_adversas TEXT NULL,
    estado ENUM('aplicada', 'programada', 'vencida', 'cancelada') DEFAULT 'aplicada',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (historial_clinico_id) REFERENCES historiales_clinicos(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_vacuna_id) REFERENCES tipos_vacunas(id) ON DELETE RESTRICT,
    FOREIGN KEY (veterinario_id) REFERENCES veterinarios(id) ON DELETE RESTRICT,
    
    INDEX idx_historial_clinico_id (historial_clinico_id),
    INDEX idx_tipo_vacuna_id (tipo_vacuna_id),
    INDEX idx_veterinario_id (veterinario_id),
    INDEX idx_fecha_aplicacion (fecha_aplicacion),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: recordatorios_vacunas
DROP TABLE IF EXISTS recordatorios_vacunas;
CREATE TABLE recordatorios_vacunas (
    id CHAR(36) PRIMARY KEY,
    vacuna_id CHAR(36) NOT NULL,
    animal_id CHAR(36) NOT NULL,
    fecha_recordatorio DATE NOT NULL,
    tipo ENUM('proxima_dosis', 'refuerzo', 'vencimiento') NOT NULL,
    estado ENUM('pendiente', 'enviado', 'completado', 'cancelado') DEFAULT 'pendiente',
    fecha_envio DATETIME NULL,
    canal ENUM('email', 'sms', 'whatsapp', 'sistema') DEFAULT 'sistema',
    destinatario VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (vacuna_id) REFERENCES vacunas(id) ON DELETE CASCADE,
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE CASCADE,
    
    INDEX idx_vacuna_id (vacuna_id),
    INDEX idx_animal_id (animal_id),
    INDEX idx_fecha_recordatorio (fecha_recordatorio),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: tratamientos
DROP TABLE IF EXISTS tratamientos;
CREATE TABLE tratamientos (
    id CHAR(36) PRIMARY KEY,
    consulta_id CHAR(36) NULL,
    historial_clinico_id CHAR(36) NOT NULL,
    tipo_tratamiento VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    objetivo TEXT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NULL,
    duracion_estimada INT NULL COMMENT 'Duración en días',
    estado ENUM('activo', 'completado', 'suspendido', 'cancelado') DEFAULT 'activo',
    efectividad ENUM('excelente', 'buena', 'regular', 'pobre', 'sin_evaluar') DEFAULT 'sin_evaluar',
    observaciones TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (consulta_id) REFERENCES consultas(id) ON DELETE SET NULL,
    FOREIGN KEY (historial_clinico_id) REFERENCES historiales_clinicos(id) ON DELETE CASCADE,
    
    INDEX idx_consulta_id (consulta_id),
    INDEX idx_historial_clinico_id (historial_clinico_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: productos_farmaceuticos
DROP TABLE IF EXISTS productos_farmaceuticos;
CREATE TABLE productos_farmaceuticos (
    id CHAR(36) PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre_comercial VARCHAR(150) NOT NULL,
    nombre_generico VARCHAR(150) NULL,
    principio_activo VARCHAR(150) NULL,
    presentacion VARCHAR(100) NULL,
    concentracion VARCHAR(50) NULL,
    laboratorio VARCHAR(100) NULL,
    registro_sanitario VARCHAR(50) NULL,
    fecha_vencimiento DATE NULL,
    stock_actual INT DEFAULT 0,
    stock_minimo INT DEFAULT 0,
    precio_unitario DECIMAL(10,2) NULL,
    requiere_receta BOOLEAN DEFAULT FALSE,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_nombre_comercial (nombre_comercial),
    INDEX idx_activo (activo),
    INDEX idx_stock_bajo (stock_actual)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: medicamentos
DROP TABLE IF EXISTS medicamentos;
CREATE TABLE medicamentos (
    id CHAR(36) PRIMARY KEY,
    tratamiento_id CHAR(36) NOT NULL,
    producto_id CHAR(36) NOT NULL,
    dosis VARCHAR(100) NOT NULL,
    frecuencia VARCHAR(100) NOT NULL,
    duracion_dias INT NOT NULL,
    via_administracion VARCHAR(50) NOT NULL,
    instrucciones_especiales TEXT NULL,
    observaciones TEXT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tratamiento_id) REFERENCES tratamientos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos_farmaceuticos(id) ON DELETE RESTRICT,
    
    INDEX idx_tratamiento_id (tratamiento_id),
    INDEX idx_producto_id (producto_id),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: procedimientos
DROP TABLE IF EXISTS procedimientos;
CREATE TABLE procedimientos (
    id CHAR(36) PRIMARY KEY,
    tratamiento_id CHAR(36) NOT NULL,
    tipo_procedimiento VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    fecha_realizacion DATETIME NOT NULL,
    veterinario_id CHAR(36) NOT NULL,
    asistente_id CHAR(36) NULL,
    duracion INT NULL COMMENT 'Duración en minutos',
    resultado TEXT NULL,
    complicaciones TEXT NULL,
    observaciones TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tratamiento_id) REFERENCES tratamientos(id) ON DELETE CASCADE,
    FOREIGN KEY (veterinario_id) REFERENCES veterinarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (asistente_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    INDEX idx_tratamiento_id (tratamiento_id),
    INDEX idx_veterinario_id (veterinario_id),
    INDEX idx_fecha_realizacion (fecha_realizacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: cirugias
DROP TABLE IF EXISTS cirugias;
CREATE TABLE cirugias (
    id CHAR(36) PRIMARY KEY,
    historial_clinico_id CHAR(36) NOT NULL,
    tipo_cirugia VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    fecha_programada DATE NOT NULL,
    fecha_realizacion DATETIME NULL,
    cirujano_id CHAR(36) NOT NULL,
    anestesiologo_id CHAR(36) NULL,
    asistentes JSON NULL,
    duracion INT NULL COMMENT 'Duración en minutos',
    tipo_anestesia VARCHAR(100) NULL,
    complicaciones TEXT NULL,
    resultado TEXT NULL,
    postoperatorio TEXT NULL,
    seguimiento_requerido BOOLEAN DEFAULT TRUE,
    estado ENUM('programada', 'realizada', 'cancelada', 'postergada') DEFAULT 'programada',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (historial_clinico_id) REFERENCES historiales_clinicos(id) ON DELETE CASCADE,
    FOREIGN KEY (cirujano_id) REFERENCES veterinarios(id) ON DELETE RESTRICT,
    FOREIGN KEY (anestesiologo_id) REFERENCES veterinarios(id) ON DELETE RESTRICT,
    
    INDEX idx_historial_clinico_id (historial_clinico_id),
    INDEX idx_cirujano_id (cirujano_id),
    INDEX idx_fecha_programada (fecha_programada),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- MÓDULO 4: ADOPCIONES
-- ============================================

-- Tabla: adoptantes
DROP TABLE IF EXISTS adoptantes;
CREATE TABLE adoptantes (
    id CHAR(36) PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    tipo_documento ENUM('CC', 'CE', 'TI', 'PA', 'PEP') NOT NULL,
    numero_documento VARCHAR(20) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    tipo_vivienda ENUM('casa', 'apartamento', 'finca', 'otro') NOT NULL,
    tiene_patio BOOLEAN DEFAULT FALSE,
    experiencia_animales TEXT NULL,
    num_personas_hogar INT NULL,
    estado ENUM('activo', 'inactivo', 'bloqueado') DEFAULT 'activo',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_numero_documento (numero_documento),
    INDEX idx_email (email),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: adopciones
DROP TABLE IF EXISTS adopciones;
CREATE TABLE adopciones (
    id CHAR(36) PRIMARY KEY,
    animal_id CHAR(36) NOT NULL,
    adoptante_id CHAR(36) NOT NULL,
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_aprobacion DATETIME NULL,
    fecha_entrega DATETIME NULL,
    estado ENUM('solicitada', 'en_evaluacion', 'aprobada', 'rechazada', 'completada', 'revocada') DEFAULT 'solicitada',
    evaluador_id CHAR(36) NULL,
    observaciones TEXT NULL,
    contrato_firmado BOOLEAN DEFAULT FALSE,
    contrato_url VARCHAR(255) NULL,
    motivo_rechazo TEXT NULL,
    motivo_revocacion TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (animal_id) REFERENCES animals(id) ON DELETE RESTRICT,
    FOREIGN KEY (adoptante_id) REFERENCES adoptantes(id) ON DELETE RESTRICT,
    FOREIGN KEY (evaluador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    INDEX idx_animal_id (animal_id),
    INDEX idx_adoptante_id (adoptante_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_solicitud (fecha_solicitud)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: visitas_domiciliarias
DROP TABLE IF EXISTS visitas_domiciliarias;
CREATE TABLE visitas_domiciliarias (
    id CHAR(36) PRIMARY KEY,
    adopcion_id CHAR(36) NOT NULL,
    fecha_programada DATE NOT NULL,
    fecha_realizada DATETIME NULL,
    visitador_id CHAR(36) NOT NULL,
    tipo_visita ENUM('pre_adopcion', 'seguimiento_1mes', 'seguimiento_3meses', 'seguimiento_6meses', 'extraordinaria') NOT NULL,
    observaciones TEXT NULL,
    condiciones_hogar JSON NULL,
    estado_animal JSON NULL,
    resultado ENUM('satisfactoria', 'observaciones', 'critica') NULL,
    recomendaciones TEXT NULL,
    fotos_respaldo JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (adopcion_id) REFERENCES adopciones(id) ON DELETE CASCADE,
    FOREIGN KEY (visitador_id) REFERENCES usuarios(id) ON DELETE RESTRICT,
    
    INDEX idx_adopcion_id (adopcion_id),
    INDEX idx_visitador_id (visitador_id),
    INDEX idx_fecha_programada (fecha_programada),
    INDEX idx_tipo_visita (tipo_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: denunciantes
DROP TABLE IF EXISTS denunciantes;
CREATE TABLE denunciantes (
    id CHAR(36) PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    direccion VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_telefono (telefono),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: denuncias
DROP TABLE IF EXISTS denuncias;
CREATE TABLE denuncias (
    id CHAR(36) PRIMARY KEY,
    numero_ticket VARCHAR(50) NOT NULL UNIQUE,
    denunciante_id CHAR(36) NULL,
    fecha_denuncia DATETIME DEFAULT CURRENT_TIMESTAMP,
    canal_recepcion ENUM('web', 'telefono', 'presencial', 'email', 'whatsapp') NOT NULL,
    tipo_denuncia ENUM('maltrato', 'abandono', 'animal_herido', 'animal_peligroso', 'otro') NOT NULL,
    descripcion TEXT NOT NULL,
    ubicacion VARCHAR(255) NOT NULL,
    latitud DECIMAL(10,8) NULL,
    longitud DECIMAL(11,8) NULL,
    evidencias JSON NULL,
    prioridad ENUM('baja', 'media', 'alta', 'urgente') DEFAULT 'media',
    estado ENUM('recibida', 'en_revision', 'asignada', 'en_atencion', 'resuelta', 'cerrada', 'desestimada') DEFAULT 'recibida',
    responsable_id CHAR(36) NULL,
    fecha_asignacion DATETIME NULL,
    fecha_resolucion DATETIME NULL,
    observaciones_resolucion TEXT NULL,
    es_anonima BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (denunciante_id) REFERENCES denunciantes(id) ON DELETE SET NULL,
    FOREIGN KEY (responsable_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    INDEX idx_numero_ticket (numero_ticket),
    INDEX idx_denunciante_id (denunciante_id),
    INDEX idx_estado (estado),
    INDEX idx_prioridad (prioridad),
    INDEX idx_fecha_denuncia (fecha_denuncia),
    INDEX idx_tipo_denuncia (tipo_denuncia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: rescates
DROP TABLE IF EXISTS rescates;
CREATE TABLE rescates (
    id CHAR(36) PRIMARY KEY,
    denuncia_id CHAR(36) NULL,
    fecha_programada DATE NOT NULL,
    fecha_ejecucion DATETIME NULL,
    equipo_rescate JSON NULL,
    animal_rescatado_id CHAR(36) NULL,
    exitoso BOOLEAN DEFAULT FALSE,
    observaciones TEXT NULL,
    motivo_fallo TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE SET NULL,
    FOREIGN KEY (animal_rescatado_id) REFERENCES animals(id) ON DELETE SET NULL,
    
    INDEX idx_denuncia_id (denuncia_id),
    INDEX idx_animal_rescatado_id (animal_rescatado_id),
    INDEX idx_fecha_programada (fecha_programada),
    INDEX idx_exitoso (exitoso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: insumos
DROP TABLE IF EXISTS insumos;
CREATE TABLE insumos (
    id CHAR(36) PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL,
    stock_actual DECIMAL(10,2) DEFAULT 0,
    stock_minimo DECIMAL(10,2) DEFAULT 0,
    ubicacion VARCHAR(100) NULL,
    fecha_vencimiento DATE NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_categoria (categoria),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: inventarios (genérica)
DROP TABLE IF EXISTS inventarios;
CREATE TABLE inventarios (
    id CHAR(36) PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    unidad_medida VARCHAR(20) NOT NULL,
    cantidad_actual DECIMAL(10,2) DEFAULT 0,
    cantidad_minima DECIMAL(10,2) DEFAULT 0,
    ubicacion VARCHAR(100) NULL,
    fecha_vencimiento DATE NULL,
    proveedor VARCHAR(150) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_categoria (categoria),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: indicadores
DROP TABLE IF EXISTS indicadores;
CREATE TABLE indicadores (
    id CHAR(36) PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NULL,
    tipo VARCHAR(50) NOT NULL,
    unidad_medida VARCHAR(50) NULL,
    formula TEXT NULL,
    frecuencia_actualizacion VARCHAR(50) NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: puntos_indicadores
DROP TABLE IF EXISTS puntos_indicadores;
CREATE TABLE puntos_indicadores (
    id CHAR(36) PRIMARY KEY,
    indicador_id CHAR(36) NOT NULL,
    fecha DATE NOT NULL,
    valor DECIMAL(15,4) NOT NULL,
    dimensiones JSON NULL,
    calidad VARCHAR(50) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (indicador_id) REFERENCES indicadores(id) ON DELETE CASCADE,
    
    INDEX idx_indicador_id (indicador_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: eventos_auditoria
DROP TABLE IF EXISTS eventos_auditoria;
CREATE TABLE eventos_auditoria (
    id CHAR(36) PRIMARY KEY,
    trace_id CHAR(36) NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    usuario_id CHAR(36) NULL,
    accion VARCHAR(100) NOT NULL,
    recurso VARCHAR(100) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    detalles JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    resultado ENUM('exitoso', 'fallido', 'denegado') NOT NULL,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    
    INDEX idx_trace_id (trace_id),
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_modulo (modulo),
    INDEX idx_accion (accion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rehabilitar foreign key checks
SET FOREIGN_KEY_CHECKS = 1;


SELECT 'Base de datos creada exitosamente!' AS mensaje;
SELECT COUNT(*) AS total_tablas FROM information_schema.tables WHERE table_schema = 'bienestar_animal';

SHOW TABLES;