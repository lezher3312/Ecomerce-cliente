<?php
// views/paginas/registroPedido.php
if (session_status() === PHP_SESSION_NONE) session_start();

$basePath  = isset($basePath) ? rtrim($basePath, '/') : rtrim(dirname($_SERVER['SCRIPT_NAME']) ?: '', '/');
$old = $_SESSION['form_registro_old'] ?? [];
unset($_SESSION['form_registro_old']);

require __DIR__ . '/../layouts/head.php';
require __DIR__ . '/../layouts/header.php';
?>
<link rel="stylesheet" href="<?= asset('pblic/css/registroPedido.css') ?>">

<main class="container" style="max-width:960px; margin:24px auto;">
  <h1>Crear cuenta para continuar</h1>

  <?php if (!empty($_SESSION['flash_registro_error'])): ?>
    <div class="alert alert-danger" role="alert" style="margin:12px 0;">
      <?= htmlspecialchars($_SESSION['flash_registro_error']); unset($_SESSION['flash_registro_error']); ?>
    </div>
  <?php endif; ?>

  <form action="<?= htmlspecialchars($basePath) ?>/registro/pedido"
        method="post"
        enctype="multipart/form-data"
        class="card"
        style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px;">

    <div style="grid-column: span 2;">
      <label>Nombre completo *</label>
      <input type="text" name="nombre_completo" required value="<?= htmlspecialchars($old['nombre_completo'] ?? '') ?>" style="width:100%;">
    </div>

    <div>
      <label>Email *</label>
      <input type="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>" style="width:100%;">
    </div>

    <div>
      <label>Teléfono</label>
      <input type="text" name="telefono" value="<?= htmlspecialchars($old['telefono'] ?? '') ?>" style="width:100%;">
    </div>

    <div>
      <label>Usuario</label>
      <input type="text" name="usuario" value="<?= htmlspecialchars($old['usuario'] ?? '') ?>" style="width:100%;" placeholder="Si lo dejas vacío, usamos el email">
    </div>

    <div>
      <label>Contraseña *</label>
      <input type="password" name="password" required minlength="6" style="width:100%;">
    </div>

    <div>
      <label>NIT / CUI</label>
      <input type="text" name="nit" value="<?= htmlspecialchars($old['nit'] ?? '') ?>" style="width:100%;">
    </div>

    <!-- 👇 AHORA pedimos DIRECCIÓN (no 'Dirección de entrega') -->
    <div style="grid-column: span 2;">
      <label>Dirección</label>
      <input type="text" name="direccion" value="<?= htmlspecialchars($old['direccion'] ?? '') ?>" style="width:100%;">
    </div>

    <div style="grid-column: span 2;">
      <label>Fotografía del cliente (opcional)</label>
      <input type="file" name="foto" accept="image/*" style="width:100%;">
      <small style="color:#64748b">Formatos comunes: JPG, PNG, WEBP, GIF, SVG, HEIC, etc.</small>
    </div>

    <div style="grid-column: span 2; display:flex; gap:8px; margin-top:8px;">
      <button class="btn btn-primary" type="submit">Registrar y continuar</button>
      <a class="btn btn-outline" href="<?= htmlspecialchars($basePath) ?>/login?next=/envio">Ya tengo cuenta</a>
    </div>
  </form>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
