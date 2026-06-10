<?php
session_start();

// Validación estricta de seguridad
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

$mensajeAccion = '';

try {
    $db = new Conexion();
    $conn = $db->conectar();

    // LÓGICA MÁGICA: Procesar acciones por POST (Eliminar o Cambiar Estado)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
        
        // Acción: Eliminar Servicio
        if ($_POST['accion'] == 'eliminar' && isset($_POST['id_servicio'])) {
            $id_servicio = (int)$_POST['id_servicio'];
            $stmtDelete = $conn->prepare("DELETE FROM servicios WHERE id = :id");
            $stmtDelete->bindParam(':id', $id_servicio, PDO::PARAM_INT);
            
            if ($stmtDelete->execute()) {
                $mensajeAccion = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> El servicio VIP ha sido eliminado correctamente.</div>";
            }
        }
        
        // Acción: Cambio Rápido de Estado (On/Off)
        if ($_POST['accion'] == 'cambiar_estado' && isset($_POST['id_servicio']) && isset($_POST['nuevo_estado'])) {
            $id_servicio = (int)$_POST['id_servicio'];
            $nuevo_estado = (int)$_POST['nuevo_estado'];
            
            $stmtUpdate = $conn->prepare("UPDATE servicios SET estado = :estado WHERE id = :id");
            $stmtUpdate->bindParam(':estado', $nuevo_estado, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':id', $id_servicio, PDO::PARAM_INT);
            
            if ($stmtUpdate->execute()) {
                $estadoTxt = $nuevo_estado == 1 ? 'activado' : 'ocultado';
                $mensajeAccion = "<div class='alert alert-success'><i class='fa-solid fa-eye'></i> El servicio ha sido $estadoTxt con éxito en la página principal.</div>";
            }
        }
    }

    // Obtener todos los servicios ordenados por su campo de orden
    $stmtServicios = $conn->query("SELECT * FROM servicios ORDER BY orden ASC");
    $servicios = $stmtServicios->fetchAll();

} catch (PDOException $e) {
    error_log("Error cargando Servicios: " . $e->getMessage());
    $servicios = [];
    $mensajeAccion = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Error crítico de conexión a la base de datos.</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Servicios VIP | Karibes Admin</title>
    
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
            --color-danger: #E74C3C;
            --shadow-card: 0 5px 20px rgba(0,0,0,0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--color-bg); color: var(--color-text); }

        .main-content { margin-left: 280px; padding: 2rem 3rem; min-height: 100vh; }

        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .welcome-text h1 { font-family: 'Playfair Display', serif; color: var(--color-primary); font-size: 2.2rem; margin-bottom: 0.3rem; }
        .welcome-text p { color: var(--color-muted); font-size: 0.95rem; }

        .btn-add {
            background: linear-gradient(135deg, var(--color-gold) 0%, #D68212 100%);
            color: var(--color-white);
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(248, 156, 29, 0.3);
            transition: all 0.3s ease;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(248, 156, 29, 0.4); }

        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 500; font-size: 0.9rem; display: flex; gap: 10px; align-items: center;}
        .alert-success { background: #D4EDDA; color: #155724; border-left: 4px solid #28A745; }
        .alert-danger { background: #F8D7DA; color: #721C24; border-left: 4px solid var(--color-danger); }

        .table-container { background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card); padding: 2rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #EEEEEE; vertical-align: middle; }
        th { color: var(--color-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        tr:hover { background-color: #F8FAFC; }

        /* Estilo de Iconos y Texto en la tabla */
        .service-icon-preview {
            width: 45px;
            height: 45px;
            background: rgba(248, 156, 29, 0.1);
            color: var(--color-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .service-info strong { color: var(--color-primary); display: block; font-size: 1.05rem; margin-bottom: 3px; }
        .service-info span { color: var(--color-muted); font-size: 0.85rem; display: block; max-width: 400px; line-height: 1.4; }

        .order-badge { background: #F4F7F6; border: 1px solid #D1D8E0; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 0.85rem; color: var(--color-text); }

        /* Magia UX: Toggle Switch en la Tabla */
        .switch { position: relative; display: inline-block; width: 42px; height: 22px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #27AE60; }
        input:checked + .slider:before { transform: translateX(20px); }

        /* Botones de Acción */
        .actions-group { display: flex; gap: 8px; }
        .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-edit { background: rgba(8, 61, 107, 0.1); color: var(--color-primary); }
        .btn-edit:hover { background: var(--color-primary); color: var(--color-white); }
        .btn-delete { background: rgba(231, 76, 60, 0.1); color: var(--color-danger); }
        .btn-delete:hover { background: var(--color-danger); color: var(--color-white); }

        @media screen and (max-width: 991px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
            .dashboard-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .btn-add { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h1>Servicios VIP</h1>
                <p>Gestione las experiencias exclusivas ofrecidas en el resort (Spa, Gastronomía, Yates).</p>
            </div>
            <a href="servicio_form.php" class="btn-add">
                <i class="fa-solid fa-plus"></i> Nuevo Servicio
            </a>
        </div>

        <?php echo $mensajeAccion; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Icono</th>
                        <th>Detalle del Servicio</th>
                        <th>Orden</th>
                        <th>Visibilidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($servicios) > 0): ?>
                        <?php foreach ($servicios as $srv): ?>
                            <tr>
                                <td>
                                    <div class="service-icon-preview">
                                        <i class="<?php echo htmlspecialchars($srv['icono_fontawesome']); ?>"></i>
                                    </div>
                                </td>
                                <td class="service-info">
                                    <strong><?php echo htmlspecialchars($srv['titulo']); ?></strong>
                                    <span><?php echo htmlspecialchars($srv['descripcion']); ?></span>
                                </td>
                                <td>
                                    <span class="order-badge">#<?php echo $srv['orden']; ?></span>
                                </td>
                                <td>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="accion" value="cambiar_estado">
                                        <input type="hidden" name="id_servicio" value="<?php echo $srv['id']; ?>">
                                        <input type="hidden" name="nuevo_estado" value="<?php echo $srv['estado'] == 1 ? 0 : 1; ?>">
                                        
                                        <label class="switch" title="Clic para alternar visibilidad">
                                            <input type="checkbox" onchange="this.form.submit()" <?php echo ($srv['estado'] == 1) ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </form>
                                </td>
                                <td>
                                    <div class="actions-group">
                                        <a href="servicio_form.php?id=<?php echo $srv['id']; ?>" class="btn-action btn-edit" title="Editar Servicio">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn-action btn-delete" title="Eliminar Servicio" onclick="confirmarEliminacion(<?php echo $srv['id']; ?>, '<?php echo addslashes(htmlspecialchars($srv['titulo'])); ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 4rem; color: var(--color-muted);">
                                <i class="fa-solid fa-bell-concierge" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; display: block;"></i>
                                No hay servicios VIP registrados.<br>Haz clic en "Nuevo Servicio" para agregar uno.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <form id="formDelete" method="POST" style="display: none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id_servicio" id="delete_id" value="">
    </form>

    <script>
        function confirmarEliminacion(id, titulo) {
            if (confirm(`⚠️ ALERTA DE SISTEMA\n\n¿Está seguro de que desea eliminar el servicio "${titulo}"?\n\nEsta acción removerá esta característica de la página principal y no se puede deshacer.`)) {
                document.getElementById('delete_id').value = id;
                document.getElementById('formDelete').submit();
            }
        }
    </script>
</body>
</html>