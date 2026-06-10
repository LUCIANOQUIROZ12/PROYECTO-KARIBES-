<?php
session_start();

// Validación estricta de seguridad: Si no hay sesión, al login.
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

try {
    $db = new Conexion();
    $conn = $db->conectar();

    // 1. KPI: Total de Reservas
    $stmtReservas = $conn->query("SELECT COUNT(*) as total FROM reservas");
    $totalReservas = $stmtReservas->fetch()['total'];

    // 2. KPI: Reservas Nuevas (Pendientes de atención)
    $stmtNuevas = $conn->query("SELECT COUNT(*) as nuevas FROM reservas WHERE estado_reserva = 'Nueva'");
    $reservasNuevas = $stmtNuevas->fetch()['nuevas'];

    // 3. KPI: Suites Activas
    $stmtSuites = $conn->query("SELECT COUNT(*) as activas FROM suites WHERE estado = 1");
    $suitesActivas = $stmtSuites->fetch()['activas'];

    // 4. Tabla de Últimas Reservas (Límite 5)
    $stmtUltimas = $conn->query("SELECT id, nombre, email, checkin, checkout, estado_reserva, fecha_solicitud FROM reservas ORDER BY id DESC LIMIT 5");
    $ultimasReservas = $stmtUltimas->fetchAll();

} catch (PDOException $e) {
    error_log("Error cargando Dashboard: " . $e->getMessage());
    $totalReservas = $reservasNuevas = $suitesActivas = 0;
    $ultimasReservas = [];
}

// Saludo dinámico según la hora del servidor
$hora = date('H');
$saludo = ($hora < 12) ? 'Buenos días' : (($hora < 19) ? 'Buenas tardes' : 'Buenas noches');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Karibes Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --color-bg: #F4F7F6;
            --color-dark: #0A1118;
            --color-primary: #083D6B;
            --color-gold: #F89C1D;
            --color-white: #FFFFFF;
            --color-text: #2C3E50;
            --color-muted: #8798A5;
            --shadow-card: 0 5px 20px rgba(0,0,0,0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: var(--color-bg);
            color: var(--color-text);
        }

        /* Layout Principal */
        .main-content {
            margin-left: 280px; /* Compensa el ancho del sidebar */
            padding: 2rem 3rem;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Header del Dashboard */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .welcome-text h1 {
            font-family: 'Playfair Display', serif;
            color: var(--color-primary);
            font-size: 2.2rem;
            margin-bottom: 0.3rem;
        }

        .welcome-text p {
            color: var(--color-muted);
            font-size: 0.95rem;
        }

        .date-display {
            background: var(--color-white);
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow-card);
            font-weight: 500;
            color: var(--color-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Tarjetas KPI */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .kpi-card {
            background: var(--color-white);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow-card);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 4px solid var(--color-gold);
            transition: transform 0.3s ease;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
        }

        .kpi-info h3 {
            font-size: 0.85rem;
            color: var(--color-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .kpi-info .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-primary);
        }

        .kpi-icon {
            width: 60px;
            height: 60px;
            background: rgba(248, 156, 29, 0.1);
            color: var(--color-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Tabla de Actividad Reciente */
        .table-container {
            background: var(--color-white);
            border-radius: 12px;
            box-shadow: var(--shadow-card);
            padding: 2rem;
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .table-header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--color-primary);
            font-size: 1.5rem;
        }

        .btn-view-all {
            background: rgba(8, 61, 107, 0.1);
            color: var(--color-primary);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-view-all:hover {
            background: var(--color-primary);
            color: var(--color-white);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #EEEEEE;
        }

        th {
            color: var(--color-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        td {
            font-size: 0.95rem;
            color: var(--color-text);
        }

        /* Badges de Estado */
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-nueva { background: #FFF3CD; color: #856404; }
        .status-atendida { background: #CCE5FF; color: #004085; }
        .status-confirmada { background: #D4EDDA; color: #155724; }
        .status-cancelada { background: #F8D7DA; color: #721C24; }

        @media screen and (max-width: 991px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
            .dashboard-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h1><?php echo $saludo; ?>, <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?></h1>
                <p>Resumen general de las operaciones de Karibes Resorts.</p>
            </div>
            <div class="date-display">
                <i class="fa-regular fa-calendar"></i>
                <?php echo date('d / M / Y'); ?>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-info">
                    <h3>Total Reservas</h3>
                    <div class="number"><?php echo $totalReservas; ?></div>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-book-open"></i></div>
            </div>

            <div class="kpi-card" style="border-left-color: #E74C3C;">
                <div class="kpi-info">
                    <h3>Pendientes (Nuevas)</h3>
                    <div class="number" style="color: #E74C3C;"><?php echo $reservasNuevas; ?></div>
                </div>
                <div class="kpi-icon" style="background: rgba(231, 76, 60, 0.1); color: #E74C3C;">
                    <i class="fa-solid fa-bell"></i>
                </div>
            </div>

            <div class="kpi-card" style="border-left-color: #2ECC71;">
                <div class="kpi-info">
                    <h3>Suites Activas</h3>
                    <div class="number"><?php echo $suitesActivas; ?></div>
                </div>
                <div class="kpi-icon" style="background: rgba(46, 204, 113, 0.1); color: #2ECC71;">
                    <i class="fa-solid fa-door-open"></i>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2>Últimas Solicitudes VIP</h2>
                <a href="reservas.php" class="btn-view-all">Ver Todas</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($ultimasReservas) > 0): ?>
                        <?php foreach ($ultimasReservas as $res): ?>
                            <tr>
                                <td>#<?php echo str_pad($res['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($res['nombre']); ?></strong><br>
                                    <span style="font-size: 0.8rem; color: var(--color-muted);"><?php echo htmlspecialchars($res['email']); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($res['checkin'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($res['checkout'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($res['estado_reserva']); ?>">
                                        <?php echo $res['estado_reserva']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="ver_reserva.php?id=<?php echo $res['id']; ?>" style="color: var(--color-primary); text-decoration: none;" title="Ver Detalles">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--color-muted);">
                                <i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5; display: block;"></i>
                                Aún no hay solicitudes de reserva en el sistema.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>