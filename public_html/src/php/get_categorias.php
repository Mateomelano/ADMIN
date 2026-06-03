<?php
include 'db.php'; // Ajusta la ruta si es necesario

$sql = "SELECT DISTINCT categoria FROM productos";
$result = $conn->query($sql);

$categoria = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categoria[] = $row['categoria'];
    }
}

$conn->close();

echo json_encode($categoria);
