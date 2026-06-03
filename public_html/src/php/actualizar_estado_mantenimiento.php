<?php
include 'db.php'; // Conexión a la base de datosinclude 'db.php'; // Conexión a la base de datos

$datos = json_decode(file_get_contents("php://input"), true);
$nuevoEstado = isset($datos["activado"]) ? (int)$datos["activado"] : 0;

$sql = "UPDATE mantenimiento SET activado = $nuevoEstado WHERE id = 1";
$conn->query($sql);

echo json_encode(["success" => $conn->affected_rows > 0]);

$conn->close();
?>
