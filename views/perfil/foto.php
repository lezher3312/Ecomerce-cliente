<?php require __DIR__ . '/../layouts/head.php'; ?>
   <link rel="stylesheet" href="<?= asset('css/registro.css') ?>">
   <link rel="stylesheet" href="<?= asset('css/formulario.css') ?>">
<link rel="stylesheet" href="<?= asset('css/alertas.css') ?>">

<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="registro">
         <h2 class="registro__heading">Agregar o Editar Fotografía</h2>
     <p class="registro__texto">Ingresa tu fotografía o edita el existente:</p>  
    
    <?php require_once __DIR__ . '/../layouts/alertas.php'?>

        <form method="POST" action="<?= url('foto') ?>" class="formulario" enctype="multipart/form-data">
              <div class="formulario__campo">
        <label for="FOTOGRAFIA" class="formulario__label">Fotografía</label>
        <input type="file"
          class="formulario__input"
          placeholder="Ingresa tu Nit"
          id="FOTOGRAFIA"
          name="FOTOGRAFIA"
        >
    </div> 
   <?php if($cliente->FOTOGRAFIA_CLIENTE){ ?>  
         <p class="registro__texto">Imagen actual:</p>
   <div class="formulario__imagen">
   <picture>
      <img src="<?php echo "https://gtis.tech/Global-client/public/imgCliente/". $cliente->FOTOGRAFIA_CLIENTE;?>" alt="Sin Imagen">
   </picture>
   <?php } else { ?>
      <p class="registro__texto">Sin imagen.</p>
   <?php  } 
      ?>
</div>


             <input type="submit" class="btn btn-primary formulario__submit" value="Guardar Fotografía">
      </form> 
      
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
