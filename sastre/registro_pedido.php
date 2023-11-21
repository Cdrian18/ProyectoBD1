<?php
include_once "conexion.php";
include_once "sesion.php";

// Verificar si no hay una sesión de empleado iniciada
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

// Obtener el nombre del empleado de la sesión
$nombreEmpleado = $_SESSION['nombreEmpleado'];

// Lógica para procesar el formulario de registro de pedido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $fecha_entrega = $_POST['fecha_entrega'];
    $estado = $_POST['estado'];
    $id_cliente = $_POST['id_cliente'];
    $nombre_cliente = $_POST['nombre_cliente'];
    $apellido_cliente = $_POST['apellido_cliente'];
    $telefono_cliente = $_POST['telefono_cliente'];
    $direccion_cliente = $_POST['direccion_cliente'];

    // Validar los datos según tus necesidades

    // Insertar el nuevo cliente o actualizar si ya existe
    $sql_insert_cliente = "INSERT INTO Clientes (id_Cliente, nombre, apellido, direccion) 
                       VALUES ('$id_cliente', '$nombre_cliente', '$apellido_cliente', '$direccion_cliente')
                       ON DUPLICATE KEY UPDATE nombre = '$nombre_cliente', apellido = '$apellido_cliente', direccion = '$direccion_cliente'";


    if ($conn->query($sql_insert_cliente) === TRUE) {
        // Obtener o actualizar el ID del cliente
        $sql_get_cliente_id = "SELECT id_Cliente FROM Clientes WHERE id_Cliente = '$id_cliente'";
        $result_cliente_id = $conn->query($sql_get_cliente_id);

        if ($result_cliente_id->num_rows > 0) {
            $row_cliente = $result_cliente_id->fetch_assoc();
            $id_cliente = $row_cliente['id_Cliente'];

            $sql_insert_telefono = "INSERT INTO Telefono (numero, Clientes_id_cliente) 
                                    VALUES ('$telefono_cliente', '$id_cliente')
                                    ON DUPLICATE KEY UPDATE numero = '$telefono_cliente'";

            $conn->query($sql_insert_telefono);

            // Insertar el nuevo pedido en la base de datos
            $sql_insert_pedido = "INSERT INTO Pedidos (fecha_entrega, estado, idCliente) 
                                 VALUES ('$fecha_entrega', '$estado', '$id_cliente')";

            if ($conn->query($sql_insert_pedido) === TRUE) {
                $id_nuevo_pedido = $conn->insert_id;

                // Obtener datos de la prenda
                $id_prenda = $_POST['prenda'];
                $cantidad = $_POST['cantidad'];
                $descripcion = $_POST['descripcion_prenda'];

                // Insertar el detalle del pedido en la base de datos
                $sql_insert_detalle_pedido = "INSERT INTO Detalles_Pedido (cantidad, Pedidos_id_Pedido, Prendas_id_Prenda, descripcion) 
                                 VALUES ('$cantidad', '$id_nuevo_pedido', '$id_prenda','$descripcion')";

                if ($conn->query($sql_insert_detalle_pedido) === TRUE) {
                } else {
                    echo "Error al insertar detalles del pedido: " . $conn->error;
                }


                // Insertar el registro en la tabla Trabajo_Empleado
                $sql_insert_trabajo = "INSERT INTO Trabajo_Empleado (Pedidos_id_pedido, Empleados_id_empleado)
                                      VALUES ('$id_nuevo_pedido', {$_SESSION['idEmpleado']})";

                $conn->query($sql_insert_trabajo);

                echo "Pedido registrado con éxito";
            } else {
                echo "Error al registrar el pedido: " . $conn->error;
            }
        } else {
            echo "Error al obtener el ID del cliente: " . $conn->error;
        }
    } else {
        echo "Error al insertar el cliente: " . $conn->error;
    }
}

// Cerrar la conexión
$conn->close();
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/rpStyles.css"> <!-- Asegúrate de tener el archivo de estilos correcto -->
    <title>Registrar Pedido</title>
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

                <label for="direccion_cliente">Dirección del Cliente:</label>
                <input type="text" id="direccion_cliente" name="direccion_cliente" required>

                <!-- Sección para añadir prendas al pedido -->
                <h3>Prendas</h3>
                <div class="prendas-section">
                    <!-- Dropdown para seleccionar la prenda -->
                    <label for="prenda">Prenda:</label>
                    <select id="prenda" name="prenda">
                        <option value="1">Camisa</option>
                        <option value="2">Pantalón</option>
                        <option value="3">Vestido</option>
                    </select>

                    <!-- Campo para la cantidad de prendas -->
                    <label for="cantidad">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" min="1" required>

                    <!-- Campo para la descripción de la prenda -->
                    <label for="descripcion_prenda">Descripción:</label>
                    <input type="text" id="descripcion_prenda" name="descripcion_prenda" required>
                </div>

                <!-- Botón para añadir más prendas -->
                <button type="button" id="addPrenda">Añadir Otra Prenda</button>

                <button type="submit">Registrar Pedido</button>
            </form>
        </div>
    </div>

    <script>
        // Script para añadir dinámicamente más campos de prendas
        document.getElementById('addPrenda').addEventListener('click', function() {
            var prendasSection = document.querySelector('.prendas-section');
            var nuevaPrenda = prendasSection.cloneNode(true);
            prendasSection.parentNode.appendChild(nuevaPrenda);
        });
    </script>

</body>

</html>