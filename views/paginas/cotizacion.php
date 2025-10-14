<?php
// views/paginas/cotizacion.php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

/*
Variables esperadas:
$items, $estado, $tipo, $basePath
$totalBase (USD, de cotizacion.TOTAL_COTIZACION)
$impuestosYOtros (USD, de cotizacion.TOTAL_CON_IMPUESTOS)
$tipoCambio (opcional, guardado en BD), $totalVentaEnQ (opcional)
Además, para el preview de TC se usa $_SESSION['tc_preview'] y $_SESSION['tc_updated_at']

Opcionales para desglose:
$impuestosDet = ['ENVIO'=>, 'ARANCEL'=>, 'DESADUANAJE'=>, 'FLETE'=>]
$recargosDet  = [['NOMBRE_RERCARGO'=>, 'VALOR_RECARGO'=>], ...]
*/

$totalBase        = (float)($totalBase ?? 0);
$impuestosYOtros  = (float)($impuestosYOtros ?? 0);
$totalFinalUSD    = $totalBase + $impuestosYOtros;

$tcSaved   = isset($tipoCambio) ? (float)$tipoCambio : 0.0;
$tcPreview = isset($_SESSION['tc_preview']) ? (float)$_SESSION['tc_preview'] : 0.0;
$tcShown   = $tcPreview > 1.2 ? $tcPreview : ($tcSaved > 1.2 ? $tcSaved : 0.0);
$updatedAt = $_SESSION['tc_updated_at'] ?? null;

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>
<link rel="stylesheet" href="<?= asset('/css/cotizacion.css') ?>">

