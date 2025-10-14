<?php require __DIR__ . '/../layouts/head.php'; ?>
   <link rel="stylesheet" href="<?= asset('css/registro.css') ?>">
   <link rel="stylesheet" href="<?= asset('css/formulario.css') ?>">
<link rel="stylesheet" href="<?= asset('css/alertas.css') ?>">

<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="registro">
     <h2 class="registro__heading">Agregar o Editar Nit</h2>
     <p class="registro__texto">Ingresa tu Nit o edita el existente:</p>
    
          <?php require_once __DIR__ . '/../layouts/alertas.php'?>
          
     <form method="POST" action="<?= url('nit') ?>" class="formulario" enctype="multipart/form-data">
      <div class="formulario__campo">
        <label for="NIT" class="formulario__label">Nit</label>
        <input type="text"
          class="formulario__input"
          placeholder="Ingresa tu Nit"
          id="NIT"
          name="NIT"
          value="<?php echo $cliente->NIT ?? '';?>"
        >
    </div> 

         <input type="submit" class="btn btn-primary formulario__submit" value="Registrar NIT">
   </form>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
