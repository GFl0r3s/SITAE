<?php
// Si existe la variable DATABASE_URL (Render/Neon) se usa PostgreSQL.
// Si no existe, se usa MySQL de MAMP en tu computadora.
$url = getenv('DATABASE_URL');
if ($url) {
    $u = parse_url($url);
    parse_str($u['query'] ?? '', $q);
    return [
        'driver'   => 'pgsql',
        'host'     => $u['host'] ?? '',
        'port'     => (string)($u['port'] ?? 5432),
        'database' => ltrim($u['path'] ?? '', '/'),
        'user'     => urldecode($u['user'] ?? ''),
        'password' => urldecode($u['pass'] ?? ''),
        'sslmode'  => $q['sslmode'] ?? 'prefer'
    ];
}
// MAMP: usuario root, contraseña root. Puerto MySQL: 8889 (revisa el tuyo en la pantalla de inicio de MAMP).
return [
    'driver'   => 'mysql',
    'host'     => getenv('SITAE_DB_HOST') ?: 'localhost',
    'port'     => getenv('SITAE_DB_PORT') ?: '3306',
    'database' => getenv('SITAE_DB_NAME') ?: 'sitae',
    'user'     => getenv('SITAE_DB_USER') ?: 'root',
    'password' => getenv('SITAE_DB_PASSWORD') !== false ? getenv('SITAE_DB_PASSWORD') : 'root'
];
