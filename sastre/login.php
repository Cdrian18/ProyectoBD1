<?php
// Verificar si no hay una sesión iniciada antes de iniciarla
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de conexión a la base de datos
include_once "conexion.php";

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario
    $cedula = $_POST['cedula'];
    $contrasena = $_POST['contrasena'];

    // Consulta SQL para verificar las credenciales
    $sql = "SELECT * FROM Empleado WHERE idEmpleado = '$cedula' AND contrasena = '$contrasena'";

    // Ejecutar la consulta
    $result = $conn->query($sql);

    // Verificar si la consulta fue exitosa
    if ($result) {
        // Verificar si hay resultados
        if ($result->num_rows > 0) {
            // Obtener los datos del empleado
            $row = $result->fetch_assoc();

            // Guardar información del empleado en la sesión
            $_SESSION['idEmpleado'] = $row['idEmpleado'];
            $_SESSION['nombreEmpleado'] = $row['nombre']; // Ajusta según la estructura real de tu tabla

            // Redirigir a la página principal o realizar otras acciones necesarias
            header("Location: main.php");
            exit();
        } else {
            // No hay resultados, mostrar mensaje de credenciales incorrectas
            $mensaje_error = "Credenciales incorrectas. Inténtalo de nuevo.";
        }
    } else {
        // Error en la consulta, mostrar mensaje de error
        $mensaje_error = "Error en la consulta: " . $conn->error;
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
    <link rel="stylesheet" href="CSS/styles.css">
    <title>Iniciar Sesión</title>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" required>

            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>

            <button type="submit">Iniciar Sesión</button>
        </form>

        <?php
            // Mostrar mensaje de error si existe
            if (isset($mensaje_error)) {
                echo "<p>$mensaje_error</p>";
            }
        ?>
    </div>
</body>
</html>
