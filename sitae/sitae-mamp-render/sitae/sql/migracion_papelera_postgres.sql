-- PostgreSQL / Neon: papelera de licencias (borrado lógico). Ejecutar UNA vez en el SQL Editor ANTES de subir el código nuevo.
-- Se puede repetir sin problema.
ALTER TABLE licencias ADD COLUMN IF NOT EXISTS eliminado_en TIMESTAMP NULL;
ALTER TABLE licencias ADD COLUMN IF NOT EXISTS eliminado_por INT NULL REFERENCES usuarios(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_licencias_eliminado ON licencias(eliminado_en);
