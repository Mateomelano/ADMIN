<?php
include 'db.php';

// Obtener productos
$result = $conn->query("SELECT id, nombre, stock FROM productos");
$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

// Obtener ventas
$ventasQuery = $conn->query("SELECT productos FROM ventas");
$ventasPorProducto = [];

while ($venta = $ventasQuery->fetch_assoc()) {
    $items = json_decode($venta['productos'], true);
    foreach ($items as $item) {
        $id = $item['id'];
        $ventasPorProducto[$id] = ($ventasPorProducto[$id] ?? 0) + $item['cantidad'];
    }
}

echo json_encode([
  "productos" => $productos,
  "ventas" => $ventasPorProducto
]);
?>
