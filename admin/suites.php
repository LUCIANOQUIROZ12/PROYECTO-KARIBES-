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

    // LÓGICA MÁGICA: Procesar Eliminación Segura (Vía POST, no GET)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
        $id_suite = (int)$_POST['id_suite'];
        
        $stmtDelete = $conn->prepare("DELETE FROM suites WHERE id = :id");
        $stmtDelete->bindParam(':id', $id_suite, PDO::PARAM_INT);
        
        if ($stmtDelete->execute()) {
            $mensajeAccion = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> La suite ha sido eliminada permanentemente del catálogo.</div>";
        } else {
            $mensajeAccion = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Error al intentar eliminar la suite.</div>";
        }
    }

    // Obtener todas las suites ordenadas por su campo de orden
    $stmtSuites = $conn->query("SELECT * FROM suites ORDER BY orden ASC");
    $suites = $stmtSuites->fetchAll();

} catch (PDOException $e) {
    error_log("Error cargando Suites: " . $e->getMessage());
    $suites = [];
    $mensajeAccion = "<div class='alert alert-danger'>Error crítico de conexión. Consulte los logs del servidor.</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Suites | Karibes Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --color-bg: #F4F7F6;
            --color-dark: #0A1118;
            /* Adaptamos el admin a los nuevos colores corporativos */
            --color-primary: #0054A6; 
            --color-gold: #F15A24;
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

        /* Botón Nueva Suite */
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
            box-shadow: 0 4px 15px rgba(241, 90, 36, 0.3);
            transition: all 0.3s ease;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(241, 90, 36, 0.4); }

        /* Alertas */
        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 500; font-size: 0.9rem; display: flex; gap: 10px; align-items: center;}
        .alert-success { background: #D4EDDA; color: #155724; border-left: 4px solid #28A745; }
        .alert-danger { background: #F8D7DA; color: #721C24; border-left: 4px solid var(--color-danger); }

        /* Tabla */
        .table-container { background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card); padding: 2rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #EEEEEE; vertical-align: middle; }
        th { color: var(--color-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        tr:hover { background-color: #F8FAFC; }

        /* Estilos de Contenido de Tabla */
        .suite-thumbnail {
            width: 80px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .suite-thumbnail:hover { transform: scale(1.1); }

        .suite-info strong { color: var(--color-primary); display: block; font-size: 1.05rem; }
        .suite-info span { color: var(--color-muted); font-size: 0.8rem; display: block; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .price-tag { font-family: var(--font-sans); font-weight: 700; color: #27AE60; font-size: 1.1rem; }
        .price-tag small { color: var(--color-muted); font-size: 0.75rem; font-weight: 500; }

        .amenities-icons i { color: var(--color-primary); background: rgba(0, 84, 166, 0.08); padding: 5px; border-radius: 4px; margin-right: 4px; font-size: 0.85rem; }

        /* Badges de Estado */
        .badge { padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-active { background: #D4EDDA; color: #155724; }
        .badge-inactive { background: #E2E3E5; color: #383D41; }

        /* Botones de Acción */
        .actions-group { display: flex; gap: 8px; }
        .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-edit { background: rgba(0, 84, 166, 0.1); color: var(--color-primary); }
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
                <h1>Catálogo de Suites y Habitaciones</h1>
                <p>Gestione las habitaciones, villas y precios mostrados en el sistema.</p>
            </div>
            <a href="suite_form.php" class="btn-add">
                <i class="fa-solid fa-plus"></i> Añadir Plan
            </a>
        </div>

        <?php echo $mensajeAccion; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Detalles del Alojamiento</th>
                        <th>Tarifa</th>
                        <th>Amenidades</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($suites) > 0): ?>
                        <?php foreach ($suites as $suite): ?>
                            <tr>
                                <td>
                                    <?php 
                                        // MAGIA UX: Ajustamos la ruta de la imagen si se subió localmente
                                        $img_src = $suite['imagen'];
                                        if(!empty($img_src) && strpos($img_src, 'http') === false) {
                                            $img_src = '../' . $img_src; 
                                        }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Suite" class="suite-thumbnail" onerror="this.src='https://via.placeholder.com/80x60?text=Sin+Imagen'">
                                </td>
                                <td class="suite-info">
                                    <strong><?php echo htmlspecialchars($suite['nombre']); ?></strong>
                                    <span><?php echo htmlspecialchars($suite['descripcion_corta']); ?></span>
                                </td>
                                <td class="price-tag">
                                    S/ <?php echo number_format($suite['precio_noche'], 2); ?> <small>/ noche</small>
                                </td>
                                <td class="amenities-icons">
                                    <?php 
                                        // Renderizado de JSON a iconos reales
                                        $amenidades = json_decode($suite['amenidades_json'], true);
                                        if (is_array($amenidades)) {
                                            foreach ($amenidades as $icono) {
                                                echo "<i class='" . htmlspecialchars($icono) . "'></i>";
                                            }
                                        } else {
                                            echo "<small style='color:#8798A5;'>No definidas</small>";
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($suite['estado'] == 1): ?>
                                        <span class="badge badge-active">Publicada</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Oculta</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions-group">
                                        <a href="suite_form.php?id=<?php echo $suite['id']; ?>" class="btn-action btn-edit" title="Editar Plan">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn-action btn-delete" title="Eliminar Plan" onclick="confirmarEliminacion(<?php echo $suite['id']; ?>, '<?php echo addslashes(htmlspecialchars($suite['nombre'])); ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 4rem; color: var(--color-muted);">
                                <i class="fa-solid fa-bed" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; display: block;"></i>
                                Aún no has registrado ninguna habitación en tu resort.<br>Haz clic en "Añadir Plan" para comenzar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <form id="formDelete" method="POST" style="display: none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id_suite" id="delete_id" value="">
    </form>

    <script>
        // MAGIA UX: Confirmación Premium con SweetAlert2
        function confirmarEliminacion(id, nombre) {
            Swal.fire({
                title: '⚠️ Alerta de Seguridad',
                html: `¿Está seguro que desea eliminar la habitación <strong>"${nombre}"</strong>?<br><br>Desaparecerá permanentemente del catálogo web.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#E74C3C',
                cancelButtonColor: '#8798A5',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_id').value = id;
                    document.getElementById('formDelete').submit();
                }
            });
        }
    </script>
</body>
</html>