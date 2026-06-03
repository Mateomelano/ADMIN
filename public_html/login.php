<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - MC Aromas</title>

    <!-- Fuente Lexend -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg"
        href="https://res.cloudinary.com/dzfzqzdcu/image/upload/v1743554383/ari6vwivcy0ndoeqpmmw.jpg">
    <link rel="stylesheet" href="build/css/app.css?v=<?php echo time(); ?>">
</head>

<body class="login-body">
    <div class="login-container">
        <!-- Logo -->
        <img src="https://res.cloudinary.com/dzfzqzdcu/image/upload/v1743554383/ari6vwivcy0ndoeqpmmw.jpg" class="logo"
            alt="MC Aromas Logo">

        <h2>Iniciar Sesión</h2>
        <p>Ingresa tus credenciales</p>

        <!-- Línea decorativa -->
        <div class="divider"><span>-</span></div>

        <form id="login-form">
            <input type="text" id="username" class="input-login" placeholder="Usuario" required>
            <input type="password" id="password" class="input-login" placeholder="Contraseña" required>
            <button type="submit" class="button-login">Login</button>
            <p id="error-message"></p>
        </form>
    </div>
    <script src="build/js/login.js?v=<?php echo time(); ?>" defer></script>
</body>

</html