<?php
include 'db.php';

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // 1. Obtener la URL de la imagen desde la base de datos
    $sql = "SELECT imagen FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($imagenUrl);
    $stmt->fetch();
    $stmt->close();

    // 2. Eliminar la imagen del servidor si existe
    if ($imagenUrl) {
        $rutaImagenServidor = realpath(__DIR__ . '/../../' . $imagenUrl);
        if ($rutaImagenServidor && file_exists($rutaImagenServidor)) {
            unlink($rutaImagenServidor);
        }
    }

    // 3. Eliminar el producto de la base de datos
    $sql = "DELETE FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Producto eliminado correctamente";
    } else {
        echo "Error al eliminar el producto";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "ID no recibido";
}

