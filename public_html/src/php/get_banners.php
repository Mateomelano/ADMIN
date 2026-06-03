<?php
include 'db.php';

$result = $conn->query("SELECT * FROM banners");
$imagenes = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $imagenes[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($imagenes);
