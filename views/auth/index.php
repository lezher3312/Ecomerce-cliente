<?php require __DIR__ . '/../layouts/head.php'; ?>
   <link rel="stylesheet" href="<?= asset('css/auth.css') ?>">
   <link rel="stylesheet" href="<?= asset('css/formulario.css') ?>">

<?php require __DIR__ . '/../layouts/header.php'; ?>
<main class="auth">
  <h2 class="auth__heading"> Iniciar Sesión</h2>
  <p class="auth__texto">Completa los campos para iniciar sesión:</p>

<form method="POST" action="/login" class="formulario">

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
        <label for="password" class="formulario__label">Password</label>
        <input type="password"
          class="formulario__input"
          placeholder="Tu Password"
          id="password"
          name="password"
        >
    </div>
      <input type="submit" class="btn btn-primary formulario__submit" value="Iniciar Sesión">

    </form>

    <div class="acciones">
        <a href="/registro" class="acciones__enlace">¿Aún no tienes una cuenta? Obtener una</a>
        <a href="/olvide" class="acciones__enlace">¿Olvidaste tu Password?</a>
    </div>

</main>



<?php require __DIR__ . '/../layouts/footer.php'; ?>
