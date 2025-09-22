<?php
// URL base dinámica (soporta subcarpeta o raíz)
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'];
$scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/'); 
$basePath = ($scriptDir === '/' ? '' : $scriptDir);

// ✅ asegura que termine con /
if ($basePath && substr($basePath, -1) !== '/') {
    $basePath .= '/';
}

define('BASE_URL', $scheme . '://' . $host . $basePath);

/**
 * asset('css/main.css') -> http://host/subcarpeta/public/css/main.css
 */
function asset(string $path): string {
  return BASE_URL . 'public/' . ltrim($path, '/');
}

/**
 * url('inicio') -> http://host/subcarpeta/inicio
 */
function url(string $path = ''): string {
  return BASE_URL . ltrim($path, '/');
}
