<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Global_import – Vista Comprador</title>

  <!-- Bootstrap (layout + offcanvas) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet">

  <!-- 🎨 Estilos globales -->
  <link rel="stylesheet" href="<?= asset('css/main.css') ?>">

  <!-- 🔝 Encabezado -->
  <link rel="stylesheet" href="<?= asset('css/header.css') ?>">

  <!-- 🔻 Pie de página -->
  <link rel="stylesheet" href="<?= asset('css/footer.css') ?>">
    <!-- CSS adicional por vista -->
    <?php if (!empty($extraCss)): ?>
      <link rel="stylesheet" href="<?= asset('css/' . $extraCss) ?>">
    <?php endif; ?>

  <!-- Íconos -->
  <link rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

  <!-- Bootstrap JS (para offcanvas móvil) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
