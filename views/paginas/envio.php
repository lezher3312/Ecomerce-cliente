<?php
// views/paginas/envio.php
// Espera: $cliente, $basePath, $cartCount, $count, $subtotal, $total, $paisActual, $regionActual, $items
$cliente  = $cliente  ?? [];
$items    = $items    ?? [];
$count    = (int)($count ?? 0);
$subtotal = (float)($subtotal ?? 0);
$total    = (float)($total ?? 0);
$basePath = $basePath ?? (dirname($_SERVER['SCRIPT_NAME']) ?: '');
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';
?>
<link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>/public/css/registroPedido.css">

<main class="container" style="max-width:1100px;margin:24px auto;">
  <h1>Datos de envío</h1>

  <?php if (!empty($_SESSION['flash_envio_ok'])): ?>
    <div class="alert" role="alert" style="margin-bottom:12px;background:#ecfdf5;border-color:#bbf7d0;color:#065f46;">
      <?= htmlspecialchars($_SESSION['flash_envio_ok']); unset($_SESSION['flash_envio_ok']); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_envio_error'])): ?>
    <div class="alert alert-danger" role="alert" style="margin-bottom:12px;">
      <?= htmlspecialchars($_SESSION['flash_envio_error']); unset($_SESSION['flash_envio_error']); ?>
    </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 360px;gap:16px;">
    <!-- Columna izquierda: formulario -->
    <section style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;">
      <form action="<?= htmlspecialchars($basePath) ?>/envio" method="post" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

        <div style="grid-column: span 2;">
          <h2 style="margin:0 0 10px 0;">Datos del cliente</h2>
        </div>

        <div style="grid-column: span 2;">
          <label>Nombre completo *</label>
          <input type="text" name="nombre_completo" required
                 value="<?= htmlspecialchars($cliente['NOMBRE_COMPLETO'] ?? '') ?>">
        </div>

        <!-- Direccion y Telefono en la misma fila -->
        <div>
          <label>Dirección *</label>
          <input type="text" name="direccion" required
                 value="<?= htmlspecialchars($cliente['DIRECCION'] ?? '') ?>">
        </div>

        <div>
          <label>Teléfono</label>
          <input type="text" name="telefono"
                 value="<?= htmlspecialchars($cliente['TELEFONO'] ?? '') ?>">
        </div>

        <!-- NIT debajo ocupando dos columnas -->
        <div style="grid-column: span 2;">
          <label>NIT / CUI</label>
          <input type="text" name="nit"
                 value="<?= htmlspecialchars($cliente['NIT'] ?? '') ?>">
        </div>

        <div style="grid-column: span 2;margin-top:8px;">
          <h2 style="margin:0 0 10px 0;">Entregar a</h2>
        </div>

        <div>
          <label>País *</label>
          <select name="pais" id="pais-select" required>
            <option value="Guatemala"       <?= ($paisActual ?? '')==='Guatemala' ? 'selected':''; ?>>Guatemala</option>
            <option value="México"          <?= ($paisActual ?? '')==='México' ? 'selected':''; ?>>México</option>
            <option value="El Salvador"     <?= ($paisActual ?? '')==='El Salvador' ? 'selected':''; ?>>El Salvador</option>
            <option value="Honduras"        <?= ($paisActual ?? '')==='Honduras' ? 'selected':''; ?>>Honduras</option>
            <option value="Estados Unidos"  <?= ($paisActual ?? '')==='Estados Unidos' ? 'selected':''; ?>>Estados Unidos</option>
            <option value="Otro"            <?= ($paisActual ?? '')==='Otro' ? 'selected':''; ?>>Otro</option>
          </select>
        </div>

        <div id="region-wrapper">
          <label>Departamento / Estado</label>
          <select name="region" id="region-select"></select>
          <input type="text" name="region" id="region-input" class="hidden"
                 placeholder="Ingresa tu departamento/estado" />
        </div>

        <div style="grid-column: span 2;">
          <label>Dirección de entrega</label>
          <input type="text" name="direccion_entrega"
                 value="<?= htmlspecialchars($cliente['DIRECCION_ENTREGA'] ?? '') ?>">
        </div>

        <div>
          <label>Latitud</label>
          <input type="text" name="latitud" id="latitud"
                 value="<?= htmlspecialchars((string)($cliente['LATITUD'] ?? '')) ?>">
        </div>

        <div>
          <label>Longitud</label>
          <input type="text" name="longitud" id="longitud"
                 value="<?= htmlspecialchars((string)($cliente['LONGITUD'] ?? '')) ?>">
        </div>

        <div style="grid-column: span 2; display:flex; gap:10px; align-items:center;">
          <button type="button" class="btn btn-outline" id="btn-geoloc">Obtener ubicación exacta</button>
          <small>Usaremos la geolocalización de tu navegador para llenar latitud/longitud.</small>
        </div>

        <div style="grid-column: span 2; display:flex; gap:8px; margin-top:8px;">
          <button class="btn btn-primary" type="submit">Guardar y continuar</button>
          <a href="<?= htmlspecialchars($basePath) ?>/carrito" class="btn btn-outline">Volver al carrito</a>
        </div>
      </form>
    </section>

    <!-- Columna derecha: resumen -->
    <aside style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;height:max-content;">
      <h3 style="margin-top:0;">Resumen de la cotización</h3>

      <?php if (empty($items)): ?>
        <div style="color:#64748b;">Tu cotización está vacía. <a href="<?= htmlspecialchars($basePath) ?>/catalogo">Ir al catálogo</a></div>
      <?php else: ?>
        <div style="max-height:320px; overflow:auto; border:1px solid #e5e7eb; border-radius:10px;">
          <table style="width:100%; border-collapse:collapse;">
            <thead style="position:sticky; top:0; background:#f8fafc; z-index:1;">
              <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid #e5e7eb;">Producto</th>
                <th style="text-align:center; padding:10px; border-bottom:1px solid #e5e7eb; width:80px;">Cant.</th>
                <th style="text-align:right; padding:10px; border-bottom:1px solid #e5e7eb; width:110px;">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $it):
                $nombre   = (string)($it['nombre'] ?? 'Producto');
                $qty      = max(1, (int)($it['cantidad'] ?? 1));
                $rowTotal = (float)($it['subtotal'] ?? ($qty * (float)($it['precio'] ?? 0)));
              ?>
              <tr>
                <td style="padding:10px; border-bottom:1px solid #f1f5f9;">
                  <div style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                    <?= htmlspecialchars($nombre) ?>
                  </div>
                </td>
                <td style="padding:10px; text-align:center; border-bottom:1px solid #f1f5f9;"><?= $qty ?></td>
                <td style="padding:10px; text-align:right; border-bottom:1px solid #f1f5f9;">Q <?= number_format($rowTotal, 2) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div style="margin-top:10px;">
          
          <hr>
          <div style="display:flex;justify-content:space-between;font-size:1.1rem;">
            <span>Total</span>
            <strong>Q <?= number_format($total, 2) ?></strong>
          </div>

          <a href="<?= htmlspecialchars($basePath) ?>/pago"
             class="btn btn-primary"
             style="margin-top:12px; width:100%; justify-content:center;">
            Pagar
          </a>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<script>
