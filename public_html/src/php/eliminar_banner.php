<?php
include 'db.php'; // Tu conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $url = $_POST['url'];

    // Extraer el public_id de la URL de Cloudinary
    preg_match("/\/([^\/]+)\.[a-zA-Z]+$/", $url, $matches);
    $public_id = $matches[1];

    // Eliminar de Cloudinary usando cURL
    $cloudName = "dzfzqzdcu";
    $apiKey = "449917526627355";
    $apiSecret = "gCcKASs-9OD-9MpZX5ZwE885W_Q";

    $timestamp = time();
    $signature = sha1("public_id=$public_id&timestamp=$timestamp$apiSecret");

    $ch = curl_init("https://api.cloudinary.com/v1_1/$cloudName/image/destroy");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'public_id' => $public_id,
        'api_key' => $apiKey,
        'timestamp' => $timestamp,
        'signature' => $signature
    ]);
    
    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if ($result['result'] === 'ok') {
        // Eliminar de la base de datos
        $stmt = $conn->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar de Cloudinary']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
