
<?php require __DIR__ . '/../layouts/head.php'; ?>
<link rel="stylesheet" href="<?= asset('css/catalogo.css') ?>">
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

    <label>Ordenar por</label>
    <select name="orden">
      <option value="recientes" <?= ($_GET['orden'] ?? '') === 'recientes' ? 'selected' : '' ?>>Más recientes</option>
      <option value="precio_asc" <?= ($_GET['orden'] ?? '') === 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
      <option value="precio_desc" <?= ($_GET['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
    </select>

    <!-- Precio toggle -->
    <div class="precio-toggle">
      <button type="button" id="btn-precio">¿Colocar precios?</button>
      <div class="precio-inputs hidden">
        <label>Precio mínimo</label>
        <input type="number" name="min" value="<?= htmlspecialchars($_GET['min'] ?? '') ?>">

        <label>Precio máximo</label>
        <input type="number" name="max" value="<?= htmlspecialchars($_GET['max'] ?? '') ?>">
      </div>
    </div>

    <button type="submit">Aplicar</button>
  </form>
</aside>


  <!-- Grid de productos -->
  <section class="productos">
    <h2>Catálogo</h2>
    <div class="grid">
      <?php foreach($productos ?? [] as $p): ?>
        <article class="card card-prod">
          <a href="detalle?id=<?= $p['id_producto'] ?>">
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
<script src="<?= asset('js/catalogo.js') ?>"></script>