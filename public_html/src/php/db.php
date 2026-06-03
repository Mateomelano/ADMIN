<?php

$host = "localhost"; 
$user = "u617835785_root"; // Usuario de MySQL (por defecto en XAMPP)
$password = "Merceriachela1"; // Deja vacío si no configuraste una contraseña
$dbname = "u617835785_mc_aromas"; 

$conn = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>