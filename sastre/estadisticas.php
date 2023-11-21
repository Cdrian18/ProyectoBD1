<?php
include_once "conexion.php";
include_once "sesion.php";
// Verificar si no hay una sesión de empleado iniciada
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

$mesActual = date('m');
$anioActual = date('Y');


// Ejecutar las consultas y almacenar los resultados


// Obtener los resultados



// Consultas SQL
$query1 = "SELECT COUNT(*) AS totalClientes FROM clientes";
$query2 = "SELECT COUNT(*) AS totalEmpleados FROM empleado";
$query3 = "SELECT COUNT(*) AS totalPedidos FROM pedidos";
$queryVentas = "SELECT SUM(total) AS totalVentasMes FROM ventas WHERE MONTH(fecha) = $mesActual AND YEAR(fecha) = $anioActual";
$queryCompras = "SELECT SUM(total) AS totalComprasMes FROM compras WHERE MONTH(fecha) = $mesActual AND YEAR(fecha) = $anioActual";
$queryProveedor = "SELECT p.nombre, SUM(d.cantidad) AS totalMateriales
                FROM compras c
                JOIN proveedores p ON c.Proveedores_id_proveedor = p.id_proveedor
                JOIN detalles_compra d ON c.id_compra = d.compras_id_compra
                GROUP BY p.id_proveedor
                ORDER BY totalMateriales DESC
                LIMIT 1";
$queryPrenda = "SELECT p.nombre, SUM(d.cantidad) AS cantidadPedida
                FROM detalles_pedido d
                JOIN prendas p ON d.Prendas_id_prenda = p.id_prenda
                GROUP BY p.id_prenda
                ORDER BY cantidadPedida DESC
                LIMIT 1";
$queryEmpleado = "SELECT nombre, MAX(salario) AS salarioMaximo FROM empleado";



// Ejecutar las consultas y almacenar los resultados
$result1 = $conn->query($query1);
$result2 = $conn->query($query2);
$result3 = $conn->query($query3);
$resultVentas = $conn->query($queryVentas);
$resultCompras = $conn->query($queryCompras);
$resultProveedor = $conn->query($queryProveedor);
$resultPrenda = $conn->query($queryPrenda);
$resultEmpleado = $conn->query($queryEmpleado);
// ...

// Obtener los resultados
$totalClientes = $result1->fetch_assoc()['totalClientes'];
$totalEmpleados = $result2->fetch_assoc()['totalEmpleados'];
$totalPedidos = $result3->fetch_assoc()['totalPedidos'];
$totalVentasMes = $resultVentas->fetch_assoc()['totalVentasMes'];
$totalComprasMes = $resultCompras->fetch_assoc()['totalComprasMes'];
$proveedorResult = $resultProveedor->fetch_assoc();
$nombreProveedor = $proveedorResult['nombre'];
$totalMateriales = $proveedorResult['totalMateriales'];
$prendaResult = $resultPrenda->fetch_assoc();
$nombrePrenda = $prendaResult['nombre'];
$cantidadPedida = $prendaResult['cantidadPedida'];
$empleadoResult = $resultEmpleado->fetch_assoc();
$nombreEmpleado = $empleadoResult['nombre'];
$salarioMaximo = $empleadoResult['salarioMaximo'];
// ...

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/Mainstyles.css">
    <link rel="stylesheet" href="CSS/ComprasStyles.css">
    <link rel="stylesheet" href="CSS/EstadisticasStyles.css">
    <title>Estadísticas</title>
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
                <li><a href="estadisticas.php">Estadísticas</a></li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-container">
            <div class="title-container">
                <h2>Estadísticas</h2>
            </div>

            <div class="panels-container">
                <div class="panel">
                    <h2>Total de clientes</h2>
                    <p><?php echo $totalClientes; ?></p>
                </div>

                <div class="panel">
                    <h2>Total de empleados</h2>
                    <p><?php echo $totalEmpleados; ?></p>
                </div>

                <div class="panel">
                    <h2>Total de pedidos</h2>
                    <p><?php echo $totalPedidos; ?></p>
                </div>

                <div class="panel">
                    <h2>Total de ventas del mes</h2>
                    <p><?php echo $totalVentasMes; ?></p>
                </div>

                <div class="panel">
                    <h2>Total de compras del mes</h2>
                    <p><?php echo $totalComprasMes; ?></p>
                </div>

                <div class="panel">
                    <h2>Proveedor con más materiales suministrados</h2>
                    <p><?php echo $nombreProveedor; ?></p>
                    <h2>Total de materiales: <?php echo $totalMateriales; ?></h2>
                </div>

                <div class="panel">
                    <h2>Prenda más pedida</h2>
                    <p><?php echo $nombrePrenda; ?></p>
                    <h2>Unidades pedidas: <?php echo $cantidadPedida; ?></h2>
                </div>
                <div class="panel">
                    <h2>Empleado con el salario más alto</h2>
                    <p><?php echo $nombreEmpleado; ?></p>
                    <h2>Salario: <?php echo $salarioMaximo; ?></h2>
                </div>
                <!-- ... Agrega más paneles según sea necesario -->
            </div>
        </div>
    </div>
</body>

</html>