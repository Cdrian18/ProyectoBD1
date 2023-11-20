<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/rpStyles.css"> <!-- Asegúrate de tener el archivo de estilos correcto -->
    <title>Lista de Envíos</title>
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
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-content">

            <h2>Lista de Envíos</h2>

            <?php
            include_once "conexion.php";
            include_once "sesion.php";

            // Verificar si no hay una sesión de empleado iniciada
            if (!isset($_SESSION['idEmpleado'])) {
                header("Location: login.php");
                exit();
            }

            // Obtener los pedidos asignados al empleado con estado en proceso o pendiente
            $idEmpleado = $_SESSION['idEmpleado'];
            $sql_pedidos = "SELECT id_pedido, estado FROM Pedidos 
                            WHERE idCliente IN (SELECT idCliente FROM Trabajo_Empleado WHERE Empleados_id_empleado = '$idEmpleado')
                            AND (estado = 'En proceso' OR estado = 'Pendiente')";
            $result_pedidos = $conn->query($sql_pedidos);

            if ($result_pedidos->num_rows > 0) {
                echo "<ul class='envios-list'>";
                while ($row_pedido = $result_pedidos->fetch_assoc()) {
                    $idPedido = $row_pedido['id_pedido'];
                    $estadoPedido = $row_pedido['estado'];

                    echo "<li class='envios-item'>";
                    echo "Pedido ID: $idPedido - Estado: $estadoPedido";
                    echo "<form action='' method='post'>";
                    echo "<input type='hidden' name='idPedido' value='$idPedido'>";
                    echo "<button type='submit' name='asignarEnvio'>Asignar Envío</button>";
                    echo "</form>";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "No hay pedidos en proceso o pendientes asignados a este empleado.";
            }

            // Lógica para asignar envío al hacer clic en el botón
            if (isset($_POST['asignarEnvio'])) {
                $idPedidoAsignar = $_POST['idPedido'];

                // Actualizar el estado del pedido a 'Entregado'
                $sql_actualizar_estado = "UPDATE Pedidos SET estado = 'Entregado' WHERE id_pedido = '$idPedidoAsignar'";
                $conn->query($sql_actualizar_estado);

                // Generar envío y guardarlo en la tabla envios
                $sql_generar_envio = "INSERT INTO Envios (estado, Pedidos_id_pedido) VALUES ('Enviado', '$idPedidoAsignar')";
                $conn->query($sql_generar_envio);

                echo "Envío asignado con éxito.";
            }

            // Cerrar la conexión
            $conn->close();
            ?>

        </div>
    </div>

    <script>
        // Si tienes scripts adicionales, agrégalos aquí
    </script>

</body>

</html>
