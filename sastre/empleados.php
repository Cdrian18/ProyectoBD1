<?php
include_once "conexion.php";
include_once "sesion.php";

// Verificar si no hay una sesión de empleado iniciada
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

function getEmpleados()
{
    global $conn;
    $sql = "SELECT * FROM empleado";
    return $conn->query($sql);
}

// Si se ha establecido $_POST['id'], eliminar el empleado con ese ID
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $conn->autocommit(FALSE); // Desactivar el autocommit
    try {
        // Primero, eliminar las filas correspondientes en la tabla telefono
        $sql = "DELETE FROM telefono WHERE Empleados_id_Empleado = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Luego, eliminar el empleado
        $sql = "DELETE FROM empleado WHERE idEmpleado = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $conn->commit(); // Hacer commit de la transacción
        echo "Empleado eliminado con éxito.";
    } catch (Exception $e) {
        $conn->rollback(); // Si hay un error, hacer rollback de la transacción
        echo "Error al eliminar el empleado: " . $e->getMessage();
    }
    exit();  // Termina el script aquí si se ha establecido $_POST['id']
}

// Si se ha establecido $_GET['id'], devolver los detalles del empleado como JSON
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT e.idEmpleado as id, e.nombre as nombre, e.apellido as apellido, e.salario as salario, e.fecha_nacimiento as fecha_nacimiento, t.numero as telefono FROM empleado e LEFT JOIN telefono t ON e.idEmpleado = t.Empleados_id_Empleado WHERE e.idEmpleado = $id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        echo json_encode($employee);
    } else {
        echo json_encode(array("error" => "No se encontró el empleado"));
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
                <li><a href="main.php">Inicio</a></li>
                <li><a href="pedidos.php">Pedidos</a></li>
                <li><a href="ventas.php">Ventas</a></li>
                <li><a href="envios.php">Envíos</a></li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="compras.php">Compras</a></li>
                <li><a href="empleados.php">Empleados</a></li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-container">
            <h2>Lista de Empleados</h2>

            <!-- Botón para agregar un nuevo empleado -->
            <a href="registrar_empleado.php" class="add-button">Registrar Empleado</a>

            <table>
                <caption>Lista de Empleados</caption>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Salario</th>
                    <th>Acciones</th>
                </tr>
                <?php
                $result = getEmpleados();
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row["idEmpleado"] . "</td><td>" . $row["nombre"] . "</td><td>" . $row["apellido"] . "</td><td>" . $row["salario"] . "</td>";
                        echo "<td><button class='view-button' data-id='" . $row["idEmpleado"] . "'>Ver más</button></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No hay empleados</td></tr>";
                }
                ?>
            </table>
        </div>

        <!-- Modal para ver más detalles del empleado -->
        <div id="employeeModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Detalles del Empleado</h2>
                <!-- Los detalles del empleado se cargarán aquí -->
                <div id="employeeDetails"></div>
                <!-- Botón para editar el empleado -->
                <a href="" id="editButton" class="add-button">Editar</a>
                <!-- Botón para eliminar el empleado -->
                <a id="deleteButton" class="add-button" onclick="deleteEmployee(this.getAttribute('data-id'))" data-id="">Eliminar</a>

            </div>
        </div>

    </div>
    <!-- Script para manejar el modal y la carga de los detalles del empleado -->
    <script>
        // Obtén el modal y el botón de cierre
        var modal = document.getElementById("employeeModal");
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

        // Cuando el usuario hace clic en el botón "Ver más", muestra el modal y carga los detalles del empleado
        var viewButtons = document.getElementsByClassName("view-button");
        for (var i = 0; i < viewButtons.length; i++) {
            viewButtons[i].onclick = function() {
                var id = this.getAttribute("data-id");
                // Haz una solicitud AJAX para obtener los detalles del empleado
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "empleados.php?id=" + id, true);
                xhr.onload = function() {
                    if (this.status == 200) {
                        var employee = JSON.parse(this.responseText);
                        // Parsea la fecha de nacimiento para obtener el año
                        var birthYear = new Date(employee.fecha_nacimiento).getFullYear();
                        // Resta el año de nacimiento del año actual para obtener la edad
                        var age = new Date().getFullYear() - birthYear;
                        // Llena el modal con los detalles del empleado
                        var details = "ID: " + employee.id + "<br>" +
                            "Nombre: " + employee.nombre + "<br>" +
                            "Apellido: " + employee.apellido + "<br>" +
                            "Salario: " + employee.salario + "<br>" +
                            "Fecha de Nacimiento: " + employee.fecha_nacimiento + "<br>" +
                            "Edad: " + age + "<br>" +
                            "Teléfono: " + employee.telefono;
                        document.getElementById("employeeDetails").innerHTML = details;
                        // Actualiza el enlace del botón de edición
                        document.getElementById("editButton").href = "editar_empleado.php?id=" + id;
                        // Actualiza el atributo data-id del botón de eliminación
                        document.getElementById("deleteButton").setAttribute("data-id", id);
                        // Muestra el modal
                        modal.style.display = "block";
                    }
                }
                xhr.send();
            }
        }

        function deleteEmployee(id) {
            var confirmation = confirm("¿Estás seguro de que quieres eliminar al empleado con ID " + id + "?");
            if (confirmation) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "empleados.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onload = function() {
                    if (this.status == 200) {
                        console.log(this.responseText);
                        alert("Empleado eliminado con exito.");
                        location.reload();
                    }
                }
                xhr.send("id=" + id);
            }
        }
    </script>
</body>

</html>