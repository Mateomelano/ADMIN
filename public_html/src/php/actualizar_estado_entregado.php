<?php
include 'db.php';

$id = $_POST['id'];
$estado = $_POST['entregado'];

$sql = "UPDATE ventas SET entregado = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $estado, $id);

$response = [];

if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Estado actualizado';
} else {
    $response['success'] = false;
    $response['message'] = 'Error al actualizar';
}

echo json_encode($response);
?>
