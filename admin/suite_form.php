<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/conexion.php';

$db = new Conexion();
$conn = $db->conectar();

$mensaje = '';
$esEdicion = false;

// Variables por defecto para el formulario
$suite = [
    'id' => '',
    'nombre' => '',
    'descripcion_corta' => '',
    'precio_noche' => '',
    'imagen' => '',
    'orden' => 0,
    'estado' => 1,
    'amenidades' => []
];

// 1. Detectar si es una EDICIÓN
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $esEdicion = true;
    $id_suite = (int)$_GET['id'];
    
    $stmtGet = $conn->prepare("SELECT * FROM suites WHERE id = :id");
    $stmtGet->bindParam(':id', $id_suite, PDO::PARAM_INT);
    $stmtGet->execute();
    
    if ($stmtGet->rowCount() > 0) {
        $row = $stmtGet->fetch();
        $suite = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'descripcion_corta' => $row['descripcion_corta'],
            'precio_noche' => $row['precio_noche'],
            'imagen' => $row['imagen'],
            'orden' => $row['orden'],
            'estado' => $row['estado'],
            'amenidades' => json_decode($row['amenidades_json'], true) ?? []
        ];
    } else {
        header("Location: suites.php");
        exit;
    }
}

// 2. Procesar el Formulario (POST & FILES)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_post = $_POST['id'] ?? '';
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion_corta']);
    $precio = (float)$_POST['precio_noche'];
    $orden = (int)$_POST['orden'];
    $estado = isset($_POST['estado']) ? 1 : 0;
    $amenidades_json = json_encode($_POST['amenidades'] ?? []);
    
    // Mantenemos la imagen anterior por defecto
    $ruta_imagen_final = $_POST['imagen_actual']; 

    // --- MAGIA: PROCESAR SUBIDA DE IMAGEN ---
    if (isset($_FILES['imagen_archivo']) && $_FILES['imagen_archivo']['error'] === UPLOAD_ERR_OK) {
        $directorioDestino = '../uploads/suites/';
        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        $nombreArchivo = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['imagen_archivo']['name']));
        $rutaFisica = $directorioDestino . $nombreArchivo;
        
        if (move_uploaded_file($_FILES['imagen_archivo']['tmp_name'], $rutaFisica)) {
            $ruta_imagen_final = 'uploads/suites/' . $nombreArchivo;
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al mover el archivo al servidor.</div>";
        }
    }

    try {
        if (!empty($id_post)) {
            $stmt = $conn->prepare("UPDATE suites SET nombre = :nombre, descripcion_corta = :desc, precio_noche = :precio, imagen = :img, amenidades_json = :amenidades, orden = :orden, estado = :estado WHERE id = :id");
            $stmt->bindParam(':id', $id_post, PDO::PARAM_INT);
        } else {
            $stmt = $conn->prepare("INSERT INTO suites (nombre, descripcion_corta, precio_noche, imagen, amenidades_json, orden, estado) VALUES (:nombre, :desc, :precio, :img, :amenidades, :orden, :estado)");
        }

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':desc', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':img', $ruta_imagen_final);
        $stmt->bindParam(':amenidades', $amenidades_json);
        $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);
        $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $accionTxt = !empty($id_post) ? 'actualizada' : 'creada';
            $mensaje = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> Habitación $accionTxt exitosamente. <a href='suites.php' style='color:inherit; text-decoration:underline; font-weight:600;'>Volver al catálogo</a></div>";
            
            if (empty($id_post)) {
                $esEdicion = true;
                $suite['id'] = $conn->lastInsertId();
                $suite['nombre'] = $nombre;
                $suite['descripcion_corta'] = $descripcion;
                $suite['precio_noche'] = $precio;
                $suite['imagen'] = $ruta_imagen_final;
                $suite['orden'] = $orden;
                $suite['estado'] = $estado;
                $suite['amenidades'] = json_decode($amenidades_json, true);
            } else {
                $suite['imagen'] = $ruta_imagen_final;
            }
        }
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Error DB: " . $e->getMessage() . "</div>";
    }
}

