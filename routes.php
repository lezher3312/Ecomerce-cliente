<?php


// Normaliza path y soporta subcarpeta automáticamente
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/'); // p.ej. /Global-client
$basePath  = ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') ? '' : $scriptDir;

// Path limpio relativo a la app
$path = rawurldecode($uri);
if (strpos($path, $basePath) === 0) {
  $path = substr($path, strlen($basePath));
}
$path = '/' . ltrim($path, '/'); // normaliza

// inicia la pagina
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($path === '/' || $path === '/inicio')) {
  require_once __DIR__ . '/controllers/InicioController.php';
  (new InicioController())->index();
  exit;
}

// Catálogo general
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

// detalle producto
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/detalle') {
  require_once __DIR__ . '/controllers/DetalleController.php';
  (new DetalleController())->show($_GET['id'] ?? null);
  exit;
}


//Autenticación
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($path === '/login')) {
  require_once __DIR__ . '/controllers/AuthController.php';
  (new AuthController())->index();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($path === '/registro')) {
  require_once __DIR__ . '/controllers/AuthController.php';
  (new AuthController())->registro();
  exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($path === '/registro')) {
  require_once __DIR__ . '/controllers/AuthController.php';
  (new AuthController())->registro();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($path === '/mensaje')) {
  require_once __DIR__ . '/controllers/AuthController.php';
  (new AuthController())->mensaje();
  exit;
}


// POST /carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/carrito') {
  require_once __DIR__ . '/controllers/CarritoController.php';
  (new CarritoController())->agregar();
  exit;
}

// GET /carrito
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/carrito') {
  require_once __DIR__ . '/controllers/CarritoController.php';
  (new CarritoController())->index();
  exit;
}


// 404 por defecto
http_response_code(404);
echo "404 — Ruta no encontrada: {$path}";
