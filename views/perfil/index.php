<?php require __DIR__ . '/../layouts/head.php'; ?>
   <link rel="stylesheet" href="<?= asset('css/registro.css') ?>">
   <link rel="stylesheet" href="<?= asset('css/formulario.css') ?>">
<link rel="stylesheet" href="<?= asset('css/alertas.css') ?>">

<?php require __DIR__ . '/../layouts/header.php'; ?> 

<main class="registro">
     <h2 class="registro__heading">Editar Datos Personales</h2>
     <p class="registro__texto">Reescribe los cambios que quieres editar:</p>
    
   <?php require_once __DIR__ . '/../layouts/alertas.php'?>

  <form method="POST" action="<?= url('datospersonales') ?>" class="formulario" enctype="multipart/form-data">
      
   <div class="formulario__campo">
        <label for="NOMBRE_COMPLETO" class="formulario__label">Nombre Completo</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Nombre Completo"
          id="NOMBRE_COMPLETO"
          name="NOMBRE_COMPLETO"
          value="<?php echo $usuario->NOMBRE_COMPLETO ?? '';?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="TELEFONO" class="formulario__label">Telefono</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu telefono"
          id="TELEFONO"
          name="TELEFONO"
          value="<?php echo $usuario->TELEFONO ?? '';?>"
        >
    </div>

    <div class="formulario__campo">
        <label for="DIRECCION" class="formulario__label">Dirección</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Dirección"
          id="DIRECCION"
          name="DIRECCION"
          value="<?php echo $usuario->DIRECCION ?? '';?>"
        >
    </div>

   <div class="formulario__campo">
        <label for="EMAIL" class="formulario__label">Email</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Email"
          id="EMAIL"
          name="EMAIL"
          value="<?php echo $usuario->EMAIL ?? '';?>"
        >
    </div>

     <div class="formulario__campo">
        <label for="USUARIO_PAGINA" class="formulario__label">Usuario de la Pagina</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Usuario de la Pagina"
          id="USUARIO_PAGINA"
          name="USUARIO_PAGINA"
          value="<?php echo $usuario->USUARIO_PAGINA ?? '';?>"
        >
    </div>
   
      <input type="submit" class="btn btn-primary formulario__submit" value="Editar cuenta">

  </form>
</main>
<?php require __DIR__ . '/../layouts/footer.php'; ?>
