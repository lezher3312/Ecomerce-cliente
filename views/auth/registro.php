<?php require __DIR__ . '/../layouts/head.php'; ?>
<link rel="stylesheet" href="<?= asset('css/registro.css') ?>">
<link rel="stylesheet" href="<?= asset('css/formulario.css') ?>">
<link rel="stylesheet" href="<?= asset('css/alertas.css') ?>">

<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="registro">
     <h2 class="registro__heading"> Registrar Nueva Cuenta</h2>
     <p class="registro__texto">Completa los campos para crear una nueva cuenta:</p>
    
     <?php require_once __DIR__ . '/../layouts/alertas.php'?>
     
<form method="POST" action="/registro" class="formulario" enctype="multipart/form-data">
         
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
        <label for="UBICACION" class="formulario__label">Ubicación</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Ubicación"
          id="UBICACION"
          name="UBICACION"
          value="<?php echo $usuario->UBICACION ?? '';?>"
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

    
     <div class="formulario__campo">
        <label for="PASSWORD_PAGINA" class="formulario__label">Password de la Pagina</label>
        <input type="password"
          class="formulario__input"
          placeholder="Tu Password"
          id="PASSWORD_PAGINA"
          name="PASSWORD_PAGINA"
        >
    </div>

    
     <div class="formulario__campo">
        <label for="PASSWORD" class="formulario__label">Repetir Password de la Pagina</label>
        <input type="password"
          class="formulario__input"
          placeholder="Repetir tu Password"
          id="PASSWORD"
          name="PASSWORD"
        >
    </div>

      <div class="formulario__campo">
        <label for="NIT" class="formulario__label">NIT</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Nit"
          id="NIT"
          name="NIT"
          value="<?php echo $usuario->NIT ?? '';?>"
        >
    </div>

      <div class="formulario__campo">
        <label for="DIRECCION_ENTREGA" class="formulario__label">Direccion de Entrega</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu direccion de entrega"
          id="DIRECCION_ENTREGA"
          name="DIRECCION_ENTREGA"
          value="<?php echo $usuario->DIRECCION_ENTREGA ?? '';?>"
        >
    </div>

      <div class="formulario__campo">
        <label for="FOTOGRAFIA_CLIENTE" class="formulario__label">Fotografía</label>
        <input type="file"
          class="formulario__input"
          placeholder="Tu Fotografía"
          id="FOTOGRAFIA_CLIENTE"
          name="FOTOGRAFIA_CLIENTE"
        >
    </div>

          <input type="submit" class="btn btn-primary formulario__submit" value="Crear Cuenta">

     </form>
   
     <div class="acciones">
        <a href="/login" class="acciones__enlace">¿Ya tienes cuenta? Iniciar Sesión</a>
        <a href="/olvide" class="acciones__enlace">¿Olvidaste tu Password?</a>
    </div>

    </main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