// ====== Regiones por país (demo) ======
const REGIONES = {
  "Guatemala": [
    "Alta Verapaz","Baja Verapaz","Chimaltenango","Chiquimula","El Progreso","Escuintla",
    "Guatemala","Huehuetenango","Izabal","Jalapa","Jutiapa","Petén","Quetzaltenango",
    "Quiché","Retalhuleu","Sacatepéquez","San Marcos","Santa Rosa","Sololá","Suchitepéquez","Totonicapán","Zacapa"
  ],
  "México": [
    "Aguascalientes","Baja California","Baja California Sur","Campeche","Chiapas","Chihuahua","Ciudad de México",
    "Coahuila","Colima","Durango","Guanajuato","Guerrero","Hidalgo","Jalisco","Estado de México","Michoacán",
    "Morelos","Nayarit","Nuevo León","Oaxaca","Puebla","Querétaro","Quintana Roo","San Luis Potosí","Sinaloa",
    "Sonora","Tabasco","Tamaulipas","Tlaxcala","Veracruz","Yucatán","Zacatecas"
  ],
  "El Salvador": ["Ahuachapán","Santa Ana","Sonsonate","Chalatenango","La Libertad","San Salvador","Cuscatlán","La Paz","Cabañas","San Vicente","Usulután","San Miguel","Morazán","La Unión"],
  "Honduras": ["Atlántida","Colón","Comayagua","Copán","Cortés","Choluteca","El Paraíso","Francisco Morazán","Gracias a Dios","Intibucá","Islas de la Bahía","La Paz","Lempira","Ocotepeque","Olancho","Santa Bárbara","Valle","Yoro"],
  "Estados Unidos": ["Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","Florida","Georgia","Hawái","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Luisiana","Maine","Maryland","Massachusetts","Míchigan","Minnesota","Misisipi","Misuri","Montana","Nebraska","Nevada","Nuevo Hampshire","Nueva Jersey","Nuevo México","Nueva York","Carolina del Norte","Dakota del Norte","Ohio","Oklahoma","Oregón","Pensilvania","Rhode Island","Carolina del Sur","Dakota del Sur","Tennessee","Texas","Utah","Vermont","Virginia","Washington","Virginia Occidental","Wisconsin","Wyoming"],
};

