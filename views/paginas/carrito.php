<?php
// views/paginas/carrito.php
// Espera: $items, $count, $subtotal, $total, $basePath, $cartCount, $pageTitle, $cssExtra

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

  <?php if (isset($_SESSION['flash_cart'])): ?>
    <div class="alert alert-info" role="alert">
      <?= htmlspecialchars($_SESSION['flash_cart']); unset($_SESSION['flash_cart']); ?>
    </div>
  <?php endif; ?>

  <?php if (empty($items)): ?>
    <div class="carrito-empty alert-vacio" role="alert" aria-live="polite">
      <h2>Tu carrito está vacío</h2>
      <p>Parece que aún no agregaste productos.</p>
      <div class="actions" style="display:flex; gap:8px; flex-wrap:wrap;">
        <a class="btn btn-primary" href="<?= htmlspecialchars($basePath) ?>/catalogo">Ir al catálogo</a>
        <a class="btn btn-outline" href="<?= htmlspecialchars($basePath) ?>/ofertas">Ver ofertas</a>
        <a class="btn btn-outline" href="<?= htmlspecialchars($basePath) ?>/login">Iniciar sesión</a>
      </div>
    </div>
  <?php else: ?>
    <div class="carrito-grid" style="display:grid; grid-template-columns: 1fr 320px; gap:16px;">
      <section class="carrito-lista" aria-label="Lista de productos en el carrito">
        <table class="carrito-tabla" style="width:100%; background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
          <thead style="background:#f8fafc;">
            <tr>
              <th scope="col" style="padding:12px; text-align:left;">Producto</th>
              <th scope="col" style="padding:12px; text-align:center;">Cantidad</th>
              <th scope="col" style="padding:12px; text-align:right;">Precio</th>
              <th scope="col" style="padding:12px; text-align:right;">Subtotal</th>
              <th scope="col" style="padding:12px; text-align:center;">Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $it):
              $id          = (int)($it['id'] ?? 0);
              $nombre      = (string)($it['nombre'] ?? 'Producto');
              $desc        = (string)($it['descripcion'] ?? '');
              $imagen      = $it['imagen'] ?? '';
              $qty         = max(1, (int)($it['cantidad'] ?? 1));
              $price       = (float)($it['precio'] ?? 0);
              $rowTotal    = $qty * $price;
          ?>
            <tr>
              <td style="padding:12px;">
                <div style="display:flex; gap:12px; align-items:flex-start;">
                  <?php if (!empty($imagen)): ?>
                    <img src="<?= htmlspecialchars($imagen) ?>" alt="<?= htmlspecialchars($nombre) ?>"
                         style="width:72px; height:72px; object-fit:cover; border-radius:8px; border:1px solid #e5e7eb;">
                  <?php endif; ?>
                  <div>
                    <div style="font-weight:600;"><?= htmlspecialchars($nombre) ?></div>
                    <?php if ($desc): ?>
                      <div style="color:#475569; font-size:0.9rem;"><?= htmlspecialchars($desc) ?></div>
                    <?php endif; ?>
                    <div style="color:#94a3b8; font-size:0.8rem;">#<?= $id ?></div>
                  </div>
                </div>
              </td>

              <td style="padding:12px; text-align:center; vertical-align:middle;">
  <form action="<?= htmlspecialchars($basePath) ?>/carrito/actualizar" method="post" style="display:flex; gap:6px; justify-content:center; align-items:center;">
    <input type="hidden" name="id_producto" value="<?= (int)$id ?>">
    <label for="qty-<?= (int)$id ?>" class="visually-hidden">Cantidad</label>
    <input id="qty-<?= (int)$id ?>" type="number" name="cantidad" min="1" value="<?= (int)$qty ?>" style="width:76px; text-align:center;">
    <button type="submit" class="btn btn-sm btn-outline">🔄 Actualizar</button>
  </form>
</td>

<td style="padding:12px; text-align:right; vertical-align:middle;">Q <?= number_format($price, 2) ?></td>
<td style="padding:12px; text-align:right; vertical-align:middle;">Q <?= number_format($rowTotal, 2) ?></td>

<td style="padding:12px; text-align:center; vertical-align:middle;">
  <form action="<?= htmlspecialchars($basePath) ?>/carrito/eliminar" method="post" onsubmit="return confirm('¿Eliminar este producto del carrito?');" style="display:inline;">
    <input type="hidden" name="id_producto" value="<?= (int)$id ?>">
    <button type="submit" class="btn btn-sm btn-danger">🗑️ Eliminar</button>
  </form>
</td>

            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </section>

      <aside class="carrito-resumen" aria-label="Resumen del pedido">
        <div class="resumen-card" style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:16px;">
          <h3 style="margin:0 0 12px 0;">Resumen</h3>

          <div class="row" style="display:flex; justify-content:space-between; margin-bottom:6px;">
            <span>Artículos (suma de cantidades)</span>
            <strong><?= $count ?></strong>
          </div>
          <div class="row" style="display:flex; justify-content:space-between;">
            <span>Subtotal</span>
            <strong>Q <?= number_format($subtotal, 2) ?></strong>
          </div>
          <hr>
          <div class="row total" style="display:flex; justify-content:space-between; font-size:1.1rem;">
            <span>Total</span>
            <strong>Q <?= number_format($total, 2) ?></strong>
          </div>

          <a href="<?= htmlspecialchars($basePath) ?>/registro/pedido"
   class="btn btn-primary w-100" style="margin-top:12px;">
  Realizar pedido
</a>

          <a href="<?= htmlspecialchars($basePath) ?>/catalogo" class="btn btn-link w-100">Agregar más productos</a>
          <a href="<?= htmlspecialchars($basePath) ?>/ofertas" class="btn btn-link w-100">Ver ofertas</a>
        </div>
      </aside>
    </div>
  <?php endif; ?>
</main>
