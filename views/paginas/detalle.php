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
           src="https://gtis.tech/<?= htmlspecialchars($producto['imagen_principal']) ?>" 
           alt="<?= htmlspecialchars($producto['nombre']) ?>">

      <?php if (!empty($producto['imagenes_extra'])): ?>
        <div class="miniaturas">
          <?php foreach ($producto['imagenes_extra'] as $img): ?>
            <img src="https://gtis.tech/<?= htmlspecialchars($img) ?>" 
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

      <!-- ================== BOTÓN CARRITO ================== -->
      <form method="post" 
      action="<?= htmlspecialchars(rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')) ?>/carrito/agregar">
        <input type="hidden" name="id_producto" value="<?= $producto['id_producto'] ?>">
        <button class="btn btn-primary" <?= $producto['existencia'] <= 0 ? 'disabled' : '' ?>>
            Agregar al carrito
        </button>
      </form>

      <!-- ================== COMPARTIR ================== -->
      <div class="compartir">
        <span>Compartir:</span>

        <!-- Facebook -->
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
           target="_blank" rel="noopener" title="Compartir en Facebook">
          <i class="fab fa-facebook"></i>
        </a>

        <!-- Twitter/X -->
        <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($producto['nombre']) ?>" 
           target="_blank" rel="noopener" title="Compartir en Twitter">
          <i class="fab fa-twitter"></i>
        </a>

        <!-- WhatsApp -->
        <a href="https://api.whatsapp.com/send?text=<?= urlencode($producto['nombre'] . ' ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
           target="_blank" rel="noopener" title="Compartir en WhatsApp">
          <i class="fab fa-whatsapp"></i>
        </a>

        <!-- Copiar enlace -->
        <button type="button" class="btn-copy" onclick="copiarEnlace()">
          <i class="fas fa-link"></i>
        </button>
      </div>

    </div>
  </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<!-- ================== SCRIPT COPIAR ENLACE ================== -->
<script>
function copiarEnlace() {
  const url = window.location.href;
  navigator.clipboard.writeText(url).then(() => {
    alert("¡Enlace copiado al portapapeles!");
  }).catch(err => {
    console.error("Error al copiar: ", err);
  });
}
</script>
