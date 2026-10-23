<?php
// Recibe el carrito (JSON) enviado por JavaScript y lo guarda en MySQL.
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

$datos = json_decode(file_get_contents('php://input'), true);
if (!is_array($datos)) {
    responder(400, ['ok' => false, 'mensaje' => 'Datos inválidos.']);
}

// ---- Validación en el servidor (nunca confiar solo en el front-end) ----
$nombre    = trim($datos['cliente']['nombre']    ?? '');
$email     = trim($datos['cliente']['email']     ?? '');
$telefono  = trim($datos['cliente']['telefono']  ?? '');
$direccion = trim($datos['cliente']['direccion'] ?? '');
$notas     = trim($datos['notas'] ?? '');
$items     = $datos['items'] ?? [];

if ($nombre === '' || $email === '' || $telefono === '' || $direccion === '') {
    responder(422, ['ok' => false, 'mensaje' => 'Completá todos los datos de entrega.']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(422, ['ok' => false, 'mensaje' => 'El email no es válido.']);
}
if (!is_array($items) || count($items) === 0) {
    responder(422, ['ok' => false, 'mensaje' => 'El carrito está vacío.']);
}

try {
    $pdo->beginTransaction();

    // 1) Cliente: si el email ya existe se actualizan sus datos, si no se crea
    $st = $pdo->prepare('SELECT id FROM clientes WHERE email = ?');
    $st->execute([$email]);
    $cliente = $st->fetch();

    if ($cliente) {
        $clienteId = (int) $cliente['id'];
        $pdo->prepare('UPDATE clientes SET nombre = ?, telefono = ?, direccion = ? WHERE id = ?')
            ->execute([$nombre, $telefono, $direccion, $clienteId]);
    } else {
        $pdo->prepare('INSERT INTO clientes (nombre, email, telefono, direccion) VALUES (?, ?, ?, ?)')
            ->execute([$nombre, $email, $telefono, $direccion]);
        $clienteId = (int) $pdo->lastInsertId();
    }

    // 2) Se leen los precios REALES desde la base (el precio del navegador no se usa)
    $stProd = $pdo->prepare('SELECT id, precio FROM productos WHERE id = ? AND activo = 1');
    $lineas = [];
    $total  = 0;

    foreach ($items as $item) {
        $id       = (int) ($item['id'] ?? 0);
        $cantidad = (int) ($item['cantidad'] ?? 0);
        if ($id <= 0 || $cantidad <= 0 || $cantidad > 50) {
            throw new RuntimeException('Hay un producto con datos inválidos.');
        }
        $stProd->execute([$id]);
        $prod = $stProd->fetch();
        if (!$prod) {
            throw new RuntimeException('Un producto del carrito ya no está disponible.');
        }
        $subtotal = $prod['precio'] * $cantidad;
        $total   += $subtotal;
        $lineas[] = [$id, $cantidad, $prod['precio'], $subtotal];
    }

    // 3) Cabecera del pedido
    $pdo->prepare('INSERT INTO pedidos (cliente_id, total, notas) VALUES (?, ?, ?)')
        ->execute([$clienteId, $total, $notas !== '' ? $notas : null]);
    $pedidoId = (int) $pdo->lastInsertId();

    // 4) Renglones del pedido
    $stDet = $pdo->prepare(
        'INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
         VALUES (?, ?, ?, ?, ?)'
    );
    foreach ($lineas as [$prodId, $cant, $precio, $sub]) {
        $stDet->execute([$pedidoId, $prodId, $cant, $precio, $sub]);
    }

    $pdo->commit();
    responder(200, ['ok' => true, 'pedido' => $pedidoId, 'total' => (float) $total]);

} catch (RuntimeException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    responder(422, ['ok' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    responder(500, ['ok' => false, 'mensaje' => 'No pudimos guardar el pedido. Intentá de nuevo.']);
}
