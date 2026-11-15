<?php
function config_db() {
    static $c = null;
    if ($c === null) $c = require __DIR__ . '/config.php';
    return $c;
}
function es_pg() { return config_db()['driver'] === 'pgsql'; }
// Escapa nombres de columna (las que tienen mayúsculas, como codOperador, necesitan comillas en PostgreSQL).
function q($columna) { return es_pg() ? '"' . $columna . '"' : '`' . $columna . '`'; }
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $c = config_db();
        $dsn = es_pg()
            ? "pgsql:host={$c['host']};port={$c['port']};dbname={$c['database']};sslmode={$c['sslmode']}"
            : "mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4";
        $pdo = new PDO($dsn, $c['user'], $c['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
    return $pdo;
}
