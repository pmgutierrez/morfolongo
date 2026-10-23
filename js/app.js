/* ============================================================
   Morfolongo - JavaScript
   Tema, menú, filtros, carrito y formularios (con SweetAlert2)
   ============================================================ */

/* ---------- Utilidades ---------- */
const formatoPesos = (n) => '$' + Math.round(n).toLocaleString('es-AR');
const emailValido = (t) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(t);

// localStorage puede fallar (modo privado): siempre con try/catch
function leerAlmacen(clave, defecto) {
  try {
    const v = localStorage.getItem(clave);
    return v ? JSON.parse(v) : defecto;
  } catch (e) {
    return defecto;
  }
}
function guardarAlmacen(clave, valor) {
  try { localStorage.setItem(clave, JSON.stringify(valor)); } catch (e) { /* sin persistencia */ }
}

/* ============================================================
   1) TEMA CLARO / OSCURO
   ============================================================ */
const btnTema = document.getElementById('btnTema');

function actualizarBotonTema() {
  if (document.body.classList.contains('dark')) {
    btnTema.textContent = '☀️ Modo claro';
    btnTema.dataset.icono = '☀️';
  } else {
    btnTema.textContent = '🌙 Modo oscuro';
    btnTema.dataset.icono = '🌙';
  }
}

(function iniciarTema() {
  const guardado = leerAlmacen('morfo_tema', null);
  const prefiereOscuro = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  if (guardado === 'dark' || (guardado === null && prefiereOscuro)) {
    document.body.classList.add('dark');
  }
  actualizarBotonTema();
})();

btnTema.addEventListener('click', () => {
  document.body.classList.toggle('dark');
  // Comprobación del tema activo con if / else
  if (document.body.classList.contains('dark')) {
    guardarAlmacen('morfo_tema', 'dark');
  } else {
    guardarAlmacen('morfo_tema', 'light');
  }
  actualizarBotonTema();
});

/* ============================================================
   2) MENÚ HAMBURGUESA Y SUBMENÚ
   ============================================================ */
const btnHamburguesa = document.getElementById('btnHamburguesa');
const menuPrincipal = document.getElementById('menuPrincipal');
const itemSubmenu = document.querySelector('.tiene-submenu');
const enlaceSubmenu = document.querySelector('.enlace-submenu');

btnHamburguesa.addEventListener('click', () => {
  const abierto = menuPrincipal.classList.toggle('abierto');
  btnHamburguesa.setAttribute('aria-expanded', abierto);
  btnHamburguesa.setAttribute('aria-label', abierto ? 'Cerrar menú' : 'Abrir menú');
});

// En pantallas táctiles el submenú se abre con un toque
enlaceSubmenu.addEventListener('click', (e) => {
  if (window.matchMedia('(max-width: 768px)').matches) {
    e.preventDefault();
    const abierto = itemSubmenu.classList.toggle('abierto');
    enlaceSubmenu.setAttribute('aria-expanded', abierto);
  }
});

// Cerrar el menú móvil al elegir una opción (menos el título del submenú)
menuPrincipal.addEventListener('click', (e) => {
  const enlace = e.target.closest('a');
  if (enlace && !enlace.classList.contains('enlace-submenu')) {
    menuPrincipal.classList.remove('abierto');
    itemSubmenu.classList.remove('abierto');
    btnHamburguesa.setAttribute('aria-expanded', 'false');
  }
});

/* ============================================================
   3) FILTRO DEL CATÁLOGO
   ============================================================ */
const tarjetas = document.querySelectorAll('.tarjeta');
const botonesFiltro = document.querySelectorAll('.filtro');

function filtrar(categoria) {
  tarjetas.forEach((t) => {
    t.hidden = !(categoria === 'todos' || t.dataset.categoria === categoria);
  });
  botonesFiltro.forEach((b) => b.classList.toggle('activo', b.dataset.filtro === categoria));
}

botonesFiltro.forEach((b) => b.addEventListener('click', () => filtrar(b.dataset.filtro)));
document.querySelectorAll('.submenu a').forEach((a) =>
  a.addEventListener('click', () => filtrar(a.dataset.filtro))
);

/* ============================================================
   4) CARRITO DE COMPRAS
   carrito = { "idProducto": { id, nombre, precio, imagen, cantidad } }
   ============================================================ */
let carrito = leerAlmacen('morfo_carrito', {});

const panelCarrito = document.getElementById('carrito');
const velo = document.getElementById('velo');
const listaCarrito = document.getElementById('carritoLista');
const msgVacio = document.getElementById('carritoVacio');
const totalCarrito = document.getElementById('carritoTotal');
const totalChico = document.getElementById('totalChico');
const contador = document.getElementById('contador');

function agregarAlCarrito(tarjeta) {
  const id = tarjeta.dataset.id;
  if (carrito[id]) {
    if (carrito[id].cantidad >= 20) {
      Swal.fire({ icon: 'info', title: 'Máximo alcanzado', text: 'Podés pedir hasta 20 unidades de cada producto.', confirmButtonColor: '#d71920' });
      return;
    }
    carrito[id].cantidad++;
  } else {
    carrito[id] = {
      id: Number(id),
      nombre: tarjeta.dataset.nombre,
      precio: Number(tarjeta.dataset.precio),
      imagen: tarjeta.dataset.imagen,
      cantidad: 1
    };
  }
  actualizarCarrito();
}

