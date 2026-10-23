<?php
// Catálogo leído desde MySQL (SELECT con JOIN entre productos y categorias)
require __DIR__ . '/php/conexion.php';

$productos = $pdo->query(
    'SELECT p.id, p.nombre, p.descripcion, p.precio, p.imagen, c.nombre AS categoria, c.slug
       FROM productos p
       JOIN categorias c ON c.id = p.categoria_id
      WHERE p.activo = 1
   ORDER BY c.id, p.id'
)->fetchAll();

function e(string $t): string { return htmlspecialchars($t, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Morfolongo | Hamburguesas que se piden dos veces</title>
  <meta name="description" content="Morfolongo: hamburguesas, aros de cebolla y bebidas. Armá tu pedido online.">
  <link rel="icon" href="Imagenes/logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Nunito:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="pagina">

  <header class="encabezado">
    <a href="#inicio" class="marca" aria-label="Morfolongo, ir al inicio">
      <img src="Imagenes/logo.png" alt="Logo de Morfolongo" width="64" height="64">
      <span>Morfolongo</span>
    </a>

    <button class="btn-hamburguesa" id="btnHamburguesa" aria-label="Abrir menú" aria-expanded="false" aria-controls="menuPrincipal">
      <span></span><span></span><span></span>
    </button>

    <nav class="nav" id="menuPrincipal" aria-label="Navegación principal">
      <ul class="menu">
        <li><a href="#inicio">Inicio</a></li>
        <li class="tiene-submenu">
          <a href="#catalogo" class="enlace-submenu" aria-haspopup="true" aria-expanded="false">Menú &#9662;</a>
          <ul class="submenu">
            <li><a href="#catalogo" data-filtro="hamburguesas">Hamburguesas</a></li>
            <li><a href="#catalogo" data-filtro="acompanamientos">Acompañamientos</a></li>
            <li><a href="#catalogo" data-filtro="bebidas">Bebidas</a></li>
          </ul>
        </li>
        <li><a href="#nosotros">Nosotros</a></li>
        <li><a href="#contacto">Contacto</a></li>
      </ul>
    </nav>

    <div class="acciones">
      <button class="btn-tema" id="btnTema" aria-label="Cambiar entre modo claro y oscuro">🌙 Modo oscuro</button>
      <button class="btn-carrito" id="btnCarrito" aria-label="Abrir carrito">
        🛒 <span class="carrito-total-chico" id="totalChico">$0</span>
        <span class="burbuja" id="contador">0</span>
      </button>
    </div>
  </header>

  <main class="principal">

    <section class="hero" id="inicio">
      <div class="hero-texto">
        <h1>Hamburguesas para pedir dos veces</h1>
        <p>Carne jugosa, queso derretido y pan de papa. Armá tu pedido en un minuto y lo llevamos a tu puerta.</p>
        <a href="#catalogo" class="btn btn-principal">Quiero mi hamburguesa</a>
      </div>
      <img class="hero-img" src="Imagenes/burguer3.jpg" alt="Morfo Doble: dos medallones con cheddar" width="420" height="420">
    </section>

    <section class="catalogo" id="catalogo">
      <h2>Elegí tu antojo</h2>

      <div class="filtros" role="group" aria-label="Filtrar productos por categoría">
        <button class="filtro activo" data-filtro="todos">Todos</button>
        <button class="filtro" data-filtro="hamburguesas">Hamburguesas</button>
        <button class="filtro" data-filtro="acompanamientos">Acompañamientos</button>
        <button class="filtro" data-filtro="bebidas">Bebidas</button>
      </div>

      <div class="grilla">
        <?php foreach ($productos as $p): ?>
          <article class="tarjeta"
                   data-id="<?= (int) $p['id'] ?>"
                   data-nombre="<?= e($p['nombre']) ?>"
                   data-precio="<?= (float) $p['precio'] ?>"
                   data-imagen="<?= e($p['imagen']) ?>"
                   data-categoria="<?= e($p['slug']) ?>">
            <div class="tarjeta-foto">
              <img src="Imagenes/<?= e($p['imagen']) ?>" alt="<?= e($p['nombre']) ?>" loading="lazy">
              <span class="etiqueta"><?= e($p['categoria']) ?></span>
              <span class="en-carrito" hidden>0</span>
            </div>
            <div class="tarjeta-cuerpo">
              <h3><?= e($p['nombre']) ?></h3>
              <p><?= e($p['descripcion']) ?></p>
              <p class="precio">$<?= number_format((float) $p['precio'], 0, ',', '.') ?></p>
              <div class="tarjeta-botones">
                <button class="btn btn-quitar" data-accion="quitar" aria-label="Quitar <?= e($p['nombre']) ?> del carrito">− Quitar</button>
                <button class="btn btn-agregar" data-accion="agregar" aria-label="Agregar <?= e($p['nombre']) ?> al carrito">+ Agregar</button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
        <?php if (!$productos): ?>
          <p>Todavía no hay productos cargados. Importá bd/final_web.sql en phpMyAdmin.</p>
        <?php endif; ?>
      </div>
    </section>

    <section class="nosotros" id="nosotros">
      <article>
        <h2>Quiénes somos</h2>
        <p>Morfolongo nació en una cocina chica y con una sola regla: la hamburguesa se arma al momento y se come caliente. Usamos carne vacuna fresca, pan de papa y cheddar de verdad.</p>
        <p>Nuestro mozo con bigote te guiña el ojo desde el logo porque sabe que, si probás una, volvés por otra.</p>
      </article>
    </section>

    <section class="contacto" id="contacto">
      <h2>Escribinos</h2>
      <p>¿Tenés un evento, una sugerencia o un pedido especial? Contanos.</p>
      <form id="formContacto" novalidate>
        <div class="campo">
          <label for="cNombre">Nombre</label>
          <input type="text" id="cNombre" name="nombre" autocomplete="name">
        </div>
        <div class="campo">
          <label for="cEmail">Email</label>
          <input type="email" id="cEmail" name="email" autocomplete="email">
        </div>
        <div class="campo">
          <label for="cTipo">Tipo de consulta</label>
          <select id="cTipo" name="tipo_consulta">
            <option value="">Elegí una opción</option>
            <option>Pedido especial</option>
            <option>Evento o cumpleaños</option>
            <option>Reclamo o sugerencia</option>
            <option>Otra consulta</option>
          </select>
        </div>
        <div class="campo campo-ancho">
          <label for="cMensaje">Mensaje</label>
          <textarea id="cMensaje" name="mensaje" rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-principal">Enviar consulta</button>
      </form>
    </section>
  </main>

  <aside class="lateral" aria-label="Información del local">
    <div class="panel">
      <h2>Horarios</h2>
      <ul>
        <li>Lunes a jueves: 19:00 a 23:30</li>
        <li>Viernes y sábado: 19:00 a 01:00</li>
        <li>Domingo: 19:00 a 23:00</li>
      </ul>
    </div>
    <div class="panel panel-promo">
      <h2>Promo de la semana</h2>
      <p>Morfo Doble + Aros de cebolla + Coca-Cola. Pedilos y sumá una cerveza artesanal.</p>
    </div>
    <div class="panel">
      <h2>Dónde estamos</h2>
      <p>Pergamino, Buenos Aires. Envíos en toda la ciudad.</p>
    </div>
    <div class="panel">
      <h2>Redes</h2>
      <ul>
        <li><a href="#" rel="noopener">Instagram @morfolongo</a></li>
        <li><a href="#" rel="noopener">Facebook /morfolongo</a></li>
        <li><a href="php/panel.php">Panel de pedidos</a></li>
      </ul>
    </div>
  </aside>

  <footer class="pie">
    <p>&copy; 2026 Morfolongo. Trabajo Integrador de Desarrollo Web Full Stack.</p>
  </footer>
</div>

<!-- Carrito lateral -->
<div class="velo" id="velo" hidden></div>
<aside class="carrito" id="carrito" aria-label="Carrito de compras" aria-hidden="true">
  <div class="carrito-cabecera">
    <h2>Tu pedido</h2>
    <button class="btn-cerrar" id="btnCerrarCarrito" aria-label="Cerrar carrito">&times;</button>
  </div>

  <ul class="carrito-lista" id="carritoLista"></ul>
  <p class="carrito-vacio" id="carritoVacio">Tu carrito está vacío. Agregá una hamburguesa y empezamos.</p>

  <div class="carrito-pie">
    <p class="carrito-total">Total: <strong id="carritoTotal">$0</strong></p>
    <button class="btn btn-secundario" id="btnVaciar">Vaciar carrito</button>

    <form id="formPedido" novalidate>
      <h3>Datos de entrega</h3>
      <div class="campo"><label for="pNombre">Nombre</label><input type="text" id="pNombre" autocomplete="name"></div>
      <div class="campo"><label for="pEmail">Email</label><input type="email" id="pEmail" autocomplete="email"></div>
      <div class="campo"><label for="pTelefono">Teléfono</label><input type="tel" id="pTelefono" autocomplete="tel"></div>
      <div class="campo"><label for="pDireccion">Dirección</label><input type="text" id="pDireccion" autocomplete="street-address"></div>
      <div class="campo"><label for="pNotas">Aclaraciones (opcional)</label><input type="text" id="pNotas" placeholder="Ej: sin cebolla"></div>
      <button type="submit" class="btn btn-principal">Confirmar pedido</button>
    </form>
  </div>
</aside>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/app.js"></script>
</body>
</html>
