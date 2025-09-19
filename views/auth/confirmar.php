<?php require __DIR__ . '/../layouts/head.php'; ?>
<link rel="stylesheet" href="<?= asset('css/registro.css') ?>">
   <link rel="stylesheet" href="<?= asset('css/alertas.css') ?>">

<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="registro">
    <h2 class="registro__heading"> Confirma Tu Cuenta</h2>
    <p class="registro__texto">Tu cuenta en Global Import</p>

    <?php require_once __DIR__ . '/../layouts/alertas.php'?>

<?php if(isset($alertas['exito'])){  ?>
<div class="acciones">
        <a href="/login" class="acciones__enlace">Iniciar Sesión</a>
    </div>
    <?php
     }
?>

</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
