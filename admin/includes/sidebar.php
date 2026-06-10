<?php
// Asegurarnos de que la sesión esté iniciada sin causar errores si ya lo está
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener el nombre del archivo actual para marcar el menú activo
$archivoActual = basename($_SERVER['PHP_SELF']);

// Variables de sesión seguras con operador de fusión nula
$nombreAdmin = $_SESSION['admin_nombre'] ?? 'Usuario VIP';
$rolAdmin = $_SESSION['admin_rol'] ?? 'Staff';
$iniciales = strtoupper(substr($nombreAdmin, 0, 2));
?>

<style>
    /* ==========================================================================
       UX/UI SIDEBAR ADMINISTRATIVO - KARIBES RESORTS
       ========================================================================== */
    .admin-sidebar {
        width: 280px;
        height: 100vh;
        background: #0A1118;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        overflow-y: auto;
    }

    /* Scrollbar minimalista para el sidebar */
    .admin-sidebar::-webkit-scrollbar { width: 5px; }
    .admin-sidebar::-webkit-scrollbar-track { background: transparent; }
    .admin-sidebar::-webkit-scrollbar-thumb { background: rgba(248, 156, 29, 0.3); border-radius: 10px; }

    /* Branding Panel */
    .sidebar-brand {
        padding: 2rem 1.5rem;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .sidebar-brand h2 {
        font-family: 'Playfair Display', serif;
        color: #FFFFFF;
        font-size: 1.5rem;
        letter-spacing: 2px;
        margin-bottom: 0.2rem;
    }

    .sidebar-brand span {
        color: #F89C1D;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 3px;
        font-family: 'Montserrat', sans-serif;
    }

    /* User Profile Micro-Component */
    .sidebar-user {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .user-avatar {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #FFCC00 0%, #F89C1D 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0A1118;
        font-weight: 700;
        font-size: 1.1rem;
        font-family: 'Montserrat', sans-serif;
        box-shadow: 0 4px 10px rgba(248, 156, 29, 0.2);
    }

    .user-info h4 {
        color: #FFFFFF;
        font-size: 0.95rem;
        margin-bottom: 0.2rem;
        font-family: 'Montserrat', sans-serif;
    }

    .user-info span {
        display: inline-block;
        background: rgba(248, 156, 29, 0.1);
        color: #F89C1D;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 1px;
    }

    /* Menu Navigation */
    .sidebar-menu {
        padding: 1.5rem 0;
        list-style: none;
        flex-grow: 1;
    }

    .menu-label {
        color: rgba(255, 255, 255, 0.3);
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 1.5rem 1.5rem 0.8rem;
        font-weight: 600;
    }

    .sidebar-menu li a {
        display: flex;
        align-items: center;
        padding: 0.8rem 1.5rem;
        color: #AAB7C4;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .sidebar-menu li a i {
        width: 25px;
        font-size: 1.1rem;
        margin-right: 10px;
        transition: all 0.3s ease;
    }

    /* Hover & Active States */
    .sidebar-menu li a:hover, 
    .sidebar-menu li a.active {
        background: rgba(255, 255, 255, 0.03);
        color: #FFFFFF;
        border-left-color: #F89C1D;
    }

    .sidebar-menu li a:hover i, 
    .sidebar-menu li a.active i {
        color: #F89C1D;
        transform: translateX(3px);
    }

    /* Logout Area */
    .sidebar-footer {
        padding: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }

    .btn-logout {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 0.8rem;
        background: rgba(231, 76, 60, 0.1);
        color: #E74C3C;
        border: 1px solid rgba(231, 76, 60, 0.2);
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .btn-logout i { margin-right: 8px; }

    .btn-logout:hover {
        background: #E74C3C;
        color: #FFFFFF;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    /* Responsividad Básica para el panel */
    @media screen and (max-width: 991px) {
        .admin-sidebar {
            transform: translateX(-100%);
        }
        .admin-sidebar.open {
            transform: translateX(0);
        }
    }
</style>

<aside class="admin-sidebar" id="adminSidebar">
    <!-- Branding -->
    <div class="sidebar-brand">
        <h2>KARIBES</h2>
        <span>Panel Admin</span>
    </div>

    <!-- Perfil del Administrador -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <?php echo $iniciales; ?>
        </div>
        <div class="user-info">
            <h4><?php echo htmlspecialchars($nombreAdmin); ?></h4>
            <span><?php echo htmlspecialchars($rolAdmin); ?></span>
        </div>
    </div>

    <!-- Navegación Dinámica -->
    <ul class="sidebar-menu">
        <li class="menu-label">Principal</li>
        <li>
            <a href="dashboard.php" class="<?php echo ($archivoActual == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="reportes.php" class="<?php echo ($archivoActual == 'reportes.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-check"></i> Gestión de Reportes
            </a>
        </li>
        
                <li>
            <a href="reservas.php" class="<?php echo ($archivoActual == 'reservas.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-calendar-check"></i> Gestión de Reservas
            </a>
        </li>


        <li class="menu-label">Contenido Web</li>
        <li>
            <a href="suites.php" class="<?php echo ($archivoActual == 'suites.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-bed"></i> Suites & Villas
            </a>
        </li>
        <li>
            <a href="servicios.php" class="<?php echo ($archivoActual == 'servicios.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-spa"></i> Servicios VIP
            </a>
        </li>
        <li>
            <a href="testimonios.php" class="<?php echo ($archivoActual == 'testimonios.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-star"></i> Testimonios
            </a>
        </li>

        <li class="menu-label">Configuración UI</li>
        <li>
            <a href="config_hero.php" class="<?php echo ($archivoActual == 'config_hero.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-image"></i> Portada (Hero)
            </a>
        </li>
        <li>
            <a href="config_nosotros.php" class="<?php echo ($archivoActual == 'config_nosotros.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-leaf"></i> Sección Nosotros
            </a>
        </li>

        <!-- Seguridad RBAC: Solo SuperAdmin puede ver esta sección -->
        <?php if ($rolAdmin === 'SuperAdmin'): ?>
            <li class="menu-label">Seguridad</li>
            <li>
                <a href="usuarios.php" class="<?php echo ($archivoActual == 'usuarios.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users-gear"></i> Administradores
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <!-- Cierre de Sesión -->
    <div class="sidebar-footer">
        <!-- Apunta a un archivo logout.php que destruirá la sesión -->
        <a href="logout.php" class="btn-logout">
            <i class="fa-solid fa-power-off"></i> Cerrar Sesión
        </a>
    </div>
</aside>