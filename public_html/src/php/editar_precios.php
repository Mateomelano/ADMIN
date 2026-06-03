<?php
include("db.php");

$ids = $_POST['ids'];
$nuevaCategoria = isset($_POST['nuevaCategoria']) ? trim($_POST['nuevaCategoria']) : null;
$nuevoPrecio = isset($_POST['nuevoPrecio']) && $_POST['nuevoPrecio'] !== "" ? floatval($_POST['nuevoPrecio']) : null;
$nuevoPrecioMayorista = isset($_POST['nuevoPrecioMayorista']) && $_POST['nuevoPrecioMayorista'] !== "" ? floatval($_POST['nuevoPrecioMayorista']) : null;
$nuevoStock = isset($_POST['nuevoStock']) && $_POST['nuevoStock'] !== "" ? intval($_POST['nuevoStock']) : null;

if (!is_array($ids)) {
  $ids = explode(",", $ids);
}
$idsLimpios = array_map('intval', $ids);
$idList = implode(",", $idsLimpios);

// 🔧 Construcción dinámica de la query:
$campos = [];
$tipos = "";
$valores = [];

if ($nuevoPrecio !== null) {
  $campos[] = "precio = ?";
  $tipos .= "d";
  $valores[] = $nuevoPrecio;
}
if ($nuevoPrecioMayorista !== null) {
  $campos[] = "preciomayorista = ?";
  $tipos .= "d";
  $valores[] = $nuevoPrecioMayorista;
}
if (!empty($nuevaCategoria)) {
  $campos[] = "categoria = ?";
  $tipos .= "s";
  $valores[] = $nuevaCategoria;
}
if ($nuevoStock !== null) {
  $campos[] = "stock = ?";
  $tipos .= "i";
  $valores[] = $nuevoStock;
}

if (empty($campos)) {
  http_response_code(400);
  echo "No se seleccionó ningún campo válido para actualizar.";
  exit;
}

$sql = "UPDATE productos SET " . implode(", ", $campos) . " WHERE id IN ($idList)";
$stmt = $conn->prepare($sql);
$stmt->bind_param($tipos, ...$valores);

if ($stmt->execute()) {
  echo "OK";
} else {
  http_response_code(500);
  echo "Error al actualizar los productos";
}

$stmt->close();
$conn->close();
