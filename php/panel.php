<?php
// Extra opcional: muestra con SELECT lo que se guardó en la base de datos.
// OJO: este panel no tiene contraseña. Es solo para la entrega del trabajo.
require __DIR__ . '/conexion.php';

$pedidos = $pdo->query(
    'SELECT ped.id, cli.nombre, cli.email, cli.telefono, cli.direccion, ped.total, ped.estado, ped.fecha,
            GROUP_CONCAT(CONCAT(d.cantidad, " x ", pr.nombre) SEPARATOR ", ") AS detalle
       FROM pedidos ped
       JOIN clientes cli       ON cli.id = ped.cliente_id
       JOIN detalle_pedido d   ON d.pedido_id = ped.id
       JOIN productos pr       ON pr.id = d.producto_id
   GROUP BY ped.id
   ORDER BY ped.fecha DESC'
)->fetchAll();

$contactos = $pdo->query('SELECT id, nombre, email, campo_especifico, mensaje, fecha FROM contactos ORDER BY fecha DESC')->fetchAll();

function e(?string $t): string { return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel | Morfolongo</title>
  <style>
    body { font-family: system-ui, sans-serif; margin: 0; padding: 1.5rem; background: #fafafa; color: #1a1a1a; }
    h1, h2 { margin: 0 0 .75rem; }
    h2 { margin-top: 2rem; }
    .tabla { overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; background: #fff; }
    th, td { border: 1px solid #ddd; padding: .5rem .75rem; text-align: left; vertical-align: top; font-size: .9rem; }
    th { background: #d71920; color: #fff; }
    a { color: #d71920; font-weight: 700; }
  </style>
</head>
<body>
  <a href="../index.php">&larr; Volver a la tienda</a>
  <h1>Panel de Morfolongo</h1>

  <h2>Pedidos (<?= count($pedidos) ?>)</h2>
  <div class="tabla">
    <table>
      <tr><th>N°</th><th>Cliente</th><th>Contacto</th><th>Dirección</th><th>Detalle</th><th>Total</th><th>Estado</th><th>Fecha</th></tr>
      <?php foreach ($pedidos as $p): ?>
        <tr>
          <td>#<?= (int) $p['id'] ?></td>
          <td><?= e($p['nombre']) ?></td>
          <td><?= e($p['email']) ?><br><?= e($p['telefono']) ?></td>
          <td><?= e($p['direccion']) ?></td>
          <td><?= e($p['detalle']) ?></td>
          <td>$<?= number_format((float) $p['total'], 0, ',', '.') ?></td>
          <td><?= e($p['estado']) ?></td>
          <td><?= e($p['fecha']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$pedidos): ?><tr><td colspan="8">Todavía no hay pedidos.</td></tr><?php endif; ?>
    </table>
  </div>

  <h2>Consultas (<?= count($contactos) ?>)</h2>
  <div class="tabla">
    <table>
      <tr><th>N°</th><th>Nombre</th><th>Email</th><th>Tipo de consulta</th><th>Mensaje</th><th>Fecha</th></tr>
      <?php foreach ($contactos as $c): ?>
        <tr>
          <td><?= (int) $c['id'] ?></td>
          <td><?= e($c['nombre']) ?></td>
          <td><?= e($c['email']) ?></td>
          <td><?= e($c['campo_especifico']) ?></td>
          <td><?= e($c['mensaje']) ?></td>
          <td><?= e($c['fecha']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$contactos): ?><tr><td colspan="6">Todavía no hay consultas.</td></tr><?php endif; ?>
    </table>
  </div>
</body>
</html>