// Inicialización de selects
(function initRegionSelector(){
  const paisSel    = document.getElementById('pais-select');
  const regionSel  = document.getElementById('region-select');
  const regionInp  = document.getElementById('region-input');

  const selectedCountry = "<?= htmlspecialchars($paisActual ?? 'Guatemala', ENT_QUOTES) ?>";
  const selectedRegion  = "<?= htmlspecialchars($regionActual ?? '', ENT_QUOTES) ?>";

  function fillRegions(country, selected) {
    const list = REGIONES[country] || [];
    regionSel.innerHTML = '';
    list.forEach(r => {
      const opt = document.createElement('option');
      opt.value = r; opt.textContent = r;
      if (r === selected) opt.selected = true;
      regionSel.appendChild(opt);
    });
  }

  function updateUI() {
    const country = paisSel.value;
    if (country === 'Otro') {
      regionSel.classList.add('hidden');
      regionInp.classList.remove('hidden');
      regionInp.name = 'region';
      regionSel.name = '__region_select_disabled';
      regionInp.value = "<?= htmlspecialchars($regionActual ?? '', ENT_QUOTES) ?>";
    } else {
      regionInp.classList.add('hidden');
      regionSel.classList.remove('hidden');
      regionSel.name = 'region';
      regionInp.name = '__region_input_disabled';
      fillRegions(country, (country === selectedCountry ? selectedRegion : ''));
    }
  }

  paisSel.addEventListener('change', updateUI);
  // Boot
  updateUI();
})();

// Geolocalización
document.getElementById('btn-geoloc')?.addEventListener('click', () => {
  if (!navigator.geolocation) {
    alert('Tu navegador no soporta geolocalización.');
    return;
  }
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      document.getElementById('latitud').value  = pos.coords.latitude.toString();
      document.getElementById('longitud').value = pos.coords.longitude.toString();
    },
    (err) => {
      let msg = 'No pudimos obtener tu ubicación.';
      if (err.code === 1) msg = 'Permiso de ubicación denegado.';
      if (err.code === 2) msg = 'Posición no disponible.';
      if (err.code === 3) msg = 'Tiempo de espera agotado.';
      alert(msg);
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  );
});
</script>

<style>
.hidden{ display:none !important; }
</style>
