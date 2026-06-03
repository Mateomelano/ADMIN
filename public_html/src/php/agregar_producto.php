<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $categoria = $_POST['categoria'];
    $marca = $_POST['marca'];
    $precio = $_POST['precio'];
    $preciomayorista = isset($_POST['precioMayorista']) ? floatval($_POST['precioMayorista']) : 0;
    $habilitado = $_POST['habilitado'];
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0; // ✅ Nuevo campo

    // Subida de imagen al servidor
    $rutaImagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $nombreArchivo = uniqid() . "_" . basename($_FILES["imagen"]["name"]);
        $directorioDestino = "../../uploads/" . $nombreArchivo;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $directorioDestino)) {
            // Guardar la URL completa en la base de datos
            $rutaImagen = "https://purple-sheep-451734.hostingersite.com/uploads/" . $nombreArchivo;
        }
    }

    $sql = "INSERT INTO productos (nombre, descripcion, categoria, marca, precio, preciomayorista, habilitado, imagen, stock) 
            VALUES ('$nombre', '$descripcion', '$categoria', '$marca', '$precio', '$preciomayorista', '$habilitado', '$rutaImagen', '$stock')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Producto agregado"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
?>
