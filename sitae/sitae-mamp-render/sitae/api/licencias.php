<?php
require __DIR__ . '/comun.php';
metodo(['GET','POST','PUT','DELETE']);
$lectura = $_SERVER['REQUEST_METHOD'] === 'GET';
$u = exigir_usuario($lectura ? ['admin','editor','lector'] : ['admin','editor']);
if (!$lectura) csrf();
$pdo = db();
$campos = ["licencia", "codOperador", "operador", "denominacion", "rtn", "identidad", "direccion", "municipio", "departamento", "pais", "telefono", "fax", "correo", "otroTel", "rNombre", "rIdentidad", "rTelefono", "rCorreo", "rDireccion", "tipo", "modelo", "fuselaje", "registro", "fabricacion", "aeropuerto", "actividad", "maxPasajeros", "indicativo", "emision", "vencimiento", "observaciones", "radarSi"];
function con_equipos($l) {
    $l['num'] = (int)$l['num']; $l['version'] = (int)$l['version'];
    $l['eq'] = ['hf'=>[],'vhf'=>[],'nav'=>[],'radar'=>[],'emerg'=>[]];
    $s = db()->prepare('SELECT * FROM equipos WHERE licencia_id=? ORDER BY id');
    $s->execute([$l['num']]);
    foreach ($s as $e) {
        $k = $e['categoria']; unset($e['id'],$e['licencia_id'],$e['categoria']);
        $l['eq'][$k][] = $e;
    }
    return $l;
}
if ($lectura) {
    if (isset($_GET['id'])) {
        $id = entero($_GET['id'],'id',false);
        $s = $pdo->prepare('SELECT * FROM licencias WHERE num=?'); $s->execute([$id]); $l = $s->fetch();
        if (!$l) responder(['error'=>'La licencia no existe.'],404);
        responder(['licencia'=>con_equipos($l)]);
    }
    if (isset($_GET['siguiente'])) {
        $s = $pdo->query('SELECT licencia FROM licencias'); $n = 0;
        foreach ($s as $l) if (preg_match('/(\d+)$/',$l['licencia'],$m)) $n = max($n,(int)$m[1]);
        responder(['codigo'=>'CNT-MA-'.str_pad((string)($n+1),4,'0',STR_PAD_LEFT)]);
    }
    $q = texto($_GET,'q',60);
    $sel = implode(',', array_map('q', ['num','licencia','codOperador','operador','indicativo','emision','vencimiento','version']));
    $s = $pdo->prepare("SELECT $sel FROM licencias WHERE licencia " . (es_pg() ? 'ILIKE' : 'LIKE') . ' ? ORDER BY num DESC');
    $s->execute(['%' . addcslashes($q, '%_\\') . '%']); responder(['licencias'=>$s->fetchAll()]);
}
$d = cuerpo();
$id = isset($_GET['id']) ? entero($_GET['id'],'id',false) : null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !$id) responder(['error'=>'Falta el identificador.'],400);
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $version = entero($d['version'] ?? null,'version',false);
    $s = $pdo->prepare('DELETE FROM licencias WHERE num=? AND version=?'); $s->execute([$id,$version]);
    if (!$s->rowCount()) responder(['error'=>'La licencia cambió o ya no existe. Recarga la tabla.'],409);
    responder(['ok'=>true]);
}
$datos = [];
foreach ($campos as $k) {
    if (in_array($k,['fabricacion','emision','vencimiento'],true)) {
        $v = texto($d,$k,10);
        if ($v !== '') {
            $fecha = DateTime::createFromFormat('!Y-m-d',$v);
            if (!$fecha || $fecha->format('Y-m-d') !== $v || $v < '1000-01-01') responder(['error'=>"Fecha inválida: $k"],422);
        }
        $datos[$k] = $v === '' ? null : $v;
    } elseif ($k === 'maxPasajeros') $datos[$k] = entero($d[$k] ?? null,$k);
    else $datos[$k] = texto($d,$k,in_array($k,['direccion','rDireccion','observaciones'],true)?10000:($k==='licencia'?60:255),in_array($k,['licencia','operador'],true));
}
foreach (['correo','rCorreo'] as $k) if ($datos[$k] !== '' && !filter_var($datos[$k],FILTER_VALIDATE_EMAIL)) responder(['error'=>"Correo inválido: $k"],422);
if (!in_array($datos['radarSi'],['No','Sí'],true)) responder(['error'=>'Selecciona si cuenta con radar.'],422);
if ($datos['emision'] && $datos['vencimiento'] && $datos['vencimiento'] < $datos['emision']) responder(['error'=>'La expiración no puede ser anterior a la emisión.'],422);
$eq = $d['eq'] ?? [];
if (!is_array($eq)) responder(['error'=>'Equipos inválidos.'],422);
$equipos = [];
foreach (['hf','vhf','nav','radar','emerg'] as $k) {
    $lista = $eq[$k] ?? [];
    if (!is_array($lista) || count($lista)>100) responder(['error'=>'Máximo cien equipos por categoría.'],422);
    foreach ($lista as $e) {
        if (!is_array($e)) responder(['error'=>'Equipo inválido.'],422);
        $fila = [$k];
        foreach (['tipo','marca','modelo','serie','banda','clase','modulacion'] as $c) $fila[] = texto($e,$c);
        $fila[] = entero($e['cantidad'] ?? null,'cantidad'); $equipos[] = $fila;
    }
}
$pdo->beginTransaction();
$cols = implode(',',array_map('q',$campos));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marcas = implode(',',array_fill(0,count($campos),'?'));
    $s = $pdo->prepare("INSERT INTO licencias ($cols,creado_por,actualizado_por) VALUES ($marcas,?,?)");
    $s->execute(array_merge(array_values($datos),[$u['id'],$u['id']])); $id = (int)$pdo->lastInsertId(es_pg() ? 'licencias_num_seq' : null);
} else {
    $version = entero($d['version'] ?? null,'version',false);
    $asignaciones = implode(',',array_map(function($c){return q($c).'=?';},$campos));
    $s = $pdo->prepare("UPDATE licencias SET $asignaciones,actualizado_por=?,actualizado_en=CURRENT_TIMESTAMP,version=version+1 WHERE num=? AND version=?");
    $s->execute(array_merge(array_values($datos),[$u['id'],$id,$version]));
    if (!$s->rowCount()) { $pdo->rollBack(); responder(['error'=>'Otra persona modificó esta licencia o la eliminó. Vuelve a abrirla.'],409); }
    $pdo->prepare('DELETE FROM equipos WHERE licencia_id=?')->execute([$id]);
}
$s = $pdo->prepare('INSERT INTO equipos (licencia_id,categoria,tipo,marca,modelo,serie,banda,clase,modulacion,cantidad) VALUES (?,?,?,?,?,?,?,?,?,?)');
foreach ($equipos as $fila) $s->execute(array_merge([$id],$fila));
$pdo->commit(); responder(['ok'=>true,'num'=>$id],$_SERVER['REQUEST_METHOD']==='POST'?201:200);
