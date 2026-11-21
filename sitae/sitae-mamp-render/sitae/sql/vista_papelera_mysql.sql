-- Vista de la papelera: muestra lo mismo que la pantalla Papelera de SITAE.
-- Es una "vista": se actualiza sola, no duplica datos y no puede quedar desincronizada.
CREATE OR REPLACE VIEW papelera_licencias AS
SELECT l.num,
       l.licencia,
       l.codOperador AS cod_operador,
       l.operador,
       u.usuario       AS eliminada_por,
       l.eliminado_en  AS fecha_eliminacion
FROM licencias l
LEFT JOIN usuarios u ON u.id = l.eliminado_por
WHERE l.eliminado_en IS NOT NULL
ORDER BY l.eliminado_en DESC;
