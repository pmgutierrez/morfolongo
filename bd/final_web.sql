-- ============================================================
--  Morfolongo - Base de datos final_web
--  Importar desde phpMyAdmin (pestaña "Importar") o ejecutar completo.
-- ============================================================
CREATE DATABASE IF NOT EXISTS final_web
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE final_web;

-- Se borran en orden inverso a las dependencias para poder reimportar
DROP TABLE IF EXISTS detalle_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS productos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS contactos;

-- ------------------------------------------------------------
-- Categorías del menú (Hamburguesas / Acompañamientos / Bebidas)
-- ------------------------------------------------------------
CREATE TABLE categorias (
  id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre  VARCHAR(60)  NOT NULL,
  slug    VARCHAR(60)  NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categorias_slug (slug)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Productos (cada producto pertenece a una categoría)
-- ------------------------------------------------------------
CREATE TABLE productos (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  categoria_id  INT UNSIGNED  NOT NULL,
  nombre        VARCHAR(100)  NOT NULL,
  descripcion   VARCHAR(255)  NOT NULL,
  precio        DECIMAL(10,2) NOT NULL,
  imagen        VARCHAR(120)  NOT NULL,
  activo        TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_productos_categoria (categoria_id),
  CONSTRAINT fk_productos_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Clientes que realizan pedidos
-- ------------------------------------------------------------
CREATE TABLE clientes (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre     VARCHAR(100) NOT NULL,
  email      VARCHAR(120) NOT NULL,
  telefono   VARCHAR(30)  NOT NULL,
  direccion  VARCHAR(160) NOT NULL,
  creado     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_clientes_email (email)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Pedidos (cabecera) - un cliente puede tener muchos pedidos
-- ------------------------------------------------------------
CREATE TABLE pedidos (
  id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  cliente_id  INT UNSIGNED  NOT NULL,
  total       DECIMAL(10,2) NOT NULL,
  estado      ENUM('pendiente','preparando','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  notas       VARCHAR(255)  NULL,
  fecha       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pedidos_cliente (cliente_id),
  CONSTRAINT fk_pedidos_cliente
    FOREIGN KEY (cliente_id) REFERENCES clientes (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Detalle del pedido (renglones del carrito)
-- ------------------------------------------------------------
CREATE TABLE detalle_pedido (
  id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  pedido_id        INT UNSIGNED  NOT NULL,
  producto_id      INT UNSIGNED  NOT NULL,
  cantidad         INT UNSIGNED  NOT NULL,
  precio_unitario  DECIMAL(10,2) NOT NULL,
  subtotal         DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_detalle_pedido (pedido_id),
  KEY idx_detalle_producto (producto_id),
  CONSTRAINT fk_detalle_pedido
    FOREIGN KEY (pedido_id) REFERENCES pedidos (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_detalle_producto
    FOREIGN KEY (producto_id) REFERENCES productos (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Contactos (formulario de consultas del sitio)
-- campo_especifico = tipo de consulta (rubro gastronomía)
-- ------------------------------------------------------------
CREATE TABLE contactos (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(100) NOT NULL,
  email             VARCHAR(120) NOT NULL,
  campo_especifico  VARCHAR(60)  NOT NULL,
  mensaje           TEXT         NOT NULL,
  fecha             DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ============================================================
--  DATOS INICIALES
-- ============================================================
INSERT INTO categorias (id, nombre, slug) VALUES
  (1, 'Hamburguesas',     'hamburguesas'),
  (2, 'Acompañamientos',  'acompanamientos'),
  (3, 'Bebidas',          'bebidas');

INSERT INTO productos (id, categoria_id, nombre, descripcion, precio, imagen) VALUES
  (1, 1, 'Morfo Clásica',   'Carne vacuna, doble queso cheddar, ketchup y mostaza en pan de papa.',               8500.00,  'burguer1.jpg'),
  (2, 1, 'Morfo Fresca',    'Carne vacuna con lechuga, tomate y mayonesa casera en pan de papa.',                 9200.00,  'burguer2.jpg'),
  (3, 1, 'Morfo Doble',     'Dos medallones de carne, dos fetas de cheddar y cebolla picada. Para mucha hambre.', 11500.00, 'burguer3.jpg'),
  (4, 2, 'Aros de cebolla', 'Aros rebozados y crocantes con salsa cremada de limón y cebollín.',                    5200.00,  'aros_de_cebolla.jpg'),
  (5, 3, 'Cerveza artesanal','Chopp de cerveza artesanal rubia o roja, bien fría (500 ml).',                       4800.00,  'cerveza_artesanal.jpg'),
  (6, 3, 'Coca-Cola',       'Vaso de Coca-Cola con hielo (500 ml).',                                               2800.00,  'coca_cola.png'),
  (7, 3, 'Agua mineral',    'Agua mineral sin gas Villavicencio (500 ml).',                                        2000.00,  'agua.png');

-- ============================================================
--  CONSULTAS DE EJEMPLO (INSERT y SELECT usados por el sitio)
-- ============================================================
-- SELECT de catálogo (index.php):
--   SELECT p.*, c.nombre AS categoria, c.slug FROM productos p
--   JOIN categorias c ON c.id = p.categoria_id WHERE p.activo = 1;
--
-- INSERT de pedido (php/guardar_pedido.php):
--   INSERT INTO clientes (...) / INSERT INTO pedidos (...) / INSERT INTO detalle_pedido (...)
--
-- SELECT de pedidos con su detalle (php/panel.php):
--   SELECT ped.id, cli.nombre, ped.total, ped.fecha FROM pedidos ped
--   JOIN clientes cli ON cli.id = ped.cliente_id ORDER BY ped.fecha DESC;
