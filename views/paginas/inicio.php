<?php $extraCss = 'inicio.css'; ?>
<?php require __DIR__ . '/../layouts/head.php'; ?>
<?php require __DIR__ . '/../layouts/header.php'; ?>


<section class="hero">
  <!-- 🎬 Doble video para rotación -->
  <video id="video1" autoplay muted playsinline class="hero-bg"></video>
  <video id="video2" autoplay muted playsinline class="hero-bg hidden"></video>

  <!-- Imagen fallback opcional -->
  <div class="hero-bg-image"></div>
  <div class="overlay"></div>

  <div class="container hero-content">
    <h1>Busca y sigue tus productos importados</h1>
    <p>Explora por categoría, nombre o código; revisa el estado de tus pedidos en tiempo real.</p>

    <!-- 🔎 Buscador -->
    <div class="search" role="search">
      <input type="search" placeholder="Buscar productos, marcas o códigos…" aria-label="Buscar">
      <button>Buscar</button>
    </div>

    <!-- 🔹 Filtros rápidos -->
    <div class="quick-filters mt-3">
      <span class="chip">Entrega rápida</span>
      <span class="chip">En oferta</span>
      <span class="chip">Hazmat</span>
      <span class="chip">Con garantía</span>
    </div>

    <!-- 🔹 Carrusel de categorías -->
    <div class="hero-categories-carousel mt-4">
      <h3>Explora por categoría</h3>
      <div class="cat-carousel">
        <?php foreach($categorias ?? [] as $c): 
          $icono = 'fas fa-tags';
          switch(strtolower($c['nombre'])) {
            case 'tecnologia': $icono = 'fas fa-laptop'; break;
            case 'hogar': $icono = 'fas fa-couch'; break;
            case 'moda': $icono = 'fas fa-tshirt'; break;
            case 'herramientas': $icono = 'fas fa-tools'; break;
          } ?>
          <a href="catalogo?cat=<?= $c['id_categoria'] ?>" class="cat-slide">
            <i class="<?= $icono ?>"></i>
            <span><?= htmlspecialchars($c['nombre']) ?></span>
          </a>
        <?php endforeach; ?>
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
    <a class="pill" href="mas-vendidos">Ver todo →</a>
  </div>
  <div class="carousel">
    <?php if (!empty($masVendidos)): ?>
      <?php foreach(array_slice($masVendidos, 0, 3) as $p): ?>
        <article class="card card-prod">
          <a href="detalle?id=<?= $p['id_producto'] ?>">
            <img src="https://gtis.tech/<?= htmlspecialchars($p['imagen_principal']) ?>" 
                 alt="<?= htmlspecialchars($p['nombre']) ?>">
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
    <a class="pill" href="novedades">Ver catálogo →</a>
  </div>
  <div class="carousel">
    <?php if (!empty($novedades)): ?>
      <?php foreach(array_slice($novedades, 0, 3) as $p): ?>
        <article class="card card-prod">
          <a href="detalle?id=<?= $p['id_producto'] ?>">
            <img src="https://gtis.tech/<?= htmlspecialchars($p['imagen_principal']) ?>" 
                 alt="<?= htmlspecialchars($p['nombre']) ?>">
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
    <a class="pill" href="ofertas">Ver más →</a>
  </div>
  <div class="carousel">
    <?php if (!empty($ofertas)): ?>
      <?php foreach(array_slice($ofertas, 0, 3) as $p): ?>
        <article class="card card-prod">
          <a href="detalle?id=<?= $p['id_producto'] ?>">
            <img src="https://gtis.tech/<?= htmlspecialchars($p['imagen_principal']) ?>" 
                 alt="<?= htmlspecialchars($p['nombre']) ?>">
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
<script>
document.addEventListener("DOMContentLoaded", () => {
  const video1 = document.getElementById("video1");
  const video2 = document.getElementById("video2");

  // Lista de videos
  const videos = [
    "<?= asset('videos/fondo1.mp4') ?>",
    "<?= asset('videos/fondo2.mp4') ?>"
  ];

  let current = 0; // índice actual
  let next = 1;    // índice siguiente
  let active = video1;
  let hidden = video2;

  // Carga inicial
  active.src = videos[current];
  hidden.src = videos[next];
  hidden.classList.add("hidden");

  // Intentar reproducir
  tryPlay(active);

  // Escucha cuando termina el video activo
  active.addEventListener("ended", switchVideo);
  hidden.addEventListener("ended", switchVideo);

  function switchVideo() {
    // Intercambia visibilidad
    active.classList.add("hidden");
    hidden.classList.remove("hidden");

    // Intenta reproducir el nuevo visible
    tryPlay(hidden);

    // Avanza los índices correctamente
    current = (current + 1) % videos.length;
    next = (current + 1) % videos.length;

    // Reasigna las referencias (efecto "anillo")
    const temp = active;
    active = hidden;
    hidden = temp;

    // Prepara el siguiente video mientras se reproduce el actual
    hidden.src = videos[next];
  }

  // Si el navegador bloquea video, mostrar imagen
  function tryPlay(video) {
    const playPromise = video.play();
    if (playPromise !== undefined) {
      playPromise.catch(() => {
        console.warn("⚠️ Video bloqueado, usando imagen de fondo");
        document.querySelector(".hero-bg-image").style.display = "block";
        video.style.display = "none";
      });
    }
  }
});
</script>

