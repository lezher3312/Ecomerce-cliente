<?php require __DIR__ . '/../layouts/head.php'; ?>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- ================= HERO ================= -->
<section class="hero">
  <div class="hero-wrap">
    <div>
      <h1>Encuentra y sigue tus productos importados</h1>
      <p>Busca por categoría, nombre o código; explora tendencias y revisa el estado de tus pedidos en tiempo real.</p>

      <div class="search" role="search">
        <select aria-label="Categoría">
          <option value="">Todos</option>
          <?php foreach($categorias ?? [] as $c): ?>
            <option value="<?= htmlspecialchars($c['id_categoria']) ?>">
              <?= htmlspecialchars($c['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <input type="search" placeholder="Buscar productos, marcas o códigos…" aria-label="Buscar">
        <button>Buscar</button>
      </div>

      <div class="quick-filters" aria-label="Filtros rápidos">
        <span class="chip">Entrega rápida</span>
        <span class="chip">En oferta</span>
        <span class="chip">Hazmat</span>
        <span class="chip">Con garantía</span>
      </div>
    </div>

    <!-- Ilustración demo -->
    <div class="illus" aria-hidden="true">
      <div class="mock">
        <div class="bar">
          <span class="dot"></span>
          <span class="dot" style="background:#ffd166"></span>
          <span class="dot" style="background:#06d6a0"></span>
        </div>
        <div class="content">
          <div class="skeleton" style="height:90px;border-radius:10px"></div>
          <div class="skeleton" style="height:90px;border-radius:10px"></div>
          <div class="skeleton" style="height:90px;border-radius:10px"></div>
          <div class="skeleton" style="height:90px;border-radius:10px"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= MÁS VENDIDOS ================= -->
<section id="destacados" class="section">
  <div class="section-head">
    <div>
      <h2>Más vendidos</h2>
      <p class="lead">Explora lo que otros compradores están pidiendo esta semana.</p>
    </div>
    <a class="pill" href="<?= $basePath ?>/mas-vendidos">Ver todo →</a>
  </div>
  <div class="carousel">
    <?php if (!empty($masVendidos)): ?>
      <?php foreach(array_slice($masVendidos, 0, 3) as $p): ?>
        <article class="card card-prod">
          <a href="<?= $basePath ?>/detalle?id=<?= $p['id_producto'] ?>">
            <img src="/uploads/<?= htmlspecialchars($p['imagen_principal']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            <div class="body">
              <span class="badge"><?= htmlspecialchars($p['categoria'] ?? '') ?></span>
              <h3><?= htmlspecialchars($p['nombre']) ?></h3>
              <div class="meta">Entrega aprox. 10–15 días</div>
              <div class="price">US$ <?= number_format($p['precio'] ?? 0, 2) ?></div>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay productos más vendidos en este momento.</p>
    <?php endif; ?>
  </div>
</section>

<!-- ================= NOVEDADES ================= -->
<section id="nuevos" class="section" style="background:var(--soft)">
  <div class="section-head">
    <div>
      <h2>Novedades</h2>
      <p class="lead">Agregados recientemente al catálogo.</p>
    </div>
    <a class="pill" href="<?= $basePath ?>/novedades">Ver catálogo →</a>
  </div>
  <div class="carousel">
    <?php if (!empty($novedades)): ?>
      <?php foreach(array_slice($novedades, 0, 3) as $p): ?>
        <article class="card card-prod">
          <a href="<?= $basePath ?>/detalle?id=<?= $p['id_producto'] ?>">
            <img src="/uploads/<?= htmlspecialchars($p['imagen_principal']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            <div class="body">
              <span class="badge"><?= htmlspecialchars($p['categoria'] ?? '') ?></span>
              <h3><?= htmlspecialchars($p['nombre']) ?></h3>
              <div class="meta">Nuevo en catálogo</div>
              <div class="price">US$ <?= number_format($p['precio'] ?? 0, 2) ?></div>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay productos nuevos en los últimos días.</p>
    <?php endif; ?>
  </div>
</section>

<!-- ================= OFERTAS ================= -->
<section id="ofertas" class="section">
  <div class="section-head">
    <div>
      <h2>Ofertas</h2>
      <p class="lead">Descuentos por tiempo limitado.</p>
    </div>
    <a class="pill" href="<?= $basePath ?>/ofertas">Ver más →</a>
  </div>
  <div class="carousel">
    <?php if (!empty($ofertas)): ?>
      <?php foreach(array_slice($ofertas, 0, 3) as $p): ?>
        <article class="card card-prod">
          <a href="<?= $basePath ?>/detalle?id=<?= $p['id_producto'] ?>">
            <img src="/uploads/<?= htmlspecialchars($p['imagen_principal']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            <div class="body">
              <span class="badge"><?= htmlspecialchars($p['categoria'] ?? '') ?></span>
              <h3><?= htmlspecialchars($p['nombre']) ?></h3>
              <div class="meta">En oferta</div>
              <div class="price">US$ <?= number_format($p['precio'] ?? 0, 2) ?></div>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No hay productos en oferta por el momento.</p>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
