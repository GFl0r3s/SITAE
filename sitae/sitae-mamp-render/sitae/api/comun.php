<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', '0');
set_exception_handler(function ($e) {
    if (db_en_transaccion()) db()->rollBack();
    error_log((string)$e);
    if ($e instanceof PDOException && in_array((string)$e->getCode(), ['23000','23505'], true)) {
        responder(['error' => 'Ya existe ese código de licencia o nombre de usuario.'], 409);
    }
    responder(['error' => 'No se pudo completar la operación. Revisa la base de datos, la configuración y el registro de PHP.'], 500);
});
function db_en_transaccion() {
    try { return db()->inTransaction(); } catch (Throwable $e) { return false; }
}
session_name('SITAESESSID');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_set_cookie_params(['lifetime'=>0, 'path'=>'/', 'secure'=>(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'), 'httponly'=>true, 'samesite'=>'Lax']);
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
if (isset($_SESSION['ultimo']) && time() - $_SESSION['ultimo'] > 1800) {
    unset($_SESSION['uid']);
}
$_SESSION['ultimo'] = time();
function responder($datos, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function metodo($permitidos) {
    if (!in_array($_SERVER['REQUEST_METHOD'], $permitidos, true)) {
        header('Allow: ' . implode(', ', $permitidos));
        responder(['error'=>'Método no permitido.'],405);
    }
}
function cuerpo() {
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) responder(['error'=>'Se requiere JSON.'],415);
    $raw = file_get_contents('php://input', false, null, 0, 2097153);
    if (strlen($raw) > 2097152) responder(['error'=>'Solicitud demasiado grande.'],413);
    $datos = json_decode($raw, true);
    if (!is_array($datos)) responder(['error'=>'JSON inválido.'],400);
    return $datos;
}
function csrf() {
    if (!hash_equals($_SESSION['csrf'], $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) responder(['error'=>'Sesión del formulario inválida. Recarga la página.'],403);
}
function usuario_actual() {
    if (empty($_SESSION['uid'])) return null;
    $s = db()->prepare('SELECT id, usuario, nombre, rol FROM usuarios WHERE id=? AND activo=1');
    $s->execute([$_SESSION['uid']]);
    $u = $s->fetch();
    if (!$u) { unset($_SESSION['uid']); return null; }
    $u['id'] = (int)$u['id'];
    return $u;
}
function exigir_usuario($roles = ['admin','editor','lector']) {
    $u = usuario_actual();
    if (!$u) responder(['error'=>'Inicia sesión para continuar.'],401);
    if (!in_array($u['rol'],$roles,true)) responder(['error'=>'No tienes permiso para esta operación.'],403);
    return $u;
}
function texto($d, $k, $max = 255, $obligatorio = false) {
    $v = $d[$k] ?? '';
    if (!is_string($v) && !is_numeric($v)) responder(['error'=>"Campo inválido: $k"],422);
    $v = trim((string)$v);
    if (strlen($v) > $max || ($obligatorio && $v === '')) responder(['error'=>"Revisa el campo $k (máximo $max bytes)."],422);
    return $v;
}
function entero($v, $nombre, $nullable = true) {
    if ($nullable && ($v === '' || $v === null)) return null;
    if (filter_var($v, FILTER_VALIDATE_INT) === false || (int)$v < 0 || (int)$v > 2147483647) responder(['error'=>"Número inválido: $nombre"],422);
    return (int)$v;
}
