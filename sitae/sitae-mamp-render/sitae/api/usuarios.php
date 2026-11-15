<?php
require __DIR__ . '/comun.php';
metodo(['GET','POST','PUT']);
$u = exigir_usuario(['admin']);
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'GET') responder(['usuarios'=>$pdo->query('SELECT id,usuario,nombre,rol,activo FROM usuarios ORDER BY usuario')->fetchAll()]);
csrf(); $d = cuerpo();
$nombre = texto($d,'nombre',150,true); $usuario = texto($d,'usuario',50,true);
if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/',$usuario)) responder(['error'=>'Usuario: de 3 a 50 letras, números, punto, guion o guion bajo.'],422);
$rol = $d['rol'] ?? 'lector';
if (!in_array($rol,['admin','editor','lector'],true)) responder(['error'=>'Rol inválido.'],422);
$activo = entero($d['activo'] ?? 1,'activo',false);
if (!in_array($activo,[0,1],true)) responder(['error'=>'Estado inválido.'],422);
$password = $d['password'] ?? '';
if (!is_string($password) || (($password !== '' || $_SERVER['REQUEST_METHOD']==='POST') && (strlen($password)<10 || strlen($password)>72))) responder(['error'=>'La contraseña debe tener entre 10 y 72 bytes.'],422);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = $pdo->prepare('INSERT INTO usuarios (usuario,nombre,password_hash,rol,activo) VALUES (?,?,?,?,?)');
    $s->execute([$usuario,$nombre,password_hash($password,PASSWORD_DEFAULT),$rol,$activo]);
    responder(['ok'=>true],201);
}
$id = entero($_GET['id'] ?? null,'id',false);
// No permitir que el administrador pierda su propio acceso accidentalmente.
if ($id === $u['id'] && ($rol !== 'admin' || !$activo)) responder(['error'=>'No puedes desactivar tu cuenta ni quitarte el rol de administrador.'],422);
$pdo->beginTransaction();
// Serializar cambios de administradores para conservar al menos uno activo.
$admins = $pdo->query("SELECT id FROM usuarios WHERE rol='admin' AND activo=1 ORDER BY id FOR UPDATE")->fetchAll(PDO::FETCH_COLUMN);
$s = $pdo->prepare('SELECT id FROM usuarios WHERE id=?'); $s->execute([$id]);
if (!$s->fetch()) { $pdo->rollBack(); responder(['error'=>'El usuario no existe.'],404); }
if (in_array((string)$id,array_map('strval',$admins),true) && count($admins)===1 && ($rol!=='admin' || !$activo)) { $pdo->rollBack(); responder(['error'=>'Debe quedar un administrador activo.'],422); }
$sql = 'UPDATE usuarios SET usuario=?,nombre=?,rol=?,activo=?'; $valores = [$usuario,$nombre,$rol,$activo];
if ($password !== '') { $sql .= ',password_hash=?'; $valores[] = password_hash($password,PASSWORD_DEFAULT); }
$sql .= ' WHERE id=?'; $valores[] = $id;
$pdo->prepare($sql)->execute($valores); $pdo->commit(); responder(['ok'=>true]);
