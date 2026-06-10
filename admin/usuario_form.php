<?php
session_start();

// Validación estricta de seguridad y RBAC
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['admin_rol'] !== 'SuperAdmin') {
    header("Location: dashboard.php");
    exit;
}

require_once '../config/conexion.php';

$db = new Conexion();
$conn = $db->conectar();

$mensaje = '';
$esEdicion = false;
$esMiPerfil = false;

// Variables por defecto (Nuevo Usuario)
$usuario_data = [
    'id' => '',
    'usuario' => '',
    'nombre_completo' => '',
    'rol' => 'Recepcion', // Por defecto el rol más inofensivo
    'estado' => 1
];

// 1. DETECTAR SI ES EDICIÓN
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $esEdicion = true;
    $id_usuario = (int)$_GET['id'];
    
    // Verificar si estoy editando mi propio perfil
    if ($id_usuario === (int)$_SESSION['admin_id']) {
        $esMiPerfil = true;
    }
    
    $stmtGet = $conn->prepare("SELECT id, usuario, nombre_completo, rol, estado FROM usuarios_admin WHERE id = :id");
    $stmtGet->bindParam(':id', $id_usuario, PDO::PARAM_INT);
    $stmtGet->execute();
    
    if ($stmtGet->rowCount() > 0) {
        $usuario_data = $stmtGet->fetch(PDO::FETCH_ASSOC);
    } else {
        header("Location: usuarios.php");
        exit;
    }
}

