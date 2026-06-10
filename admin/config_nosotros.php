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
$nosotros = [
    'id' => '',
    'titulo' => 'La Experiencia Karibes',
    'parrafo_1' => 'Diseñado para los viajeros más exigentes del mundo, Karibes Resorts Internacional redefine el concepto de hospitalidad. Cada detalle de nuestra arquitectura e interiorismo ha sido curado para fundirse armoniosamente con la naturaleza caribeña.',
    'parrafo_2' => 'Despierta con el sonido de las olas, relájate en nuestras piscinas infinitas y déjate consentir por un servicio de mayordomía personalizado disponible 24/7.',
    'imagen_principal' => '',
    'imagen_secundaria' => ''
];

try {
    $stmtGet = $conn->query("SELECT * FROM configuracion_nosotros LIMIT 1");
    if ($stmtGet->rowCount() > 0) {
        $nosotros = $stmtGet->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error obteniendo Nosotros: " . $e->getMessage());
}

// 2. PROCESAR FORMULARIO (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_post = $_POST['id'] ?? '';
    $titulo = trim($_POST['titulo']);
    $parrafo_1 = trim($_POST['parrafo_1']);
    $parrafo_2 = trim($_POST['parrafo_2']);
    
    // Mantener las imágenes actuales por defecto
    $ruta_img_principal = $_POST['img_principal_actual'];
    $ruta_img_secundaria = $_POST['img_secundaria_actual'];

    // Crear carpeta si no existe
    $directorioDestino = '../uploads/nosotros/';
    if (!file_exists($directorioDestino)) {
        mkdir($directorioDestino, 0777, true);
    }

    // PROCESAR SUBIDA DE IMAGEN PRINCIPAL
    if (isset($_FILES['imagen_principal_archivo']) && $_FILES['imagen_principal_archivo']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo1 = time() . '_main_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['imagen_principal_archivo']['name']));
        if (move_uploaded_file($_FILES['imagen_principal_archivo']['tmp_name'], $directorioDestino . $nombreArchivo1)) {
            $ruta_img_principal = 'uploads/nosotros/' . $nombreArchivo1;
        }
    }

    // PROCESAR SUBIDA DE IMAGEN SECUNDARIA
    if (isset($_FILES['imagen_secundaria_archivo']) && $_FILES['imagen_secundaria_archivo']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo2 = time() . '_sec_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['imagen_secundaria_archivo']['name']));
        if (move_uploaded_file($_FILES['imagen_secundaria_archivo']['tmp_name'], $directorioDestino . $nombreArchivo2)) {
            $ruta_img_secundaria = 'uploads/nosotros/' . $nombreArchivo2;
        }
    }

    // ACTUALIZAR O INSERTAR
    try {
        if (!empty($id_post)) {
            $stmt = $conn->prepare("UPDATE configuracion_nosotros SET titulo = :titulo, parrafo_1 = :p1, parrafo_2 = :p2, imagen_principal = :img1, imagen_secundaria = :img2 WHERE id = :id");
            $stmt->bindParam(':id', $id_post, PDO::PARAM_INT);
        } else {
            $stmt = $conn->prepare("INSERT INTO configuracion_nosotros (titulo, parrafo_1, parrafo_2, imagen_principal, imagen_secundaria) VALUES (:titulo, :p1, :p2, :img1, :img2)");
        }

        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':p1', $parrafo_1);
        $stmt->bindParam(':p2', $parrafo_2);
        $stmt->bindParam(':img1', $ruta_img_principal);
        $stmt->bindParam(':img2', $ruta_img_secundaria);

        if ($stmt->execute()) {
            $mensaje = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> La sección 'Experiencia' ha sido actualizada en la página principal.</div>";
            
            if (empty($id_post)) {
                $nosotros['id'] = $conn->lastInsertId();
            }
            $nosotros['titulo'] = $titulo;
            $nosotros['parrafo_1'] = $parrafo_1;
            $nosotros['parrafo_2'] = $parrafo_2;
            $nosotros['imagen_principal'] = $ruta_img_principal;
            $nosotros['imagen_secundaria'] = $ruta_img_secundaria;
        }
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Error de Base de Datos: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Nosotros | Karibes Admin</title>
    
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
        .form-grid { display: grid; grid-template-columns: 1.2fr 1.8fr; gap: 3rem; align-items: start; }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 0.8rem 1rem; border: 1px solid #D1D8E0; border-radius: 6px; font-size: 0.95rem; color: var(--color-text); font-family: var(--font-sans); transition: all 0.3s; }
        .form-control:focus { outline: none; border-color: var(--color-gold); box-shadow: 0 0 10px rgba(248, 156, 29, 0.1); }
        textarea.form-control { height: 110px; resize: none; }

        input[type="file"] { padding: 0.5rem; background: #f8f9fa; cursor: pointer; border: 1px solid #D1D8E0; border-radius: 6px; width: 100%; }
        input[type="file"]::-webkit-file-upload-button { background: var(--color-primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; transition: 0.3s; }
        input[type="file"]::-webkit-file-upload-button:hover { background: var(--color-dark); }

        .btn-submit { background: linear-gradient(135deg, var(--color-primary) 0%, #1592E6 100%); color: var(--color-white); padding: 1rem; border: none; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; width: 100%; margin-top: 1.5rem; transition: 0.3s; box-shadow: 0 4px 15px rgba(8, 61, 107, 0.3); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(8, 61, 107, 0.4); }

        /* MAGIA UX: Simulador "Experiencia" a Escala */
        .simulator-box { border-radius: 12px; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: 1px solid #E9ECEF; position: sticky; top: 20px; background: #FBFBF9; padding: 2rem;}
        .simulator-header { text-align: center; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; color: var(--color-muted); margin-bottom: 2rem; }
        
        .sim-content-wrapper { display: flex; flex-direction: column; gap: 2rem; }

        /* Réplica de la sección Frontend */
        .sim-images-container { position: relative; width: 100%; max-width: 400px; margin: 0 auto; height: 280px; }
        
        .sim-img-main { width: 80%; height: 220px; object-fit: cover; border-radius: 4px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); background: #eee; }
        
        .sim-img-secondary { position: absolute; bottom: 0; right: 0; width: 55%; height: 160px; object-fit: cover; border: 8px solid #FBFBF9; border-radius: 4px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); background: #ddd; z-index: 2; }

        .sim-text-container { text-align: left; }
        
        .sim-section-title { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--color-primary); margin-bottom: 1rem; position: relative; padding-bottom: 1rem; }
        .sim-section-title::after { content: ''; position: absolute; left: 0; bottom: 0; width: 40px; height: 3px; background: linear-gradient(135deg, #FFCC00 0%, #F89C1D 100%); }
        
        .sim-paragraph { font-family: var(--font-sans); font-size: 0.9rem; color: #596A7B; margin-bottom: 1rem; line-height: 1.6; }

        @media screen and (max-width: 1024px) {
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
                <h1>Sección: La Experiencia</h1>
                <p>Configure la narrativa y las imágenes superpuestas que presentan el concepto de su resort al mundo.</p>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="form-card">
            <form action="config_nosotros.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($nosotros['id']); ?>">
                <input type="hidden" name="img_principal_actual" value="<?php echo htmlspecialchars($nosotros['imagen_principal']); ?>">
                <input type="hidden" name="img_secundaria_actual" value="<?php echo htmlspecialchars($nosotros['imagen_secundaria']); ?>">

                <div class="form-grid">
                    
                    <div class="form-main-col">
                        <div class="form-group">
                            <label for="titulo">Título de la Sección *</label>
                            <input type="text" id="titulo" name="titulo" class="form-control" value="<?php echo htmlspecialchars($nosotros['titulo']); ?>" required oninput="syncPreview()">
                        </div>

                        <div class="form-group">
                            <label for="parrafo_1">Párrafo Principal (Filosofía) *</label>
                            <textarea id="parrafo_1" name="parrafo_1" class="form-control" required oninput="syncPreview()"><?php echo htmlspecialchars($nosotros['parrafo_1']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="parrafo_2">Párrafo Secundario (Detalles) *</label>
                            <textarea id="parrafo_2" name="parrafo_2" class="form-control" required oninput="syncPreview()"><?php echo htmlspecialchars($nosotros['parrafo_2']); ?></textarea>
                        </div>

                        <div style="background: #F8FAFC; padding: 1.5rem; border-radius: 8px; border: 1px solid #D1D8E0; margin-top: 2rem;">
                            <h4 style="margin-bottom: 1rem; color: var(--color-primary); font-size: 0.95rem;"><i class="fa-solid fa-images"></i> Composición Fotográfica</h4>
                            
                            <div class="form-group">
                                <label for="imagen_principal_archivo">1. Imagen Principal (Fondo Largo)</label>
                                <input type="file" id="imagen_principal_archivo" name="imagen_principal_archivo" accept="image/png, image/jpeg, image/webp" onchange="previewFileMain()">
                                <small style="color: var(--color-muted);">Sugerencia: Foto general del lobby o exterior (Aprox. 800x600px).</small>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="imagen_secundaria_archivo">2. Imagen Secundaria (Superpuesta)</label>
                                <input type="file" id="imagen_secundaria_archivo" name="imagen_secundaria_archivo" accept="image/png, image/jpeg, image/webp" onchange="previewFileSecondary()">
                                <small style="color: var(--color-muted);">Sugerencia: Un detalle de lujo, vista de balcón o gastronomía (Aprox. 600x600px).</small>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Guardar y Publicar
                        </button>
                    </div>

                    <div class="form-side-col">
                        <div class="simulator-box">
                            <div class="simulator-header">
                                <i class="fa-solid fa-eye"></i> Vista Previa en Tiempo Real
                            </div>
                            
                            <div class="sim-content-wrapper">
                                <div class="sim-images-container">
                                    <?php 
                                        $imgMain = !empty($nosotros['imagen_principal']) ? '../' . $nosotros['imagen_principal'] : 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80';
                                        $imgSec = !empty($nosotros['imagen_secundaria']) ? '../' . $nosotros['imagen_secundaria'] : 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80';
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imgMain); ?>" class="sim-img-main" id="simImgMain" alt="Imagen Principal">
                                    <img src="<?php echo htmlspecialchars($imgSec); ?>" class="sim-img-secondary" id="simImgSec" alt="Imagen Secundaria">
                                </div>

                                <div class="sim-text-container">
                                    <h2 class="sim-section-title" id="simTitulo"><?php echo htmlspecialchars($nosotros['titulo']); ?></h2>
                                    <p class="sim-paragraph" id="simParrafo1"><?php echo htmlspecialchars($nosotros['parrafo_1']); ?></p>
                                    <p class="sim-paragraph" id="simParrafo2"><?php echo htmlspecialchars($nosotros['parrafo_2']); ?></p>
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
            document.getElementById('simTitulo').innerText = document.getElementById('titulo').value || 'La Experiencia Karibes';
            document.getElementById('simParrafo1').innerText = document.getElementById('parrafo_1').value || 'Redacta el primer párrafo...';
            document.getElementById('simParrafo2').innerText = document.getElementById('parrafo_2').value || 'Redacta el segundo párrafo...';
        }

        // Lógica para previsualizar Imagen Principal
        function previewFileMain() {
            const preview = document.getElementById('simImgMain');
            const file = document.getElementById('imagen_principal_archivo').files[0];
            const reader = new FileReader();

            reader.addEventListener("load", function () {
                preview.src = reader.result;
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        // Lógica para previsualizar Imagen Secundaria
        function previewFileSecondary() {
            const preview = document.getElementById('simImgSec');
            const file = document.getElementById('imagen_secundaria_archivo').files[0];
            const reader = new FileReader();

            reader.addEventListener("load", function () {
                preview.src = reader.result;
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>