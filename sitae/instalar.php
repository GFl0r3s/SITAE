<?php
// Instalador de una sola vez: crea las tablas (PostgreSQL) y el primer administrador.
// - En Render: requiere la variable de entorno INSTALL_KEY.
// - En MAMP: funciona sin clave, solo desde http://localhost/...
// Cuando ya existe un administrador activo, se bloquea solo.
require __DIR__ . '/api/conexion.php';
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');

$clave = getenv('INSTALL_KEY') ?: '';
$local = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true) && empty($_SERVER['HTTP_X_FORWARDED_FOR']);
if ($clave === '' && !$local) { http_response_code(404); exit('No disponible.'); }

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function pagina($cuerpo) {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>Instalación SITAE</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>'
       . '<body class="bg-light"><div class="container py-5" style="max-width:460px"><div class="card shadow-sm"><div class="card-body p-4">'
       . '<h1 class="h4 mb-3">Instalación de SITAE</h1>' . $cuerpo . '</div></div></div></body></html>';
    exit;
}

$mensaje = '';
try {
    $pdo = db();
    try { $hay = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin' AND activo=1")->fetchColumn(); }
    catch (PDOException $e) { $hay = 0; } // las tablas todavía no existen
    if ($hay > 0) pagina('<p class="mb-0">Ya existe un administrador. Esta página queda bloqueada. <a href="index.html">Ir al inicio de sesión</a>.</p>');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre = trim($_POST['nombre'] ?? ''); $usuario = trim($_POST['usuario'] ?? ''); $password = (string)($_POST['password'] ?? '');
        if ($clave !== '' && !hash_equals($clave, (string)($_POST['clave'] ?? ''))) $mensaje = 'Clave de instalación incorrecta.';
        elseif ($nombre === '' || strlen($nombre) > 150 || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $usuario) || strlen($password) < 10 || strlen($password) > 72)
            $mensaje = 'Datos inválidos: usuario de 3 a 50 caracteres (letras, números, . _ -) y contraseña de 10 a 72.';
        else {
            if (es_pg()) $pdo->exec(file_get_contents(__DIR__ . '/sql/sitae_postgres.sql'));
            else { try { $pdo->query('SELECT 1 FROM usuarios LIMIT 1'); } catch (PDOException $e) { throw new RuntimeException('Importa primero sql/sitae.sql en phpMyAdmin.'); } }
            $pdo->prepare("INSERT INTO usuarios (usuario,nombre,password_hash,rol) VALUES (?,?,?,'admin')")
                ->execute([$usuario, $nombre, password_hash($password, PASSWORD_DEFAULT)]);
            pagina('<p class="text-success">Administrador creado.</p><a class="btn btn-primary" href="index.html">Iniciar sesión</a>');
        }
    }
} catch (Throwable $e) {
    error_log((string)$e);
    $mensaje = ($e instanceof RuntimeException) ? $e->getMessage() : 'No se pudo conectar con la base de datos o crear las tablas. Revisa DATABASE_URL (Render) o la configuración de MAMP.';
}
$campoClave = $clave !== '' ? '<label class="form-label">Clave de instalación</label><input name="clave" type="password" class="form-control mb-3" required>' : '';
pagina(($mensaje ? '<div class="alert alert-danger">' . h($mensaje) . '</div>' : '')
  . '<form method="post" autocomplete="off">' . $campoClave
  . '<label class="form-label">Nombre completo</label><input name="nombre" class="form-control mb-3" maxlength="150" required>'
  . '<label class="form-label">Usuario</label><input name="usuario" class="form-control mb-3" pattern="[A-Za-z0-9_.-]{3,50}" required>'
  . '<label class="form-label">Contraseña (10 a 72 caracteres)</label><input name="password" type="password" class="form-control mb-3" minlength="10" maxlength="72" required>'
  . '<button class="btn btn-primary w-100">Crear administrador</button></form>');
