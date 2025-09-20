<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/* Base y path actual (para activar el botón en /carrito) */
$basePath = defined('BASE_PATH')
  ? rtrim(BASE_PATH, '/')
  : rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = '/' . ltrim(substr($uri, strlen($basePath)), '/');

/* Calcular $cartCount (suma de cantidades) */
$cartCount = 0;

try {
  // Resolver ID cliente si hay sesión
  $idCliente = null;
  if (!empty($_SESSION['cliente']['ID_CLIENTE'])) {
    $idCliente = (int)$_SESSION['cliente']['ID_CLIENTE'];
  } elseif (!empty($_SESSION['usuarios']['id_cliente'])) {
    $idCliente = (int)$_SESSION['usuarios']['id_cliente'];
  } elseif (!empty($_SESSION['ID_CLIENTE'])) {
    $idCliente = (int)$_SESSION['ID_CLIENTE'];
  }

  if ($idCliente) {
    // Cliente logueado: sumar cantidades en cotización WEB abierta
    require_once __DIR__ . '/../../config/conexion.php';
    $pdo = Conexion::getConexion();

    $sql = "SELECT COALESCE(SUM(d.CANTIDAD), 0) AS qty
              FROM cotizacion c
              JOIN det_cotizacion_producto d ON d.ID_COTIZACION = c.ID_COTIZACION
             WHERE c.ID_CLIENTE = :cli
               AND c.TIPO_COTIZACION = 'WEB'
               AND c.ESTADO IN ('BORRADOR','PENDIENTE')";
    // Si quieres contar líneas (registros) en vez de cantidades, usa:
    // $sql = "SELECT COUNT(*) FROM cotizacion c
    //         JOIN det_cotizacion_producto d ON d.ID_COTIZACION = c.ID_COTIZACION
    //        WHERE c.ID_CLIENTE = :cli
    //          AND c.TIPO_COTIZACION = 'WEB'
    //          AND c.ESTADO IN ('BORRADOR','PENDIENTE')";

    $st = $pdo->prepare($sql);
    $st->execute([':cli' => $idCliente]);
    $cartCount = (int)$st->fetchColumn();
  } else {
    // Invitado: sumar cantidades guardadas en $_SESSION['cart']
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $qty) {
        $cartCount += max(0, (int)$qty);
      }
      // Si prefieres contar líneas distintas:
      // $cartCount = count(array_filter($_SESSION['cart'], fn($q) => (int)$q > 0));
    }
  }
} catch (Throwable $e) {
  // Silencio en caso de error para no romper el header
  $cartCount = $cartCount ?? 0;
}
?>

<header class="header">
  <nav class="nav">
    <a class="brand" href="<?= $basePath ?>inicio" aria-label="Inicio">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm0 3.2a6.8 6.8 0 1 1-6.8 6.8A6.808 6.808 0 0 1 12 5.2Z"/></svg>
      <span>Global-import</span>
    </a>

    <div class="navlinks">
      <a href="<?= $basePath ?>inicio" class="<?= $uri === $basePath.'/' || $uri === $basePath.'inicio' ? 'active' : '' ?>">Inicio</a>
      <a href="<?= $basePath ?>catalogo" class="<?= $path === 'catalogo' ? 'active' : '' ?>">Catálogo</a>
      <a href="<?= $basePath ?>inicio#destacados">Destacados</a>
      <a href="<?= $basePath ?>inicio#nuevos">Nuevos</a>
      <a href="<?= $basePath ?>inicio#ofertas">Ofertas</a>
      <a href="<?= $basePath ?>ayuda" class="<?= $uri === $basePath.'ayuda' ? 'active' : '' ?>">Ayuda</a>
      <a href="<?= htmlspecialchars($basePath) ?>carrito"
   class="btn-cart <?= ($path === 'carrito') ? 'active' : '' ?>">
   <span class="cart-icon" aria-hidden="true">🛒Carrito</span>
   <span class="cart-badge"<?= $cartCount ? '' : ' style="display:none;"' ?>>
      <?= (int)$cartCount ?>
   </span>
</a>

    </div>

    <div class="nav-cta">
      <a href="<?= $basePath ?>login" class="btn btn-outline">Iniciar sesión</a>
      <a href="<?= $basePath ?>registro" class="btn btn-primary">Crear cuenta</a>
      <button class="hamb" aria-label="Menú">☰</button>
    </div>
  </nav>
</header>
