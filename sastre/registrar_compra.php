<?php
include_once "conexion.php";
include_once "sesion.php";
// Verificar si no hay una sesión de empleado iniciada
if (!isset($_SESSION['idEmpleado'])) {
    header("Location: login.php");
    exit();
}

// Consulta para obtener los materiales
$materialsQuery = "SELECT id_material, nombre FROM materiales";
$materialsResult = $conn->query($materialsQuery);

// Consulta para obtener los proveedores
$providersQuery = "SELECT id_proveedor, nombre FROM proveedores";
$providersResult = $conn->query($providersQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtén los valores del formulario
    $fecha = $_POST['fecha'];
    $total = $_POST['total'];
    $proveedor = $_POST['proveedor'];

    // Inserta la nueva compra en la base de datos
    $compraQuery = "INSERT INTO compras (fecha, total, Proveedores_id_proveedor) VALUES ('$fecha', '$total', '$proveedor')";
    
    if ($conn->query($compraQuery) === TRUE) {
        $last_id = $conn->insert_id;
        echo "<script type='text/javascript'>alert('Nueva compra registrada con éxito. ID de la compra: " . $last_id . "');</script>";

        // Obtén los arrays de materiales, cantidades y precios
        $materiales = explode(',',rtrim($_POST['material'][0], ','));
        $cantidades = explode(',',rtrim($_POST['cantidad'][0], ','));
        $precios = explode(',',rtrim($_POST['precio'][0], ','));

        // Itera sobre los arrays e inserta cada detalle de la compra en la base de datos
        for ($i = 0; $i < count($materiales); $i++) {
            $material = $materiales[$i];
            $cantidad = $cantidades[$i];
            $precio = $precios[$i];

            $detalleCompraQuery = "INSERT INTO detalles_compra (compras_id_compra, Materiales_id_material, cantidad, precio) VALUES ('$last_id', '$material', '$cantidad', '$precio')";
            if ($conn->query($detalleCompraQuery) !== TRUE) {
                echo "Error: " . $detalleCompraQuery . "<br>" . $conn->error;
            }
        }
    } else {
        echo "Error: " . $compraQuery . "<br>" . $conn->error;
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
            </ul>
        </nav>

        <!-- Contenido principal -->
        <div class="main-container">
            <h2>Agregar Compra</h2>

            <!-- Formulario para agregar una nueva compra -->
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <label for="fecha">Fecha:</label>
                <input type="date" id="fecha" name="fecha">

                <label for="material">Material:</label>
                <select id="material" name="material">
                    <?php while ($row = $materialsResult->fetch_assoc()) : ?>
                        <option value="<?= $row['id_material'] ?>"><?= $row['nombre'] ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" name="cantidad">

                <label for="precio">Precio por unidad:</label>
                <input type="number" id="precio" name="precio">

                <button type="button" id="agregarMaterial">Agregar Material</button>

                <table id="materialesTable">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <input type="hidden" id="materialesInput" name="material[]">
                        <input type="hidden" id="cantidadesInput" name="cantidad[]">
                        <input type="hidden" id="preciosInput" name="precio[]">
                    </tbody>
                </table>

                <label for="total">Total:</label>
                <input type="number" id="total" name="total" readonly>

                <label for="proveedor">Proveedor:</label>
                <select id="proveedor" name="proveedor">
                    <?php
                    if ($providersResult->num_rows > 0) {
                        while ($row = $providersResult->fetch_assoc()) {
                            echo "<option value='" . $row["id_proveedor"] . "'>" . $row["nombre"] . "</option>";
                        }
                    }
                    ?>
                </select>

                <button type="submit">Agregar Compra</button>
            </form>
        </div>

    </div>

    <script>
        var materiales = [];
        var cantidades = [];
        var precios = [];

        function actualizarInputs() {
            var materialesInput = document.getElementsByName('material[]')[0];
            var cantidadesInput = document.getElementsByName('cantidad[]')[0];
            var preciosInput = document.getElementsByName('precio[]')[0];
            materialesInput.value = materiales.join(",");
            cantidadesInput.value = cantidades.join(",");
            preciosInput.value = precios.join(",");
        }

        document.getElementById('agregarMaterial').addEventListener('click', function() {
            var materialesTable = document.getElementById('materialesTable');

            var materialSelect = document.getElementById('material');
            var materialId = materialSelect.value;
            var materialName = materialSelect.options[materialSelect.selectedIndex].text;
            var cantidad = document.getElementById('cantidad').value;
            var precio = document.getElementById('precio').value;

            var eliminarButton = document.createElement('button');
            eliminarButton.textContent = 'Eliminar';
            eliminarButton.addEventListener('click', function() {
                var fila = this.parentNode.parentNode;
                var index = fila.rowIndex - 1;
                fila.parentNode.removeChild(fila);
                materiales.splice(index, 1);
                cantidades.splice(index, 1);
                precios.splice(index, 1);
                actualizarInputs();
                calcularTotal();
            });

            var row = materialesTable.insertRow(-1);
            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);
            var cell4 = row.insertCell(3);

            cell1.textContent = materialName;
            cell2.textContent = cantidad;
            cell3.textContent = precio;
            cell4.appendChild(eliminarButton);

            materiales.push(materialId);
            cantidades.push(cantidad);
            precios.push(precio);

            document.getElementById('material').value = '';
            document.getElementById('cantidad').value = '';
            document.getElementById('precio').value = '';

            actualizarInputs();
            calcularTotal();
        });

        function calcularTotal() {
            var rows = document.getElementById('materialesTable').rows;
            var total = 0;

            for (var i = 1; i < rows.length; i++) {
                total += rows[i].cells[1].textContent * rows[i].cells[2].textContent;
            }

            document.getElementById('total').value = total;
        }
    </script>


</body>

</html>