function quitarDelCarrito(id) {
  if (!carrito[id]) {
    // Nada para quitar: se avisa con un mensaje breve
    Swal.fire({ toast: true, position: 'top-end', icon: 'info', title: 'Ese producto no está en tu carrito', showConfirmButton: false, timer: 1800 });
    return;
  }
  carrito[id].cantidad--;
  if (carrito[id].cantidad <= 0) {
    delete carrito[id];
  }
  actualizarCarrito();
}

function totalesCarrito() {
  let cantidad = 0;
  let total = 0;
  Object.values(carrito).forEach((it) => {
    cantidad += it.cantidad;
    total += it.cantidad * it.precio;
  });
  return { cantidad, total };
}

function actualizarCarrito() {
  guardarAlmacen('morfo_carrito', carrito);
  const { cantidad, total } = totalesCarrito();

  // Contadores del encabezado y total
  contador.textContent = cantidad;
  totalChico.textContent = formatoPesos(total);
  totalCarrito.textContent = formatoPesos(total);

  // Lista del carrito
  listaCarrito.innerHTML = '';
  Object.values(carrito).forEach((it) => {
    const li = document.createElement('li');
    li.innerHTML = `
      <img src="Imagenes/${it.imagen}" alt="">
      <div class="item-info">${it.nombre}<small>${formatoPesos(it.precio)} c/u &middot; ${formatoPesos(it.precio * it.cantidad)}</small></div>
      <div class="item-ctrl">
        <button type="button" data-id="${it.id}" data-accion="quitar" aria-label="Quitar uno">−</button>
        <span>${it.cantidad}</span>
        <button type="button" data-id="${it.id}" data-accion="agregar" aria-label="Agregar uno">+</button>
      </div>`;
    listaCarrito.appendChild(li);
  });
  msgVacio.hidden = cantidad > 0;

  // Burbuja con la cantidad en cada tarjeta del catálogo
  tarjetas.forEach((t) => {
    const badge = t.querySelector('.en-carrito');
    const enCarrito = carrito[t.dataset.id];
    if (enCarrito) {
      badge.textContent = enCarrito.cantidad;
      badge.hidden = false;
    } else {
      badge.hidden = true;
    }
  });
}

// Botones "Agregar" y "Quitar" de cada tarjeta (delegación de eventos)
document.querySelector('.grilla').addEventListener('click', (e) => {
  const boton = e.target.closest('button[data-accion]');
  if (!boton) return;
  const tarjeta = boton.closest('.tarjeta');
  if (boton.dataset.accion === 'agregar') {
    agregarAlCarrito(tarjeta);
  } else {
    quitarDelCarrito(tarjeta.dataset.id);
  }
});

// Botones + / − dentro del carrito
listaCarrito.addEventListener('click', (e) => {
  const boton = e.target.closest('button[data-accion]');
  if (!boton) return;
  if (boton.dataset.accion === 'agregar') {
    agregarAlCarrito(document.querySelector(`.tarjeta[data-id="${boton.dataset.id}"]`));
  } else {
    quitarDelCarrito(boton.dataset.id);
  }
});

// Abrir y cerrar el panel del carrito
function abrirCarrito() {
  panelCarrito.classList.add('abierto');
  panelCarrito.setAttribute('aria-hidden', 'false');
  velo.hidden = false;
}
function cerrarCarrito() {
  panelCarrito.classList.remove('abierto');
  panelCarrito.setAttribute('aria-hidden', 'true');
  velo.hidden = true;
}
document.getElementById('btnCarrito').addEventListener('click', abrirCarrito);
document.getElementById('btnCerrarCarrito').addEventListener('click', cerrarCarrito);
velo.addEventListener('click', cerrarCarrito);
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrarCarrito(); });

// Vaciar carrito con confirmación
document.getElementById('btnVaciar').addEventListener('click', async () => {
  if (totalesCarrito().cantidad === 0) return;
  const r = await Swal.fire({
    icon: 'question', title: '¿Vaciar el carrito?', text: 'Se van a quitar todos los productos.',
    showCancelButton: true, confirmButtonText: 'Sí, vaciar', cancelButtonText: 'Cancelar', confirmButtonColor: '#d71920'
  });
  if (r.isConfirmed) {
    carrito = {};
    actualizarCarrito();
  }
});

/* ============================================================
   5) CONFIRMAR PEDIDO -> PHP -> MySQL
   ============================================================ */
const formPedido = document.getElementById('formPedido');

function marcarError(campo, hayError) {
  campo.classList.toggle('error', hayError);
}

