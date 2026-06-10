<?php
session_start();

// 1. Validación estricta de seguridad
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

$alertaToast = '';

try {
    $db = new Conexion();
    $conn = $db->conectar();

    // 2. LÓGICA MÁGICA: Procesar cambio de estado rápido vía POST (Auto-Submit)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_reserva']) && isset($_POST['nuevo_estado'])) {
        $id_reserva = (int)$_POST['id_reserva'];
        $nuevo_estado = $_POST['nuevo_estado'];
        
        $estadosPermitidos = ['Nueva', 'Atendida', 'Confirmada', 'Cancelada'];
        if (in_array($nuevo_estado, $estadosPermitidos)) {
            $stmtUpdate = $conn->prepare("UPDATE reservas SET estado_reserva = :estado WHERE id = :id");
            $stmtUpdate->bindParam(':estado', $nuevo_estado, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id', $id_reserva, PDO::PARAM_INT);
            if ($stmtUpdate->execute()) {
                // Bandera para disparar el Toast Premium de SweetAlert2
                $alertaToast = 'success';
                $estadoActualizado = $nuevo_estado;
                $idActualizado = str_pad($id_reserva, 4, '0', STR_PAD_LEFT);
            }
        }
    }

    // 3. Obtener todas las reservas ordenadas de la más reciente a la más antigua
    $stmtReservas = $conn->query("SELECT * FROM reservas ORDER BY fecha_solicitud DESC");
    $reservas = $stmtReservas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error cargando Reservas: " . $e->getMessage());
    $reservas = [];
    $alertaToast = 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bandeja de Reservas | Karibes Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --color-bg: #F4F7F6;
            --color-dark: #0A1118;
            /* Adaptado a la nueva identidad de marca de Karibe'S */
            --color-primary: #0054A6; 
            --color-gold: #F15A24;    
            --color-cyan: #00AEEF;    
            --color-white: #FFFFFF;
            --color-text: #2C3E50;
            --color-muted: #8798A5;
            --shadow-card: 0 5px 20px rgba(0,0,0,0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--color-bg); color: var(--color-text); }

        .main-content { margin-left: 280px; padding: 2rem 3rem; min-height: 100vh; }

        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .welcome-text h1 { font-family: 'Playfair Display', serif; color: var(--color-primary); font-size: 2.2rem; margin-bottom: 0.3rem; }
        .welcome-text p { color: var(--color-muted); font-size: 0.95rem; }

        /* Herramientas y Buscador */
        .table-tools { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .search-box { position: relative; width: 320px; }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--color-muted); }
        .search-box input { width: 100%; padding: 0.8rem 1rem 0.8rem 40px; border: 1px solid #D1D8E0; border-radius: 8px; outline: none; transition: all 0.3s ease; font-size: 0.9rem;}
        .search-box input:focus { border-color: var(--color-gold); box-shadow: 0 0 10px rgba(241,90,20,0.1); }

        /* Tabla Estilizada */
        .table-container { background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card); padding: 2rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1.2rem 1rem; text-align: left; border-bottom: 1px solid #EEEEEE; vertical-align: middle; }
        th { color: var(--color-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        td { font-size: 0.9rem; color: var(--color-text); }
        tr:hover { background-color: #F8FAFC; }

        .client-info strong { color: var(--color-dark); display: block; font-size: 0.95rem; margin-bottom: 3px; }
        .client-info span { color: var(--color-muted); font-size: 0.8rem; display: block; }

        /* Badges de Interés (Mágicos) */
        .interest-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: rgba(0, 174, 239, 0.1); color: var(--color-primary); border: 1px solid rgba(0, 174, 239, 0.2); }
        .interest-general { background: #F1F2F6; color: #57606f; border: 1px solid #ced6e0; }

        /* Noches de Estadía */
        .nights-counter { display: inline-block; background: #FFF200; color: #000; font-weight: 700; font-size: 0.75rem; padding: 2px 6px; border-radius: 3px; margin-left: 5px; }

        /* Select de Estado */
        .select-status { 
            padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; 
            text-transform: uppercase; border: 1px solid transparent; outline: none; cursor: pointer; 
            appearance: none; -webkit-appearance: none; text-align: center; width: 130px;
        }
        .status-wrapper { position: relative; display: inline-block; }
        /* Flecha elegante del select */
        .status-wrapper::after { content: '\f107'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; font-size: 0.8rem; }
        
        .status-Nueva { background: #FFF3CD; color: #856404; }
        .status-wrapper.status-Nueva::after { color: #856404; }
        .status-Atendida { background: #CCE5FF; color: #004085; }
        .status-wrapper.status-Atendida::after { color: #004085; }
        .status-Confirmada { background: #D4EDDA; color: #155724; }
        .status-wrapper.status-Confirmada::after { color: #155724; }
        .status-Cancelada { background: #F8D7DA; color: #721C24; }
        .status-wrapper.status-Cancelada::after { color: #721C24; }

        .btn-action { color: var(--color-primary); background: rgba(0, 84, 166, 0.1); width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; transition: all 0.3s ease; }
        .btn-action:hover { background: var(--color-primary); color: var(--color-white); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0, 84, 166, 0.2); }

        @media screen and (max-width: 1200px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
            .table-tools { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .search-box { width: 100%; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h1>Bandeja de Reservas</h1>
                <p>Monitoree las intenciones de compra y gestione el estado de ocupación del hotel en Huancayo.</p>
            </div>
        </div>

        <div class="table-container">
            <div class="table-tools">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por huésped, correo o teléfono..." onkeyup="filterTable()">
                </div>
                <div style="font-size: 0.85rem; color: var(--color-muted); font-weight: 600;">
                    Total de Solicitudes: <span style="color:var(--color-primary); font-size:1rem;"><?php echo count($reservas); ?></span>
                </div>
            </div>

            <table id="reservasTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Huésped / Contacto</th>
                        <th>Alojamiento de Interés</th>
                        <th>Estancia (In - Out)</th>
                        <th>Estado</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reservas) > 0): ?>
                        <?php foreach ($reservas as $res): 
                            // MAGIA PHP: Extraer el tag de interés del mensaje comercial
                            $mensaje_completo = $res['mensaje'];
                            $suite_interes = "";
                            if (preg_match('/\[Interesado en:\s*(.*?)\]/', $mensaje_completo, $matches)) {
                                $suite_interes = $matches[1];
                            }

                            // MAGIA PHP: Calcular noches dinámicamente
                            $f_in = new DateTime($res['checkin']);
                            $f_out = new DateTime($res['checkout']);
                            $noches = $f_in->diff($f_out)->days;
                        ?>
                            <tr>
                                <td><strong style="color:var(--color-primary);">#<?php echo str_pad($res['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                <td class="client-info">
                                    <strong><?php echo htmlspecialchars($res['nombre']); ?></strong>
                                    <span><i class="fa-regular fa-envelope"></i> <?php echo htmlspecialchars($res['email']); ?></span>
                                    <span><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($res['telefono']); ?></span>
                                </td>
                                <td>
                                    <?php if(!empty($suite_interes)): ?>
                                        <span class="interest-badge"><i class="fa-solid fa-hotel"></i> <?php echo htmlspecialchars($suite_interes); ?></span>
                                    <?php else: ?>
                                        <span class="interest-badge interest-general"><i class="fa-regular fa-paper-plane"></i> Contacto General</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-weight: 500; color: var(--color-text);"><?php echo date('d/m/y', strtotime($res['checkin'])); ?></span> 
                                    <i class="fa-solid fa-arrow-right-long" style="font-size:0.75rem; color:var(--color-muted); margin:0 4px;"></i> 
                                    <span style="font-weight: 500; color: var(--color-text);"><?php echo date('d/m/y', strtotime($res['checkout'])); ?></span>
                                    <span class="nights-counter"><?php echo $noches; ?>N</span>
                                </td>
                                <td>
                                    <form method="POST" style="margin:0;" class="form-status">
                                        <input type="hidden" name="id_reserva" value="<?php echo $res['id']; ?>">
                                        <div class="status-wrapper status-<?php echo $res['estado_reserva']; ?>">
                                            <select name="nuevo_estado" class="select-status status-<?php echo $res['estado_reserva']; ?>" onchange="this.form.submit()">
                                                <option value="Nueva" <?php echo ($res['estado_reserva'] == 'Nueva') ? 'selected' : ''; ?>>Nueva</option>
                                                <option value="Atendida" <?php echo ($res['estado_reserva'] == 'Atendida') ? 'selected' : ''; ?>>Atendida</option>
                                                <option value="Confirmada" <?php echo ($res['estado_reserva'] == 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                                                <option value="Cancelada" <?php echo ($res['estado_reserva'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                                            </select>
                                        </div>
                                    </form>
                                </td>
                                <td style="text-align: center;">
                                    <a href="ver_reserva.php?id=<?php echo $res['id']; ?>" class="btn-action" title="Abrir Ficha de Operación">
                                        <i class="fa-solid fa-envelope-open-text"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 4rem; color: var(--color-muted);">
                                <i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; display: block;"></i>
                                No hay ninguna solicitud de reserva registrada todavía.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filterTable() {
            let input = document.getElementById("searchInput");
            let filter = input.value.toUpperCase();
            let table = document.getElementById("reservasTable");
            let tr = table.getElementsByTagName("tr");

            for (let i = 1; i < tr.length; i++) {
                let tdContent = tr[i].getElementsByTagName("td")[1]; // Columna del Huésped
                if (tdContent) {
                    let txtValue = tdContent.textContent || tdContent.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }
    </script>

    <?php if ($alertaToast == 'success'): ?>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: 'success',
            title: 'Reserva #<?php echo $idActualizado; ?> cambiada a <?php echo $estadoActualizado; ?>'
        });
    </script>
    <?php elseif ($alertaToast == 'error'): ?>
    <script>
        Swal.fire({ title: 'Error', text: 'Error crítico de conexión a la Base de Datos.', icon: 'error', confirmButtonColor: '#F15A24' });
    </script>
    <?php endif; ?>
</body>
</html>