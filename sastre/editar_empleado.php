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
    $idEmpleado = $_POST['idEmpleado'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $salario = $_POST['salario'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $telefono = $_POST['telefono'];

    // Preparar la consulta SQL para actualizar la tabla empleados
    $sql = "UPDATE empleado SET nombre = ?, apellido = ?, salario = ?, fecha_nacimiento = ? WHERE idEmpleado = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisi", $nombre, $apellido, $salario, $fecha_nacimiento, $idEmpleado);

    // Ejecutar la consulta SQL
    if ($stmt->execute()) {
        // Preparar la consulta SQL para actualizar la tabla telefono
        $sqlTelefono = "UPDATE telefono SET numero = ? WHERE Empleados_id_Empleado = ?";
        $stmtTelefono = $conn->prepare($sqlTelefono);
        $stmtTelefono->bind_param("si", $telefono, $idEmpleado);

        // Ejecutar la consulta SQL
        if ($stmtTelefono->execute()) {
            echo "Empleado y teléfono actualizados con éxito.";
            // Redirige al usuario a empleados.php solo si la actualización fue exitosa
            header("Location: empleados.php");
            exit();
        } else {
            echo "Error al actualizar el teléfono: " . $stmtTelefono->error;
        }
    } else {
        echo "Error al actualizar el empleado: " . $stmt->error;
    }
} else {
    // Si el formulario no se ha enviado, obtener los datos del empleado existente
    $idEmpleado = $_GET['id'];

    // Preparar la consulta SQL para obtener los datos del empleado
    $sql = "SELECT e.nombre, e.apellido, e.salario, e.fecha_nacimiento, t.numero FROM empleado e JOIN telefono t ON e.idEmpleado = t.Empleados_id_Empleado WHERE e.idEmpleado = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idEmpleado);
    // Ejecutar la consulta SQL
    if ($stmt->execute()) {
        // Obtener los resultados de la consulta
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Obtener los datos del empleado
            $row = $result->fetch_assoc();
            $nombre = $row['nombre'];
            $apellido = $row['apellido'];
            $salario = $row['salario'];
            $fecha_nacimiento = $row['fecha_nacimiento'];
            $telefono = $row['numero'];
        } else {
            echo "No se encontró el empleado.";
            exit();
        }
    } else {
        echo "Error al obtener los datos del empleado: " . $stmt->error;
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
            <h2>Editar Empleado</h2>

            <form method="post" action="editar_empleado.php">
                <label for="idEmpleado">ID del Empleado:</label><br>
                <p id="idEmpleado"><?php echo $idEmpleado; ?></p>
                <input type="hidden" name="idEmpleado" value="<?php echo $idEmpleado; ?>">
                <label for="nombre">Nombre:</label><br>
                <input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"><br>
                <label for="apellido">Apellido:</label><br>
                <input type="text" id="apellido" name="apellido" value="<?php echo $apellido; ?>"><br>
                <label for="salario">Salario:</label><br>
                <input type="number" id="salario" name="salario" value="<?php echo $salario; ?>"><br>
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label><br>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo $fecha_nacimiento; ?>"><br>
                <label for="telefono">Teléfono:</label><br>
                <input type="tel" id="telefono" name="telefono" value="<?php echo $telefono; ?>"><br>
                <input type="submit" value="Actualizar">
            </form>
        </div>

    </div>
</body>

</html>