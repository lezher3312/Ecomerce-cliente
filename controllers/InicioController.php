<?php
class InicioController {
  public function index(){
    $categorias = []; $masVendidos = []; $novedades = []; $ofertas = [];
    require __DIR__ . '/../views/paginas/inicio.php';

  }
}