// 2. PROCESAR FORMULARIO (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_post = $_POST['id'] ?? '';
    $nombre = trim($_POST['nombre_completo']);
    // Sanitizar el username desde PHP por seguridad adicional (sin espacios, minúsculas)
    $username = strtolower(preg_replace('/\s+/', '', trim($_POST['usuario']))); 
    $password_raw = $_POST['password'] ?? '';
    
    // Si es mi propio perfil, fuerzo los valores vitales para evitar que me auto-sabotee mediante inspección de elementos HTML
    if ($esMiPerfil) {
        $rol = 'SuperAdmin';
        $estado = 1;
    } else {
        $rol = $_POST['rol'] ?? 'Recepcion';
        $estado = isset($_POST['estado']) ? 1 : 0;
    }

    try {
        // Verificar que el nombre de usuario no exista ya (excepto si soy yo mismo)
        $stmtCheck = $conn->prepare("SELECT id FROM usuarios_admin WHERE usuario = :user AND id != :id");
        $stmtCheck->bindParam(':user', $username);
        $stmtCheck->bindValue(':id', $id_post ?: 0, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            $mensaje = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> El nombre de usuario '@$username' ya está en uso por otro empleado.</div>";
        } else {
            // LÓGICA DE ACTUALIZACIÓN O INSERCIÓN
            if (!empty($id_post)) {
                // UPDATE
                if (!empty($password_raw)) {
                    // Actualiza todo INCLUYENDO contraseña
                    $hash = password_hash($password_raw, PASSWORD_BCRYPT);
                    $stmt = $conn->prepare("UPDATE usuarios_admin SET usuario = :user, password = :pass, nombre_completo = :nombre, rol = :rol, estado = :estado WHERE id = :id");
                    $stmt->bindParam(':pass', $hash);
                } else {
                    // Actualiza todo EXCEPTO la contraseña
                    $stmt = $conn->prepare("UPDATE usuarios_admin SET usuario = :user, nombre_completo = :nombre, rol = :rol, estado = :estado WHERE id = :id");
                }
                $stmt->bindParam(':id', $id_post, PDO::PARAM_INT);
                
            } else {
                // INSERT (Contraseña obligatoria)
                if (empty($password_raw)) {
                    throw new Exception("La contraseña es obligatoria para nuevos usuarios.");
                }
                $hash = password_hash($password_raw, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO usuarios_admin (usuario, password, nombre_completo, rol, estado) VALUES (:user, :pass, :nombre, :rol, :estado)");
                $stmt->bindParam(':pass', $hash);
            }

            $stmt->bindParam(':user', $username);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':rol', $rol);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $accionTxt = !empty($id_post) ? 'actualizadas' : 'creadas';
                $mensaje = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> Credenciales $accionTxt exitosamente. <a href='usuarios.php' style='color:inherit; text-decoration:underline;'>Volver al personal</a></div>";
                
                if (empty($id_post)) {
                    $esEdicion = true;
                    $usuario_data['id'] = $conn->lastInsertId();
                }
                
                // Si me edité a mí mismo, actualizo mi nombre en la sesión actual
                if ($esMiPerfil) {
                    $_SESSION['admin_nombre'] = $nombre;
                    $_SESSION['admin_usuario'] = $username;
                }

                $usuario_data['usuario'] = $username;
                $usuario_data['nombre_completo'] = $nombre;
                $usuario_data['rol'] = $rol;
                $usuario_data['estado'] = $estado;
            }
        }
    } catch (Exception $e) {
        $mensaje = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Error: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $esEdicion ? 'Editar Credenciales' : 'Registrar Staff'; ?> | Karibes Admin</title>
    
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

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--color-bg); color: var(--color-text); }
        .main-content { margin-left: 280px; padding: 2rem 3rem; min-height: 100vh; }

        .dashboard-header { margin-bottom: 2rem; }
        .welcome-text h1 { font-family: 'Playfair Display', serif; color: var(--color-primary); font-size: 2.2rem; margin-bottom: 0.3rem; }
        .welcome-text p { color: var(--color-muted); font-size: 0.95rem; }

        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--color-muted); text-decoration: none; font-weight: 600; margin-bottom: 1.5rem; transition: color 0.3s; }
        .btn-back:hover { color: var(--color-primary); }

        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 500; font-size: 0.9rem; display: flex; gap: 10px; align-items: center;}
        .alert-success { background: #D4EDDA; color: #155724; border-left: 4px solid #28A745; }
        .alert-danger { background: #F8D7DA; color: #721C24; border-left: 4px solid #E74C3C; }
        .alert-info { background: #CCE5FF; color: #004085; border-left: 4px solid #0056B3; }

        .form-card { background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card); padding: 2.5rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start; }

        .form-group { margin-bottom: 1.5rem; position: relative; }
        .form-group label { display: block; font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 0.8rem 1rem; border: 1px solid #D1D8E0; border-radius: 6px; font-size: 0.95rem; color: var(--color-text); font-family: var(--font-sans); transition: all 0.3s; }
        .form-control:focus { outline: none; border-color: var(--color-gold); box-shadow: 0 0 10px rgba(248, 156, 29, 0.1); }
        .form-control:disabled { background: #E9ECEF; cursor: not-allowed; }

        /* Magia UX: Input con ícono interno */
        .input-icon-wrapper { position: relative; }
        .input-icon-wrapper i { position: absolute; top: 50%; left: 15px; transform: translateY(-50%); color: var(--color-muted); }
        .input-icon-wrapper input { padding-left: 40px; }
        .toggle-password { position: absolute; top: 50%; right: 15px; transform: translateY(-50%); color: var(--color-muted); cursor: pointer; transition: 0.3s; }
        .toggle-password:hover { color: var(--color-primary); }

        /* MAGIA UX: Selector de Rol Visual */
        .role-selector-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; margin-top: 0.5rem; }
        .role-radio { display: none; }
        .role-card { display: flex; align-items: flex-start; gap: 15px; padding: 1.2rem; background: var(--color-bg); border: 2px solid transparent; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; }
        .role-card i { font-size: 1.5rem; color: var(--color-muted); margin-top: 2px; }
        .role-content h4 { font-size: 0.95rem; color: var(--color-text); margin-bottom: 3px; }
        .role-content p { font-size: 0.75rem; color: var(--color-muted); line-height: 1.4; }

        .role-radio:checked + .role-card { background: rgba(8, 61, 107, 0.04); border-color: var(--color-primary); }
        .role-radio:checked + .role-card i, .role-radio:checked + .role-card h4 { color: var(--color-primary); }
        
        .role-radio:checked + .role-card.role-superadmin { border-color: var(--color-gold); background: rgba(248, 156, 29, 0.05); }
        .role-radio:checked + .role-card.role-superadmin i, .role-radio:checked + .role-card.role-superadmin h4 { color: #B9710B; }

        .switch-container { display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem; }
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #27AE60; }
        input:checked + .slider:before { transform: translateX(24px); }
        .slider.disabled { background-color: #A5D6A7; cursor: not-allowed; }

        .btn-submit { background: linear-gradient(135deg, var(--color-primary) 0%, #1592E6 100%); color: var(--color-white); padding: 1rem; border: none; border-radius: 6px; font-weight: 600; font-size: 1rem; cursor: pointer; width: 100%; margin-top: 1.5rem; transition: 0.3s; box-shadow: 0 4px 15px rgba(8, 61, 107, 0.3); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(8, 61, 107, 0.4); }

        @media screen and (max-width: 991px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
            .form-grid { grid-template-columns: 1fr; gap: 2rem; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <a href="usuarios.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver a Usuarios</a>

        <div class="dashboard-header">
            <div class="welcome-text">
                <h1><?php echo $esEdicion ? 'Actualizar Credenciales' : 'Registrar Nuevo Staff'; ?></h1>
                <p>Gestione el acceso administrativo. Las contraseñas se almacenan mediante cifrado BCRYPT de alta seguridad.</p>
            </div>
        </div>

        <?php echo $mensaje; ?>
        
        <?php if($esMiPerfil): ?>
            <div class="alert alert-info">
                <i class="fa-solid fa-circle-info"></i> Estás editando tu propio perfil. Algunas opciones de seguridad (Rol y Estado) han sido bloqueadas para evitar la pérdida de acceso accidental.
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form action="usuario_form.php<?php echo $esEdicion ? '?id='.$usuario_data['id'] : ''; ?>" method="POST" autocomplete="off">
                
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario_data['id']); ?>">

                <div class="form-grid">
                    
                    <div class="form-main-col">
                        <div class="form-group">
                            <label for="nombre_completo">Nombre Completo del Colaborador *</label>
                            <input type="text" id="nombre_completo" name="nombre_completo" class="form-control" value="<?php echo htmlspecialchars($usuario_data['nombre_completo']); ?>" required placeholder="Ej: Carlos Mendoza">
                        </div>

                        <div class="form-group">
                            <label for="usuario">Nombre de Usuario (Username) *</label>
                            <div class="input-icon-wrapper">
                                <i class="fa-solid fa-at"></i>
                                <input type="text" id="usuario" name="usuario" class="form-control" value="<?php echo htmlspecialchars($usuario_data['usuario']); ?>" required placeholder="ejemplo: cmendoza" oninput="sanitizeUsername(this)">
                            </div>
                            <small style="color: var(--color-muted); display:block; margin-top:5px;">El sistema no permite espacios ni mayúsculas en este campo.</small>
                        </div>

                        <div class="form-group">
                            <label for="password">Contraseña de Acceso <?php echo $esEdicion ? '(Dejar en blanco para conservar la actual)' : '*'; ?></label>
                            <div class="input-icon-wrapper">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="<?php echo $esEdicion ? '••••••••' : 'Escriba una contraseña segura'; ?>" <?php echo !$esEdicion ? 'required' : ''; ?>>
                                <i class="fa-regular fa-eye toggle-password" id="toggleBtn" onclick="togglePassword()"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-side-col">
                        <div class="form-group">
                            <label>Nivel de Permisos (RBAC) *</label>
                            <div class="role-selector-grid">
                                
                                <label>
                                    <input type="radio" name="rol" value="SuperAdmin" class="role-radio" <?php echo ($usuario_data['rol'] === 'SuperAdmin') ? 'checked' : ''; ?> <?php echo $esMiPerfil ? 'disabled' : ''; ?>>
                                    <div class="role-card role-superadmin">
                                        <i class="fa-solid fa-crown"></i>
                                        <div class="role-content">
                                            <h4>SuperAdmin</h4>
                                            <p>Acceso total. Puede crear, editar y eliminar otros usuarios, además de gestionar todo el resort.</p>
                                        </div>
                                    </div>
                                </label>

                                <label>
                                    <input type="radio" name="rol" value="Recepcion" class="role-radio" <?php echo ($usuario_data['rol'] === 'Recepcion') ? 'checked' : ''; ?> <?php echo $esMiPerfil ? 'disabled' : ''; ?>>
                                    <div class="role-card">
                                        <i class="fa-solid fa-concierge-bell"></i>
                                        <div class="role-content">
                                            <h4>Recepción VIP</h4>
                                            <p>Visualiza y gestiona las reservas de los huéspedes. No puede modificar la página web.</p>
                                        </div>
                                    </div>
                                </label>

                                <label>
                                    <input type="radio" name="rol" value="Marketing" class="role-radio" <?php echo ($usuario_data['rol'] === 'Marketing') ? 'checked' : ''; ?> <?php echo $esMiPerfil ? 'disabled' : ''; ?>>
                                    <div class="role-card">
                                        <i class="fa-solid fa-bullhorn"></i>
                                        <div class="role-content">
                                            <h4>Marketing & Contenido</h4>
                                            <p>Edita las Suites, Servicios, Testimonios y la Portada, pero no tiene acceso a seguridad.</p>
                                        </div>
                                    </div>
                                </label>

                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #eee;">
                            <label>Acceso al Sistema</label>
                            <div class="switch-container">
                                <label class="switch">
                                    <input type="checkbox" name="estado" value="1" <?php echo ($usuario_data['estado'] == 1) ? 'checked' : ''; ?> <?php echo $esMiPerfil ? 'disabled' : ''; ?>>
                                    <span class="slider <?php echo $esMiPerfil ? 'disabled' : ''; ?>"></span>
                                </label>
                                <span style="font-size: 0.85rem; font-weight:600; color: <?php echo $esMiPerfil ? '#27AE60' : 'var(--color-text)'; ?>;">
                                    <?php echo $esMiPerfil ? 'Siempre Activo (Tú)' : 'Cuenta Habilitada'; ?>
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fa-solid <?php echo $esEdicion ? 'fa-floppy-disk' : 'fa-user-check'; ?>"></i> 
                            <?php echo $esEdicion ? 'Guardar Cambios' : 'Registrar Colaborador'; ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // UX: Mostrar/Ocultar contraseña
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggleBtn');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.classList.remove('fa-eye');
                toggleBtn.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleBtn.classList.remove('fa-eye-slash');
                toggleBtn.classList.add('fa-eye');
            }
        }

        // UX: Forzar formato de username en vivo (minúsculas, sin espacios)
        function sanitizeUsername(input) {
            input.value = input.value.toLowerCase().replace(/\s+/g, '');
        }
    </script>
</body>
</html>