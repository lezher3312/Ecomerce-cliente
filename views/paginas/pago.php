<?php
// views/paginas/pago.php
if (session_status() === PHP_SESSION_NONE) session_start();
require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';

/*
Recibe (ahora trabajamos en GTQ):
$basePath
$formasPago              // ['tarjeta'|'transferencia'|'deposito'|'contraentrega' => 'Label']
$totalQ                  // total final de la cotización en Quetzales
$impuestos               // ENVIO, ARANCEL, DESADUANAJE, FLETE (opc)
$recargos                // [ ['nombre','valor'], ... ] (opc, en Q)
$saldoPendienteQ         // saldo pendiente en Q
$pagosPrevios            // lista de pagos (id, descripcion, monto, fecha, estado, forma)
*/
$totalQ         = (float)($totalQ ?? 0);
$saldoPendienteQ= (float)($saldoPendienteQ ?? $totalQ);
?>
<link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>/public/css/cotizacion.css">

<main class="cotz-container" style="max-width:1100px;">
  <h1>Pago de cotización (GTQ)</h1>

  <?php if (!empty($_SESSION['flash_pago_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_pago_error']); unset($_SESSION['flash_pago_error']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_pago_ok'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_pago_ok']); unset($_SESSION['flash_pago_ok']); ?></div>
  <?php endif; ?>

  <div class="grid" style="grid-template-columns: 1fr 380px; gap:16px;">
    <!-- Métodos -->
    <section class="card">
      <h2>Registrar pago</h2>

      <form method="post" action="<?= htmlspecialchars($basePath) ?>/pago/crear" class="stack" id="pagoForm" style="gap:14px;" enctype="multipart/form-data">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div>
            <label style="display:block;margin:0 0 6px;">Cuotas</label>
            <select name="cuotas" id="cuotasSel">
              <option value="1">Pago único (1)</option>
              <option value="2">2 pagos</option>
              <option value="3">3 pagos</option>
              <option value="4">4 pagos</option>
            </select>
            <small class="muted" id="sugCuota"></small>
          </div>
          <div>
            <label style="display:block;margin:0 0 6px;">Monto a pagar (Q)</label>
            <input type="number" step="0.01" min="0.01" max="<?= htmlspecialchars((string)$saldoPendienteQ) ?>"
                   name="monto_q" id="montoQ"
                   value="<?= htmlspecialchars((string)number_format($saldoPendienteQ,2,'.','')) ?>"
                   class="w-100" required>
            <small class="muted">Saldo pendiente: <strong>Q <?= number_format($saldoPendienteQ, 2) ?></strong></small>
          </div>
        </div>

        <div>
          <label style="display:block;margin:10px 0 6px;">Selecciona método</label>
          <?php foreach ($formasPago as $key => $label): ?>
            <label style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
              <input type="radio" name="metodo" value="<?= htmlspecialchars($key) ?>" required>
              <span><?= htmlspecialchars($label) ?></span>
            </label>
          <?php endforeach; ?>
        </div>

        <!-- Datos TARJETA -->
        <div id="cardFields" class="hidden" style="border:1px dashed #e5e7eb;border-radius:10px;padding:12px;">
          <h3 style="margin-top:0;">Datos de la tarjeta</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
              <label>Tipo de tarjeta</label>
              <select name="tipo_tarjeta">
                <option value="">Seleccione…</option>
                <option value="Visa">Visa</option>
                <option value="Mastercard">Mastercard</option>
                <option value="Debito">Débito</option>
                <option value="Credito">Crédito</option>
              </select>
            </div>
            <div>
              <label>Últimos 4 dígitos</label>
              <input type="text" name="numero4" maxlength="4" pattern="[0-9]{4}" placeholder="1234">
            </div>
            <div>
              <label>Mes expiración</label>
              <input type="number" name="exp_month" min="1" max="12" placeholder="MM">
            </div>
            <div>
              <label>Año expiración</label>
              <input type="number" name="exp_year" min="<?= date('Y') ?>" max="<?= date('Y')+15 ?>" placeholder="YYYY">
            </div>
            <div style="grid-column:span 2;">
              <label>Nombre del titular</label>
              <input type="text" name="titular" placeholder="Como aparece en la tarjeta">
            </div>
          </div>
          <small class="muted">No almacenamos CVV.</small>
        </div>

        <!-- Datos TRANSFERENCIA/DEPÓSITO -->
        <div id="transFields" class="hidden" style="border:1px dashed #e5e7eb;border-radius:10px;padding:12px;">
          <h3 style="margin-top:0;">Transferencia / depósito</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
              <label>Banco</label>
              <select name="id_banco">
                <option value="0">Seleccione…</option>
                <option value="1">Banrural</option>
                <option value="2">Banco Industrial</option>
                <option value="3">G&T Continental</option>
                <option value="99">Otro</option>
              </select>
            </div>
            <div>
              <label>Comprobante / referencia</label>
              <input type="text" name="comprobante" placeholder="No. boleta / referencia">
            </div>
            <div style="grid-column:span 2;">
              <label>Foto del comprobante (JPG/PNG, máx 5MB)</label>
              <input type="file" name="img_comprobante" accept=".jpg,.jpeg,.png,image/*">
            </div>
          </div>
        </div>

        <div>
          <label>Referencia (opcional)</label>
          <input type="text" name="referencia" class="w-100" placeholder="Observación, referencia adicional, etc.">
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:6px;">Registrar pago</button>
        <a href="<?= htmlspecialchars($basePath) ?>/cotizacion" class="btn btn-outline">Volver al resumen</a>
      </form>

      <?php if (!empty($pagosPrevios)): ?>
        <hr>
        <h3>Pagos registrados</h3>
        <div style="overflow:auto;">
          <table class="tabla" style="min-width:640px;">
            <thead>
              <tr><th>ID</th><th>Fecha</th><th>Descripción</th><th>Forma</th><th>Monto (Q)</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody>
              <?php foreach ($pagosPrevios as $p): ?>
                <tr>
                  <td>#<?= (int)$p['ID_PAGO'] ?></td>
                  <td><?= htmlspecialchars($p['FECHA_PAGO'] ?? '') ?></td>
                  <td><?= htmlspecialchars($p['DESCRIPCION'] ?? '') ?></td>
                  <td><?= htmlspecialchars(strtoupper($p['FORMA_PAGO'] ?? '')) ?></td>
                  <td class="num">Q <?= number_format((float)($p['MONTO'] ?? 0), 2) ?></td>
                  <td><?= ((string)($p['ESTADO'] ?? '') === '1') ? 'VIGENTE' : ((string)($p['ESTADO'] ?? '') === '0' ? 'ANULADO' : htmlspecialchars((string)$p['ESTADO'])) ?></td>
                  <td>
                    <?php if ((string)($p['ESTADO'] ?? '') !== '0'): ?>
                      <form method="post" action="<?= htmlspecialchars($basePath) ?>/pago/anular" onsubmit="return confirm('¿Anular este pago?');" style="display:inline;">
                        <input type="hidden" name="id_pago" value="<?= (int)$p['ID_PAGO'] ?>">
                        <button class="btn btn-outline btn-sm" type="submit">Anular</button>
                      </form>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <!-- Resumen -->
    <aside class="card">
      <h2>Resumen</h2>

      <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
        <table class="tabla" style="border:none;border-radius:0;margin:0;">
          <tbody>
            <tr>
              <td style="text-align:left;">Total cotización (GTQ)</td>
              <td class="num"><strong>Q <?= number_format($totalQ, 2) ?></strong></td>
            </tr>

            <?php if (!empty($impuestos)): ?>
              <tr><td colspan="2" style="background:#f8fafc;font-weight:600;">Impuestos</td></tr>
              <?php foreach (['ENVIO'=>'Envío','ARANCEL'=>'Arancel','DESADUANAJE'=>'Desaduanaje','FLETE'=>'Flete'] as $k=>$lbl): ?>
                <?php $v = (float)($impuestos[$k] ?? 0); if ($v <= 0) continue; ?>
                <tr>
                  <td style="text-align:left;"><?= htmlspecialchars($lbl) ?></td>
                  <td class="num">Q <?= number_format($v, 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($recargos)): ?>
              <tr><td colspan="2" style="background:#f8fafc;font-weight:600;">Recargos</td></tr>
              <?php foreach ($recargos as $r): ?>
                <tr>
                  <td style="text-align:left;"><?= htmlspecialchars($r['nombre'] ?? '') ?></td>
                  <td class="num">Q <?= number_format((float)($r['valor'] ?? 0), 2) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>

            <tr>
              <td style="text-align:left;">Saldo pendiente</td>
              <td class="num"><strong>Q <?= number_format($saldoPendienteQ, 2) ?></strong></td>
            </tr>
          </tbody>
        </table>
      </div>
    </aside>
  </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

