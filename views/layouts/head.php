<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Marketplace – Vista Comprador</title>

  <!-- CSS principal -->
  <link rel="stylesheet" href="<?= asset('css/main.css') ?>">

  <!-- CSS extra por vista -->
  <?php if (!empty($extraCss)): ?>
    <link rel="stylesheet" href="<?= asset('css/' . $extraCss) ?>">
  <?php endif; ?>
</head>
<body>
