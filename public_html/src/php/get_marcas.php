<?php
include 'db.php'; // Ajusta la ruta si es necesario

$sql = "SELECT DISTINCT marca FROM productos";
$result = $conn->query($sql);

$marcas = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $marcas[] = $row['marca'];
    }
}

$conn->close();

echo json_encode($marcas);
