<?php require __DIR__ . '/../layouts/head.php'; ?>
<link rel="stylesheet" href="<?= asset('css/catalogo.css') ?>">
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="catalogo">
  <section class="productos">
    <h2>Novedades (últimos 5 días)</h2>
    <div class="grid">
      <?php if (!empty($productos)): ?>
        <?php foreach($productos as $p): ?>
          <article class="card card-prod">
            <a href="/detalle?id=<?= $p['id_producto'] ?>">
              <img src="/uploads/<?= htmlspecialchars($p['imagen_principal']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
              <div class="body">
                <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                <p class="price">US$ <?= number_format($p['precio'], 2) ?></p>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No hay productos nuevos en los últimos 5 días.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
