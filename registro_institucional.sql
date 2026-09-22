CREATE DATABASE registro_institucional
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE registro_institucional;

-- =========================================
-- TABLA DE ROLES
-- =========================================

CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Roles iniciales
INSERT INTO roles (nombre, descripcion) VALUES
('Super Admin', 'Control total del sistema'),
('Administrador', 'Administración general'),
('Usuario', 'Usuario estándar');


-- =========================================
-- TABLA DE USUARIOS
-- =========================================

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,

    correo VARCHAR(150) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    rol_id INT UNSIGNED NOT NULL DEFAULT 3,

    -- Verificación del correo
    correo_verificado TINYINT(1) NOT NULL DEFAULT 0,

    token_verificacion VARCHAR(255) DEFAULT NULL,
    token_expira DATETIME DEFAULT NULL,
    intentos_verificacion TINYINT UNSIGNED NOT NULL DEFAULT 0,

    -- Estado de la cuenta
    estado ENUM(
        'pendiente',
        'activo',
        'rechazado',
        'bloqueado'
    ) NOT NULL DEFAULT 'pendiente',

    -- Fechas importantes
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_verificacion DATETIME DEFAULT NULL,
    fecha_aprobacion DATETIME DEFAULT NULL,

    -- Super Admin que aprobó/rechazó
    aprobado_por INT UNSIGNED DEFAULT NULL,

    CONSTRAINT fk_usuario_rol
        FOREIGN KEY (rol_id)
        REFERENCES roles(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_usuario_aprobador
        FOREIGN KEY (aprobado_por)
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);


-- =========================================
-- TABLA DE AUDITORÍA
-- =========================================

CREATE TABLE auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    usuario_id INT UNSIGNED DEFAULT NULL,

    accion VARCHAR(100) NOT NULL,

    descripcion TEXT,

    realizado_por INT UNSIGNED DEFAULT NULL,

    ip VARCHAR(45) DEFAULT NULL,

    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,

    CONSTRAINT fk_auditoria_realizado
        FOREIGN KEY (realizado_por)
        REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);