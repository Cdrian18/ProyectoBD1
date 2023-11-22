<?php
include_once "conexion.php";
include_once "sesion.php";
// Verificar si no hay una sesión de empleado iniciada
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

function getCompras()
{
    global $conn;
    // Modifica la consulta SQL para incluir un JOIN
    $sql = "SELECT c.id_compra, c.fecha, c.total, p.nombre AS nombre_del_proveedor 
            FROM compras c
            JOIN proveedores p ON c.Proveedores_id_proveedor = p.id_proveedor";
    return $conn->query($sql);
}

function getDetallesCompra($idCompra)
{
    global $conn;
    $sql = "SELECT d.compras_id_compra, d.cantidad, d.precio, m.nombre 
            FROM detalles_compra d
            INNER JOIN materiales m ON d.Materiales_id_material = m.id_material 
            WHERE d.compras_id_compra = $idCompra";
    $result = $conn->query($sql);
    $detalles = [];
    if ($result === false) {
        die("Error: " . $conn->error);
    }
    $detalles = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $detalles[] = $row;
        }
    }
    return $detalles;
}

if (isset($_POST['id_compra'])) {
    $idCompra = $_POST['id_compra'];
    $detallesCompra = getDetallesCompra($idCompra);
    echo json_encode($detallesCompra);
    exit;
}

require_once 'compras.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/Mainstyles.css">
    <link rel="stylesheet" href="CSS/ComprasStyles.css">
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
                <li><a href="compras.php">Compras</a></li>
                <li><a href="empleados.php">Empleados</a></li>
                <li><a href="clientes.php">Clientes</a></li>
                <li><a href="estadisticas.php">Estadísticas</a></li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-container">
            <h2>Lista de Compras</h2>

            <!-- Botón para agregar una nueva compra -->
            <a href="registrar_compra.php" class="add-button">Agregar Compra</a>

            <table>
                <caption>Lista de Compras</caption>
                <tr>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Proveedor</th>
                    <th>Detalles</th>
                </tr>
                <?php
                $result = getCompras();
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row["fecha"] . "</td><td>" . $row["total"] . "</td><td>" . $row["nombre_del_proveedor"] . "</td>";
                        echo "<td><button onclick='verDetalles(\"" . $row["id_compra"] . "\")'>Ver detalles</button></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No hay compras</td></tr>";
                }
                ?>
            </table>

            <form id="detalleForm" method="post" style="display: none;">
                <input type="hidden" id="id_compra" name="id_compra">
            </form>
        </div>

    </div>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detalles de la Compra</h3>
            <p id="detalleCompra"></p>
        </div>
    </div>

    <script>
        var modal = document.getElementById("myModal");

        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };

        var closeButton = document.getElementsByClassName("close")[0];
        closeButton.onclick = function() {
            modal.style.display = "none";
        }

        function mostrarDetallesCompra(detallesCompra) {
            var detalleCompra = document.getElementById("detalleCompra");
            detalleCompra.innerHTML = "ID Compra: " + detallesCompra[0].compras_id_compra + "<br><br>";
            detallesCompra.forEach(function(detalle) {
                detalleCompra.innerHTML +=
                    "Producto: " + detalle.nombre + "<br>" +
                    "Cantidad: " + detalle.cantidad + "<br>" +
                    "Precio: " + detalle.precio + "<br><br>";
            });
            modal.style.display = "block";
        };

        function verDetalles(idCompra) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "compras.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    var detallesCompra = JSON.parse(this.responseText);
                    mostrarDetallesCompra(detallesCompra);
                }
            };
            xhr.send("id_compra=" + idCompra);
        }
    </script>

</body>

</html>