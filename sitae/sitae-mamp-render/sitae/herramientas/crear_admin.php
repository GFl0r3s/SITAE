<?php
// Solo se ejecuta por consola, nunca desde una URL.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../api/conexion.php';
try {
    if ((int)db()->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin' AND activo=1")->fetchColumn() > 0) {
        fwrite(STDERR,"Ya existe un administrador. Usa la pantalla Usuarios para crear más cuentas.\n"); exit(1);
    }
    echo "Nombre completo: "; $nombre = trim(fgets(STDIN));
    echo "Usuario (3-50 letras/números/._-): "; $usuario = trim(fgets(STDIN));
    echo "Contraseña (10-72 bytes; será visible en esta consola): "; $password = rtrim(fgets(STDIN),"\r\n");
    if (!$nombre || strlen($nombre)>150 || !preg_match('/^[A-Za-z0-9_.-]{3,50}$/',$usuario) || strlen($password)<10 || strlen($password)>72) throw new RuntimeException('Datos inválidos.');
    $s = db()->prepare("INSERT INTO usuarios (usuario,nombre,password_hash,rol) VALUES (?,?,?,'admin')");
    $s->execute([$usuario,$nombre,password_hash($password,PASSWORD_DEFAULT)]);
    echo "Administrador creado. Abre http://localhost:8888/sitae/\n";
} catch (Throwable $e) { fwrite(STDERR,"No se creó el administrador. Revisa la configuración, el SQL y los datos introducidos.\n"); exit(1); }
