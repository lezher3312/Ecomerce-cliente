<?php
class AuthController {
  public function index(){

    require __DIR__ . '/../views/auth/index.php';
  }
  public function registro(){

    require __DIR__ . '/../views/auth/registro.php';
  }
}
