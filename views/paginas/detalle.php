<?php 
  $extraCss = 'detalle.css'; 
  require __DIR__ . '/../layouts/head.php'; 
  require __DIR__ . '/../layouts/header.php'; 
?>

<main class="detalle-producto">
  <div class="detalle-wrap">
    
    <!-- ================== FOTOS ================== -->
    <div class="detalle-fotos">
      <img class="foto-principal" 
           src="/uploads/<?= htmlspecialchars($producto['imagen_principal']) ?>" 
           alt="<?= htmlspecialchars($producto['nombre']) ?>">

      <?php if (!empty($producto['imagenes_extra'])): ?>
        <div class="miniaturas">
          <?php foreach ($producto['imagenes_extra'] as $img): ?>
            <img src="/uploads/<?= htmlspecialchars($img) ?>" 
                 alt="Foto extra de <?= htmlspecialchars($producto['nombre']) ?>">
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ================== INFO ================== -->
    <div class="detalle-info">
      <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
      <span class="categoria"><?= htmlspecialchars($producto['categoria']) ?></span>

      <p class="descripcion"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>

      <div class="precios">
        <?php if (!empty($producto['precio_oferta'])): ?>
          <span class="precio-actual precio-tachado">
            US$ <?= number_format($producto['precio'], 2) ?>
          </span>
          <span class="precio-oferta">
            US$ <?= number_format($producto['precio_oferta'], 2) ?>
          </span>
        <?php else: ?>
          <span class="precio-actual">
            US$ <?= number_format($producto['precio'], 2) ?>
          </span>
        <?php endif; ?>
      </div>

      <div class="meta">
        <span>Entrega estimada: 10–15 días</span><br>
        <span>Disponibilidad: <?= $producto['existencia'] > 0 ? 'En stock' : 'Agotado' ?></span>
      </div>

      <form method="post" action="/carrito/agregar">
        <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
       <button class="btn btn-primary" <?= $producto['existencia'] <= 0 ? 'disabled' : '' ?>>
            Agregar al carrito
        </button>
      </form>
    </div>
  </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
