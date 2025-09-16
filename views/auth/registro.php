<?php require __DIR__ . '/../layouts/head.php'; ?>
<link rel="stylesheet" href="<?= asset('css/registro.css') ?>">
<link rel="stylesheet" href="<?= asset('css/formulario.css') ?>">
<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="registro">
     <h2 class="registro__heading"> Registrar Nueva Cuenta</h2>
     <p class="registro__texto">Completa los campos para crear una nueva cuenta:</p>
    
     <form method="POST" action="/login" class="formulario">
         
    <div class="formulario__campo">
        <label for="nombre" class="formulario__label">Nombre</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Nombre Completo"
          id="nombre"
          name="nombre"
        >
    </div>

     <div class="formulario__campo">
        <label for="telefono" class="formulario__label">Telefono</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu telefono"
          id="telefono"
          name="telefono"
        >
    </div>

     <div class="formulario__campo">
        <label for="direccion" class="formulario__label">Dirección</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Dirección"
          id="direccion"
          name="direccion"
        >
    </div>

     <div class="formulario__campo">
        <label for="ubicacion" class="formulario__label">Ubicación</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Ubicación"
          id="ubicacion"
          name="ubicacion"
        >
    </div>

     <div class="formulario__campo">
        <label for="longitud" class="formulario__label">Longitud</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Logitud"
          id="longitud"
          name="longitud"
        >
    </div>

     <div class="formulario__campo">
        <label for="email" class="formulario__label">Email</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Email"
          id="email"
          name="email"
        >
    </div>

     <div class="formulario__campo">
        <label for="usuario_pagina" class="formulario__label">Usuario de la Pagina</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Usuario de la Pagina"
          id="usuario_pagina"
          name="usuario_pagina"
        >
    </div>

    
     <div class="formulario__campo">
        <label for="password" class="formulario__label">Password de la Pagina</label>
        <input type="password"
          class="formulario__input"
          placeholder="Tu Password"
          id="password"
          name="password"
        >
    </div>

    
     <div class="formulario__campo">
        <label for="password" class="formulario__label">Repetir Password de la Pagina</label>
        <input type="password"
          class="formulario__input"
          placeholder="Repetir tu Password"
          id="password"
          name="password"
        >
    </div>

      <div class="formulario__campo">
        <label for="nit" class="formulario__label">NIT</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu Nit"
          id="nit"
          name="nit"
        >
    </div>

      <div class="formulario__campo">
        <label for="direccion_entrega" class="formulario__label">Direccion de Entrega</label>
        <input type="text"
          class="formulario__input"
          placeholder="Tu direccion de entrega"
          id="direccion_entrega"
          name="direccion_entrega"
        >
    </div>

      <div class="formulario__campo">
        <label for="fotografia_cliente" class="formulario__label">Fotografía</label>
        <input type="file"
          class="formulario__input"
          placeholder="Tu Fotografía"
          id="fotografia_cliente"
          name="fotografia_cliente"
        >
    </div>

          <input type="submit" class="btn btn-primary formulario__submit" value="Crear Cuenta">

     </form>
   
     <div class="acciones">
        <a href="/registro" class="acciones__enlace">¿Ya tienes cuenta? Iniciar Sesión</a>
        <a href="/olvide" class="acciones__enlace">¿Olvidaste tu Password?</a>
    </div>

    </main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
