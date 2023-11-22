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

    // Preparar la consulta SQL para actualizar la tabla clientes
    $sql = "UPDATE clientes SET nombre = ?, apellido = ?, direccion = ? WHERE id_Cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nombre, $apellido, $direccion, $idCliente);

    // Ejecutar la consulta SQL
    if ($stmt->execute()) {
        // Preparar la consulta SQL para actualizar la tabla telefono
        $sqlTelefono = "UPDATE telefono SET numero = ? WHERE Clientes_id_cliente = ?";
        $stmtTelefono = $conn->prepare($sqlTelefono);
        $stmtTelefono->bind_param("si", $telefono, $idCliente);

        // Ejecutar la consulta SQL
        if ($stmtTelefono->execute()) {
            echo "Cliente y teléfono actualizados con éxito.";
            // Redirige al usuario a clientes.php solo si la actualización fue exitosa
            header("Location: clientes.php");
            exit();
        } else {
            echo "Error al actualizar el teléfono: " . $stmtTelefono->error;
        }
    } else {
        echo "Error al actualizar el cliente: " . $stmt->error;
    }
} else {
    // Si el formulario no se ha enviado, obtener los datos del cliente existente
    $idCliente = $_GET['id'];

    // Preparar la consulta SQL para obtener los datos del cliente
    $sql = "SELECT c.nombre, c.apellido, c.direccion, t.numero FROM clientes c JOIN telefono t ON c.id_Cliente = t.Clientes_id_cliente WHERE c.id_Cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idCliente);
    // Ejecutar la consulta SQL
    if ($stmt->execute()) {
        // Obtener los resultados de la consulta
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Obtener los datos del cliente
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
            $apellido = $row['apellido'];
            $direccion = $row['direccion'];
            $telefono = $row['numero'];
        } else {
            echo "No se encontró el cliente.";
            exit();
        }
    } else {
        echo "Error al obtener los datos del cliente: " . $stmt->error;
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
    <title>Editar Cliente</title>
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
                <li><a href="compras.php">Compras</a></li>
                <li><a href="empleados.php">Empleados</a></li>
                <li><a href="clientes.php">Clientes</a></li>
                <li><a href="estadisticas.php">Estadísticas</a></li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-container">
            <h2>Editar Cliente</h2>

            <form method="post" action="editar_cliente.php">
                <label for="idCliente">ID del Cliente:</label><br>
                <p id="idCliente"><?php echo $idCliente; ?></p>
                <input type="hidden" name="idCliente" value="<?php echo $idCliente; ?>">
                <label for="nombre">Nombre:</label><br>
                <input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"><br>
                <label for="apellido">Apellido:</label><br>
                <input type="text" id="apellido" name="apellido" value="<?php echo $apellido; ?>"><br>
                <label for="direccion">Dirección:</label><br>
                <input type="text" id="direccion" name="direccion" value="<?php echo $direccion; ?>"><br>
                <label for="telefono">Teléfono:</label><br>
                <input type="tel" id="telefono" name="telefono" value="<?php echo $telefono; ?>"><br>
                <input type="submit" value="Actualizar">
            </form>
        </div>

    </div>
</body>

</html>