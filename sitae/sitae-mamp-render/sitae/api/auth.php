<?php
require __DIR__ . '/comun.php';
metodo(['GET','POST']);
if ($_SERVER['REQUEST_METHOD'] === 'GET') responder(['usuario'=>usuario_actual(),'csrf'=>$_SESSION['csrf']]);
csrf();
$d = cuerpo();
if (($d['accion'] ?? '') === 'salir') {
    $_SESSION = [];
    session_destroy();
    setcookie('SITAESESSID','', ['expires'=>time()-3600,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
    responder(['ok'=>true]);
}
if (($d['accion'] ?? '') !== 'entrar') responder(['error'=>'Acción inválida.'],400);
$nombre = texto($d,'usuario',50,true);
$password = $d['password'] ?? '';
if (!is_string($password) || strlen($password) > 72) responder(['error'=>'Usuario o contraseña incorrectos.'],401);
// Cinco intentos fallidos por IP y usuario durante quince minutos.
$clave = hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . strtolower($nombre));
$ahora = time();
$pdo = db();
$pdo->beginTransaction();
$pdo->prepare(es_pg()
    ? 'INSERT INTO login_intentos (clave,fallos,ventana) VALUES (?,0,?) ON CONFLICT (clave) DO NOTHING'
    : 'INSERT IGNORE INTO login_intentos (clave,fallos,ventana) VALUES (?,0,?)')->execute([$clave,$ahora]);
$s = $pdo->prepare('SELECT fallos,ventana FROM login_intentos WHERE clave=? FOR UPDATE');
$s->execute([$clave]); $intento = $s->fetch();
if ($ahora - (int)$intento['ventana'] >= 900) {
    $pdo->prepare('UPDATE login_intentos SET fallos=0,ventana=? WHERE clave=?')->execute([$ahora,$clave]);
    $intento['fallos'] = 0;
}
if ((int)$intento['fallos'] >= 5) { $pdo->commit(); responder(['error'=>'Demasiados intentos. Espera quince minutos.'],429); }
$s = $pdo->prepare('SELECT * FROM usuarios WHERE usuario=?');
$s->execute([$nombre]); $u = $s->fetch();
// Hash ficticio para que los usuarios inexistentes también verifiquen una contraseña.
$hash = $u ? $u['password_hash'] : '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
$valido = password_verify($password,$hash);
if (!$u || !$valido || !(int)$u['activo']) {
    $pdo->prepare('UPDATE login_intentos SET fallos=fallos+1 WHERE clave=?')->execute([$clave]);
    $pdo->commit(); responder(['error'=>'Usuario o contraseña incorrectos.'],401);
}
$pdo->prepare('DELETE FROM login_intentos WHERE clave=?')->execute([$clave]);
$pdo->commit();
session_regenerate_id(true);
$_SESSION['uid'] = (int)$u['id'];
$_SESSION['csrf'] = bin2hex(random_bytes(32));
responder(['usuario'=>usuario_actual(),'csrf'=>$_SESSION['csrf']]);
