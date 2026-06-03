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
    <script src="build/js/ventas.js?v=<?php echo time(); ?>" defer></script>
    <!-- FUENTE LEXEND-->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet" />
    <!-- CHART.JS-->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</head>

<body>


    <aside class="sidebar">
        <nav>
            <img src="https://res.cloudinary.com/dzfzqzdcu/image/upload/v1743554383/ari6vwivcy0ndoeqpmmw.jpg"
                class="logo" alt="">
            <ul>
                <li><a href="index.php">Información</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="stock.php">Stock</a></li>
                <li><a href="banners.php">Banners</a></li>
                <li><a href="pedidos.php">Pedidos</a></li>
                <li><a href="ventas.php">Ventas</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li><a href="usuarios.php">Usuarios</a></li>
                <li><a href="src/php/logout.php">
                        <button id="logout-button">Cerrar Sesión</button>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <div class="content">

        <section class="tabla-ventas">
            <h2>🧾 Ventas realizadas</h2>
            <table id="tabla-ventas" style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Total Mayorista</th>
                        <th>Entregado</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </section>

        <section class="kpis-ventas" style="display: flex; justify-content: center; gap: 20px; margin: 40px 0;">
            <div class="kpi-card" id="kpi-total-vendido"
                style="flex: 1; background: #f5f5f5; padding: 20px; border-radius: 10px; text-align: center;">
                <h3>Total Vendido</h3>
                <p style="font-size: 24px; font-weight: bold;">$0</p>
            </div>
            <div class="kpi-card" id="kpi-total-productos"
                style="flex: 1; background: #f5f5f5; padding: 20px; border-radius: 10px; text-align: center;">
                <h3>Productos Vendidos</h3>
                <p style="font-size: 24px; font-weight: bold;">0</p>
            </div>
            <div class="kpi-card" id="kpi-producto-top"
                style="flex: 1; background: #f5f5f5; padding: 20px; border-radius: 10px; text-align: center;">
                <h3>Producto más vendido</h3>
                <p style="font-size: 20px; font-weight: bold;">-</p>
            </div>
        </section>


        <section class="resumen-ventas" style="margin-bottom: 40px;">
            <h2>📊 Resumen de Ventas</h2>
            <div style="max-width: 800px; margin: 20px auto;">
                <canvas id="grafico-productos"></canvas>
            </div>
            <div style="max-width: 800px; margin: 40px auto 0;">
                <canvas id="grafico-evolucion"></canvas>
            </div>
        </section>
    </div>


</body>

</html>