<script>
  (function(){
    const form   = document.getElementById('pagoForm');
    const card   = document.getElementById('cardFields');
    const trans  = document.getElementById('transFields');
    const cuotas = document.getElementById('cuotasSel');
    const montoQ = document.getElementById('montoQ');
    const sug    = document.getElementById('sugCuota');
    const saldo  = <?= json_encode((float)$saldoPendienteQ) ?>;
    const totalQ = <?= json_encode((float)$totalQ) ?>;

    function toggleExtra() {
      const m = (form.querySelector('input[name="metodo"]:checked')||{}).value || '';
      card.classList.toggle('hidden', !(m === 'tarjeta'));
      trans.classList.toggle('hidden', !(m === 'transferencia' || m === 'deposito'));
    }
    form.querySelectorAll('input[name="metodo"]').forEach(r => r.addEventListener('change', toggleExtra));

    function updateSugerencia(){
      const n = parseInt(cuotas.value || '1', 10);
      const sugerido = (totalQ / (isNaN(n) || n < 1 ? 1 : n));
      sug.textContent = 'Sugerido por cuota: Q ' + (sugerido.toFixed(2));
      // Si el monto supera el saldo, ajústalo
      if (parseFloat(montoQ.value || '0') > saldo) {
        montoQ.value = saldo.toFixed(2);
      }
    }

    cuotas.addEventListener('change', updateSugerencia);
    updateSugerencia();
  })();
</script>

<style>.hidden{display:none!important;}</style>
