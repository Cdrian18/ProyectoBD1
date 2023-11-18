<?php
session_start();

// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sastre";

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");


    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Recibir datos del formulario
    $cedula = $_POST['cedula'];
    $contrasena = $_POST['contrasena'];

    // Consulta SQL para verificar las credenciales
    $sql = "SELECT idEmpleado, nombre FROM Empleado WHERE idEmpleado = '$cedula' AND contrasena = '$contrasena'";

    $result = $conn->query($sql);

    // Verificar si la consulta fue exitosa
    if ($result) {
        // Verificar si hay resultados
        if ($result->num_rows > 0) {
            // Inicio de sesión exitoso, obtener información del empleado
            $row = $result->fetch_assoc();

            // Almacenar información del empleado en variables de sesión
            $_SESSION['idEmpleado'] = $row['idEmpleado'];
            $_SESSION['nombreEmpleado'] = $row['nombre'];

            // Redirigir a la página principal
            header("Location: main.php");
            exit();
        } else {
            // No hay resultados, mostrar mensaje de credenciales incorrectas
            $mensajeError = "Credenciales incorrectas. Inténtalo de nuevo.";
        }
    } else {
        // Error en la consulta, mostrar mensaje de error
        $mensajeError = "Error en la consulta: " . $conn->error;
    }

    // Cerrar la conexión
    $conn->close();
}
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
        
        <?php
        // Mostrar mensaje de error si existe
        if (isset($mensajeError)) {
            echo "<p>$mensajeError</p>";
        }
        ?>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>

            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>
