<?php
// views/paginas/cotizacion.php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';
?>
<link rel="stylesheet" href="<?= asset('../public/css/cotizacion.css') ?>">

<main class="cotz-container">
  <h1>Cotización</h1>

  <?php
    // estados: 0=inactivo, 1=activo, 2=falta impuesto, 3=procesado, 4=confirmado, 5=anulado
    $labels = [0=>'Inactivo',1=>'Activo',2=>'Falta de impuesto',3=>'Procesado',4=>'Confirmado',5=>'Anulado'];
    $pasos  = [1,2,3,4,5];
  ?>
  <div class="stepper">
    <?php foreach ($pasos as $p): ?>
      <div class="step <?= ($estado >= $p) ? 'done' : (($estado+1)===$p ? 'current' : '') ?>">
        <div class="dot"></div>
        <div class="lbl"><?= htmlspecialchars($labels[$p]) ?></div>
        <?php if ($p !== 5): ?><div class="bar"></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <form method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/tipo" class="tipo-form">
    <label>Tipo de cotización:</label>
    <label><input type="radio" name="tipo_cotizacion" value="1" <?= ($tipo===1?'checked':'') ?>> Compra + transporte</label>
    <label><input type="radio" name="tipo_cotizacion" value="2" <?= ($tipo===2?'checked':'') ?>> Solo transporte (HAZMAT + PESO)</label>
    <button class="btn btn-outline" type="submit">Actualizar</button>
  </form>

  <?php if (empty($items)): ?>
    <div class="alert">Tu cotización está vacía.</div>
    <div class="actions">
      <a class="btn" href="<?= htmlspecialchars($basePath) ?>/catalogo">Ir al catálogo</a>
    </div>
  <?php else: ?>
    <div class="grid">
      <article class="card">
        <h2>Productos</h2>
        <table class="tabla">
          <thead>
            <tr>
              <th>Producto</th>
              <th class="num">Precio unit.</th>
              <th class="num">Oferta</th>
              <th class="num">Cant.</th>
              <th class="num">Cargo adicional (u)</th>
              <th class="num">Cargo total</th>
              <th class="num">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td>
                  <div class="prod">
                    <?php if (!empty($it['imagen'])): ?>
                      <img src="<?= htmlspecialchars($it['imagen']) ?>" alt="" />
                    <?php endif; ?>
                    <div>
                      <div class="name"><?= htmlspecialchars($it['nombre']) ?></div>
                      <div class="desc"><?= htmlspecialchars($it['descripcion']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="num">Q <?= number_format((float)($it['precio_unit'] ?? $it['precio'] ?? 0), 2) ?></td>
                <td class="num">
                  <?php if (!empty($it['oferta_activa'])): ?>
                    −<?= number_format((float)$it['oferta_porc'], 0) ?>%
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td class="num"><?= (int)$it['cantidad'] ?></td>
                <td class="num">Q <?= number_format((float)$it['cargo_unit'], 2) ?></td>
                <td class="num">Q <?= number_format((float)($it['cargo_total'] ?? ((float)$it['cargo_unit'] * (int)$it['cantidad'])), 2) ?></td>
                <td class="num"><strong>Q <?= number_format((float)$it['subtotal'], 2) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="6" class="num">Subtotal</td>
              <td class="num"><strong>Q <?= number_format((float)$totalBase, 2) ?></strong></td>
            </tr>
            <?php if ($estado >= 3): ?>
              <tr>
                <td colspan="6" class="num">Impuestos / otros cargos</td>
                <td class="num"><strong>Q <?= number_format((float)$impuestosYOtros, 2) ?></strong></td>
              </tr>
              <tr>
                <td colspan="6" class="num">Total con impuestos</td>
                <td class="num"><strong>Q <?= number_format(max((float)$totalBase + (float)$impuestosYOtros, (float)$totalConImpuestos), 2) ?></strong></td>
              </tr>
            <?php endif; ?>
          </tfoot>
        </table>
      </article>

      <aside class="card">
        <h2>Acciones</h2>

        <?php if ($estado === 1): ?>
          <form method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/pedir" class="stack">
            <button class="btn btn-primary" type="submit">Pedir cotización</button>
          </form>
        <?php elseif ($estado === 2): ?>
          <div class="muted">Estado: Falta de impuesto. Estamos procesando tu solicitud.</div>
        <?php elseif ($estado === 3): ?>
          <div class="muted">Estado: Procesado. Ya puedes confirmar.</div>
          <form method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/confirmar" class="stack">
            <button class="btn btn-primary" type="submit">Confirmar y continuar a envío</button>
          </form>
        <?php endif; ?>

        <?php if (in_array($estado, [1,2,3], true)): ?>
          <form method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/anular" class="stack">
            <button class="btn btn-outline" type="submit">Anular cotización</button>
          </form>
        <?php elseif ($estado === 4): ?>
          <div class="muted">Confirmada. Redireccionando a envío…</div>
        <?php elseif ($estado === 5): ?>
          <div class="muted">Cotización anulada.</div>
          <a class="btn" href="<?= htmlspecialchars($basePath) ?>/carrito">Volver al carrito</a>
        <?php endif; ?>
      </aside>
    </div>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
