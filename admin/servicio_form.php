<?php
session_start();

// Validación estricta de seguridad
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

$db = new Conexion();
$conn = $db->conectar();

$mensaje = '';
$esEdicion = false;

// Variables base para el formulario (Estado inicial: Nuevo Servicio)
$servicio = [
    'id' => '',
    'titulo' => '',
    'descripcion' => '',
    'icono_fontawesome' => 'fa-solid fa-spa', // Icono por defecto
    'orden' => 0,
    'estado' => 1
];

// 1. LÓGICA DE DETECCIÓN: ¿Estamos editando un servicio existente?
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $esEdicion = true;
    $id_servicio = (int)$_GET['id'];
    
    $stmtGet = $conn->prepare("SELECT * FROM servicios WHERE id = :id LIMIT 1");
    $stmtGet->bindParam(':id', $id_servicio, PDO::PARAM_INT);
    $stmtGet->execute();
    
    if ($stmtGet->rowCount() > 0) {
        $servicio = $stmtGet->fetch(PDO::FETCH_ASSOC);
    } else {
        header("Location: servicios.php");
        exit;
    }
}

// 2. PROCESAMIENTO DEL FORMULARIO (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_post = $_POST['id'] ?? '';
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $icono = trim($_POST['icono_fontawesome']);
    $orden = (int)$_POST['orden'];
    $estado = isset($_POST['estado']) ? 1 : 0;

    try {
        if (!empty($id_post)) {
            // Consulta de Actualización (UPDATE)
            $stmt = $conn->prepare("UPDATE servicios SET titulo = :titulo, descripcion = :desc, icono_fontawesome = :icono, orden = :orden, estado = :estado WHERE id = :id");
            $stmt->bindParam(':id', $id_post, PDO::PARAM_INT);
        } else {
            // Consulta de Inserción (INSERT)
            $stmt = $conn->prepare("INSERT INTO servicios (titulo, descripcion, icono_fontawesome, orden, estado) VALUES (:titulo, :desc, :icono, :orden, :estado)");
        }

        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':desc', $descripcion);
        $stmt->bindParam(':icono', $icono);
        $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $accionTxt = !empty($id_post) ? 'actualizado' : 'registrado';
            $mensaje = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> El servicio VIP ha sido $accionTxt correctamente. <a href='servicios.php' style='color:inherit; text-decoration:underline;'>Volver al listado</a></div>";
            
            // Sincronizar array en pantalla si es un registro nuevo exitoso
            if (empty($id_post)) {
                $esEdicion = true;
                $servicio['id'] = $conn->lastInsertId();
                $servicio['titulo'] = $titulo;
                $servicio['descripcion'] = $descripcion;
                $servicio['icono_fontawesome'] = $icono;
                $servicio['orden'] = $orden;
                $servicio['estado'] = $estado;
            }
        }
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Error crítico: " . $e->getMessage() . "</div>";
    }
}

