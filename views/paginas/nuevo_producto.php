<?php
// views/paginas/nuevo_producto.php
if (session_status() === PHP_SESSION_NONE) session_start();

/*
Esperados:
$categorias  = [ ['id'=>..,'nombre'=>..], ... ]
$proveedores = [ ['id'=>..,'nombre'=>..], ... ]
$basePath, $tipo (1/2)
*/

$embed = !empty($_GET['embed']);
if (!$embed) {
  require __DIR__ . '/../layouts/head.php';
  require __DIR__ . '/../layouts/header.php';
}

$basePath = $basePath ?? (defined('BASE_PATH') ? rtrim((string)BASE_PATH,'/') : rtrim((string)(dirname($_SERVER['SCRIPT_NAME']) ?: ''), '/'));
if ($basePath !== '' && $basePath[0] !== '/') $basePath = '/'.$basePath;
$tipo = (int)($tipo ?? 1);
?>
<link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>/public/css/nuevo_producto.css">


<main class="cotz-container" style="max-width:760px;margin:auto;">
  <h1>Agregar producto a cotización</h1>
  <p class="muted">Completa los datos; el subtotal se calcula automáticamente (Precio × Cantidad). Si seleccionas “Solo transporte”, puedes subir una boleta.</p>

  <form method="post" action="<?= htmlspecialchars($basePath) ?>/nuevo-producto/guardar" enctype="multipart/form-data" class="stack" id="npForm">
    <input type="hidden" name="embed" value="<?= $embed ? 1 : 0 ?>">

    <div class="grid-2">
      <label>Tipo de cotización
        <select name="tipo_cotizacion" id="tipoCotizacion">
          <option value="1" <?= $tipo===1?'selected':'' ?>>Compra + transporte</option>
          <option value="2" <?= $tipo===2?'selected':'' ?>>Solo transporte (HAZMAT+PESO)</option>
        </select>
      </label>

      <label>Nombre del producto
        <input type="text" name="nombre" required maxlength="200">
      </label>

      <label>Categoría
        <select name="id_categoria">
          <option value="">— Selecciona —</option>
          <?php foreach (($categorias ?? []) as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars((string)$c['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Proveedor
        <select name="id_proveedor">
          <option value="">— Selecciona —</option>
          <?php foreach (($proveedores ?? []) as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars((string)$p['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Precio (USD)
        <input type="number" name="precio" id="npPrecio" step="0.01" min="0" required>
      </label>

      <label>Cantidad
        <input type="number" name="cantidad" id="npCantidad" min="1" value="1" required>
      </label>

      <label>Subtotal (auto)
        <input type="text" id="npSubtotal" readonly>
      </label>

      <label>Link del producto
        <input type="url" name="link" placeholder="https://...">
      </label>
    </div>

    <div id="boletaGroup" style="display: <?= $tipo===2 ? 'block' : 'none' ?>;">
      <label>Boleta de pago (imagen/pdf) — requerido si “Solo transporte”
        <input type="file" name="boleta" accept="image/*,application/pdf">
      </label>
      <p class="muted">Acepta imágenes comunes (jpg, png, webp, etc.) o PDF.</p>
    </div>

    <div class="actions">
      <button class="btn btn-primary" type="submit">Guardar y agregar</button>
      <?php if ($embed): ?>
        <button class="btn btn-outline" type="button" onclick="window.parent.postMessage({type:'closeNuevoProducto'}, '*')">Cancelar</button>
      <?php endif; ?>
    </div>
  </form>
</main>

<?php if (!$embed) require __DIR__ . '/../layouts/footer.php'; ?>

<script>
(function(){
  const precio  = document.getElementById('npPrecio');
  const cant    = document.getElementById('npCantidad');
  const sub     = document.getElementById('npSubtotal');
  const tipoSel = document.getElementById('tipoCotizacion');
  const boletaG = document.getElementById('boletaGroup');
  const form    = document.getElementById('npForm');

  function updSubtotal(){
    const p = parseFloat(precio.value || '0');
    const q = parseInt(cant.value || '1', 10);
    const s = (isFinite(p) ? p : 0) * (isFinite(q) ? q : 0);
    sub.value = 'US$ ' + (Math.round(s*100)/100).toFixed(2);
  }
  precio?.addEventListener('input', updSubtotal);
  cant?.addEventListener('input', updSubtotal);
  updSubtotal();

  tipoSel?.addEventListener('change', ()=>{
    const v = parseInt(tipoSel.value || '1', 10);
    boletaG.style.display = (v === 2) ? 'block' : 'none';
  });

  form?.addEventListener('submit', (e)=>{
    const v = parseInt(tipoSel.value || '1', 10);
    if (v === 2) {
      const f = form.querySelector('input[name="boleta"]');
      if (!f || !f.files || f.files.length === 0) {
        e.preventDefault();
        alert('Para “Solo transporte”, sube una boleta de pago (imagen o PDF).');
      }
    }
  });
})();
</script>
