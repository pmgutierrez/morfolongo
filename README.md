# 🍔 Morfolongo

Tienda online de hamburguesas con catálogo en grilla, carrito de compras y registro de pedidos en base de datos.

**Trabajo Integrador 2026 – Desarrollo Web Full Stack**
Temática unificada: *Sitio web para un comercio o emprendimiento* (rubro: gastronomía).

---

## Funcionalidades

- **Catálogo en grilla** con foto, nombre, descripción, categoría y precio de cada producto, leído desde MySQL.
- **Carrito de compras** en JavaScript:
  - Botón **+ Agregar** y botón **− Quitar** en cada tarjeta.
  - Burbuja en la foto con la cantidad de ese producto en el carrito.
  - Panel lateral con detalle, total, controles + / − y opción de vaciar.
  - Se conserva al recargar la página (`localStorage`).
- **Pedido guardado en MySQL:** el carrito se envía en JSON a PHP, que guarda cliente, pedido y renglones dentro de una transacción.
- **Precios seguros:** PHP toma los precios desde la base de datos y no confía en los que envía el navegador.
- **Menú con submenú desplegable** (Menú → Hamburguesas / Acompañamientos / Bebidas) y botón hamburguesa en móvil.
- **Filtro interactivo** del catálogo por categoría.
- **Modo claro / oscuro** con variables CSS y `classList.toggle()`; recuerda la elección del usuario.
- **Formulario de contacto** (nombre, email, tipo de consulta, mensaje) guardado en la tabla `contactos`.
- **Validación completa** en el front-end con alertas de **SweetAlert2** y validación también en el servidor.
- **Aside** con horarios, promoción de la semana, ubicación y redes.
- **Panel de pedidos** (`php/panel.php`) que muestra con `SELECT` los pedidos y consultas guardados.
- **Diseño responsive** para móvil, tablet y escritorio.

## Tecnologías

| Capa | Tecnología |
|---|---|
| Estructura | HTML5 semántico |
| Estilos | CSS3: Grid, Flexbox, Media Queries, variables (`:root`) |
| Lógica | JavaScript (DOM, eventos, `fetch`) |
| Alertas | [SweetAlert2](https://sweetalert2.github.io/) (CDN) |
| Backend | PHP 8 con PDO y consultas preparadas |
| Base de datos | MySQL / MariaDB (phpMyAdmin) |
| Tipografías | Lilita One (títulos) y Nunito (texto), de Google Fonts |

## Estructura del proyecto

```
morfolongo/
├── index.php                 # Página principal (catálogo leído con SELECT)
├── README.md
├── bd/
│   └── final_web.sql         # Base de datos completa con datos iniciales
├── css/
│   └── estilos.css           # Estilos, temas y responsive
├── js/
│   └── app.js                # Tema, menú, filtros, carrito y formularios
├── php/
│   ├── conexion.php          # Conexión PDO a MySQL
│   ├── guardar_pedido.php    # INSERT de cliente, pedido y detalle
│   ├── guardar_contacto.php  # INSERT de consultas
│   └── panel.php             # SELECT de pedidos y consultas
└── Imagenes/                 # Logo y fotos de los productos
```

## Requisitos

- [XAMPP](https://www.apachefriends.org/) (o Laragon / WAMP) con **Apache**, **MySQL** y **PHP 8** o superior.
- Un navegador actualizado.
- Conexión a internet para cargar SweetAlert2 y las tipografías.

## Instalación

1. **Copiá** la carpeta `morfolongo` dentro de `htdocs` (XAMPP).
2. **Encendé** Apache y MySQL desde el panel de XAMPP.
3. **Importá la base de datos:** entrá a `http://localhost/phpmyadmin`, abrí la pestaña *Importar* y subí `bd/final_web.sql`. Esto crea la base `final_web`, las tablas y los productos.
4. **Revisá la conexión** en `php/conexion.php`. Por defecto usa:

   ```php
   $host = 'localhost';
   $base = 'final_web';
   $user = 'root';
   $pass = '';
   ```

5. **Abrí** `http://localhost/morfolongo/`.

## Base de datos `final_web`

| Tabla | Descripción | Relación |
|---|---|---|
| `categorias` | Hamburguesas, Acompañamientos, Bebidas | 1 → N `productos` |
| `productos` | Catálogo con precio e imagen | FK `categoria_id` → `categorias` |
| `clientes` | Datos de entrega (email único) | 1 → N `pedidos` |
| `pedidos` | Cabecera: cliente, total, estado, fecha | FK `cliente_id` → `clientes` |
| `detalle_pedido` | Renglones: producto, cantidad, precio, subtotal | FK `pedido_id` → `pedidos`, FK `producto_id` → `productos` |
| `contactos` | Consultas del formulario (`id, nombre, email, campo_especifico, mensaje, fecha`) | Independiente |

En `contactos`, el campo `campo_especifico` guarda el **tipo de consulta** (pedido especial, evento o cumpleaños, reclamo o sugerencia, otra consulta).

## Cómo funciona el carrito

```
Tarjeta del catálogo ──(+ / −)──► carrito en JavaScript (localStorage)
                                        │
                              "Confirmar pedido"
                                        │  validación + SweetAlert2
                                        ▼
                      fetch POST (JSON) ──► php/guardar_pedido.php
                                        │  valida, lee precios reales y abre una transacción
                                        ▼
                      MySQL: clientes ► pedidos ► detalle_pedido
                                        │
                                        ▼
                         respuesta JSON ──► alerta con el N° de pedido
```

## Cumplimiento de la consigna

| Criterio | Dónde se cumple |
|---|---|
| Temática y coherencia | Marca Morfolongo, paleta del logo (rojo, amarillo, negro) en modo claro y oscuro, campos del formulario propios del rubro |
| HTML5 y estructura | `index.php`: `header`, `nav`, `main`, `section`, `article`, `aside`, `footer`; submenú desplegable y botón hamburguesa |
| CSS3 y responsive | `css/estilos.css`: Grid general, Flexbox en menú, tarjetas y botones, `@media` para móvil/tablet/escritorio, variables `:root` y `body.dark` |
| JavaScript e interacción | `js/app.js`: `addEventListener`, `classList.toggle()`, condicionales `if / else`, filtros y carrito |
| Base de datos y MySQL | `bd/final_web.sql`: 6 tablas con PK y FK; `INSERT` en `php/guardar_*.php`; `SELECT` en `index.php` y `php/panel.php`; formulario validado con SweetAlert2 |
| Extras | Tabla de datos con PHP, filtro interactivo, efectos `hover`/`transition`, aside funcional |

## Notas de seguridad

- Todas las consultas usan **sentencias preparadas (PDO)** para evitar inyección SQL.
- Los datos que se muestran en `panel.php` y `index.php` se escapan con `htmlspecialchars`.
- `php/panel.php` **no tiene contraseña**: es solo para la entrega del trabajo. Antes de publicar el sitio en un servidor real hay que protegerlo.
- Si se despliega en un hosting, usar un usuario de MySQL propio con contraseña y no subir credenciales reales al repositorio.

## Créditos

- Logo de Morfolongo: material propio del proyecto.
- Fotografías de productos: tomadas de bancos de imágenes y sitios web con fines académicos.
- Marcas que aparecen en algunas fotos (Coca-Cola, Villavicencio) pertenecen a sus respectivos dueños.

## Autor

**Tu nombre** – Trabajo Integrador 2026, Desarrollo Web Full Stack.
