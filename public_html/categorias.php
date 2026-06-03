<?php
session_start();
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
  header("Location: login.php");
  exit;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>MC Aromas Admin Page</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" type="image/jpeg"
    href="https://res.cloudinary.com/dzfzqzdcu/image/upload/v1743554383/ari6vwivcy0ndoeqpmmw.jpg">
  <link rel="stylesheet" href="build/css/app.css?v=<?php echo time(); ?>">
  <!-- JS de esta pantalla -->
  <script src="build/js/categorias.js?v=<?php echo time(); ?>" defer></script>
  <!-- FUENTE -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
  <aside class="sidebar">
    <nav>
      <img src="https://res.cloudinary.com/dzfzqzdcu/image/upload/v1743554383/ari6vwivcy0ndoeqpmmw.jpg" class="logo"
        alt="">
      <ul>
        <li><a href="index.php">Información</a></li>
        <li><a href="productos.php">Productos</a></li>
        <li><a href="stock.php">Stock</a></li>
        <li><a href="banners.php">Banners</a></li>
        <li><a href="pedidos.php">Pedidos</a></li>
        <li><a href="ventas.php">Ventas</a></li>
        <li><a href="categorias.php">Categorias</a></li>
        <li><a href="src/php/logout.php"><button id="logout-button">Cerrar Sesión</button></a></li>
      </ul>
    </nav>
  </aside>

  <div class="content">
    <h2>🗂️ Marcas y Categorías (Sidebar)</h2>
    <p class="muted">Tildá lo que querés mostrar en el sitio..</p>

    <!-- Form para crear una marca -->
    <div class="row" style="gap:8px; margin-bottom:14px">
      <input id="nueva-marca-nombre" placeholder="Nueva marca" />
      <label>Orden <input id="nueva-marca-orden" type="number" value="0" style="width:80px"></label>
      <label class="switch"><input type="checkbox" id="nueva-marca-visible" checked> Visible</label>
      <button class="btn" id="btn-agregar-marca">+ Agregar marca</button>
    </div>

    <div id="marcas-list" class="cards-container"></div>

    <div class="actions">
      <button id="guardar" class="btn-primary">Guardar cambios</button>
      <span id="status" class="muted"></span>
    </div>
  </div>
  </div>
</body>

</html>