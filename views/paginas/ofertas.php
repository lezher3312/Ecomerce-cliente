<?php require __DIR__ . '/../layouts/head.php'; ?>
<link rel="stylesheet" href="<?= asset('css/catalogo.css') ?>">
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="catalogo">
  <section class="productos">
    <h2>Ofertas</h2>

    <div class="grid">
      <?php if (!empty($productos)): ?>
        <?php foreach ($productos as $p): ?>
          <article class="card card-prod">
            <a href="detalle?id=<?= $p['id_producto'] ?>">
              <img src="https://gtis.tech/<?= htmlspecialchars($p['imagen_principal']) ?>" 
                   alt="<?= htmlspecialchars($p['nombre']) ?>">
              <div class="body">
                <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                <p class="categoria"><?= htmlspecialchars($p['categoria']) ?></p>

                <?php if ($p['precio_oferta']): ?>
                  <!-- Oferta activa -->
                  <p class="price">
                    <span class="precio-tachado">US$ <?= number_format($p['precio'], 2) ?></span>
                    <span class="precio-oferta">US$ <?= number_format($p['precio_oferta'], 2) ?></span>
                  </p>
                  <p class="meta">Válido hasta <?= htmlspecialchars($p['FIN_OFERTA']) ?></p>
                <?php else: ?>
                  <!-- Oferta futura -->
                  <p class="price">
                    <span class="precio-actual">US$ <?= number_format($p['precio'], 2) ?></span>
                  </p>
                  <p class="meta">Disponible a partir de <?= htmlspecialchars($p['INICIO_OFERTA']) ?></p>
                <?php endif; ?>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No hay productos en oferta por el momento.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
