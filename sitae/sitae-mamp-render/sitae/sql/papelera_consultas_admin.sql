-- Consultas para el ADMINISTRADOR (Neon → SQL Editor). La eliminación definitiva solo se hace aquí.

-- 1) Ver la papelera (qué se borró, quién y cuándo)
SELECT l.num, l.licencia, l.operador, l.eliminado_en, u.usuario AS eliminado_por
FROM licencias l LEFT JOIN usuarios u ON u.id = l.eliminado_por
WHERE l.eliminado_en IS NOT NULL
ORDER BY l.eliminado_en DESC;

-- 2) Borrar DEFINITIVAMENTE una licencia de la papelera (también borra sus equipos). No se puede deshacer.
DELETE FROM licencias WHERE num = 123 AND eliminado_en IS NOT NULL;   -- cambia 123 por el número

-- 3) Vaciar toda la papelera (¡cuidado!)
-- DELETE FROM licencias WHERE eliminado_en IS NOT NULL;

-- 4) Restaurar una licencia a mano
-- UPDATE licencias SET eliminado_en = NULL, eliminado_por = NULL WHERE num = 123;
