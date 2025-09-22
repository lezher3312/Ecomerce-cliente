<?php
// Normaliza path y soporta subcarpeta automáticamente
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$basePath  = ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') ? '' : $scriptDir;

// Asegurar barra inicial en basePath
if ($basePath && $basePath[0] !== '/') {
    $basePath = '/' . $basePath;
}

// Path limpio relativo a la app
$path = rawurldecode($uri);
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
$path = '/' . ltrim($path, '/'); // normaliza

// ---------------- RUTAS ----------------

// Inicio
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($path === '/' || $path === '/inicio')) {
    require_once __DIR__ . '/controllers/InicioController.php';
    (new InicioController())->index();
    exit;
}

// Catálogo
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/catalogo') {
    require_once __DIR__ . '/controllers/CatalogoController.php';
    (new CatalogoController())->index();
    exit;
}

// Novedades
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/novedades') {
    require_once __DIR__ . '/controllers/CatalogoController.php';
    (new CatalogoController())->novedades();
    exit;
}

// Más vendidos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/mas-vendidos') {
    require_once __DIR__ . '/controllers/CatalogoController.php';
    (new CatalogoController())->masVendidos();
    exit;
}

// Ofertas
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/ofertas') {
    require_once __DIR__ . '/controllers/CatalogoController.php';
    (new CatalogoController())->ofertas();
    exit;
}

// Detalle producto
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/detalle') {
    require_once __DIR__ . '/controllers/DetalleController.php';
    (new DetalleController())->show($_GET['id'] ?? null);
    exit;
}

// ---------------- AUTENTICACIÓN ----------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/login') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->index();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/registro') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->registro();
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/registro') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->registro();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/mensaje') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->mensaje();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/confirmar-cuenta') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->confirmar();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/olvide') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->olvide();
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/olvide') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->olvide();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/reestablecer') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->reestablecer();
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/reestablecer') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->reestablecer();
    exit;
}

// ---------------- CARRITO ----------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/carrito') {
    require_once __DIR__ . '/controllers/CarritoController.php';
    (new CarritoController())->index();
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/carrito/agregar') {
    require_once __DIR__ . '/controllers/CarritoController.php';
    (new CarritoController())->agregar();
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/carrito/actualizar') {
    require_once __DIR__ . '/controllers/CarritoActualizarController.php';
    (new CarritoActualizarController())->actualizar();
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/carrito/eliminar') {
    require_once __DIR__ . '/controllers/CarritoActualizarController.php';
    (new CarritoActualizarController())->eliminar();
    exit;
}

// ---------------- PEDIDOS / ENVÍOS ----------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/registro/pedido') {
    require_once __DIR__ . '/controllers/registroPedidoController.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new RegistroPedidoController())->registrar();
    } else {
        (new RegistroPedidoController())->index();
    }
    exit;
}
if ($path === '/envio') {
    require_once __DIR__ . '/controllers/envioController.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        (new EnvioController())->guardar();
    } else {
        (new EnvioController())->index();
    }
    exit;
}

