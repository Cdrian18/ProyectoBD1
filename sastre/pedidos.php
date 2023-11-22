<?php
include_once "conexion.php";
include_once "sesion.php";

// Verificar si no hay una sesión de empleado iniciada
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

// Lógica para mostrar la lista de pedidos
$sql_lista_pedidos = "SELECT P.*, C.nombre AS nombre_cliente, C.apellido AS apellido_cliente, D.cantidad, D.descripcion, PR.nombre AS nombre_prenda
                     FROM Pedidos P
                     JOIN Trabajo_Empleado TE ON P.id_pedido = TE.Pedidos_id_pedido
                     JOIN Clientes C ON P.idCliente = C.id_Cliente
                     LEFT JOIN Detalles_Pedido D ON P.id_pedido = D.Pedidos_id_Pedido
                     LEFT JOIN Prendas PR ON D.Prendas_id_Prenda = PR.id_prenda
                     WHERE TE.Empleados_id_empleado = {$_SESSION['idEmpleado']}";

$result_lista_pedidos = $conn->query($sql_lista_pedidos);
if ($result_lista_pedidos === FALSE) {
    die("Error en la consulta SQL: " . $conn->error);
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
    <title>Lista de Pedidos</title>
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
            <h1>Bienvenido(a), <?php echo $nombreEmpleado; ?></h1>

            <h2>Lista de Pedidos</h2>
            <!-- Mostrar la lista de pedidos desde la base de datos -->
            <?php
            if ($result_lista_pedidos->num_rows > 0) {
                echo "<ul>";
                while ($row_pedido = $result_lista_pedidos->fetch_assoc()) {
                    echo "<li onclick='mostrarDetallesPedido(" . $row_pedido['id_pedido'] . ", \"" . $row_pedido['estado'] . "\", \"" . $row_pedido['fecha_entrega'] . "\", \"" . $row_pedido['nombre_cliente'] . "\", \"" . $row_pedido['apellido_cliente'] . "\", \"" . $row_pedido['cantidad'] . "\", \"" . $row_pedido['descripcion'] . "\", \"" . $row_pedido['nombre_prenda'] . "\")'>Pedido " . $row_pedido['id_pedido'] . " - Estado: " . $row_pedido['estado'] . " - Fecha de Entrega: " . $row_pedido['fecha_entrega'] . "</li>";
                }
                echo "</ul>";
            } else {
                echo "No hay pedidos disponibles.";
            }
            ?>

            <!-- Botón para añadir otro pedido -->
            <a href="registro_pedido.php" class="add-button">Añadir Pedido</a>
        </div>
    </div>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detalles del Pedido</h3>
            <p id="detallePedido"></p>
        </div>
    </div>

    <!-- Script para manejar la apertura y cierre del modal y mostrar detalles del pedido -->
    <script>
        var modal = document.getElementById("myModal");

        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };

        function mostrarDetallesPedido(idPedido, estado, fechaEntrega, nombreCliente, apellidoCliente, cantidad, descripcion, nombrePrenda) {
            var detallePedido = document.getElementById("detallePedido");
            detallePedido.innerHTML = "Pedido " + idPedido + "<br>" +
                "Estado: " + estado + "<br>" +
                "Fecha de Entrega: " + fechaEntrega + "<br>" +
                "Cliente: " + nombreCliente + " " + apellidoCliente + "<br>" +
                "Detalles: Cantidad: " + cantidad + ", Descripción: " + descripcion + ", Prenda: " + nombrePrenda;
            modal.style.display = "block";
        }

        document.getElementsByClassName("close")[0].onclick = function() {
            modal.style.display = "none";
        };
    </script>

</body>

</html>