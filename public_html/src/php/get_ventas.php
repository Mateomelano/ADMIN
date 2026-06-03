<?php
include 'db.php';

$result = $conn->query("SELECT * FROM ventas ORDER BY fecha DESC");

$ventas = [];

while ($row = $result->fetch_assoc()) {
  $ventas[] = $row;
}

echo json_encode($ventas, JSON_UNESCAPED_UNICODE);
?>
