<?php
session_start();
include 'db.php';

header('Content-Type: application/json'); // Respuesta JSON

$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

$sql = "SELECT password FROM usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $fila = $resultado->fetch_assoc();
    if (password_verify($password, $fila['password'])) {
        $_SESSION['loggedIn'] = true;
        echo json_encode(['success' => true]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
exit;
?>