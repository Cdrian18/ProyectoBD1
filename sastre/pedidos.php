<?php
include_once "conexion.php";
include_once "sesion.php";
// Verificar si no hay una sesión de empleado iniciada
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se ha enviado el formulario de registro de pedido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $fecha_entrega = $_POST['fecha_entrega'];
    $estado = $_POST['estado'];
    $id_cliente = $_POST['id_cliente'];
    $nombre_cliente = $_POST['nombre_cliente'];
    $apellido_cliente = $_POST['apellido_cliente'];
    $telefono_cliente = $_POST['telefono_cliente'];

    // Validar los datos según tus necesidades

    // Insertar el nuevo cliente si no existe
    $sql_cliente = "INSERT INTO Clientes (id_Cliente, nombre, apellido, telefono) 
                    VALUES ('$id_cliente', '$nombre_cliente', '$apellido_cliente', '$telefono_cliente')
                    ON DUPLICATE KEY UPDATE nombre = '$nombre_cliente', apellido = '$apellido_cliente', telefono = '$telefono_cliente'";
    
    $conn->query($sql_cliente);

    // Obtener el ID del cliente
    $sql_get_cliente_id = "SELECT id_Cliente FROM Clientes WHERE id_Cliente = '$id_cliente'";
    $result_cliente_id = $conn->query($sql_get_cliente_id);

    if ($result_cliente_id->num_rows > 0) {
        $row_cliente = $result_cliente_id->fetch_assoc();
        $id_cliente = $row_cliente['id_Cliente'];
    }

    // Insertar el nuevo pedido en la base de datos
    $sql_pedido = "INSERT INTO Pedidos (fecha_entrega, estado, idCliente) 
                   VALUES ('$fecha_entrega', '$estado', '$id_cliente')";
    
    if ($conn->query($sql_pedido) === TRUE) {
        echo "Pedido registrado con éxito";
    } else {
        echo "Error al registrar el pedido: " . $conn->error;
    }
}

// Cerrar la conexión a la base de datos
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/PedidosStyles.css">
    <title>Registrar Pedido</title>
</head>

<body>

    <div class="header">
        <div class="logo">Nombre de la Sastrería</div>
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
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-content">
            <h1><?php echo $nombreEmpleado; ?></h1>

            <h2>Registrar Pedido</h2>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <label for="fecha_entrega">Fecha de Entrega:</label>
                <input type="date" id="fecha_entrega" name="fecha_entrega" required>

                <label for="estado">Estado:</label>
                <select id="estado" name="estado">
                    <option value="Pendiente">Pendiente</option>
                    <option value="En proceso">En proceso</option>
                    <option value="Entregado">Entregado</option>
                </select>

                <!-- Agregar campos para el cliente -->
                <label for="id_cliente">ID del Cliente:</label>
                <input type="text" id="id_cliente" name="id_cliente" required>

                <label for="nombre_cliente">Nombre del Cliente:</label>
                <input type="text" id="nombre_cliente" name="nombre_cliente" required>

                <label for="apellido_cliente">Apellido del Cliente:</label>
                <input type="text" id="apellido_cliente" name="apellido_cliente" required>

                <label for="telefono_cliente">Teléfono del Cliente:</label>
                <input type="text" id="telefono_cliente" name="telefono_cliente" required>

                <button type="submit">Registrar Pedido</button>
            </form>
        </div>
    </div>

</body>

</html>
