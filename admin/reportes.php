<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

$db = new Conexion();
$conn = $db->conectar();

// MAGIA: EXPORTACIÓN A EXCEL NATIVA
if (isset($_GET['exportar']) && $_GET['exportar'] == 'excel') {
    // Forzar descarga del archivo
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Reporte_Reservas_Karibes_" . date('Ymd_His') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Consulta para el reporte
    $stmtExport = $conn->query("SELECT id, nombre, email, telefono, checkin, checkout, estado_reserva, fecha_solicitud, mensaje FROM reservas ORDER BY fecha_solicitud DESC");
    
    // Imprimir tabla HTML (Excel lo interpreta nativamente conservando columnas y filas)
    echo "<table border='1'>";
    echo "<tr style='background-color:#0054A6; color:white;'>";
    echo "<th>ID Reserva</th><th>Fecha de Solicitud</th><th>Cliente</th><th>Email</th><th>Telefono</th><th>Check-in</th><th>Check-out</th><th>Estado</th><th>Mensaje del Cliente</th>";
    echo "</tr>";

    while ($row = $stmtExport->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['fecha_solicitud'] . "</td>";
        echo "<td>" . utf8_decode($row['nombre']) . "</td>";
        echo "<td>" . utf8_decode($row['email']) . "</td>";
        echo "<td>" . utf8_decode($row['telefono']) . "</td>";
        echo "<td>" . $row['checkin'] . "</td>";
        echo "<td>" . $row['checkout'] . "</td>";
        echo "<td>" . utf8_decode($row['estado_reserva']) . "</td>";
        echo "<td>" . utf8_decode($row['mensaje']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit; // Detener la ejecución para no imprimir el resto del HTML administrativo
}

// Cargar reservas para la vista en pantalla
$stmtReservas = $conn->query("SELECT * FROM reservas ORDER BY fecha_solicitud DESC");
$reservas = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Exportaciones | Karibes Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --color-bg: #F4F7F6;
            --color-primary: #0054A6; /* Nuevo color Karibes */
            --color-gold: #F15A24; /* Nuevo color Karibes */
            --color-white: #FFFFFF;
            --color-text: #2C3E50;
            --color-muted: #8798A5;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--color-bg); color: var(--color-text); }
        .main-content { margin-left: 280px; padding: 2rem 3rem; min-height: 100vh; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .welcome-text h1 { color: var(--color-primary); font-size: 2.2rem; }
        .welcome-text p { color: var(--color-muted); }
        
        /* Botón Mágico Excel */
        .btn-excel {
            background: #217346; /* Color oficial de Excel */
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(33, 115, 70, 0.3);
            transition: all 0.3s ease;
        }
        .btn-excel:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(33, 115, 70, 0.5); }

        .table-container { background: var(--color-white); border-radius: 12px; padding: 2rem; box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #EEEEEE; font-size: 0.9rem;}
        th { color: var(--color-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .badge-nueva { background: #FFF3CD; color: #856404; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h1>Reporte de Reservas</h1>
                <p>Visualice todas las solicitudes ingresadas desde el Front-End y expórtelas para su análisis.</p>
            </div>
            
            <a href="reportes.php?exportar=excel" class="btn-excel">
                <i class="fa-solid fa-file-excel"></i> Exportar a Excel
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha Registro</th>
                        <th>Huésped</th>
                        <th>Fechas de Estancia</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reservas as $res): ?>
                        <tr>
                            <td><strong>#<?php echo str_pad($res['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($res['fecha_solicitud'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($res['nombre']); ?></strong><br>
                                <span style="color:var(--color-muted); font-size:0.8rem;"><?php echo htmlspecialchars($res['email']); ?></span>
                            </td>
                            <td>
                                In: <?php echo date('d/m/Y', strtotime($res['checkin'])); ?><br>
                                Out: <?php echo date('d/m/Y', strtotime($res['checkout'])); ?>
                            </td>
                            <td><span class="badge badge-<?php echo strtolower($res['estado_reserva']); ?>"><?php echo $res['estado_reserva']; ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>