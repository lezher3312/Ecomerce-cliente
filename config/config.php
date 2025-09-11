<?php
// URL base dinámica (soporta subcarpeta o raíz)
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'];
$scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/'); // p.ej. /marketplace-cliente
$basePath = ($scriptDir === '/' ? '' : $scriptDir);

define('BASE_URL', $scheme . '://' . $host . $basePath);

/**
 * asset('css/main.css') -> http://host/subcarpeta/public/css/main.css
 */
function asset(string $path): string {
  return BASE_URL . '/public/' . ltrim($path, '/');
}
