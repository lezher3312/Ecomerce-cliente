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

// DEBUG (descomenta si sigue en blanco)
// echo "PATH={$path} | basePath={$basePath}"; exit;

// Ruta: GET /
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($path === '/' || $path === '/inicio')) {
  require_once __DIR__ . '/controllers/InicioController.php';
  (new InicioController())->index();
  exit;
}
// GET /catalogo
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $path === '/catalogo') {
  require_once __DIR__ . '/controllers/CatalogoController.php';
  (new CatalogoController())->index();
  exit;
}


// 404 por defecto
http_response_code(404);
echo "404 — Ruta no encontrada: {$path}";
