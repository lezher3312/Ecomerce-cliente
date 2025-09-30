<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Global_import – Vista Comprador</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet">

  <!-- CSS principal -->
  <link rel="stylesheet" href="<?= asset('css/main.css') ?>">

  <!-- Librería de íconos FontAwesome -->
  <link rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<!-- Bootstrap JS (para el menú offcanvas) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- CSS extra por vista -->
  <?php if (!empty($extraCss)): ?>
    <link rel="stylesheet" href="<?= asset('css/' . $extraCss) ?>">
  <?php endif; ?>
</head>
<body>
