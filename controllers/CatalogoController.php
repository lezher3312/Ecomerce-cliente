<?php
require_once __DIR__ . '/../model/ProductoModel.php';

class CatalogoController {
    private $model;

    public function __construct() {
        $this->model = new ProductoModel();
    }

    // Catálogo general con filtros
    public function index() {
        $categoria = $_GET['categoria'] ?? null;
        $min       = $_GET['min'] ?? null;
        $max       = $_GET['max'] ?? null;
        $orden     = $_GET['orden'] ?? 'recientes';

        $categorias = $this->model->obtenerCategorias();
        $productos  = $this->model->obtenerProductos($categoria, $min, $max, $orden);

        require __DIR__ . '/../views/paginas/catalogo.php';
    }

    // ====================
    // Novedades (últimos 5 días)
    // ====================
    public function novedades() {
        $productos = $this->model->obtenerNovedades();
        require __DIR__ . '/../views/paginas/novedades.php';
    }

    // ====================
    // Más vendidos
    // ====================
    public function masVendidos() {
        $productos = $this->model->obtenerMasVendidos();
        require __DIR__ . '/../views/paginas/mas-vendidos.php';
    }

    // ====================
    // Ofertas
    // ====================
    public function ofertas() {
        $productos = $this->model->obtenerOfertas();
        require __DIR__ . '/../views/paginas/ofertas.php';
    }
}
