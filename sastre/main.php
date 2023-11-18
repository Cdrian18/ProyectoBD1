<?php
// Verificar si la sesión no está iniciada, redirigir al inicio de sesión
session_start();
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

// Puedes acceder a la variable de sesión 'nombreEmpleado'
$nombreEmpleado = $_SESSION['nombreEmpleado'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/Mainstyles.css">
    <title>Página Principal</title>
</head>
<body>

    <div class="header">
        <div class="logo">Sastrería</div>
    </div>

    <div class="container">
        <!-- Menú lateral -->
        <nav class="sidebar">
            <ul>
                <li><a href="pagina_principal.php">Inicio</a></li>
                <li><a href="pedidos.php">Pedidos</a></li>
                <li><a href="ventas.php">Ventas</a></li>
                <li><a href="envios.php">Envíos</a></li>
                <li><a href="perfil.php">Perfil</a></li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-content">
            <h1>Bienvenido, <?php echo $nombreEmpleado; ?></h1>
            <!-- Contenido de la sección principal -->
        </div>
    </div>

</body>
</html>
