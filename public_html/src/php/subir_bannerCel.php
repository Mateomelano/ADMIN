<?php
include 'db.php';

$cloud_name = "dzfzqzdcu";
$upload_preset = "Banners";

if (isset($_FILES['imagen'])) {
    $imagen = $_FILES['imagen']['tmp_name'];
    $nombre = $_FILES['imagen']['name'];

    $data = [
        'file' => new CURLFile($imagen),
        'upload_preset' => $upload_preset
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/upload");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);

    if (isset($json['secure_url'])) {
        $url = $json['secure_url'];

        $stmt = $conn->prepare("INSERT INTO bannersCel (url) VALUES (?)");
        $stmt->bind_param("s", $url);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Imagen subida exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir a Cloudinary']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No se ha recibido ninguna imagen']);
}
