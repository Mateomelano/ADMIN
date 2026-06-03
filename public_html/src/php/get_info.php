<?php
include 'db.php'; // Conexión a la base de datos

$marcas = $conn->query("SELECT DISTINCT marca FROM productos");
$listaMarcas = [];
if ($marcas) {
    while ($fila = $marcas->fetch_assoc()) {
        $listaMarcas[] = $fila['marca'];
    }
}

$info = [
    "totalProductos" => $conn->query("SELECT COUNT(*) AS total FROM productos")->fetch_assoc()['total'],
    "totalHabilitados" => $conn->query("SELECT COUNT(*) AS total FROM productos WHERE habilitado = 1")->fetch_assoc()['total'],
    "totalDeshabilitados" => $conn->query("SELECT COUNT(*) AS total FROM productos WHERE habilitado = 0")->fetch_assoc()['total'],
    "productoMasCaro" => $conn->query("SELECT nombre, precio FROM productos ORDER BY precio DESC LIMIT 1")->fetch_assoc(),
    "productoMasBarato" => $conn->query("SELECT nombre, precio FROM productos WHERE precio > 0 ORDER BY precio ASC LIMIT 1")->fetch_assoc(),
    "marcas" => $listaMarcas
];

header("Content-Type: application/json");
echo json_encode($info);
?>
