<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

// Obtener el nombre del empleado de la sesión
$nombreEmpleado = $_SESSION['nombreEmpleado'];
$idEmpleado = $_SESSION['idEmpleado'];
?>