// MAGIA UX: Catálogo de amenidades adaptado al Resort Climatizado de Huancayo
$catalogo_amenidades = [
    'fa-solid fa-temperature-half' => 'Climatización Inverter',
    'fa-solid fa-hot-tub-person' => 'Jacuzzi / Tina',
    'fa-solid fa-couch' => 'Terraza Interior',
    'fa-solid fa-wifi' => 'Wi-Fi de Alta Velocidad',
    'fa-solid fa-tv' => 'Smart TV',
    'fa-solid fa-martini-glass' => 'Frigobar',
    'fa-solid fa-mug-hot' => 'Hervidora y Café',
    'fa-solid fa-shirt' => 'Plancha / Secadora',
    'fa-solid fa-vault' => 'Caja Fuerte',
    'fa-solid fa-bell-concierge' => 'Atención 24/7'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Editar Habitación' : 'Nueva Habitación'; ?> | Karibes Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --color-bg: #F4F7F6;
            --color-dark: #0A1118;
            /* Nuevos Colores Karibes */
            --color-primary: #0054A6; 
            --color-gold: #F15A24;    
            --color-white: #FFFFFF;
            --color-text: #2C3E50;
            --color-muted: #8798A5;
            --shadow-card: 0 5px 20px rgba(0,0,0,0.05);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--color-bg); color: var(--color-text); }
        .main-content { margin-left: 280px; padding: 2rem 3rem; min-height: 100vh; }
        .dashboard-header { margin-bottom: 2rem; }
        .welcome-text h1 { font-family: 'Playfair Display', serif; color: var(--color-primary); font-size: 2.2rem; }
        .welcome-text p { color: var(--color-muted); font-size: 0.95rem; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--color-muted); text-decoration: none; font-weight: 600; margin-bottom: 1.5rem; transition: 0.3s;}
        .btn-back:hover { color: var(--color-gold); }
        
        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 500; font-size: 0.9rem; display: flex; gap: 10px; align-items: center;}
        .alert-success { background: #D4EDDA; color: #155724; border-left: 4px solid #28A745; }
        .alert-danger { background: #F8D7DA; color: #721C24; border-left: 4px solid #E74C3C; }
        
        .form-card { background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card); padding: 2.5rem; }
        .form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 3rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 0.8rem 1rem; border: 1px solid #D1D8E0; border-radius: 6px; font-size: 0.95rem; }
        .form-control:focus { outline: none; border-color: var(--color-gold); box-shadow: 0 0 10px rgba(241, 90, 36, 0.1); }
        
        /* Input File Personalizado */
        input[type="file"] { padding: 0.5rem; background: #f8f9fa; cursor: pointer; border: 1px solid #D1D8E0; border-radius: 6px;}
        input[type="file"]::-webkit-file-upload-button { background: var(--color-primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; transition: 0.3s; font-weight: 600;}
        input[type="file"]::-webkit-file-upload-button:hover { background: var(--color-dark); }

        .image-preview-box { width: 100%; height: 220px; background: #E9ECEF; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-top: 1rem; border: 2px dashed #D1D8E0; position: relative;}
        .image-preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .image-preview-box .placeholder { color: var(--color-muted); font-size: 0.85rem; text-align: center; position: absolute; }
        .image-preview-box .placeholder i { font-size: 2.5rem; margin-bottom: 0.5rem; display: block; }
        
        /* Moneda Input */
        .input-group { position: relative; display: flex; align-items: stretch; width: 100%; }
        .input-group-text { padding: 0.8rem 1rem; background: #F8FAFC; border: 1px solid #D1D8E0; border-right: none; border-radius: 6px 0 0 6px; color: var(--color-primary); font-weight: 600; }
        .input-group .form-control { border-radius: 0 6px 6px 0; }

        .amenities-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem; margin-top: 0.5rem; }
        .amenity-checkbox { display: none; }
        .amenity-card { display: flex; flex-direction: column; align-items: center; padding: 1rem; background: var(--color-bg); border: 2px solid transparent; border-radius: 8px; cursor: pointer; text-align: center; transition: 0.2s;}
        .amenity-checkbox:checked + .amenity-card { background: rgba(0, 84, 166, 0.05); border-color: var(--color-primary); color: var(--color-primary); }
        .amenity-checkbox:checked + .amenity-card i, .amenity-checkbox:checked + .amenity-card span { color: var(--color-primary); font-weight: 600;}

        .switch-container { display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem; }
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #27AE60; }
        input:checked + .slider:before { transform: translateX(24px); }

        .btn-submit { background: linear-gradient(135deg, var(--color-primary) 0%, #00AEEF 100%); color: var(--color-white); padding: 1rem; border: none; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; width: 100%; margin-top: 1rem; transition: 0.3s;}
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0, 84, 166, 0.3); }

        @media screen and (max-width: 991px) { .main-content { margin-left: 0; padding: 1.5rem; } .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <a href="suites.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver a los Planes</a>

        <div class="dashboard-header">
            <div class="welcome-text">
                <h1><?php echo $esEdicion ? 'Editar Habitación / Suite' : 'Crear Nueva Habitación'; ?></h1>
                <p>Gestione la imagen (subida local), tarifa en Soles Peruanos y características del alojamiento.</p>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="form-card">
            <form action="suite_form.php<?php echo $esEdicion ? '?id='.$suite['id'] : ''; ?>" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($suite['id']); ?>">
                <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($suite['imagen']); ?>">

                <div class="form-grid">
                    <div class="form-main-col">
                        <div class="form-group">
                            <label for="nombre">Nombre de la Habitación / Suite *</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($suite['nombre']); ?>" required placeholder="Ej: Suite con Jacuzzi y Terraza">
                        </div>

                        <div class="form-group">
                            <label for="descripcion_corta">Descripción Comercial *</label>
                            <textarea id="descripcion_corta" name="descripcion_corta" class="form-control" required rows="4" placeholder="Redacte un texto atractivo... Ej: Habitación amplia con calefacción sistema Inverter y terraza al interior del área encapsulada."><?php echo htmlspecialchars($suite['descripcion_corta']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Amenidades Incluidas</label>
                            <div class="amenities-grid">
                                <?php foreach ($catalogo_amenidades as $iconoClase => $texto): ?>
                                    <?php $isChecked = in_array($iconoClase, $suite['amenidades']) ? 'checked' : ''; ?>
                                    <label>
                                        <input type="checkbox" name="amenidades[]" value="<?php echo $iconoClase; ?>" class="amenity-checkbox" <?php echo $isChecked; ?>>
                                        <div class="amenity-card">
                                            <i class="<?php echo $iconoClase; ?>"></i>
                                            <span style="font-size:0.75rem; color:#8798A5; margin-top: 5px;"><?php echo $texto; ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-side-col">
                        <div class="form-group">
                            <label for="precio_noche">Tarifa del Alojamiento *</label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" id="precio_noche" name="precio_noche" class="form-control" step="0.01" value="<?php echo htmlspecialchars($suite['precio_noche']); ?>" required placeholder="Ej: 250.00">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="imagen_archivo">Subir Imagen de Portada</label>
                            <input type="file" id="imagen_archivo" name="imagen_archivo" class="form-control" accept="image/png, image/jpeg, image/webp" onchange="previewFile()">
                            <small style="color: var(--color-muted); display:block; margin-top:5px;">Archivos soportados: JPG, PNG, WEBP.</small>
                            
                            <div class="image-preview-box">
                                <?php 
                                    // Verificación de ruta para preview local
                                    $hasImage = !empty($suite['imagen']); 
                                    $img_src = $suite['imagen'];
                                    if($hasImage && strpos($img_src, 'http') === false) {
                                        $img_src = '../' . $img_src; 
                                    }
                                ?>
                                <img id="imgPreview" src="<?php echo htmlspecialchars($img_src); ?>" style="<?php echo $hasImage ? 'display:block;' : 'display:none;'; ?>">
                                
                                <div id="imgPlaceholder" class="placeholder" style="<?php echo $hasImage ? 'display:none;' : 'display:block;'; ?>">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    Seleccione una imagen para previsualizarla
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <div class="form-group" style="flex: 1;">
                                <label for="orden">Orden de Lista</label>
                                <input type="number" id="orden" name="orden" class="form-control" value="<?php echo htmlspecialchars($suite['orden']); ?>">
                            </div>

                            <div class="form-group" style="flex: 1;">
                                <label>Publicado en Web</label>
                                <div class="switch-container">
                                    <label class="switch">
                                        <input type="checkbox" name="estado" value="1" <?php echo ($suite['estado'] == 1) ? 'checked' : ''; ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fa-solid <?php echo $esEdicion ? 'fa-floppy-disk' : 'fa-plus'; ?>"></i> 
                            <?php echo $esEdicion ? 'Guardar Cambios' : 'Registrar Habitación'; ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Lógica para previsualizar la foto desde la computadora ANTES de subirla al servidor
        function previewFile() {
            const preview = document.getElementById('imgPreview');
            const file = document.getElementById('imagen_archivo').files[0];
            const placeholder = document.getElementById('imgPlaceholder');
            const reader = new FileReader();

            reader.addEventListener("load", function () {
                preview.src = reader.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>