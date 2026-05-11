# Proyecto Final DAW - Tienda WoW

Este proyecto es una tienda online hecha en PHP para vender juegos, expansiones y suscripciones de World of Warcraft.
Lo preparé como proyecto final de 2º de DAW, así que está pensado como una aplicación web completa pero sencilla de entender y ejecutar en local.

## ¿Para qué sirve la aplicación?

La web permite:
- ver productos (juegos y mensualidades),
- marcar favoritos,
- añadir productos al carrito,
- gestionar cantidades y comprar,
- ver historial de pedidos,
- valorar productos,
- cambiar idioma (español/inglés),
- y, si entras como admin, gestionar productos y usuarios.

## Funcionalidades principales

- **Catálogo de productos** con precio normal o precio con descuento.
- **Favoritos** guardados en el navegador.
- **Carrito lateral + página de carrito** con:
  - control de cantidad,
  - eliminación de productos,
  - total general,
  - y total por artículo (precio por unidad + total de línea).
- **Descuentos visibles** en favoritos y carrito (badge de porcentaje y precio original/rebajado).
- **Login y registro** de usuarios.
- **Pedidos** guardados en base de datos y visualización en "Mis pedidos".
- **Panel de administración** para CRUD de productos y gestión de roles.
- **Soporte i18n ES/EN** con archivos JSON en `i18n/`.

## Estructura básica del proyecto

La app real está dentro de esta carpeta:

- `ProyectoFinal_DelRosarioMarreroEleazar/`

Dentro de ella, lo más importante es:

- `index.php`, `login.php`, `carrito.php`, `favoritos.php`, `misPedidos.php` (vistas principales)
- `config/conexion.php` (conexión PDO a MySQL)
- `includes/` (header, footer e i18n)
- `js/` (lógica frontend: carrito, tienda, noticias, UI)
- `css/` (estilos)
- `i18n/es.json` y `i18n/en.json` (traducciones)
- `tienda_wow.sql` (base de datos)

## Requisitos previos

Para levantarlo en local necesitas:

- PHP 8.x (recomendado)
- MySQL o MariaDB
- Un entorno local tipo **XAMPP**, **Laragon**, WAMP o similar
- Navegador web

## Cómo ejecutar el proyecto en local

### 1) Colocar el proyecto en tu servidor local

Si usas XAMPP:
- copia la carpeta en `C:\xampp\htdocs\`

Si usas Laragon:
- copia la carpeta en `C:\laragon\www\`

Después, la carpeta debe quedar accesible desde el servidor web local.

### 2) Crear e importar la base de datos

1. Abre phpMyAdmin (o tu cliente SQL).
2. Crea una base de datos llamada:
   - `tienda_wow`
3. Importa el archivo:
   - `ProyectoFinal_DelRosarioMarreroEleazar/tienda_wow.sql`

### 3) Revisar conexión a base de datos

En el archivo `ProyectoFinal_DelRosarioMarreroEleazar/config/conexion.php` se usan estos valores por defecto:

- host: `localhost`
- bd: `tienda_wow`
- usuario: `root`
- contraseña: vacía

Si en tu entorno usas otros datos, cámbialos ahí.

### 4) Abrir la aplicación en el navegador

Con XAMPP o Laragon arrancados (Apache + MySQL), entra en:

- `http://localhost/TiendaWoW_ProyectoFinal/ProyectoFinal_DelRosarioMarreroEleazar/`

## Notas de uso

- El idioma se guarda por cookie y las traducciones están en `i18n/`.
- El carrito y favoritos usan `localStorage` para parte del estado en cliente.
- Para compras reales y pedidos, sí se usa base de datos.

## Estado actual

Actualmente el proyecto incluye:
- carrito con visualización de descuentos (badge, precio original tachado, precio rebajado, precio por unidad y total por artículo),
- favoritos con el mismo estilo de descuento,
- y textos de interfaz preparados para español/inglés en las zonas principales.

