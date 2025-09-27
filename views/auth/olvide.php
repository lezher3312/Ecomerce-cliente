<?php require __DIR__ . '/../layouts/head.php'; ?>
<link rel="stylesheet" href="<?= asset('css/registro.css') ?>">
<link rel="stylesheet" href="<?= asset('css/alertas.css') ?>">
<link rel="stylesheet" href="<?= asset('css/formulario.css') ?>">
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="registro">
 <h2 class="registro__heading"> Olvidaste tu contraseña</h2> 
 <p class="registro__texto">Recupera tu acceso a Global-import</p>
     
 <?php require_once __DIR__ . '/../layouts/alertas.php'?>
 
 <form method="POST" action="<?= url('olvide') ?>" class="formulario">
        <div class="formulario__campo">
            <label for="EMAIL" class="formulario__label">Email</label>
            <input type="email"
                   class="formulario__input"
                   placeholder="Tu Email"
                   id="EMAIL"
                   name="EMAIL"
            >

            <input type="submit" class="btn btn-primary formulario__submit" value="Enviar Instrucciones">
        </div>
    </form>

</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
