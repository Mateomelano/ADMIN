<?php
include 'db.php'; // Conexión a la base de datos

$sql = "SELECT activado FROM mantenimiento WHERE id = 1";
$resultado = $conn->query($sql);

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    echo json_encode(["activado" => $fila["activado"]]);
} else {
    echo json_encode(["activado" => 0]);
}

$conn->close();
?>
