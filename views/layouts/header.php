<?php
require_once __DIR__ . '/../../config/funciones.php';

if (session_status() === PHP_SESSION_NONE) session_start();

/* Path actual */
$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = '/' . ltrim(substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME']))), '/');

/* Calcular carrito */
$cartCount = 0;
try {
  $idCliente = null;
  if (!empty($_SESSION['cliente']['ID_CLIENTE'])) {
    $idCliente = (int)$_SESSION['cliente']['ID_CLIENTE'];
  } elseif (!empty($_SESSION['usuarios']['id_cliente'])) {
    $idCliente = (int)$_SESSION['usuarios']['id_cliente'];
  } elseif (!empty($_SESSION['ID_CLIENTE'])) {
    $idCliente = (int)$_SESSION['ID_CLIENTE'];
  }

  if ($idCliente) {
    require_once __DIR__ . '/../../config/conexion.php';
    $pdo = Conexion::getConexion();

    $sql = "SELECT COALESCE(SUM(d.CANTIDAD), 0) AS qty
              FROM cotizacion c
              JOIN det_cotizacion_producto d ON d.ID_COTIZACION = c.ID_COTIZACION
             WHERE c.ID_CLIENTE = :cli
               AND c.TIPO_COTIZACION = 'WEB'
               AND c.ESTADO IN ('BORRADOR','PENDIENTE')";

    $st = $pdo->prepare($sql);
    $st->execute([':cli' => $idCliente]);
    $cartCount = (int)$st->fetchColumn();
  } else {
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $qty) {
        $cartCount += max(0, (int)$qty);
      }
    }
  }
} catch (Throwable $e) {
  $cartCount = $cartCount ?? 0;
}
?>

<header class="header">
  <nav class="nav d-flex align-items-center justify-content-between">
    <!-- Logo -->
    <a class="brand d-flex align-items-center gap-2" href="<?= url('inicio') ?>" aria-label="Inicio">
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm0 3.2a6.8 6.8 0 1 1-6.8 6.8A6.808 6.808 0 0 1 12 5.2Z"/>
      </svg>
      <span>Global-import</span>
    </a>

    <!-- Links visibles en PC -->
    <div class="navlinks d-none d-md-flex">
      <a href="<?= url('inicio') ?>" class="<?= $path === '/inicio' || $path === '/' ? 'active' : '' ?>">Inicio</a>
      <a href="<?= url('catalogo') ?>" class="<?= $path === '/catalogo' ? 'active' : '' ?>">Catálogo</a>
      <a href="<?= url('inicio#destacados') ?>">Destacados</a>
      <a href="<?= url('inicio#nuevos') ?>">Nuevos</a>
      <a href="<?= url('inicio#ofertas') ?>">Ofertas</a>
      <a href="<?= url('ayuda') ?>" class="<?= $path === '/ayuda' ? 'active' : '' ?>">Ayuda</a>
      <?php if (isauth()): ?>
        <!-- ✨ Solo mostrar si hay sesión -->
        <a href="<?= url('cotizacion') ?>" class="<?= $path === '/cotizacion' ? 'active' : '' ?>">cotizaciones</a>
      <?php endif; ?>
      <a href="<?= url('carrito') ?>" class="btn-cart <?= $path === '/carrito' ? 'active' : '' ?>">
        🛒 Mi Carrito
        <span class="cart-badge"<?= $cartCount ? '' : ' style="display:none;"' ?>>
          <?= (int)$cartCount ?>
        </span>
      </a>
      
    </div>

    <!-- Botones de sesión -->
    <div class="nav-cta d-flex align-items-center gap-2">
      <?php if(isauth()): ?>
      <button class="btn btn-outline" id="menu" data-nombre="<?php echo $_SESSION['NOMBRE'];?>" data-email="<?php echo $_SESSION['EMAIL']; ?>"><?php echo $_SESSION['USUARIO'] ?? '';?></button>
        <form method="POST" action="<?= url('logout') ?>">
          <input type="submit" value="Cerrar Sesión" class="btn btn-primary">
        </form>
      <?php else: ?>
        <a href="<?= url('login') ?>" class="btn btn-outline">Iniciar sesión</a>
        <a href="<?= url('registro') ?>" class="btn btn-primary">Crear cuenta</a>
      <?php endif; ?>

      <!-- Botón hamburguesa solo visible en móvil -->
      <button class="btn btn-outline d-md-none" type="button" 
              data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" 
              aria-controls="offcanvasMenu">
        ☰
      </button>
    </div>
  </nav>
</header>

<!-- =========================
     MENÚ LATERAL SOLO EN MÓVIL
     ========================= -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menú</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link <?= $path === '/inicio' || $path === '/' ? 'active' : '' ?>" href="<?= url('inicio') ?>">Inicio</a></li>
      <li class="nav-item"><a class="nav-link <?= $path === '/catalogo' ? 'active' : '' ?>" href="<?= url('catalogo') ?>">Catálogo</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= url('inicio#destacados') ?>">Destacados</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= url('inicio#nuevos') ?>">Nuevos</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= url('inicio#ofertas') ?>">Ofertas</a></li>
      <li class="nav-item"><a class="nav-link <?= $path === '/ayuda' ? 'active' : '' ?>" href="<?= url('ayuda') ?>">Ayuda</a></li>
      <li class="nav-item"><a class="nav-link <?= $path === '/carrito' ? 'active' : '' ?>" href="<?= url('carrito') ?>">🛒 Carrito (<?= (int)$cartCount ?>)</a></li>
    </ul>
  </div>
</div>
