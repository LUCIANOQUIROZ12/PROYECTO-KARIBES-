<?php
session_start();

// 1. Validación estricta de seguridad
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

// Validar que llegue un ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: reservas.php");
    exit;
}

$id_reserva = (int)$_GET['id'];
$mensajeAccion = '';

try {
    $db = new Conexion();
    $conn = $db->conectar();

    // 2. LÓGICA DE ACTUALIZACIÓN: Procesar cambio de estado desde este detalle
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_estado'])) {
        $nuevo_estado = $_POST['nuevo_estado'];
        $estadosPermitidos = ['Nueva', 'Atendida', 'Confirmada', 'Cancelada'];
        
        if (in_array($nuevo_estado, $estadosPermitidos)) {
            $stmtUpdate = $conn->prepare("UPDATE reservas SET estado_reserva = :estado WHERE id = :id");
            $stmtUpdate->bindParam(':estado', $nuevo_estado, PDO::PARAM_STR);
            $stmtUpdate->bindParam(':id', $id_reserva, PDO::PARAM_INT);
            if ($stmtUpdate->execute()) {
                $mensajeAccion = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> El estado de la reserva se actualizó correctamente a '$nuevo_estado'.</div>";
            }
        }
    }

    // 3. OBTENER DATOS DE LA RESERVA
    $stmt = $conn->prepare("SELECT * FROM reservas WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id_reserva, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        // Si el ID no existe en la DB, regresamos a la lista
        header("Location: reservas.php");
        exit;
    }

    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    // MAGIA: Calcular número de noches dinámicamente en PHP
    $f_in = new DateTime($reserva['checkin']);
    $f_out = new DateTime($reserva['checkout']);
    $intervalo = $f_in->diff($f_out);
    $noches = $intervalo->days;

} catch (PDOException $e) {
    error_log("Error en ver_reserva.php: " . $e->getMessage());
    header("Location: reservas.php");
    exit;
}

// --- MAGIA: Formatear Mensaje Automático para WhatsApp del Cliente ---
// Limpiar teléfono (dejar solo números)
$telefono_limpio = preg_replace('/[^0-9]/', '', $reserva['telefono']);
// Si el teléfono no tiene código de país, asumimos Perú (+51) por defecto
if (strlen($telefono_limpio) == 9) { $telefono_limpio = "51" . $telefono_limpio; }

