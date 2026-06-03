<?php
include 'db.php';

$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$habilitadoFiltro = isset($_GET['habilitado']) && $_GET['habilitado'] !== '' ? (int) $_GET['habilitado'] : null;
$orden = isset($_GET['orden']) ? $_GET['orden'] : null;
$columna = isset($_GET['columna']) && in_array($_GET['columna'], ['precio', 'preciomayorista', 'stock']) ? $_GET['columna'] : null; // Ahora también permite ordenar por stock

$sql = "SELECT id, nombre, categoria, marca, precio, preciomayorista, habilitado, descripcion, imagen, stock FROM productos";
$filtros = [];

// 🟢 Filtro de búsqueda
if (!empty($q)) {
    $filtros[] = "(id LIKE '%$q%' OR 
                   nombre LIKE '%$q%' OR
                   descripcion LIKE '%$q%' OR 
                   categoria LIKE '%$q%' OR 
                   marca LIKE '%$q%' OR 
                   imagen LIKE '%$q%' OR
                   preciomayorista LIKE '%$q%' OR 
                   precio LIKE '%$q%' OR
                   stock LIKE '%$q%')"; // Ahora busca también en stock
}

// 🟢 Filtro de habilitado
if ($habilitadoFiltro === 1) {
    $filtros[] = "habilitado = 1";
} elseif ($habilitadoFiltro === 0) {
    $filtros[] = "habilitado = 0";
}

// Si hay filtros, agrégales a la consulta
if (!empty($filtros)) {
    $sql .= " WHERE " . implode(" AND ", $filtros);
}

// 🟢 Ordenar correctamente
if ($columna && ($orden === "asc" || $orden === "desc")) {
    $sql .= " ORDER BY $columna $orden";
}

$result = $conn->query($sql);
$productos = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}

echo json_encode($productos);
?>
