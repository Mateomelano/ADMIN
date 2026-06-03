<?php
include 'db.php';

// Aseguramos UTF-8 para que nombres/productos con tildes no rompan el json_encode.
$conn->set_charset("utf8mb4");

$result = $conn->query("SELECT * FROM ventas ORDER BY fecha DESC");

$ventas = [];

while ($row = $result->fetch_assoc()) {
  $ventas[] = $row;
}

echo json_encode($ventas, JSON_UNESCAPED_UNICODE);
?>
