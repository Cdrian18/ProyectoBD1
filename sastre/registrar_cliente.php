<?php
include_once "conexion.php";
include_once "sesion.php";

// Verificar si no hay una sesión de empleado iniciada
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

// Si se ha enviado el formulario, procesar los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $idCliente = $_POST['idCliente'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    // Preparar la consulta SQL para insertar en la tabla clientes
    $sql = "INSERT INTO clientes (id_Cliente, nombre, apellido, direccion) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $idCliente, $nombre, $apellido, $direccion);

    // Ejecutar la consulta SQL
    if ($stmt->execute()) {
        // Preparar la consulta SQL para insertar en la tabla telefono
        $sqlTelefono = "INSERT INTO telefono (Clientes_id_cliente, numero) VALUES (?, ?)";
        $stmtTelefono = $conn->prepare($sqlTelefono);
        $stmtTelefono->bind_param("is", $idCliente, $telefono);

        // Ejecutar la consulta SQL
        if ($stmtTelefono->execute()) {
            echo "Cliente y teléfono registrados con éxito.";
            // Redirige al usuario a clientes.php solo si la inserción fue exitosa
            header("Location: clientes.php");
            exit();
        } else {
            echo "Error al insertar el teléfono: " . $stmtTelefono->error;
        }
    } else {
        echo "Error al insertar el cliente: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/Mainstyles.css">
    <link rel="stylesheet" href="CSS/ComprasStyles.css">
    <link rel="stylesheet" href="CSS/EmpleadoStyle.css">
    <title>Registro de Cliente</title>
</head>

<body>

    <div class="header">
        <div class="logo">Sastrería</div>
    </div>

    <div class="container">
        <!-- Menú lateral -->
        <nav class="sidebar">
            <ul>
                <li><a href="main.php">Inicio</a></li>
                <li><a href="pedidos.php">Pedidos</a></li>
                <li><a href="ventas.php">Ventas</a></li>
                <li><a href="envios.php">Envíos</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="compras.php">Compras</a></li>
                <li><a href="empleados.php">Empleados</a></li>
                <li><a href="clientes.php">Clientes</a></li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-container">
            <h2>Registro de Cliente</h2>

            <!-- Aquí va el formulario de registro de clientes -->
            <form method="post" action="registrar_cliente.php">
                <label for="idCliente">ID del Cliente:</label><br>
                <input type="number" id="idCliente" name="idCliente"><br>
                <label for="nombre">Nombre:</label><br>
                <input type="text" id="nombre" name="nombre"><br>
                <label for="apellido">Apellido:</label><br>
                <input type="text" id="apellido" name="apellido"><br>
                <label for="direccion">Dirección:</label><br>
                <input type="text" id="direccion" name="direccion"><br>
                <label for="telefono">Teléfono:</label><br>
                <input type="tel" id="telefono" name="telefono"><br>
                <input type="submit" value="Registrar">
            </form>
        </div>

    </div>
</body>

</html>
