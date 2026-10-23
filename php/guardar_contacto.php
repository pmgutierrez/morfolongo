<?php
// Guarda las consultas del formulario de contacto en la tabla contactos.
header('Content-Type: application/json; charset=utf-8');

function responder(int $codigo, array $datos): void {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
}

require __DIR__ . '/conexion.php';

$nombre  = trim($_POST['nombre']  ?? '');
$email   = trim($_POST['email']   ?? '');
$tipo    = trim($_POST['tipo_consulta'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

$tiposValidos = ['Pedido especial', 'Evento o cumpleaños', 'Reclamo o sugerencia', 'Otra consulta'];

if ($nombre === '' || $email === '' || $tipo === '' || $mensaje === '') {
    responder(422, ['ok' => false, 'mensaje' => 'Completá todos los campos.']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(422, ['ok' => false, 'mensaje' => 'El email no es válido.']);
}
if (!in_array($tipo, $tiposValidos, true)) {
    responder(422, ['ok' => false, 'mensaje' => 'Elegí un tipo de consulta válido.']);
}

try {
    $st = $pdo->prepare(
        'INSERT INTO contactos (nombre, email, campo_especifico, mensaje) VALUES (?, ?, ?, ?)'
    );
    $st->execute([$nombre, $email, $tipo, $mensaje]);
    responder(200, ['ok' => true]);
} catch (Throwable $e) {
    responder(500, ['ok' => false, 'mensaje' => 'No pudimos guardar tu consulta. Intentá de nuevo.']);
}
