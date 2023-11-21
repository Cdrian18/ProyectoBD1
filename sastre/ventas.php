<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/ventasStyles.css"> <!-- Asegúrate de tener el archivo de estilos correcto -->
    <title>Registro de Ventas</title>
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
                <li><a href="estadisticas.php">Estadísticas</a></li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-content">

            <h2>Registro de Ventas</h2>

            <?php
            include_once "conexion.php";
            include_once "sesion.php";

            // Verificar si no hay una sesión de empleado iniciada
            if (!isset($_SESSION['idEmpleado'])) {
                header("Location: login.php");
                exit();
            }

            // Lógica para registrar la venta al hacer clic en el botón
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrarVenta'])) {
                $idPedidoVenta = $_POST['idPedido'];

                // Obtener la fecha actual
                $fechaActual = date('Y-m-d');

                // Verificar si la venta ya existe para evitar duplicados
                $sql_venta_existente = "SELECT * FROM Ventas WHERE Pedidos_id_pedido = '$idPedidoVenta'";
                $result_venta_existente = $conn->query($sql_venta_existente);

                if ($result_venta_existente->num_rows == 0) {
                    // Obtener los detalles del pedido
                    $sql_detalles_pedido = "SELECT cantidad, Prendas.precio
                                            FROM Detalles_Pedido
                                            INNER JOIN Prendas ON Detalles_Pedido.Prendas_id_prenda = Prendas.id_prenda
                                            WHERE Detalles_Pedido.Pedidos_id_pedido = '$idPedidoVenta'";
                    $result_detalles_pedido = $conn->query($sql_detalles_pedido);

                    // Verificar si la consulta fue exitosa
                    if ($result_detalles_pedido !== false) {
                        $totalVenta = 0;

                        // Calcular el total de la venta
                        while ($row_detalle_pedido = $result_detalles_pedido->fetch_assoc()) {
                            $cantidad = $row_detalle_pedido['cantidad'];
                            $precioUnitario = $row_detalle_pedido['precio'];
                            $totalVenta += $cantidad * $precioUnitario;
                        }

                        // Insertar la venta en la tabla Ventas
                        $sql_insert_venta = "INSERT INTO Ventas (fecha, total, Pedidos_id_pedido) 
                                             VALUES ('$fechaActual', '$totalVenta', '$idPedidoVenta')";
                        $conn->query($sql_insert_venta);

                        // Cambiar el estado del pedido a "Vendido"
                        $sql_update_estado_pedido = "UPDATE Pedidos SET estado = 'Vendido' WHERE id_pedido = '$idPedidoVenta'";
                        $conn->query($sql_update_estado_pedido);

                        echo "Venta registrada con éxito.";
                    } else {
                        echo "Error al obtener los detalles del pedido: " . $conn->error;
                    }
                } else {
                    echo "La venta para este pedido ya existe.";
                }
            }

            // Mostrar la tabla de pedidos
            echo "<h3>Tabla de Pedidos</h3>";

            // Obtener los pedidos con estado "Entregado"
            $sql_pedidos_entregados = "SELECT Pedidos.id_pedido, Clientes.nombre AS nombre_cliente, Clientes.apellido AS apellido_cliente, Detalles_Pedido.cantidad, Prendas.precio
                                        FROM Pedidos
                                        INNER JOIN Clientes ON Pedidos.idCliente = Clientes.id_cliente
                                        INNER JOIN Detalles_Pedido ON Pedidos.id_pedido = Detalles_Pedido.Pedidos_id_pedido
                                        INNER JOIN Prendas ON Detalles_Pedido.Prendas_id_prenda = Prendas.id_prenda
                                        WHERE Pedidos.estado = 'Entregado'";
            $result_pedidos_entregados = $conn->query($sql_pedidos_entregados);

            // Verificar si la consulta fue exitosa
            if ($result_pedidos_entregados !== false) {
                // Verificar si hay resultados
                if ($result_pedidos_entregados->num_rows > 0) {
                    echo "<table class='ventas-table'>";
                    echo "<tr><th>ID Pedido</th><th>Cliente</th><th>Cantidad</th><th>Precio Unitario</th><th>Total</th><th>Acciones</th></tr>";

                    while ($row_pedido_entregado = $result_pedidos_entregados->fetch_assoc()) {
                        $idPedido = $row_pedido_entregado['id_pedido'];
                        $nombreCliente = $row_pedido_entregado['nombre_cliente'] . ' ' . $row_pedido_entregado['apellido_cliente'];
                        $cantidad = $row_pedido_entregado['cantidad'];
                        $precioUnitario = $row_pedido_entregado['precio'];
                        $total = $cantidad * $precioUnitario;

                        echo "<tr>";
                        echo "<td>$idPedido</td><td>$nombreCliente</td><td>$cantidad</td><td>$precioUnitario</td><td>$total</td>";
                        echo "<td><form action='' method='post'>
                                <input type='hidden' name='idPedido' value='$idPedido'>
                                <button type='submit' name='registrarVenta'>Registrar Venta</button>
                              </form></td>";
                        echo "</tr>";
                    }

                    echo "</table>";
                } else {
                    echo "No hay pedidos entregados para registrar ventas.";
                }
            } else {
                echo "Error en la consulta de pedidos entregados: " . $conn->error;
            }

            // Mostrar la tabla de ventas
            echo "<h3>Tabla de Ventas</h3>";

            // Obtener las ventas registradas
            $sql_ventas = "SELECT * FROM Ventas";
            $result_ventas = $conn->query($sql_ventas);

            // Verificar si la consulta fue exitosa
            if ($result_ventas !== false) {
                // Verificar si hay resultados
                if ($result_ventas->num_rows > 0) {
                    echo "<table class='ventas-table'>";
                    echo "<tr><th>ID Venta</th><th>Fecha</th><th>Total</th><th>ID Pedido</th></tr>";

                    while ($row_venta = $result_ventas->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row_venta['id_venta'] . "</td>";
                        echo "<td>" . $row_venta['fecha'] . "</td>";
                        echo "<td>" . $row_venta['total'] . "</td>";
                        echo "<td>" . $row_venta['Pedidos_id_pedido'] . "</td>";
                        echo "</tr>";
                    }

                    echo "</table>";
                } else {
                    echo "No hay ventas registradas.";
                }
            } else {
                echo "Error en la consulta de ventas: " . $conn->error;
            }
            ?>

        </div>
    </div>

    <script>
        // Si tienes scripts adicionales, agrégalos aquí
    </script>

</body>

</html>