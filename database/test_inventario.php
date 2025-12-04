<?php
// test_inventario.php - Archivo para probar las funciones de inventario

require_once 'database/inventario_crud.php';

echo "<h1>Prueba del Sistema de Inventario</h1>";

$inventario = new InventarioCRUD();

// Probar obtener todos los productos
echo "<h2>Todos los Productos</h2>";
$productos = $inventario->obtenerProductos();
if ($productos && count($productos) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Categoría</th><th>Marca</th></tr>";
    foreach ($productos as $producto) {
        echo "<tr>";
        echo "<td>" . $producto['id_producto'] . "</td>";
        echo "<td>" . $producto['codigo_barras'] . "</td>";
        echo "<td>" . $producto['nombre'] . "</td>";
        echo "<td>$" . number_format($producto['precio'], 2) . "</td>";
        echo "<td>" . $producto['stock'] . "</td>";
        echo "<td>" . ($producto['categoria'] ?? 'N/A') . "</td>";
        echo "<td>" . ($producto['marca'] ?? 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay productos en el inventario</p>";
}

// Probar buscar producto por código
echo "<h2>Buscar Producto por Código</h2>";
$codigo_prueba = '1234567890123';
$producto = $inventario->buscarProductoPorCodigo($codigo_prueba);
if ($producto) {
    echo "<p><strong>Producto encontrado:</strong></p>";
    echo "<p>Nombre: " . $producto['nombre'] . "</p>";
    echo "<p>Precio: $" . number_format($producto['precio'], 2) . "</p>";
    echo "<p>Stock: " . $producto['stock'] . "</p>";
} else {
    echo "<p>No se encontró producto con código: $codigo_prueba</p>";
}

// Probar obtener categorías
echo "<h2>Categorías Disponibles</h2>";
$categorias = $inventario->obtenerCategorias();
if ($categorias) {
    echo "<ul>";
    foreach ($categorias as $categoria) {
        echo "<li>ID: " . $categoria['id_categoria'] . " - " . $categoria['nombre'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Error al obtener categorías</p>";
}

// Probar obtener marcas
echo "<h2>Marcas Disponibles</h2>";
$marcas = $inventario->obtenerMarcas();
if ($marcas) {
    echo "<ul>";
    foreach ($marcas as $marca) {
        echo "<li>ID: " . $marca['id_marca'] . " - " . $marca['nombre_marca'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Error al obtener marcas</p>";
}

// Probar productos con stock bajo
echo "<h2>Productos con Stock Bajo</h2>";
$stock_bajo = $inventario->obtenerProductosStockBajo();
if ($stock_bajo && count($stock_bajo) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Código</th><th>Nombre</th><th>Stock Actual</th><th>Stock Mínimo</th></tr>";
    foreach ($stock_bajo as $producto) {
        echo "<tr style='background-color: #ffebee;'>";
        echo "<td>" . $producto['codigo_barras'] . "</td>";
        echo "<td>" . $producto['nombre'] . "</td>";
        echo "<td>" . $producto['stock'] . "</td>";
        echo "<td>" . $producto['stock_minimo'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No hay productos con stock bajo</p>";
}

// Probar estadísticas del inventario
echo "<h2>Estadísticas del Inventario</h2>";
$stats = $inventario->obtenerEstadisticasInventario();
if ($stats) {
    echo "<p>Total de Productos: " . $stats['total_productos'] . "</p>";
    echo "<p>Valor Total del Inventario: $" . number_format($stats['valor_total'], 2) . "</p>";
    echo "<p>Productos con Stock Bajo: " . $stats['productos_stock_bajo'] . "</p>";
    echo "<p>Productos Sin Stock: " . $stats['productos_sin_stock'] . "</p>";
} else {
    echo "<p>Error al obtener estadísticas</p>";
}

echo "<br><a href='Inventario.html'>Ir al módulo de inventario</a>";
echo "<br><a href='test_reportes.php'>Ir al test de reportes</a>";
?>