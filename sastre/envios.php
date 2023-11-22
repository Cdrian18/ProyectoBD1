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
                <li><a href="compras.php">Compras</a></li>
                <li><a href="empleados.php">Empleados</a></li>
                <li><a href="clientes.php">Clientes</a></li>
                <li><a href="estadisticas.php">Estadísticas</a></li>
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
            $sql_pedidos = "SELECT P.id_pedido, P.estado, C.direccion
                FROM Pedidos P
                JOIN Clientes C ON P.idCliente = C.id_Cliente
                WHERE P.idCliente IN (SELECT idCliente FROM Trabajo_Empleado WHERE Empleados_id_empleado = '$idEmpleado')
                AND (P.estado = 'En proceso' OR P.estado = 'Pendiente')";
            $result_pedidos = $conn->query($sql_pedidos);

            if ($result_pedidos === FALSE) {
                die("Error en la consulta: " . $conn->error);
            }

            if ($result_pedidos->num_rows > 0) {
                echo "<ul class='envios-list'>";
                while ($row_pedido = $result_pedidos->fetch_assoc()) {
                    $idPedido = $row_pedido['id_pedido'];
                    $estadoPedido = $row_pedido['estado'];
                    $direccionCliente = $row_pedido['direccion'];

                    echo "<li class='envios-item'>";
                    echo "Pedido ID: $idPedido - Estado: $estadoPedido - Dirección Cliente: $direccionCliente";
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

            // Resto del código...

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