// Catálogo maestro de íconos Premium recomendados para Hoteles de Ultra-Lujo
$iconos_hospitalidad = [
    'fa-solid fa-spa' => 'Spa & Relax',
    'fa-solid fa-utensils' => 'Alta Cocina',
    'fa-solid fa-ship' => 'Yates / Naútica',
    'fa-solid fa-martini-glass-citrus' => 'Mixología / Bar',
    'fa-solid fa-helicopter' => 'Helipuerto / Vuelos',
    'fa-solid fa-car-rear' => 'Vehículos Blindados',
    'fa-solid fa-concierge-bell' => 'Mayordomo 24/7',
    'fa-solid fa-dumbbell' => 'Wellness Center',
    'fa-solid fa-water' => 'Piscinas Infinitas',
    'fa-solid fa-champagne-glasses' => 'Eventos Privados',
    'fa-solid fa-crown' => 'Servicios Reales',
    'fa-solid fa-wine-glass' => 'Cava de Vinos'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Editar Servicio VIP' : 'Nuevo Servicio VIP'; ?> | Karibes Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,400&display=swap" rel="stylesheet">
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--color-bg); color: var(--color-text); }

        .main-content { margin-left: 280px; padding: 2rem 3rem; min-height: 100vh; }

        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .welcome-text h1 { font-family: 'Playfair Display', serif; color: var(--color-primary); font-size: 2.2rem; margin-bottom: 0.3rem; }
        .welcome-text p { color: var(--color-muted); font-size: 0.95rem; }

        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--color-muted); text-decoration: none; font-weight: 600; margin-bottom: 1.5rem; transition: color 0.3s; }
        .btn-back:hover { color: var(--color-primary); }

        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 500; font-size: 0.9rem; display: flex; gap: 10px; align-items: center;}
        .alert-success { background: #D4EDDA; color: #155724; border-left: 4px solid #28A745; }
        .alert-danger { background: #F8D7DA; color: #721C24; border-left: 4px solid #E74C3C; }

        .form-card { background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card); padding: 2.5rem; }
        .form-grid { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 3rem; }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 0.8rem 1rem; border: 1px solid #D1D8E0; border-radius: 6px; font-size: 0.95rem; color: var(--color-text); transition: all 0.3s; }
        .form-control:focus { outline: none; border-color: var(--color-gold); box-shadow: 0 0 10px rgba(248, 156, 29, 0.1); }
        textarea.form-control { height: 120px; resize: none; }

        /* MAGIA UX: Grid de Selector de Iconos */
        .icon-selector-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.8rem; margin-top: 0.5rem; max-height: 280px; overflow-y: auto; padding: 5px; border: 1px solid #E9ECEF; border-radius: 6px; background-color: #FAFAFA; }
        .icon-selector-grid::-webkit-scrollbar { width: 4px; }
        .icon-selector-grid::-webkit-scrollbar-thumb { background: var(--color-muted); border-radius: 4px; }
        
        .icon-radio { display: none; }
        .icon-box { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem 0.5rem; background: var(--color-white); border: 2px solid transparent; border-radius: 6px; cursor: pointer; transition: all 0.2s ease; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .icon-box i { font-size: 1.4rem; color: var(--color-muted); margin-bottom: 0.4rem; }
        .icon-box span { font-size: 0.65rem; font-weight: 600; color: var(--color-muted); display: block; }

        .icon-radio:checked + .icon-box { background: rgba(8, 61, 107, 0.04); border-color: var(--color-primary); }
        .icon-radio:checked + .icon-box i { color: var(--color-gold); transform: scale(1.1); }
        .icon-radio:checked + .icon-box span { color: var(--color-primary); }

        /* MAGIA UX: Simulador Exacto del Frontend en Modo Oscuro */
        .simulator-box { background: var(--color-dark); border-radius: 12px; padding: 2.5rem; position: sticky; top: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); }
        .simulator-box .sim-label { color: var(--color-gold); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; display: block; margin-bottom: 1.5rem; opacity: 0.7; text-align: center;}
        
        /* Estilos idénticos al Front-End */
        .frontend-service-item { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 3rem 2rem; text-align: center; border-radius: 8px; position: relative; }
        .frontend-service-item::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: linear-gradient(135deg, #FFCC00 0%, #F89C1D 100%); }
        .frontend-service-icon { width: 80px; height: 80px; line-height: 80px; background: linear-gradient(135deg, #FFCC00 0%, #F89C1D 100%); color: var(--color-white); font-size: 2.5rem; border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 5px 15px rgba(248,156,29,0.2); }
        .frontend-service-item h4 { font-family: 'Playfair Display', serif; font-style: normal; color: var(--color-white); font-size: 1.4rem; margin-bottom: 1rem; font-weight: 600; }
        .frontend-service-item p { color: #AAB7C4; font-size: 0.95rem; line-height: 1.6; word-wrap: break-word; }

        .switch-container { display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem; }
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #27AE60; }
        input:checked + .slider:before { transform: translateX(24px); }

        .btn-submit { background: linear-gradient(135deg, var(--color-gold) 0%, #D68212 100%); color: var(--color-white); padding: 1rem; border: none; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; width: 100%; margin-top: 1.5rem; transition: 0.3s; box-shadow: 0 4px 15px rgba(248,156,29,0.2); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(248,156,29,0.3); }

        @media screen and (max-width: 991px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
            .form-grid { grid-template-columns: 1fr; gap: 2rem; }
            .simulator-box { position: static; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <a href="servicios.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver a Servicios</a>

        <div class="dashboard-header">
            <div class="welcome-text">
                <h1><?php echo $esEdicion ? 'Editar Servicio VIP' : 'Crear Servicio VIP'; ?></h1>
                <p>Configure las características de lujo. El simulador derecho mostrará los cambios en tiempo real.</p>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="form-card">
            <form action="servicio_form.php<?php echo $esEdicion ? '?id='.$servicio['id'] : ''; ?>" method="POST" id="srvForm">
                
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($servicio['id']); ?>">

                <div class="form-grid">
                    <div class="form-main-col">
                        <div class="form-group">
                            <label for="titulo">Título del Servicio *</label>
                            <input type="text" id="titulo" name="titulo" class="form-control" value="<?php echo htmlspecialchars($servicio['titulo']); ?>" required placeholder="Ej: Yates Privados" oninput="syncPreview()">
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción del Servicio *</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" required placeholder="Describa la experiencia exclusiva..." oninput="syncPreview()"><?php echo htmlspecialchars($servicio['descripcion']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Seleccionar Icono de Ultra-Lujo *</label>
                            <div class="icon-selector-grid">
                                <?php foreach ($iconos_hospitalidad as $clase => $nombre_icon): ?>
                                    <?php $checked = ($servicio['icono_fontawesome'] === $clase) ? 'checked' : ''; ?>
                                    <label>
                                        <input type="radio" name="icono_fontawesome" value="<?php echo $clase; ?>" class="icon-radio" <?php echo $checked; ?> onchange="updatePreviewIcon('<?php echo $clase; ?>')">
                                        <div class="icon-box">
                                            <i class="<?php echo $clase; ?>"></i>
                                            <span><?php echo $nombre_icon; ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-side-col">
                        <div class="simulator-box">
                            <span class="sim-label"><i class="fa-solid fa-desktop"></i> Simulador en Vivo de la Web</span>
                            
                            <div class="frontend-service-item">
                                <div class="frontend-service-icon">
                                    <i id="previewIcon" class="<?php echo htmlspecialchars($servicio['icono_fontawesome']); ?>"></i>
                                </div>
                                <h4 id="previewTitle"><?php echo !empty($servicio['titulo']) ? htmlspecialchars($servicio['titulo']) : 'Título del Servicio'; ?></h4>
                                <p id="previewDesc"><?php echo !empty($servicio['descripcion']) ? htmlspecialchars($servicio['descripcion']) : 'Aquí se mostrará el texto publicitario que redacte en el bloque izquierdo de manera fluida.'; ?></p>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                            <div class="form-group" style="flex: 1;">
                                <label for="orden">Orden de Lista</label>
                                <input type="number" id="orden" name="orden" class="form-control" value="<?php echo htmlspecialchars($servicio['orden']); ?>">
                            </div>

                            <div class="form-group" style="flex: 1;">
                                <label>Visibilidad Front</label>
                                <div class="switch-container">
                                    <label class="switch">
                                        <input type="checkbox" name="estado" value="1" <?php echo ($servicio['estado'] == 1) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                    <span style="font-size: 0.85rem; font-weight:600;">Visible</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fa-solid <?php echo $esEdicion ? 'fa-floppy-disk' : 'fa-circle-plus'; ?>"></i> 
                            <?php echo $esEdicion ? 'Guardar Cambios VIP' : 'Publicar Servicio'; ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function syncPreview() {
            const inputTitle = document.getElementById('titulo').value;
            const inputDesc = document.getElementById('descripcion').value;
            
            const previewTitle = document.getElementById('previewTitle');
            const previewDesc = document.getElementById('previewDesc');
            
            // Si el campo está vacío, restaurar placeholders elegantes
            previewTitle.innerText = inputTitle.trim() !== '' ? inputTitle : 'Título del Servicio';
            previewDesc.innerText = inputDesc.trim() !== '' ? inputDesc : 'Aquí se mostrará el texto publicitario que redacte en el bloque izquierdo de manera fluida.';
        }

        function updatePreviewIcon(claseIcono) {
            const previewIcon = document.getElementById('previewIcon');
            // Limpiamos clases anteriores y seteamos la nueva
            previewIcon.className = claseIcono;
        }
    </script>
</body>
</html>