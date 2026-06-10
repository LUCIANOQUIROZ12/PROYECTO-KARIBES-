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

// 1. OBTENER DATOS ACTUALES (Siempre habrá solo 1 registro de configuración)
$hero = [
    'id' => '',
    'imagen_fondo' => '',
    'subtitulo' => 'El paraíso a tu medida',
    'titulo_html' => 'Redescubre el <span>Lujo</span> en el Caribe',
    'descripcion' => 'Un santuario de exclusividad donde el océano se encuentra con la sofisticación absoluta.',
    'texto_boton' => 'Reservar Ahora'
];

try {
    $stmtGet = $conn->query("SELECT * FROM configuracion_hero LIMIT 1");
    if ($stmtGet->rowCount() > 0) {
        $hero = $stmtGet->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error obteniendo Hero: " . $e->getMessage());
}

// 2. PROCESAR FORMULARIO (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_post = $_POST['id'] ?? '';
    $subtitulo = trim($_POST['subtitulo']);
    $titulo_html = trim($_POST['titulo_html']);
    $descripcion = trim($_POST['descripcion']);
    $texto_boton = trim($_POST['texto_boton']);
    
    // Mantener la imagen actual por defecto
    $ruta_imagen_final = $_POST['imagen_actual'];

    // PROCESAR SUBIDA DE NUEVA IMAGEN DE FONDO
    if (isset($_FILES['imagen_fondo_archivo']) && $_FILES['imagen_fondo_archivo']['error'] === UPLOAD_ERR_OK) {
        $directorioDestino = '../uploads/hero/';
        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        $nombreArchivo = time() . '_hero_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['imagen_fondo_archivo']['name']));
        $rutaFisica = $directorioDestino . $nombreArchivo;
        
        if (move_uploaded_file($_FILES['imagen_fondo_archivo']['tmp_name'], $rutaFisica)) {
            $ruta_imagen_final = 'uploads/hero/' . $nombreArchivo;
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al subir la imagen al servidor.</div>";
        }
    }

    // SI NO HAY ERROR PREVIO, ACTUALIZAMOS O INSERTAMOS
    if (empty($mensaje)) {
        try {
            if (!empty($id_post)) {
                $stmt = $conn->prepare("UPDATE configuracion_hero SET subtitulo = :sub, titulo_html = :titulo, descripcion = :desc, texto_boton = :btn, imagen_fondo = :img WHERE id = :id");
                $stmt->bindParam(':id', $id_post, PDO::PARAM_INT);
            } else {
                $stmt = $conn->prepare("INSERT INTO configuracion_hero (subtitulo, titulo_html, descripcion, texto_boton, imagen_fondo) VALUES (:sub, :titulo, :desc, :btn, :img)");
            }

            $stmt->bindParam(':sub', $subtitulo);
            $stmt->bindParam(':titulo', $titulo_html);
            $stmt->bindParam(':desc', $descripcion);
            $stmt->bindParam(':btn', $texto_boton);
            $stmt->bindParam(':img', $ruta_imagen_final);

            if ($stmt->execute()) {
                $mensaje = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> Portada actualizada exitosamente en la web pública.</div>";
                
                // Actualizar array local para reflejar cambios inmediatos
                if (empty($id_post)) {
                    $hero['id'] = $conn->lastInsertId();
                }
                $hero['subtitulo'] = $subtitulo;
                $hero['titulo_html'] = $titulo_html;
                $hero['descripcion'] = $descripcion;
                $hero['texto_boton'] = $texto_boton;
                $hero['imagen_fondo'] = $ruta_imagen_final;
            }
        } catch (PDOException $e) {
            $mensaje = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Error de Base de Datos: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Portada | Karibes Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
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

        .dashboard-header { margin-bottom: 2rem; }
        .welcome-text h1 { font-family: 'Playfair Display', serif; color: var(--color-primary); font-size: 2.2rem; margin-bottom: 0.3rem; }
        .welcome-text p { color: var(--color-muted); font-size: 0.95rem; }

        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 500; font-size: 0.9rem; display: flex; gap: 10px; align-items: center;}
        .alert-success { background: #D4EDDA; color: #155724; border-left: 4px solid #28A745; }
        .alert-danger { background: #F8D7DA; color: #721C24; border-left: 4px solid #E74C3C; }

        .form-card { background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card); padding: 2.5rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start; }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 0.8rem 1rem; border: 1px solid #D1D8E0; border-radius: 6px; font-size: 0.95rem; color: var(--color-text); font-family: var(--font-sans); transition: all 0.3s; }
        .form-control:focus { outline: none; border-color: var(--color-gold); box-shadow: 0 0 10px rgba(248, 156, 29, 0.1); }
        textarea.form-control { height: 100px; resize: none; }

        /* Estilo para input file */
        input[type="file"] { padding: 0.5rem; background: #f8f9fa; cursor: pointer; border: 1px solid #D1D8E0; border-radius: 6px; width: 100%; }
        input[type="file"]::-webkit-file-upload-button { background: var(--color-primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; transition: 0.3s; }
        input[type="file"]::-webkit-file-upload-button:hover { background: var(--color-dark); }

        .info-badge { background: rgba(8, 61, 107, 0.05); border-left: 3px solid var(--color-primary); padding: 10px; font-size: 0.8rem; color: var(--color-muted); border-radius: 4px; margin-top: 5px; }
        .info-badge code { background: #E9ECEF; padding: 2px 5px; border-radius: 3px; color: var(--color-primary); font-weight: 600; }

        /* MAGIA UX: Simulador Hero a Escala */
        .simulator-box { border-radius: 12px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.3); border: 4px solid var(--color-dark); position: sticky; top: 20px; }
        .simulator-header { background: var(--color-dark); color: var(--color-gold); padding: 10px; text-align: center; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
        
        .sim-hero {
            position: relative;
            width: 100%;
            height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: var(--color-dark);
        }
        
        .sim-hero-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-size: cover;
            background-position: center;
            z-index: 1;
            transition: background-image 0.5s ease;
        }

        .sim-hero-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(10, 17, 24, 0.4) 0%, rgba(8, 61, 107, 0.6) 100%);
            z-index: 2;
        }

        .sim-hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            color: var(--color-white);
            padding: 0 2rem;
            max-width: 90%;
        }

        .sim-hero-subtitle {
            font-family: var(--font-sans);
            font-size: 0.8rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 1rem;
            color: var(--color-gold);
            display: block;
        }

        .sim-hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: var(--color-white);
            text-shadow: 0 4px 15px rgba(0,0,0,0.3);
            font-weight: 700;
        }

        .sim-hero-title span { font-style: italic; color: var(--color-gold); }

        .sim-hero-desc { font-size: 0.9rem; margin-bottom: 2rem; font-weight: 300; line-height: 1.6; }

        .sim-btn {
            display: inline-block;
            padding: 0.8rem 1.8rem;
            background: linear-gradient(135deg, #FFCC00 0%, #F89C1D 100%);
            color: var(--color-white);
            font-family: var(--font-sans);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-radius: 2px;
            text-decoration: none;
        }

        .btn-submit { background: linear-gradient(135deg, var(--color-primary) 0%, #1592E6 100%); color: var(--color-white); padding: 1rem; border: none; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; width: 100%; margin-top: 1.5rem; transition: 0.3s; box-shadow: 0 4px 15px rgba(8, 61, 107, 0.3); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(8, 61, 107, 0.4); }

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
        <div class="dashboard-header">
            <div class="welcome-text">
                <h1>Apariencia de Portada</h1>
                <p>Modifique los textos persuasivos y la imagen de fondo principal. Los cambios se visualizarán en vivo en la tarjeta derecha.</p>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="form-card">
            <form action="config_hero.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($hero['id']); ?>">
                <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($hero['imagen_fondo']); ?>">

                <div class="form-grid">
                    <div class="form-main-col">
                        
                        <div class="form-group">
                            <label for="subtitulo">Subtítulo (Superior)</label>
                            <input type="text" id="subtitulo" name="subtitulo" class="form-control" value="<?php echo htmlspecialchars($hero['subtitulo']); ?>" required oninput="syncPreview()">
                        </div>

                        <div class="form-group">
                            <label for="titulo_html">Título Principal (Soporta HTML) *</label>
                            <input type="text" id="titulo_html" name="titulo_html" class="form-control" value="<?php echo htmlspecialchars($hero['titulo_html']); ?>" required oninput="syncPreview()">
                            <div class="info-badge">
                                💡 Envuelve una palabra en <code>&lt;span&gt;Palabra&lt;/span&gt;</code> para pintarla en <strong>Dorado Metálico y Cursiva</strong>.
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Párrafo de Bienvenida *</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" required oninput="syncPreview()"><?php echo htmlspecialchars($hero['descripcion']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="texto_boton">Texto del Botón de Acción *</label>
                            <input type="text" id="texto_boton" name="texto_boton" class="form-control" value="<?php echo htmlspecialchars($hero['texto_boton']); ?>" required oninput="syncPreview()">
                        </div>

                        <div class="form-group" style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 1.5rem;">
                            <label for="imagen_fondo_archivo" style="color:var(--color-gold);">📸 Actualizar Imagen de Fondo (Alta Resolución)</label>
                            <input type="file" id="imagen_fondo_archivo" name="imagen_fondo_archivo" accept="image/png, image/jpeg, image/webp" onchange="previewFile()">
                            <small style="color: var(--color-muted); display:block; margin-top:5px;">Para que la portada ocupe toda la pantalla, sugerimos fotos horizontales panorámicas (Mínimo 1920x1080px).</small>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Publicar Cambios en la Web
                        </button>
                    </div>

                    <div class="form-side-col">
                        <div class="simulator-box">
                            <div class="simulator-header">
                                <i class="fa-solid fa-display"></i> Vista Previa (Simulador Web)
                            </div>
                            <div class="sim-hero">
                                <?php 
                                    $bgImage = !empty($hero['imagen_fondo']) ? '../' . $hero['imagen_fondo'] : 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1920&q=80';
                                ?>
                                <div class="sim-hero-bg" id="simBg" style="background-image: url('<?php echo htmlspecialchars($bgImage); ?>');"></div>
                                <div class="sim-hero-overlay"></div>
                                
                                <div class="sim-hero-content">
                                    <span class="sim-hero-subtitle" id="simSubtitulo"><?php echo htmlspecialchars($hero['subtitulo']); ?></span>
                                    <h1 class="sim-hero-title" id="simTitulo"><?php echo $hero['titulo_html']; ?></h1>
                                    <p class="sim-hero-desc" id="simDesc"><?php echo htmlspecialchars($hero['descripcion']); ?></p>
                                    <a href="#" class="sim-btn" id="simBtn" onclick="return false;"><?php echo htmlspecialchars($hero['texto_boton']); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Sincronizar textos al instante
        function syncPreview() {
            document.getElementById('simSubtitulo').innerText = document.getElementById('subtitulo').value || 'Subtítulo';
            
            // Usamos innerHTML para que el administrador vea el efecto real de las etiquetas <span>
            document.getElementById('simTitulo').innerHTML = document.getElementById('titulo_html').value || 'Título Principal';
            
            document.getElementById('simDesc').innerText = document.getElementById('descripcion').value || 'Descripción';
            document.getElementById('simBtn').innerText = document.getElementById('texto_boton').value || 'Botón';
        }

        // Lógica para previsualizar la foto desde la computadora ANTES de subirla al servidor
        function previewFile() {
            const bgElement = document.getElementById('simBg');
            const file = document.getElementById('imagen_fondo_archivo').files[0];
            const reader = new FileReader();

            reader.addEventListener("load", function () {
                // Actualiza el background-image del simulador instantáneamente
                bgElement.style.backgroundImage = `url('${reader.result}')`;
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>