formPedido.addEventListener('submit', async (e) => {
  e.preventDefault();

  const campos = {
    nombre: document.getElementById('pNombre'),
    email: document.getElementById('pEmail'),
    telefono: document.getElementById('pTelefono'),
    direccion: document.getElementById('pDireccion')
  };
  const notas = document.getElementById('pNotas').value.trim();

  // Validaciones con if / else
  if (totalesCarrito().cantidad === 0) {
    Swal.fire({ icon: 'warning', title: 'Tu carrito está vacío', text: 'Agregá al menos un producto para hacer el pedido.', confirmButtonColor: '#d71920' });
    return;
  }

  let faltan = false;
  Object.values(campos).forEach((c) => {
    const vacio = c.value.trim() === '';
    marcarError(c, vacio);
    if (vacio) faltan = true;
  });
  if (faltan) {
    Swal.fire({ icon: 'error', title: 'Faltan datos', text: 'Completá nombre, email, teléfono y dirección.', confirmButtonColor: '#d71920' });
    return;
  }

  if (!emailValido(campos.email.value.trim())) {
    marcarError(campos.email, true);
    Swal.fire({ icon: 'error', title: 'Email no válido', text: 'Revisá que tenga el formato nombre@dominio.com.', confirmButtonColor: '#d71920' });
    return;
  }

  const soloDigitos = campos.telefono.value.replace(/\D/g, '');
  if (soloDigitos.length < 8) {
    marcarError(campos.telefono, true);
    Swal.fire({ icon: 'error', title: 'Teléfono no válido', text: 'Ingresá al menos 8 números.', confirmButtonColor: '#d71920' });
    return;
  }

  // El carrito de JavaScript se convierte en JSON y se envía a PHP
  const carga = {
    cliente: {
      nombre: campos.nombre.value.trim(),
      email: campos.email.value.trim(),
      telefono: campos.telefono.value.trim(),
      direccion: campos.direccion.value.trim()
    },
    notas: notas,
    items: Object.values(carrito).map((it) => ({ id: it.id, cantidad: it.cantidad }))
  };

  try {
    const resp = await fetch('php/guardar_pedido.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(carga)
    });
    const datos = await resp.json();

    if (datos.ok) {
      carrito = {};
      actualizarCarrito();
      formPedido.reset();
      cerrarCarrito();
      Swal.fire({
        icon: 'success',
        title: '¡Pedido recibido!',
        html: `Tu pedido <strong>#${datos.pedido}</strong> por <strong>${formatoPesos(datos.total)}</strong> está en preparación.`,
        confirmButtonColor: '#d71920'
      });
    } else {
      Swal.fire({ icon: 'error', title: 'No se pudo enviar', text: datos.mensaje, confirmButtonColor: '#d71920' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Sin conexión con el servidor', text: 'Verificá que Apache y MySQL estén encendidos.', confirmButtonColor: '#d71920' });
  }
});

/* ============================================================
   6) FORMULARIO DE CONTACTO -> PHP -> MySQL
   ============================================================ */
const formContacto = document.getElementById('formContacto');

formContacto.addEventListener('submit', async (e) => {
  e.preventDefault();

  const nombre = document.getElementById('cNombre');
  const email = document.getElementById('cEmail');
  const tipo = document.getElementById('cTipo');
  const mensaje = document.getElementById('cMensaje');
  const todos = [nombre, email, tipo, mensaje];

  let faltan = false;
  todos.forEach((c) => {
    const vacio = c.value.trim() === '';
    marcarError(c, vacio);
    if (vacio) faltan = true;
  });

  if (faltan) {
    Swal.fire({ icon: 'error', title: 'Faltan campos', text: 'Completá todos los campos antes de enviar.', confirmButtonColor: '#d71920' });
    return;
  }
  if (!emailValido(email.value.trim())) {
    marcarError(email, true);
    Swal.fire({ icon: 'error', title: 'Email no válido', text: 'Revisá que tenga el formato nombre@dominio.com.', confirmButtonColor: '#d71920' });
    return;
  }
  if (mensaje.value.trim().length < 10) {
    marcarError(mensaje, true);
    Swal.fire({ icon: 'error', title: 'Mensaje muy corto', text: 'Escribí al menos 10 caracteres.', confirmButtonColor: '#d71920' });
    return;
  }

  try {
    const resp = await fetch('php/guardar_contacto.php', { method: 'POST', body: new FormData(formContacto) });
    const datos = await resp.json();

    if (datos.ok) {
      formContacto.reset();
      Swal.fire({ icon: 'success', title: '¡Consulta enviada!', text: 'Te respondemos por email lo antes posible.', confirmButtonColor: '#d71920' });
    } else {
      Swal.fire({ icon: 'error', title: 'No se pudo enviar', text: datos.mensaje, confirmButtonColor: '#d71920' });
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Sin conexión con el servidor', text: 'Verificá que Apache y MySQL estén encendidos.', confirmButtonColor: '#d71920' });
  }
});

// Quitar el borde rojo apenas el usuario corrige un campo
document.querySelectorAll('input, select, textarea').forEach((c) =>
  c.addEventListener('input', () => c.classList.remove('error'))
);

// Estado inicial del carrito (recupera lo guardado en el navegador)
actualizarCarrito();
