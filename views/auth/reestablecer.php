<?php require __DIR__ . '/../layouts/head.php'; ?>
<link rel="stylesheet" href="<?= asset('css/registro.css') ?>">
<link rel="stylesheet" href="<?= asset('css/alertas.css') ?>">
<link rel="stylesheet" href="<?= asset('css/formulario.css') ?>">
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="registro">
 <h2 class="registro__heading"> Reestablece tu contraseña</h2> 
 <p class="registro__texto">Coloca tu nuevo password.</p>
     
 <?php require_once __DIR__ . '/../layouts/alertas.php'?>
 
<?php if($token_valido) { ?>
    <form method="POST" class="formulario">
        <div class="formulario__campo">
            <label for="PASSWORD_PAGINA" class="formulario__label">Nuevo Password</label>
            <input 
            type="password"
            class="formulario__input"
            placeholder="Tu Nuevo Password"
            id="PASSWORD_PAGINA"
            name="PASSWORD_PAGINA"
            >
        </div>
       
         <input type="submit" class="btn btn-primary formulario__submit" value="Guardar Password">
    </form>

    <?php
     }
    ?>

</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
