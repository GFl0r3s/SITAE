-- Importar en phpMyAdmin. No elimina tablas existentes.
CREATE DATABASE IF NOT EXISTS sitae CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sitae;
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('admin','editor','lector') NOT NULL DEFAULT 'lector',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS licencias (
  num INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `licencia` VARCHAR(60) NOT NULL UNIQUE,
  `codOperador` VARCHAR(255) NOT NULL,
  `operador` VARCHAR(255) NOT NULL,
  `denominacion` VARCHAR(255) NOT NULL,
  `rtn` VARCHAR(255) NOT NULL,
  `identidad` VARCHAR(255) NOT NULL,
  `direccion` TEXT NOT NULL,
  `municipio` VARCHAR(255) NOT NULL,
  `departamento` VARCHAR(255) NOT NULL,
  `pais` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(255) NOT NULL,
  `fax` VARCHAR(255) NOT NULL,
  `correo` VARCHAR(255) NOT NULL,
  `otroTel` VARCHAR(255) NOT NULL,
  `rNombre` VARCHAR(255) NOT NULL,
  `rIdentidad` VARCHAR(255) NOT NULL,
  `rTelefono` VARCHAR(255) NOT NULL,
  `rCorreo` VARCHAR(255) NOT NULL,
  `rDireccion` TEXT NOT NULL,
  `tipo` VARCHAR(255) NOT NULL,
  `modelo` VARCHAR(255) NOT NULL,
  `fuselaje` VARCHAR(255) NOT NULL,
  `registro` VARCHAR(255) NOT NULL,
  `fabricacion` DATE NULL,
  `aeropuerto` VARCHAR(255) NOT NULL,
  `actividad` VARCHAR(255) NOT NULL,
  `maxPasajeros` INT UNSIGNED NULL,
  `indicativo` VARCHAR(255) NOT NULL,
  `emision` DATE NULL,
  `vencimiento` DATE NULL,
  `observaciones` TEXT NOT NULL,
  `radarSi` VARCHAR(255) NOT NULL,
  version INT UNSIGNED NOT NULL DEFAULT 1,
  creado_por INT UNSIGNED NULL,
  actualizado_por INT UNSIGNED NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  eliminado_en TIMESTAMP NULL DEFAULT NULL,
  eliminado_por INT UNSIGNED NULL,
  FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
  FOREIGN KEY (eliminado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS equipos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  licencia_id INT UNSIGNED NOT NULL,
  categoria ENUM('hf','vhf','nav','radar','emerg') NOT NULL,
  tipo VARCHAR(255) NOT NULL DEFAULT '',
  marca VARCHAR(255) NOT NULL DEFAULT '',
  modelo VARCHAR(255) NOT NULL DEFAULT '',
  serie VARCHAR(255) NOT NULL DEFAULT '',
  banda VARCHAR(255) NOT NULL DEFAULT '',
  clase VARCHAR(255) NOT NULL DEFAULT '',
  modulacion VARCHAR(255) NOT NULL DEFAULT '',
  cantidad INT UNSIGNED NULL,
  FOREIGN KEY (licencia_id) REFERENCES licencias(num) ON DELETE CASCADE,
  INDEX (licencia_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS login_intentos (
  clave CHAR(64) PRIMARY KEY,
  fallos INT NOT NULL DEFAULT 0,
  ventana BIGINT NOT NULL
) ENGINE=InnoDB;
