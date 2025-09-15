<?php
require_once __DIR__ . '/../model/ProductoModel.php';

class InicioController {
    private $model;

    public function __construct() {
        $this->model = new ProductoModel();
    }

    public function index(){
        // Obtener categorías
        $categorias = $this->model->obtenerCategorias();

        // Obtener máximo 3 productos para cada bloque
        $masVendidos = $this->model->obtenerMasVendidos(3);
        $novedades   = $this->model->obtenerNovedades(3);
        $ofertas     = $this->model->obtenerOfertas(3);

        // Pasar los datos a la vista
        require __DIR__ . '/../views/paginas/inicio.php';
    }
}
