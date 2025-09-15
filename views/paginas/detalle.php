<?php require __DIR__ . '/../layouts/head.php'; ?>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="detalle">
  <?php if ($producto): ?>
    <div class="galeria">
      <img src="<?= htmlspecialchars($producto['imagen_principal']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>">
      <div class="thumbs">
        <?php foreach ($producto['imagenes'] ?? [] as $img): ?>
          <img src="<?= htmlspecialchars($img) ?>" alt="">
        <?php endforeach; ?>
      </div>
    </div>

    <div class="info">
      <h1><?= htmlspecialchars($producto['nombre']) ?></h1>
      <p class="precio">US$ <?= number_format($producto['precio'], 2) ?></p>
      <p><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
      <p><strong>Entrega estimada:</strong> <?= htmlspecialchars($producto['entrega_estimada'] ?? "3–5 días") ?></p>
      <button class="btn-agregar">Agregar al carrito</button>
    </div>
  <?php else: ?>
    <p>Producto no encontrado.</p>
  <?php endif; ?>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
