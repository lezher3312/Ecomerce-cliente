<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cartCount = 0;
if (!empty($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $it) {
        $cartCount += isset($it['cantidad']) ? (int)$it['cantidad'] : 1;
    }
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$basePath  = ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '') ? '' : $scriptDir;
?>
<header class="header">
  <nav class="nav">
    <a class="brand" href="<?= $basePath ?>/inicio" aria-label="Inicio">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm0 3.2a6.8 6.8 0 1 1-6.8 6.8A6.808 6.808 0 0 1 12 5.2Z"/></svg>
      <span>Global-import</span>
    </a>

    <div class="navlinks">
      <a href="<?= $basePath ?>/inicio" class="<?= $uri === $basePath.'/' || $uri === $basePath.'/inicio' ? 'active' : '' ?>">Inicio</a>
      <a href="<?= $basePath ?>/catalogo" class="<?= $path === '/catalogo' ? 'active' : '' ?>">Catálogo</a>
      <a href="<?= $basePath ?>/inicio#destacados">Destacados</a>
      <a href="<?= $basePath ?>/inicio#nuevos">Nuevos</a>
      <a href="<?= $basePath ?>/inicio#ofertas">Ofertas</a>
      <a href="<?= $basePath ?>/ayuda" class="<?= $uri === $basePath.'/ayuda' ? 'active' : '' ?>">Ayuda</a>
      <a href="<?= $basePath ?>/carrito" 
   class="btn-cart <?= $path === '/carrito' ? 'active' : '' ?>">
   <span class="cart-icon" aria-hidden="true">🛒Carrito</span>
   <span class="cart-badge"<?= $cartCount ? '' : ' style="display:none;"' ?>>
      <?= $cartCount ?>
   </span>
</a>
    </div>

    <div class="nav-cta">
      <a href="<?= $basePath ?>/login" class="btn btn-outline">Iniciar sesión</a>
      <a href="<?= $basePath ?>/registro" class="btn btn-primary">Crear cuenta</a>
      <button class="hamb" aria-label="Menú">☰</button>
    </div>
  </nav>
</header>
