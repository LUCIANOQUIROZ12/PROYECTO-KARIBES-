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

    // LÓGICA MÁGICA DE MODERACIÓN (POST)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
        
        // Acción: Eliminar Definitivamente
        if ($_POST['accion'] == 'eliminar' && isset($_POST['id_testimonio'])) {
            $id_testimonio = (int)$_POST['id_testimonio'];
            $stmtDelete = $conn->prepare("DELETE FROM testimonios WHERE id = :id");
            $stmtDelete->bindParam(':id', $id_testimonio, PDO::PARAM_INT);
            
            if ($stmtDelete->execute()) {
                $mensajeAccion = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> El testimonio ha sido eliminado permanentemente del sistema.</div>";
            }
        }
        
        // Acción: Alternar Visibilidad (On/Off)
        if ($_POST['accion'] == 'cambiar_estado' && isset($_POST['id_testimonio']) && isset($_POST['nuevo_estado'])) {
            $id_testimonio = (int)$_POST['id_testimonio'];
            $nuevo_estado = (int)$_POST['nuevo_estado'];
            
            $stmtUpdate = $conn->prepare("UPDATE testimonios SET estado = :estado WHERE id = :id");
            $stmtUpdate->bindParam(':estado', $nuevo_estado, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':id', $id_testimonio, PDO::PARAM_INT);
            
            if ($stmtUpdate->execute()) {
                $estadoTxt = $nuevo_estado == 1 ? 'publicado' : 'ocultado';
                $mensajeAccion = "<div class='alert alert-success'><i class='fa-solid fa-eye'></i> El testimonio ha sido $estadoTxt en la página principal.</div>";
            }
        }
    }

    // Extraer testimonios del más reciente al más antiguo
    $stmtTestimonios = $conn->query("SELECT * FROM testimonios ORDER BY creado_en DESC");
    $testimonios = $stmtTestimonios->fetchAll();

} catch (PDOException $e) {
    error_log("Error cargando Testimonios: " . $e->getMessage());
    $testimonios = [];
    $mensajeAccion = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Error conectando a la base de datos.</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderación de Testimonios | Karibes Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,400;1,600&display=swap" rel="stylesheet">
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

        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
        .welcome-text h1 { font-family: 'Playfair Display', serif; color: var(--color-primary); font-size: 2.2rem; margin-bottom: 0.3rem; }
        .welcome-text p { color: var(--color-muted); font-size: 0.95rem; }

        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 500; font-size: 0.9rem; display: flex; gap: 10px; align-items: center;}
        .alert-success { background: #D4EDDA; color: #155724; border-left: 4px solid #28A745; }
        .alert-danger { background: #F8D7DA; color: #721C24; border-left: 4px solid var(--color-danger); }

        /* MAGIA UX: Muro de Tarjetas en Grid */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .testimonial-card {
            background: var(--color-white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow-card);
            position: relative;
            display: flex;
            flex-direction: column;
            border-top: 4px solid var(--color-gold);
            transition: transform 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        .quote-icon {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 2.5rem;
            color: rgba(248, 156, 29, 0.15);
        }

        .testimony-content {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 1.1rem;
            color: var(--color-primary);
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex-grow: 1; /* Empuja el footer hacia abajo */
        }

        .client-info {
            margin-bottom: 1.5rem;
        }

        .client-info h4 {
            color: var(--color-dark);
            font-size: 1rem;
            margin-bottom: 0.2rem;
        }

        .client-info span {
            color: var(--color-muted);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            border-top: 1px solid #EEEEEE;
        }

        /* Toggle Switch Moderación */
        .switch-wrapper { display: flex; align-items: center; gap: 10px; }
        .switch { position: relative; display: inline-block; width: 40px; height: 20px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #27AE60; }
        input:checked + .slider:before { transform: translateX(20px); }
        .status-label { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--color-muted); }

        .btn-delete {
            background: rgba(231, 76, 60, 0.1);
            color: var(--color-danger);
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-delete:hover {
            background: var(--color-danger);
            color: var(--color-white);
        }

        /* Estado opaco cuando está inactivo */
        .testimonial-card.inactive {
            opacity: 0.6;
            border-top-color: var(--color-muted);
        }

        @media screen and (max-width: 991px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h1>Moderación de Reseñas</h1>
                <p>Revise, oculte o elimine los testimonios que aparecen en el portal de Karibes.</p>
            </div>
            <div style="text-align: right; color: var(--color-muted); font-size: 0.9rem; font-weight: 600;">
                Total de reseñas: <span style="color: var(--color-primary);"><?php echo count($testimonios); ?></span>
            </div>
        </div>

        <?php echo $mensajeAccion; ?>

        <div class="testimonials-grid">
            <?php if (count($testimonios) > 0): ?>
                <?php foreach ($testimonios as $test): ?>
                    <div class="testimonial-card <?php echo $test['estado'] == 0 ? 'inactive' : ''; ?>">
                        <i class="fa-solid fa-quote-right quote-icon"></i>
                        
                        <div class="testimony-content">
                            "<?php echo htmlspecialchars($test['comentario']); ?>"
                        </div>
                        
                        <div class="client-info">
                            <h4><?php echo htmlspecialchars($test['nombre_cliente']); ?></h4>
                            <span>
                                <i class="fa-solid fa-location-dot"></i> 
                                <?php echo htmlspecialchars($test['origen']); ?> 
                                &nbsp;|&nbsp; 
                                <?php echo date('d M Y', strtotime($test['creado_en'])); ?>
                            </span>
                        </div>
                        
                        <div class="card-footer">
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="accion" value="cambiar_estado">
                                <input type="hidden" name="id_testimonio" value="<?php echo $test['id']; ?>">
                                <input type="hidden" name="nuevo_estado" value="<?php echo $test['estado'] == 1 ? 0 : 1; ?>">
                                
                                <div class="switch-wrapper">
                                    <label class="switch" title="Publicar / Ocultar">
                                        <input type="checkbox" onchange="this.form.submit()" <?php echo ($test['estado'] == 1) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <span class="status-label">
                                        <?php echo $test['estado'] == 1 ? '<span style="color:#27AE60;">Visible</span>' : 'Oculto'; ?>
                                    </span>
                                </div>
                            </form>

                            <button type="button" class="btn-delete" title="Eliminar Testimonio" onclick="confirmarEliminacion(<?php echo $test['id']; ?>, '<?php echo addslashes(htmlspecialchars($test['nombre_cliente'])); ?>')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem; background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card);">
                    <i class="fa-regular fa-comments" style="font-size: 3rem; margin-bottom: 1rem; color: var(--color-muted); opacity: 0.5;"></i>
                    <h3 style="color: var(--color-primary); margin-bottom: 0.5rem;">Bandeja Vacía</h3>
                    <p style="color: var(--color-muted);">No existen testimonios registrados en la base de datos en este momento.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <form id="formDelete" method="POST" style="display: none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id_testimonio" id="delete_id" value="">
    </form>

    <script>
        function confirmarEliminacion(id, nombre) {
            if (confirm(`⚠️ MODERACIÓN DE CONTENIDO\n\n¿Está seguro de que desea eliminar la reseña de "${nombre}"?\n\nEsta acción borrará el comentario permanentemente. Si solo desea quitarlo de la web, utilice el interruptor de visibilidad.`)) {
                document.getElementById('delete_id').value = id;
                document.getElementById('formDelete').submit();
            }
        }
    </script>
</body>
</html>