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
  <!-- Estilos -->
  <link rel="stylesheet" href="build/css/app.css?v=<?php echo time(); ?>">
  <!-- JS -->
  <script src="build/js/index.js?v=<?php echo time(); ?>"></script>
  <!-- FUENTE LEXEND-->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet" />
</head>

<body>

  <div id="main-content">
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
          <li><a href="src/php/logout.php">
              <button id="logout-button">Cerrar Sesión</button>
            </a>
          </li>
        </ul>
      </nav>
    </aside>
    <main class="content">
      <section id="informacion" class="informacion-section">
        <h2>Información General</h2>
        <div class="info-cards" id="info-cards">
          <!-- Aquí se cargarán las tarjetas dinámicamente -->
        </div>
      </section>
    </main>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      fetch('src/php/get_info.php')
        .then(response => response.json())
        .then(data => {
          const infoCards = document.getElementById('info-cards');
          infoCards.innerHTML = `
              <div class="info-card">
                  <h3>Total de Productos</h3>
                  <p>${data.totalProductos}</p>
              </div>
              <div class="info-card">
                  <h3>Productos Habilitados</h3>
                  <p>${data.totalHabilitados}</p>
              </div>
              <div class="info-card">
                  <h3>Productos Deshabilitados</h3>
                  <p>${data.totalDeshabilitados}</p>
              </div>
              <div class="info-card">
                  <h3>Producto Más Caro</h3>
                  <p>${data.productoMasCaro.nombre} ($${parseFloat(data.productoMasCaro.precio).toFixed(2)})</p>
              </div>
              <div class="info-card">
                  <h3>Producto Más Barato</h3>
                  <p>${data.productoMasBarato.nombre} ($${parseFloat(data.productoMasBarato.precio).toFixed(2)})</p>
              </div>
              <div class="info-card">
                  <h3>Marcas con las que trabajamos</h3>
                  <p>${data.marcas.join(", ")}</p>
              </div>
            `;
        })
        .catch(error => console.error('Error al obtener la información:', error));
    });
  </script>





</body>

</html>