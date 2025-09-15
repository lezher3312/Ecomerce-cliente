<?php
require_once __DIR__ . '/../model/ProductoModel.php';

class CatalogoController {
    public function index() {
        $model = new ProductoModel();

        // filtros desde GET
        $categoria = $_GET['categoria'] ?? null;
        $min = $_GET['min'] ?? null;
        $max = $_GET['max'] ?? null;
        $orden = $_GET['orden'] ?? 'recientes';

        $categorias = $model->obtenerCategorias();
        $productos = $model->obtenerProductos($categoria, $min, $max, $orden);

        // pasar variables a la vista
        require __DIR__ . '/../views/paginas/catalogo.php';
    }
}