<main class="cotz-container">
  <h1>Cotización</h1>

  <?php if ($flash): ?>
    <div class="alert"><?= htmlspecialchars($flash) ?></div>
  <?php endif; ?>

  <?php
    $labels = [0=>'Inactivo',1=>'Activo',2=>'Falta de impuesto',3=>'Procesado',4=>'Confirmado',5=>'Anulado'];
    $pasos  = [1,2,3,4,5];
  ?>
  <div class="stepper" id="js-stepper">
    <?php foreach ($pasos as $p): ?>
      <div class="step <?= ($estado >= $p) ? 'done' : (($estado+1)===$p ? 'current' : '') ?>" data-step="<?= $p ?>">
        <div class="dot"></div>
        <div class="lbl"><?= htmlspecialchars($labels[$p]) ?></div>
        <?php if ($p !== 5): ?><div class="bar"></div><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <form method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/tipo" class="tipo-form">
    <label>Tipo de cotización:</label>
    <label><input type="radio" name="tipo_cotizacion" value="1" <?= ($tipo===1?'checked':'') ?>> Compra + transporte</label>
    <label><input type="radio" name="tipo_cotizacion" value="2" <?= ($tipo===2?'checked':'') ?>> Solo transporte (HAZMAT+PESO)</label>
    <button class="btn btn-outline" type="submit">Actualizar</button>
  </form>

  <?php if (empty($items)): ?>
    <div class="alert">Tu cotización está vacía.</div>
    <div class="actions">
      <a class="btn" href="<?= htmlspecialchars($basePath) ?>/catalogo">Ir al catálogo</a>
    </div>
  <?php else: ?>
    <div class="grid">
      <article class="card">
        <h2>Productos</h2>
        <table class="tabla">
          <thead>
            <tr>
              <th>Producto</th>
              <th class="num">Precio unit.</th>
              <th class="num">Oferta</th>
              <th class="num">Cant.</th>
              <th class="num">Cargo (u)</th>
              <th class="num">Cargo total</th>
              <th class="num">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $it): ?>
              <tr>
                <td>
                  <div class="prod">
                    <?php if (!empty($it['imagen'])): ?>
                      <img src="<?= htmlspecialchars($it['imagen']) ?>" alt="" />
                    <?php endif; ?>
                    <div>
                      <div class="name"><?= htmlspecialchars($it['nombre']) ?></div>
                      <div class="desc"><?= htmlspecialchars($it['descripcion']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="num">US$ <?= number_format((float)($it['precio_unit'] ?? $it['precio'] ?? 0), 2) ?></td>
                <td class="num">
                  <?php if (!empty($it['oferta_activa'])): ?>
                    −<?= number_format((float)$it['oferta_porc'], 0) ?>%
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td class="num"><?= (int)$it['cantidad'] ?></td>
                <td class="num">US$ <?= number_format((float)$it['cargo_unit'], 2) ?></td>
                <td class="num">US$ <?= number_format((float)($it['cargo_total'] ?? ((float)$it['cargo_unit'] * (int)$it['cantidad'])), 2) ?></td>
                <td class="num"><strong>US$ <?= number_format((float)$it['subtotal'], 2) ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="6" class="num">Total cotización (USD)</td>
              <td class="num"><strong id="js-total-base">US$ <?= number_format($totalBase, 2) ?></strong></td>
            </tr>
            <tr>
              <td colspan="6" class="num">
                Impuestos / otros cargos (USD)
                <?php
                  // ==== DESGLOSE ====
                  $__imp  = (isset($impuestosDet) && is_array($impuestosDet)) ? $impuestosDet : [];
                  $__recs = (isset($recargosDet)   && is_array($recargosDet))   ? $recargosDet   : [];
                  $__has  = (!empty($__imp) || !empty($__recs));

                  $__sumaImp = (float)($__imp['ENVIO'] ?? 0)
                             + (float)($__imp['ARANCEL'] ?? 0)
                             + (float)($__imp['DESADUANAJE'] ?? 0)
                             + (float)($__imp['FLETE'] ?? 0);
                  $__sumaRec = 0.0;
                  foreach ($__recs as $__r) { $__sumaRec += (float)($__r['VALOR_RECARGO'] ?? 0); }
                ?>
                <?php if ($__has): ?>
                  <div class="muted" style="text-align:left; font-weight:400; margin-top:6px;">
                    <div><strong>Impuestos:</strong></div>
                    <ul style="margin:6px 0 8px 16px;">
                      <li>Envío: US$ <?= number_format((float)($__imp['ENVIO'] ?? 0), 2) ?></li>
                      <li>Arancel: US$ <?= number_format((float)($__imp['ARANCEL'] ?? 0), 2) ?></li>
                      <li>Desaduanaje: US$ <?= number_format((float)($__imp['DESADUANAJE'] ?? 0), 2) ?></li>
                      <li>Flete: US$ <?= number_format((float)($__imp['FLETE'] ?? 0), 2) ?></li>
                    </ul>
                    <?php if (!empty($__recs)): ?>
                      <div><strong>Recargos:</strong></div>
                      <ul style="margin:6px 0 0 16px;">
                        <?php foreach ($__recs as $__r): ?>
                          <li><?= htmlspecialchars($__r['NOMBRE_RERCARGO'] ?? 'Recargo') ?>:
                              US$ <?= number_format((float)($__r['VALOR_RECARGO'] ?? 0), 2) ?></li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                    <div style="margin-top:6px;">
                      <em>Suma detalle:</em>
                      US$ <?= number_format($__sumaImp + $__sumaRec, 2) ?>
                      <?php if (abs((($__sumaImp + $__sumaRec) - $impuestosYOtros)) < 0.01): ?>
                        <span class="muted"> · coincide con TOTAL_CON_IMPUESTOS</span>
                      <?php else: ?>
                        <span style="color:#b91c1c;"> · no coincide con TOTAL_CON_IMPUESTOS</span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endif; ?>
                <!-- ==== /DESGLOSE ==== -->
              </td>
              <td class="num"><strong id="js-impuestos">US$ <?= number_format($impuestosYOtros, 2) ?></strong></td>
            </tr>
            <tr>
              <td colspan="6" class="num">Total final (USD)</td>
              <td class="num"><strong id="js-total-final">US$ <?= number_format($totalFinalUSD, 2) ?></strong></td>
            </tr>
          </tfoot>
        </table>
      </article>

      <aside class="card">
        <h2>Moneda</h2>

        <div id="js-moneda-box">
          <?php if ($tcShown > 1.2): ?>
            <p>Tipo de cambio (preview/guardado): <strong id="js-tc">1 USD = Q <?= number_format($tcShown, 2) ?></strong></p>
            <?php if (!empty($updatedAt)): ?>
              <?php
                try { $dt = new DateTime($updatedAt); $updatedStr = $dt->format('d/m/y h:i A'); }
                catch(Exception $e){ $updatedStr = $updatedAt; }
              ?>
              <p class="muted">Última actualización: <span id="js-tc-updated"><?= htmlspecialchars($updatedStr) ?></span></p>
            <?php endif; ?>
            <p>Total final en quetzales (preview): <strong id="js-total-gtq">Q <?= number_format($totalFinalUSD * $tcShown, 2) ?></strong></p>
            <?php if (!empty($totalVentaEnQ)): ?>
              <p class="muted">Guardado en BD: <strong>Q <?= number_format((float)$totalVentaEnQ, 2) ?></strong></p>
            <?php endif; ?>
          <?php else: ?>
            <p class="muted" id="js-no-tc">Aún no hay tipo de cambio consultado/guardado.</p>
          <?php endif; ?>
        </div>

        <form method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/actualizar-tc" class="stack">
          <button class="btn btn-outline" type="submit">Actualizar tipo de cambio (USD→GTQ)</button>
        </form>

        <form method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/ver-quetzales" class="stack">
          <button class="btn btn-primary" type="submit">Ver total en quetzales (y guardar)</button>
        </form>

        <h2 style="margin-top:1rem;">Acciones</h2>
        <div id="js-estado-actions">
          <?php if ($estado === 1): ?>
            <!-- Fallback oculto por si el iframe no carga -->
            <form id="fallbackPedir" method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/pedir"></form>
            <!-- Abrir modal -->
            <button type="button" id="btnOpenEnvio" class="btn btn-primary">Pedir cotización</button>
          <?php elseif ($estado === 2): ?>
            <div class="muted" id="js-estado-text">Estado: Falta de impuesto. Estamos procesando tu solicitud.</div>
          <?php elseif ($estado === 3): ?>
            <div class="muted" id="js-estado-text">Estado: Procesado. Ya puedes confirmar.</div>
            <form method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/confirmar" class="stack" id="js-confirm-form">
              <button class="btn btn-primary" type="submit">Confirmar y continuar a pago</button>
            </form>
          <?php endif; ?>

          <?php if (in_array($estado, [1,2,3], true)): ?>
            <form method="post" action="<?= htmlspecialchars($basePath) ?>/cotizacion/anular" class="stack" style="margin-top:8px;">
              <button class="btn btn-outline" type="submit">Anular cotización</button>
            </form>
          <?php elseif ($estado === 4): ?>
            <div class="muted" id="js-estado-text">Confirmada. Redireccionando a pago…</div>
          <?php elseif ($estado === 5): ?>
            <div class="muted" id="js-estado-text">Cotización anulada.</div>
            <a class="btn" href="<?= htmlspecialchars($basePath) ?>/carrito">Volver al carrito</a>
          <?php endif; ?>
        </div>
      </aside>
    </div>
  <?php endif; ?>
</main>

<!-- ===================== MODAL IFRAME ENVÍO ===================== -->
<div id="envioModal" class="modal" aria-hidden="true">
  <div class="modal-backdrop" data-close="1"></div>
  <div class="modal-content" role="dialog" aria-labelledby="envioModalTitle" aria-modal="true">
    <button class="modal-close" type="button" aria-label="Cerrar" data-close="1">×</button>
    <h2 id="envioModalTitle" class="sr-only">Datos de envío</h2>
    <div class="modal-body">
      <div id="envioLoading" class="loading">Cargando envío…</div>
      <iframe id="envioFrame"
              src="<?= htmlspecialchars($basePath) ?>/envio?embed=1"
              style="width:100%;height:80vh;border:0;border-radius:12px;background:#fff;display:block;"></iframe>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<!-- ===================== SCRIPTS: MODAL + FALLBACK + MENSAJERÍA + POLLING ===================== -->
<script>
(function(){
  const modal   = document.getElementById('envioModal');
  const iframe  = document.getElementById('envioFrame');
  const loading = document.getElementById('envioLoading');
  const openBtn = document.getElementById('btnOpenEnvio');
  const fallbackForm = document.getElementById('fallbackPedir');
  const basePath = <?= json_encode($basePath) ?>;

  function openModal(){
    if (!modal) return;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.documentElement.style.overflow = 'hidden';
  }
  function closeModal(){
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.documentElement.style.overflow = '';
  }

  if (openBtn) {
    openBtn.addEventListener('click', () => {
      let triedFallback = false;
      let loaded = false;

      if (iframe) {
        const url = new URL(iframe.src, window.location.origin);
        url.searchParams.set('_ts', Date.now().toString());
        iframe.src = url.toString();
        if (loading) loading.style.display = 'block';
      }

      openModal();

      const t = setTimeout(() => {
        if (!loaded && !triedFallback && fallbackForm) {
          triedFallback = true;
          fallbackForm.submit(); // POST /cotizacion/pedir
        }
      }, 1200);

      iframe?.addEventListener('load', () => {
        loaded = true;
        if (loading) loading.style.display = 'none';
        clearTimeout(t);
      }, { once: true });
    });
  }

  modal?.addEventListener('click', (e) => {
    if (e.target.dataset.close === '1' || e.target.classList.contains('modal-backdrop')) {
      closeModal();
    }
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  window.addEventListener('message', (ev) => {
    if (!ev || !ev.data || typeof ev.data !== 'object') return;
    if (ev.data.type === 'envioSaved') {
      closeModal();
      setEstadoFaltaImpuesto();
      startPollingEstado();
    }
  });

  function setEstadoFaltaImpuesto(){
    const actions = document.getElementById('js-estado-actions');
    if (!actions) return;
    actions.innerHTML = `
      <div class="muted" id="js-estado-text">Estado: Falta de impuesto. Estamos procesando tu solicitud.</div>
      <form method="post" action="${basePath}/cotizacion/anular" class="stack" style="margin-top:8px;">
        <button class="btn btn-outline" type="submit">Anular cotización</button>
      </form>
    `;
    updateStepper(2);
  }

  let pollTimer = null;
  function startPollingEstado(){
    if (pollTimer) return;
    async function tick(){
      try{
        const res = await fetch(`${basePath}/cotizacion/estado-json`, {headers:{'Accept':'application/json'}});
        if (!res.ok) throw new Error('bad');
        const data = await res.json();
        if (!data || !data.ok) return;

        if (typeof data.totalBase === 'number') setText('#js-total-base', `US$ ${fmt2(data.totalBase)}`);
        if (typeof data.impuestosYOtros === 'number') setText('#js-impuestos', `US$ ${fmt2(data.impuestosYOtros)}`);
        if (typeof data.totalFinalUSD === 'number') setText('#js-total-final', `US$ ${fmt2(data.totalFinalUSD)}`);

        if (typeof data.tcShown === 'number' && data.tcShown > 0 && typeof data.totalFinalUSD === 'number'){
          const monBox = document.getElementById('js-moneda-box');
          const gtq = `Q ${fmt2(data.totalFinalUSD * data.tcShown)}`;
          if (monBox){
            monBox.innerHTML = `
              <p>Tipo de cambio (preview/guardado): <strong id="js-tc">1 USD = Q ${fmt2(data.tcShown)}</strong></p>
              <p>Total final en quetzales (preview): <strong id="js-total-gtq">${gtq}</strong></p>
            `;
          }
        }

        if (typeof data.estado === 'number'){
          if (data.estado >= 3){
            const actions = document.getElementById('js-estado-actions');
            if (actions){
              actions.innerHTML = `
                <div class="muted" id="js-estado-text">Estado: Procesado. Ya puedes confirmar.</div>
                <form method="post" action="${basePath}/cotizacion/confirmar" class="stack" id="js-confirm-form">
                  <button class="btn btn-primary" type="submit">Confirmar y continuar a pago</button>
                </form>
                <form method="post" action="${basePath}/cotizacion/anular" class="stack" style="margin-top:8px;">
                  <button class="btn btn-outline" type="submit">Anular cotización</button>
                </form>
              `;
            }
            updateStepper(3);
            clearInterval(pollTimer); pollTimer = null;
          } else {
            updateStepper(2);
          }
        }
      }catch(e){ /* silencio */ }
    }
    tick();
    pollTimer = setInterval(tick, 5000);
  }

  function setText(sel, txt){ const el = document.querySelector(sel); if (el) el.textContent = txt; }
  function fmt2(n){ return Number(n).toFixed(2); }
  function updateStepper(estado){
    const nodes = document.querySelectorAll('#js-stepper .step');
    nodes.forEach(n => {
      const step = parseInt(n.getAttribute('data-step'), 10);
      n.classList.remove('done','current');
      if (estado >= step) n.classList.add('done');
      else if ((estado + 1) === step) n.classList.add('current');
    });
  }
})();
</script>
