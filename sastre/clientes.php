<?php
include_once "conexion.php";
include_once "sesion.php";

// Verificar si no hay una sesión de empleado iniciada
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

function getClientes()
{
    global $conn;
    $sql = "SELECT * FROM clientes";
    return $conn->query($sql);
}

// Si se ha establecido $_POST['id'], eliminar el cliente con ese ID
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $conn->autocommit(FALSE); // Desactivar el autocommit
    try {
        // Primero, eliminar las filas correspondientes en la tabla telefono
        $sql = "DELETE FROM telefono WHERE Clientes_id_cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Luego, eliminar el cliente
        $sql = "DELETE FROM clientes WHERE id_Cliente = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conn->commit(); // Hacer commit de la transacción
        echo "Cliente eliminado con éxito.";
    } catch (Exception $e) {
        $conn->rollback(); // Si hay un error, hacer rollback de la transacción
        echo "Error al eliminar el cliente: " . $e->getMessage();
    }
    exit();  // Termina el script aquí si se ha establecido $_POST['id']
}

// Si se ha establecido $_GET['id'], devolver los detalles del cliente como JSON
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT c.id_Cliente as id, c.nombre as nombre, c.apellido as apellido, c.direccion as direccion, t.numero as telefono FROM clientes c LEFT JOIN telefono t ON c.id_Cliente = t.Clientes_id_cliente WHERE c.id_Cliente = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
        echo json_encode($cliente);
    } else {
        echo json_encode(array("error" => "No se encontró al cliente"));
    }
    exit();  // Termina el script aquí si se ha establecido $_GET['id']
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
    <title>Clientes</title>
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
            <h2>Lista de Clientes</h2>

            <!-- Botón para agregar un nuevo cliente -->
            <a href="registrar_cliente.php" class="add-button">Registrar Cliente</a>

            <table>
                <caption>Lista de Clientes</caption>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Dirección</th>
                    <th>Acciones</th>
                </tr>
                <?php
                $result = getClientes();
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row["id_Cliente"] . "</td><td>" . $row["nombre"] . "</td><td>" . $row["apellido"] . "</td><td>" . $row["direccion"] . "</td>";
                        echo "<td><button class='view-button' data-id='" . $row["id_Cliente"] . "'>Ver más</button></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay clientes</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- Modal para ver más detalles del cliente -->
        <div id="clienteModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Detalles del Cliente</h2>
                <!-- Los detalles del cliente se cargarán aquí -->
                <div id="clienteDetails"></div>
                <!-- Botón para editar el cliente -->
                <a href="" id="editButton" class="add-button">Editar</a>
                <!-- Botón para eliminar el cliente -->
                <a id="deleteButton" class="add-button" onclick="deleteCliente(this.getAttribute('data-id'))" data-id="">Eliminar</a>

            </div>
        </div>

    </div>
    <!-- Script para manejar el modal y la carga de los detalles del cliente -->
    <script>
        // Obtén el modal y el botón de cierre
        var modal = document.getElementById("clienteModal");
        var closeButton = document.getElementsByClassName("close")[0];

        // Cuando el usuario hace clic en el botón de cierre, cierra el modal
        closeButton.onclick = function() {
            modal.style.display = "none";
        }

        // Cuando el usuario hace clic en cualquier lugar fuera del modal, cierra el modal
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Cuando el usuario hace clic en el botón "Ver más", muestra el modal y carga los detalles del cliente
        var viewButtons = document.getElementsByClassName("view-button");
        for (var i = 0; i < viewButtons.length; i++) {
            viewButtons[i].onclick = function() {
                var id = this.getAttribute("data-id");
                // Haz una solicitud AJAX para obtener los detalles del cliente
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "clientes.php?id=" + id, true);
                xhr.onload = function() {
                    if (this.status == 200) {
                        var cliente = JSON.parse(this.responseText);
                        // Llena el modal con los detalles del cliente
                        var details = "ID: " + cliente.id + "<br>" +
                            "Nombre: " + cliente.nombre + "<br>" +
                            "Apellido: " + cliente.apellido + "<br>" +
                            "Dirección: " + cliente.direccion + "<br>" +
                            "Teléfono: " + cliente.telefono;
                        document.getElementById("clienteDetails").innerHTML = details;
                        // Actualiza el enlace del botón de edición
                        document.getElementById("editButton").href = "editar_cliente.php?id=" + id;
                        // Actualiza el atributo data-id del botón de eliminación
                        document.getElementById("deleteButton").setAttribute("data-id", id);
                        // Muestra el modal
                        modal.style.display = "block";
                    }
                }
                xhr.send();
            }
        }

        function deleteCliente(id) {
            var confirmation = confirm("¿Estás seguro de que quieres eliminar al cliente con ID " + id + "?");
            if (confirmation) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "clientes.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function() {
                    if (this.status == 200) {
                        console.log(this.responseText);
                        alert("Cliente eliminado con exito.");
                        location.reload();
                    }
                }
                xhr.send("id=" + id);
            }
        }
    </script>
</body>

</html>
