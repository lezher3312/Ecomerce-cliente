<?php
// views/paginas/carrito.php
// Variables esperadas desde el Controller (con fallbacks):
// $items, $count, $subtotal, $total, $basePath, $cartCount, $pageTitle, $cssExtra


$items    = $items    ?? [];
$count    = isset($count)    ? (int)$count    : 0;
$subtotal = isset($subtotal) ? (float)$subtotal : 0.0;
$total    = isset($total)    ? (float)$total    : $subtotal;
$basePath = rtrim($basePath ?? (dirname($_SERVER['SCRIPT_NAME']) ?: ''), '/');
$cssExtra = $cssExtra ?? ($basePath ? "$basePath/public/css/carrito.css" : "/public/css/carrito.css");
?>
<link rel="stylesheet" href="<?= htmlspecialchars($cssExtra) ?>">

<main class="carrito-wrap container">
  <h1 class="carrito-title">🛒 Mi carrito</h1>

  <?php if (!empty($_SESSION['flash_cart'])): ?>
    <div class="alert alert-success" role="status">
      <?= htmlspecialchars($_SESSION['flash_cart']) ?>
    </div>
    <?php unset($_SESSION['flash_cart']); ?>
  <?php endif; ?>

  <?php if (empty($items)): ?>
    <div class="carrito-empty alert-vacio" role="alert" aria-live="polite">
      <h2>Tu carrito está vacío</h2>
      <p>Parece que aún no agregaste productos.</p>
      <div class="actions">
        <a class="btn btn-primary" href="<?= htmlspecialchars($basePath) ?>/catalogo">Ir al catálogo</a>
        <a class="btn btn-outline" href="<?= htmlspecialchars($basePath) ?>/login">Iniciar sesión</a>
      </div>
    </div>
  <?php else: ?>
    <div class="carrito-grid">
      <section class="carrito-lista" aria-label="Lista de productos en el carrito">
        <table class="carrito-tabla">
          <thead>
            <tr>
              <th scope="col">Producto</th>
              <th scope="col" class="th-center">Cantidad</th>
              <th scope="col" class="th-right">Precio</th>
              <th scope="col" class="th-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $it):
              $id       = (int)($it['id'] ?? 0);
              $nombre   = (string)($it['nombre'] ?? 'Producto');
              $imagen   = $it['imagen'] ?? '';
              $qty      = max(1, (int)($it['cantidad'] ?? 1));
              $price    = (float)($it['precio'] ?? 0);
              $rowTotal = $qty * $price;
          ?>
            <tr>
              <td class="td-producto">
                <?php if (!empty($imagen)): ?>
                  <img src="<?= htmlspecialchars($imagen) ?>"
                       alt="<?= htmlspecialchars($nombre) ?>"
                       class="prod-img">
                <?php endif; ?>
                <div class="prod-info">
                  <div class="prod-nombre"><?= htmlspecialchars($nombre) ?></div>
                  <div class="prod-id">#<?= $id ?></div>
                </div>
              </td>
              <td class="th-center"><?= $qty ?></td>
              <td class="th-right">Q <?= number_format($price, 2) ?></td>
              <td class="th-right">Q <?= number_format($rowTotal, 2) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <aside class="carrito-resumen" aria-label="Resumen del pedido">
        <div class="resumen-card">
          <h3>Resumen</h3>
          <div class="row">
            <span>Artículos</span>
            <strong><?= $count ?></strong>
          </div>
          <div class="row">
            <span>Subtotal</span>
            <strong>Q <?= number_format($subtotal, 2) ?></strong>
          </div>
          <hr>
          <div class="row total">
            <span>Total</span>
            <strong>Q <?= number_format($total, 2) ?></strong>
          </div>
          <a href="<?= htmlspecialchars($basePath) ?>/checkout" class="btn btn-primary w-100">Proceder al pago</a>
          <a href="<?= htmlspecialchars($basePath) ?>/catalogo" class="btn btn-link w-100">Seguir comprando</a>
        </div>
      </aside>
    </div>
  <?php endif; ?>
</main>
