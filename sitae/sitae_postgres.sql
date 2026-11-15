-- PostgreSQL (Render / Neon). Se ejecuta solo desde instalar.php o desde el editor SQL de Neon.
CREATE TABLE IF NOT EXISTS usuarios (
  id SERIAL PRIMARY KEY,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol VARCHAR(10) NOT NULL DEFAULT 'lector' CHECK (rol IN ('admin','editor','lector')),
  activo SMALLINT NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS licencias (
  num SERIAL PRIMARY KEY,
  licencia VARCHAR(60) NOT NULL UNIQUE,
  "codOperador" VARCHAR(255) NOT NULL,
  "operador" VARCHAR(255) NOT NULL,
  "denominacion" VARCHAR(255) NOT NULL,
  "rtn" VARCHAR(255) NOT NULL,
  "identidad" VARCHAR(255) NOT NULL,
  "direccion" TEXT NOT NULL,
  "municipio" VARCHAR(255) NOT NULL,
  "departamento" VARCHAR(255) NOT NULL,
  "pais" VARCHAR(255) NOT NULL,
  "telefono" VARCHAR(255) NOT NULL,
  "fax" VARCHAR(255) NOT NULL,
  "correo" VARCHAR(255) NOT NULL,
  "otroTel" VARCHAR(255) NOT NULL,
  "rNombre" VARCHAR(255) NOT NULL,
  "rIdentidad" VARCHAR(255) NOT NULL,
  "rTelefono" VARCHAR(255) NOT NULL,
  "rCorreo" VARCHAR(255) NOT NULL,
  "rDireccion" TEXT NOT NULL,
  "tipo" VARCHAR(255) NOT NULL,
  "modelo" VARCHAR(255) NOT NULL,
  "fuselaje" VARCHAR(255) NOT NULL,
  "registro" VARCHAR(255) NOT NULL,
  "fabricacion" DATE NULL,
  "aeropuerto" VARCHAR(255) NOT NULL,
  "actividad" VARCHAR(255) NOT NULL,
  "maxPasajeros" INT NULL,
  "indicativo" VARCHAR(255) NOT NULL,
  "emision" DATE NULL,
  "vencimiento" DATE NULL,
  "observaciones" TEXT NOT NULL,
  "radarSi" VARCHAR(255) NOT NULL,
  version INT NOT NULL DEFAULT 1,
  creado_por INT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
  actualizado_por INT NULL REFERENCES usuarios(id) ON DELETE SET NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS equipos (
  id SERIAL PRIMARY KEY,
  licencia_id INT NOT NULL REFERENCES licencias(num) ON DELETE CASCADE,
  categoria VARCHAR(5) NOT NULL CHECK (categoria IN ('hf','vhf','nav','radar','emerg')),
  tipo VARCHAR(255) NOT NULL DEFAULT '',
  marca VARCHAR(255) NOT NULL DEFAULT '',
  modelo VARCHAR(255) NOT NULL DEFAULT '',
  serie VARCHAR(255) NOT NULL DEFAULT '',
  banda VARCHAR(255) NOT NULL DEFAULT '',
  clase VARCHAR(255) NOT NULL DEFAULT '',
  modulacion VARCHAR(255) NOT NULL DEFAULT '',
  cantidad INT NULL
);
CREATE INDEX IF NOT EXISTS idx_equipos_licencia ON equipos(licencia_id);
CREATE TABLE IF NOT EXISTS login_intentos (
  clave CHAR(64) PRIMARY KEY,
  fallos INT NOT NULL DEFAULT 0,
  ventana BIGINT NOT NULL
);
