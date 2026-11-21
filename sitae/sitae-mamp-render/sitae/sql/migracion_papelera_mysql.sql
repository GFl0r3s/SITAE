-- MySQL / MAMP: papelera de licencias (borrado lógico). Ejecutar UNA sola vez en phpMyAdmin (base de datos sitae).
-- Si dice "Duplicate column name", ya estaba aplicada.
ALTER TABLE licencias
  ADD COLUMN eliminado_en TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN eliminado_por INT UNSIGNED NULL,
  ADD CONSTRAINT fk_licencias_eliminado_por FOREIGN KEY (eliminado_por) REFERENCES usuarios(id) ON DELETE SET NULL;
