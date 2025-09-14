<?php require __DIR__ . '/../layouts/head.php'; ?>
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="catalogo">
  <!-- Sidebar de filtros -->
  <aside class="filtros">
    <form method="get">
      <h3>Filtros</h3>

      <label>Categoría</label>
      <select name="categoria">
        <option value="">Todas</option>
        <?php foreach($categorias ?? [] as $c): ?>
          <option value="<?= $c['id_categoria'] ?>"
            <?= ($_GET['categoria'] ?? '') == $c['id_categoria'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Precio mínimo</label>
      <input type="number" name="min" value="<?= htmlspecialchars($_GET['min'] ?? '') ?>">

      <label>Precio máximo</label>
      <input type="number" name="max" value="<?= htmlspecialchars($_GET['max'] ?? '') ?>">

      <label>Ordenar por</label>
      <select name="orden">
        <option value="recientes">Más recientes</option>
        <option value="precio_asc">Precio: menor a mayor</option>
        <option value="precio_desc">Precio: mayor a menor</option>
      </select>

      <button type="submit">Aplicar</button>
    </form>
  </aside>

  <!-- Grid de productos -->
  <section class="productos">
    <h2>Catálogo</h2>
    <div class="grid">
      <?php foreach($productos ?? [] as $p): ?>
        <article class="card card-prod">
          <a href="detalle.php?id=<?= $p['id_producto'] ?>">
            <img src="<?= htmlspecialchars($p['imagen_principal']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            <div class="body">
              <h3><?= htmlspecialchars($p['nombre']) ?></h3>
              <p class="price">US$ <?= number_format($p['precio'], 2) ?></p>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
