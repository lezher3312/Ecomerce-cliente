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
if ($path === '/login') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->index();
    exit;
}
if ($path === '/registro') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->registro();
    exit;
}
if ($path === '/mensaje') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->mensaje();
    exit;
}
if ($path === '/confirmar-cuenta') {
    require_once __DIR__ . '/controllers/AuthController.php';
    (new AuthController())->confirmar();
    exit;
}

// ---------------- CARRITO ----------------
if ($path === '/carrito') {
    require_once __DIR__ . '/controllers/CarritoController.php';
    (new CarritoController())->index();
    exit;
}
if ($path === '/carrito/agregar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/controllers/CarritoController.php';
    (new CarritoController())->agregar();
    exit;
}
if ($path === '/carrito/actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/controllers/CarritoActualizarController.php';
    (new CarritoActualizarController())->actualizar();
    exit;
}
if ($path === '/carrito/eliminar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/controllers/CarritoActualizarController.php';
    (new CarritoActualizarController())->eliminar();
    exit;
}

// ---------------- PEDIDOS / ENVÍOS ----------------
if ($path === '/registro/pedido') {
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

// ---------------- 404 POR DEFECTO ----------------
http_response_code(404);
echo "404 — Ruta no encontrada: {$path}";