$msg_whatsapp = "Hola *".htmlspecialchars($reserva['nombre'])."*, le saludamos de *Karibe'S Resort Climatizado (Huancayo)*. Recibimos su solicitud de reserva para las fechas del ".date('d/m/Y', strtotime($reserva['checkin']))." al ".date('d/m/Y', strtotime($reserva['checkout'])).". Nos gustaría coordinar los detalles de su estadía y formas de pago. ✨";
$url_whatsapp = "https://api.whatsapp.com/send?phone=".$telefono_limpio."&text=".urlencode($msg_whatsapp);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Reserva #<?php echo str_pad($reserva['id'], 4, '0', STR_PAD_LEFT); ?> | Karibes Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --color-bg: #F4F7F6;
            --color-dark: #0A1118;
            --color-primary: #0054A6; /* Sincronizado con tu nuevo logo */
            --color-gold: #F15A24;    /* Sincronizado con tu nuevo logo */
            --color-white: #FFFFFF;
            --color-text: #2C3E50;
            --color-muted: #8798A5;
            --shadow-card: 0 5px 20px rgba(0,0,0,0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--color-bg); color: var(--color-text); }

        .main-content { margin-left: 280px; padding: 2rem 3rem; min-height: 100vh; transition: all 0.3s; }

        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--color-muted); text-decoration: none; font-weight: 600; margin-bottom: 1.5rem; transition: color 0.3s; }
        .btn-back:hover { color: var(--color-primary); }

        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .welcome-text h1 { font-family: 'Playfair Display', serif; color: var(--color-primary); font-size: 2.2rem; }

        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 500; font-size: 0.9rem; border-left: 4px solid #28A745; background: #D4EDDA; color: #155724; }

        /* Tarjeta Principal Split Layout */
        .voucher-card { background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card); overflow: hidden; display: grid; grid-template-columns: 1.8fr 1.2fr; border-top: 6px solid var(--color-primary); }
        
        .voucher-main { padding: 3rem; border-right: 1px dashed #E2E8F0; position: relative; }
        .voucher-side { padding: 3rem; background: #F8FAFC; display: flex; flex-direction: column; justify-content: space-between; }

        .voucher-section-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; color: var(--color-muted); font-weight: 700; margin-bottom: 1.5rem; border-bottom: 1px solid #E2E8F0; padding-bottom: 0.5rem; }

        /* Detalles Informativos */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem; }
        .info-block label { display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--color-muted); font-weight: 600; margin-bottom: 0.3rem; }
        .info-block p { font-size: 1rem; color: var(--color-text); font-weight: 500; }
        .info-block p strong { color: var(--color-primary); font-size: 1.1rem; }

        /* Mensaje Premium */
        .message-box { background: #FFFDF9; border: 1px solid #FFEAA7; border-left: 4px solid var(--color-gold); padding: 1.5rem; border-radius: 6px; font-style: italic; color: #7F8C8D; line-height: 1.6; font-size: 0.95rem; }

        /* Estados */
        .status-badge { display: inline-block; padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; text-align: center; margin-top: 0.5rem; }
        .status-Nueva { background: #FFF3CD; color: #856404; }
        .status-Atendida { background: #CCE5FF; color: #004085; }
        .status-Confirmada { background: #D4EDDA; color: #155724; }
        .status-Cancelada { background: #F8D7DA; color: #721C24; }

        /* Select de Actualización Rápida */
        .select-luxe { width: 100%; padding: 0.8rem; border: 1px solid #D1D8E0; border-radius: 6px; background: white; font-size: 0.9rem; font-weight: 500; color: var(--color-text); outline: none; margin-bottom: 1rem; }
        .btn-update { width: 100%; padding: 0.8rem; border: none; background: var(--color-primary); color: white; font-weight: 600; border-radius: 6px; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; }
        .btn-update:hover { background: var(--color-dark); }

        /* Botones de Contacto Canales */
        .btn-channel { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 1rem; border-radius: 6px; font-weight: 600; text-decoration: none; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; margin-bottom: 1rem; transition: 0.3s; border: none; cursor: pointer;}
        .btn-whatsapp { background: #25D366; color: white; box-shadow: 0 4px 12px rgba(37, 211, 102, 0.2); }
        .btn-whatsapp:hover { background: #1ebd59; transform: translateY(-2px); }
        .btn-print { background: #636E72; color: white; }
        .btn-print:hover { background: #2D3436; transform: translateY(-2px); }

        /* ==========================================================================
           MAGIA CSS: REGLAS DE OPTIMIZACIÓN PARA IMPRESIÓN FISICA O PDF
           ========================================================================== */
        @media print {
            body { background: white; color: black; }
            .main-content { margin-left: 0; padding: 0; }
            /* Escondemos barra de administración, botones de contacto y selectores */
            include, sidebar, .btn-back, .dashboard-header, .voucher-side, .modal-close-btn, .alert { display: none !important; }
            /* Hacemos que la tarjeta ocupe todo el ancho de la hoja */
            .voucher-card { grid-template-columns: 1fr; border: none; box-shadow: none; }
            .voucher-main { padding: 0; border: none; }
            .message-box { background: #fff; border: 1px solid #ccc; }
        }

        @media screen and (max-width: 991px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
            .voucher-card { grid-template-columns: 1fr; }
            .voucher-main { border-right: none; border-bottom: 1px dashed #E2E8F0; padding: 2rem; }
            .voucher-side { padding: 2rem; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        
        <a href="reservas.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Listado</a>

        <div class="dashboard-header">
            <div class="welcome-text">
                <h1>Ficha de Reserva</h1>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 0.85rem; color: var(--color-muted); font-weight: 600;">Código del Sistema:</span>
                <h2 style="color: var(--color-gold);">#<?php echo str_pad($reserva['id'], 4, '0', STR_PAD_LEFT); ?></h2>
            </div>
        </div>

        <?php echo $mensajeAccion; ?>

        <div class="voucher-card">
            <div class="voucher-main">
                
                <div class="voucher-section-title"><i class="fa-solid fa-user-tie"></i> Información del Huésped</div>
                <div class="info-grid">
                    <div class="info-block">
                        <label>Nombre Completo</label>
                        <p><strong><?php echo htmlspecialchars($reserva['nombre']); ?></strong></p>
                    </div>
                    <div class="info-block">
                        <label>Correo Electrónico</label>
                        <p><?php echo htmlspecialchars($reserva['email']); ?></p>
                    </div>
                    <div class="info-block">
                        <label>Teléfono / WhatsApp</label>
                        <p><i class="fa-brands fa-whatsapp" style="color:#25D366;"></i> <?php echo htmlspecialchars($reserva['telefono']); ?></p>
                    </div>
                    <div class="info-block">
                        <label>Fecha de Solicitud</label>
                        <p><?php echo date('d M Y, H:i A', strtotime($reserva['fecha_solicitud'])); ?></p>
                    </div>
                </div>

                <div class="voucher-section-title"><i class="fa-regular fa-calendar-days"></i> Detalles de la Estancia</div>
                <div class="info-grid">
                    <div class="info-block">
                        <label><i class="fa-solid fa-plane-arrival"></i> Fecha de Check-In</label>
                        <p><?php echo date('d / m / Y', strtotime($reserva['checkin'])); ?></p>
                    </div>
                    <div class="info-block">
                        <label><i class="fa-solid fa-plane-departure"></i> Fecha de Check-Out</label>
                        <p><?php echo date('d / m / Y', strtotime($reserva['checkout'])); ?></p>
                    </div>
                    <div class="info-block">
                        <label>Tiempo de Permanencia</label>
                        <p><span style="background: rgba(0, 84, 166, 0.1); padding: 4px 10px; border-radius: 4px; color: var(--color-primary); font-weight: 700;"><?php echo $noches; ?> Noches</span></p>
                    </div>
                    <div class="info-block">
                        <label>Estado de Gestión</label>
                        <div>
                            <span class="status-badge status-<?php echo $reserva['estado_reserva']; ?>">
                                <?php echo $reserva['estado_reserva']; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="voucher-section-title"><i class="fa-regular fa-comment-dots"></i> Mensaje o Requerimientos Especiales</div>
                <div class="message-box">
                    <?php echo nl2br(htmlspecialchars($reserva['mensaje'])); ?>
                </div>
            </div>

            <div class="voucher-side">
                
                <div style="margin-bottom: 2rem;">
                    <div class="voucher-section-title"><i class="fa-solid fa-sliders"></i> Cambiar Estado</div>
                    <form method="POST">
                        <input type="hidden" name="cambiar_estado" value="1">
                        <select name="nuevo_estado" class="select-luxe">
                            <option value="Nueva" <?php echo ($reserva['estado_reserva'] == 'Nueva') ? 'selected' : ''; ?>>Nueva</option>
                            <option value="Atendida" <?php echo ($reserva['estado_reserva'] == 'Atendida') ? 'selected' : ''; ?>>Atendida</option>
                            <option value="Confirmada" <?php echo ($reserva['estado_reserva'] == 'Confirmada') ? 'selected' : ''; ?>>Confirmada</option>
                            <option value="Cancelada" <?php echo ($reserva['estado_reserva'] == 'Cancelada') ? 'selected' : ''; ?>>Cancelada</option>
                        </select>
                        <button type="submit" class="btn-update">Actualizar Ficha</button>
                    </form>
                </div>

                <div>
                    <div class="voucher-section-title"><i class="fa-solid fa-paper-plane"></i> Canales Inmediatos</div>
                    
                    <a href="<?php echo $url_whatsapp; ?>" target="_blank" class="btn-channel btn-whatsapp">
                        <i class="fa-brands fa-whatsapp" style="font-size:1.2rem;"></i> Contactar Cliente
                    </a>

                    <button type="button" class="btn-channel btn-print" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> Imprimir Ficha
                    </button>
                </div>

            </div>
        </div>
    </div>

</body>
</html>