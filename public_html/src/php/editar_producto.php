<?php
include 'db.php'; // Ajusta la ruta si es necesario

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;

    if (!$id) {
        echo json_encode(["success" => false, "error" => "ID no válido"]);
        exit;
    }

    $habilitado = isset($_POST['habilitado']) ? intval($_POST['habilitado']) : null;

    // Verificar si se envía una nueva imagen
    $imagenUrl = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        // Subir imagen al servidor
        $nombreArchivo = uniqid() . "_" . basename($_FILES["imagen"]["name"]);
        $directorioDestino = "../../uploads/" . $nombreArchivo;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $directorioDestino)) {
            $imagenUrl = "https://purple-sheep-451734.hostingersite.com/uploads/" . $nombreArchivo;
        } else {
            echo json_encode(["success" => false, "error" => "Error al subir la imagen."]);
            exit;
        }
    } else {
        // Si no hay nueva imagen, mantener la URL anterior
        $imagenUrl = isset($_POST['imagenUrlActual']) ? $_POST['imagenUrlActual'] : null;
    }

    // Preparar consulta de actualización
    $nombre = isset($_POST['nombre']) ? $conn->real_escape_string($_POST['nombre']) : null;
    $descripcion = isset($_POST['descripcion']) ? $conn->real_escape_string($_POST['descripcion']) : null;
    $categoria = isset($_POST['categoria']) ? $conn->real_escape_string($_POST['categoria']) : null;
    $marca = isset($_POST['marca']) ? $conn->real_escape_string($_POST['marca']) : null;
    $precio = isset($_POST['precio']) ? floatval($_POST['precio']) : null;
    $preciomayorista = isset($_POST['preciomayorista']) ? floatval($_POST['preciomayorista']) : null;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0; // Nuevo campo

    $stmt = $conn->prepare("UPDATE productos 
        SET nombre = ?, descripcion = ?, categoria = ?, marca = ?, precio = ?, preciomayorista = ?, habilitado = ?, imagen = ?, stock = ?
        WHERE id = ?");

    $stmt->bind_param("ssssddisii", $nombre, $descripcion, $categoria, $marca, $precio, $preciomayorista, $habilitado, $imagenUrl, $stock, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Producto actualizado correctamente."]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido"]);
}

$conn->close();
?>

