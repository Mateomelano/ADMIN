<?php
include 'db.php';

// Aseguramos UTF-8 para que nombres con tildes/ñ no rompan el json_encode.
$conn->set_charset("utf8mb4");

// Se seleccionan SOLO las columnas seguras: nunca la contraseña ni los tokens.
$result = $conn->query("SELECT id, nombre, apellido, dni, telefono, mail FROM usuariosWeb ORDER BY id DESC");

$usuarios = [];

while ($row = $result->fetch_assoc()) {
  $usuarios[] = $row;
}

echo json_encode($usuarios, JSON_UNESCAPED_UNICODE);
?>
