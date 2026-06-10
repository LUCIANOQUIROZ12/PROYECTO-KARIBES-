<?php
session_start();

// 1. VALIDACIÓN DE SESIÓN ESTRICTA
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// 2. CONTROL DE ACCESO BASADO EN ROLES (RBAC)
// Solo el SuperAdmin puede acceder a esta pantalla
if ($_SESSION['admin_rol'] !== 'SuperAdmin') {
    // Si intenta entrar forzando la URL, lo enviamos de vuelta al dashboard
    header("Location: dashboard.php");
    exit;
}

require_once '../config/conexion.php';

$mensajeAccion = '';

try {
    $db = new Conexion();
    $conn = $db->conectar();

    // LÓGICA MÁGICA: Procesar acciones por POST (Eliminar o Cambiar Estado)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
        
        $id_target = (int)$_POST['id_usuario'];
        $mi_id = (int)$_SESSION['admin_id'];

        // PREVENCIÓN: Evitar que el administrador se modifique o elimine a sí mismo desde aquí
        if ($id_target === $mi_id) {
            $mensajeAccion = "<div class='alert alert-danger'><i class='fa-solid fa-shield-halved'></i> <strong>Acción bloqueada:</strong> No puedes suspender o eliminar tu propia cuenta activa.</div>";
        } else {
            // Acción: Eliminar Usuario
            if ($_POST['accion'] == 'eliminar') {
                $stmtDelete = $conn->prepare("DELETE FROM usuarios_admin WHERE id = :id");
                $stmtDelete->bindParam(':id', $id_target, PDO::PARAM_INT);
                
                if ($stmtDelete->execute()) {
                    $mensajeAccion = "<div class='alert alert-success'><i class='fa-solid fa-check-circle'></i> El usuario ha sido eliminado permanentemente del sistema.</div>";
                }
            }
            
            // Acción: Suspender / Activar Acceso
            if ($_POST['accion'] == 'cambiar_estado' && isset($_POST['nuevo_estado'])) {
                $nuevo_estado = (int)$_POST['nuevo_estado'];
                
                $stmtUpdate = $conn->prepare("UPDATE usuarios_admin SET estado = :estado WHERE id = :id");
                $stmtUpdate->bindParam(':estado', $nuevo_estado, PDO::PARAM_INT);
                $stmtUpdate->bindParam(':id', $id_target, PDO::PARAM_INT);
                
                if ($stmtUpdate->execute()) {
                    $estadoTxt = $nuevo_estado == 1 ? 'reactivado' : 'suspendido';
                    $mensajeAccion = "<div class='alert alert-success'><i class='fa-solid fa-user-shield'></i> El acceso del usuario ha sido $estadoTxt.</div>";
                }
            }
        }
    }

    // Extraer todos los usuarios del sistema, ordenados por rol y luego por nombre
    $stmtUsuarios = $conn->query("SELECT * FROM usuarios_admin ORDER BY FIELD(rol, 'SuperAdmin', 'Recepcion', 'Marketing'), nombre_completo ASC");
    $usuarios = $stmtUsuarios->fetchAll();

} catch (PDOException $e) {
    error_log("Error cargando Usuarios: " . $e->getMessage());
    $usuarios = [];
    $mensajeAccion = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Error conectando a la base de datos.</div>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Administradores | Karibes Admin</title>
    
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

        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
        .welcome-text h1 { font-family: 'Playfair Display', serif; color: var(--color-primary); font-size: 2.2rem; margin-bottom: 0.3rem; }
        .welcome-text p { color: var(--color-muted); font-size: 0.95rem; }

        .btn-add {
            background: linear-gradient(135deg, var(--color-dark) 0%, var(--color-primary) 100%);
            color: var(--color-gold);
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(8, 61, 107, 0.2);
            transition: all 0.3s ease;
            border: 1px solid rgba(248, 156, 29, 0.3);
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(8, 61, 107, 0.3); border-color: var(--color-gold); color: var(--color-white);}

        .alert { padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 500; font-size: 0.9rem; display: flex; gap: 10px; align-items: center;}
        .alert-success { background: #D4EDDA; color: #155724; border-left: 4px solid #28A745; }
        .alert-danger { background: #F8D7DA; color: #721C24; border-left: 4px solid var(--color-danger); }

        .table-container { background: var(--color-white); border-radius: 12px; box-shadow: var(--shadow-card); padding: 2rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #EEEEEE; vertical-align: middle; }
        th { color: var(--color-muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        tr:hover { background-color: #F8FAFC; }

        /* Magia UX: Avatares y Perfil de Usuario */
        .user-profile-cell { display: flex; align-items: center; gap: 15px; }
        .avatar-initials {
            width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.1rem; color: var(--color-white); background: var(--color-primary);
        }
        .user-info strong { color: var(--color-primary); display: block; font-size: 1rem; }
        .user-info span { color: var(--color-muted); font-size: 0.8rem; }
        .user-info .username-tag { background: #E9ECEF; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 0.75rem; color: #495057; }

        /* Badges de Roles Especiales */
        .role-badge { padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 5px;}
        .role-SuperAdmin { background: rgba(248, 156, 29, 0.15); color: #B9710B; border: 1px solid rgba(248, 156, 29, 0.3); }
        .role-Recepcion { background: #E3F2FD; color: #1565C0; border: 1px solid #BBDEFB;}
        .role-Marketing { background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9;}

        /* Magia UX: Toggle Switch Estado */
        .switch { position: relative; display: inline-block; width: 42px; height: 22px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #27AE60; }
        input:checked + .slider:before { transform: translateX(20px); }

        /* Estilo para mi propio usuario */
        .current-user-row { background-color: rgba(248, 156, 29, 0.03); }
        .current-user-tag { font-size: 0.7rem; color: var(--color-gold); font-weight: 600; margin-left: 5px; text-transform: uppercase; }

        /* Botones de Acción */
        .actions-group { display: flex; gap: 8px; }
        .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; text-decoration: none; transition: all 0.3s ease; border: none; cursor: pointer; }
        .btn-edit { background: rgba(8, 61, 107, 0.1); color: var(--color-primary); }
        .btn-edit:hover { background: var(--color-primary); color: var(--color-white); }
        .btn-delete { background: rgba(231, 76, 60, 0.1); color: var(--color-danger); }
        .btn-delete:hover { background: var(--color-danger); color: var(--color-white); }
        .btn-disabled { background: #F8F9FA; color: #CED4DA; cursor: not-allowed; }

        @media screen and (max-width: 991px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
            .dashboard-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <div class="welcome-text">
                <h1>Control de Administradores</h1>
                <p>Gestione el acceso del personal al panel de Karibes. <strong style="color:var(--color-primary);"><i class="fa-solid fa-lock"></i> Nivel: SuperAdmin</strong></p>
            </div>
            <a href="usuario_form.php" class="btn-add">
                <i class="fa-solid fa-user-plus"></i> Registrar Staff
            </a>
        </div>

        <?php echo $mensajeAccion; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Personal</th>
                        <th>Rol de Acceso</th>
                        <th>Último Acceso</th>
                        <th>Estado (Login)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usr): 
                        // Es el usuario actual logueado?
                        $esMiUsuario = ($usr['id'] == $_SESSION['admin_id']);
                        
                        // Generar iniciales para el avatar
                        $palabras = explode(' ', $usr['nombre_completo']);
                        $iniciales = strtoupper(substr($palabras[0], 0, 1) . (isset($palabras[1]) ? substr($palabras[1], 0, 1) : ''));
                        
                        // Formatear última conexión
                        $ultimoAcceso = !empty($usr['ultimo_acceso']) ? date('d M Y, H:i', strtotime($usr['ultimo_acceso'])) : 'Nunca ha iniciado sesión';
                        
                        // Determinar el icono del rol
                        $iconoRol = 'fa-user';
                        if($usr['rol'] == 'SuperAdmin') $iconoRol = 'fa-crown';
                        if($usr['rol'] == 'Recepcion') $iconoRol = 'fa-concierge-bell';
                        if($usr['rol'] == 'Marketing') $iconoRol = 'fa-bullhorn';
                    ?>
                        <tr class="<?php echo $esMiUsuario ? 'current-user-row' : ''; ?>">
                            <td>
                                <div class="user-profile-cell">
                                    <div class="avatar-initials" style="<?php echo $usr['rol'] == 'SuperAdmin' ? 'background: linear-gradient(135deg, #FFCC00 0%, #F89C1D 100%); color: #0A1118;' : ''; ?>">
                                        <?php echo $iniciales; ?>
                                    </div>
                                    <div class="user-info">
                                        <strong>
                                            <?php echo htmlspecialchars($usr['nombre_completo']); ?>
                                            <?php if($esMiUsuario) echo '<span class="current-user-tag">(TÚ)</span>'; ?>
                                        </strong>
                                        <span class="username-tag">@<?php echo htmlspecialchars($usr['usuario']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge role-<?php echo $usr['rol']; ?>">
                                    <i class="fa-solid <?php echo $iconoRol; ?>"></i> <?php echo $usr['rol']; ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--color-muted);"><i class="fa-regular fa-clock"></i> <?php echo $ultimoAcceso; ?></span>
                            </td>
                            <td>
                                <?php if ($esMiUsuario): ?>
                                    <span style="font-size: 0.8rem; color: #27AE60; font-weight:600;"><i class="fa-solid fa-circle-check"></i> Activo (Intocable)</span>
                                <?php else: ?>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="accion" value="cambiar_estado">
                                        <input type="hidden" name="id_usuario" value="<?php echo $usr['id']; ?>">
                                        <input type="hidden" name="nuevo_estado" value="<?php echo $usr['estado'] == 1 ? 0 : 1; ?>">
                                        
                                        <label class="switch" title="<?php echo $usr['estado'] == 1 ? 'Suspender Acceso' : 'Permitir Acceso'; ?>">
                                            <input type="checkbox" onchange="this.form.submit()" <?php echo ($usr['estado'] == 1) ? 'checked' : ''; ?>>
                                            <span class="slider"></span>
                                        </label>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions-group">
                                    <a href="usuario_form.php?id=<?php echo $usr['id']; ?>" class="btn-action btn-edit" title="Editar Credenciales">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    
                                    <?php if ($esMiUsuario): ?>
                                        <button type="button" class="btn-action btn-disabled" title="No puedes eliminarte a ti mismo" disabled>
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn-action btn-delete" title="Eliminar Personal" onclick="confirmarEliminacion(<?php echo $usr['id']; ?>, '<?php echo addslashes(htmlspecialchars($usr['nombre_completo'])); ?>')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <form id="formDelete" method="POST" style="display: none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id_usuario" id="delete_id" value="">
    </form>

    <script>
        function confirmarEliminacion(id, nombre) {
            if (confirm(`⚠️ ALERTA DE SEGURIDAD\n\n¿Está absolutamente seguro de que desea eliminar al usuario "${nombre}"?\n\nEsta persona perderá el acceso al panel administrativo de Karibes inmediatamente.`)) {
                document.getElementById('delete_id').value = id;
                document.getElementById('formDelete').submit();
            }
        }
    </script>
</body>
</html>