<?php
require_once __DIR__ . '/../model/ProductoModel.php';

class DetalleController {
    public function show($id) {
        if (!$id) {
            http_response_code(400);
            echo "Producto no especificado";
            exit;
        }

        $model = new ProductoModel();
        $producto = $model->obtenerPorId($id);

        if (!$producto) {
            http_response_code(404);
            echo "Producto no encontrado";
            exit;
        }

        require __DIR__ . '/../views/paginas/detalle.php';
    }
}
