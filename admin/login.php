<?php
// Inicializar sesión de forma segura
session_start();

// Si el administrador ya está logueado, redirigir al dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Requerir conexión a la base de datos (asumiendo que config/ está un nivel arriba)
require_once '../config/conexion.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitización básica de entrada
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        $error = "Por favor, ingrese sus credenciales de acceso.";
    } else {
        try {
            $db = new Conexion();
            $conn = $db->conectar();

            // Preparar consulta para evitar Inyección SQL
            $stmt = $conn->prepare("SELECT id, usuario, password, nombre_completo, rol, estado FROM usuarios_admin WHERE usuario = :usuario LIMIT 1");
            $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verificar si la cuenta está activa
                if ($row['estado'] == 1) {
                    // Verificar la contraseña contra el hash BCRYPT
                    if (password_verify($password, $row['password'])) {
                        
                        // Regenerar ID de sesión para prevenir Session Fixation
                        session_regenerate_id(true);

                        // Crear variables de sesión
                        $_SESSION['admin_id'] = $row['id'];
                        $_SESSION['admin_usuario'] = $row['usuario'];
                        $_SESSION['admin_nombre'] = $row['nombre_completo'];
                        $_SESSION['admin_rol'] = $row['rol'];

                        // Actualizar el último acceso en la base de datos
                        $updateStmt = $conn->prepare("UPDATE usuarios_admin SET ultimo_acceso = NOW() WHERE id = :id");
                        $updateStmt->bindParam(':id', $row['id'], PDO::PARAM_INT);
                        $updateStmt->execute();

                        // Redirigir al panel de control
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        $error = "La contraseña ingresada es incorrecta.";
                    }
                } else {
                    $error = "Su cuenta ha sido suspendida. Contacte al SuperAdmin.";
                }
            } else {
                $error = "El usuario ingresado no existe en el sistema.";
            }
        } catch (PDOException $e) {
            error_log("Error de BD en Login: " . $e->getMessage());
            $error = "Error de conexión con el servidor. Intente más tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karibes Admin | Acceso Seguro</title>
    
    <!-- Fuentes y Recursos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --color-dark: #0A1118;
            --color-primary: #083D6B;
            --color-gold: #F89C1D;
            --color-white: #FFFFFF;
            --color-error: #E74C3C;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        /* Overlay oscuro para resaltar el panel */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, rgba(10, 17, 24, 0.8) 0%, rgba(8, 61, 107, 0.7) 100%);
            z-index: 1;
        }

        .login-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            padding: 3rem 2.5rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 0.8s ease forwards;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-header h1 {
            font-family: 'Playfair Display', serif;
            color: var(--color-white);
            font-size: 2rem;
            letter-spacing: 2px;
            margin-bottom: 0.5rem;
        }

        .brand-header span {
            color: var(--color-gold);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-weight: 600;
        }

        .alert {
            background: rgba(231, 76, 60, 0.15);
            border-left: 4px solid var(--color-error);
            color: #ffcccc;
            padding: 1rem;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-group i.icon-input {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-control {
            width: 100%;
            padding: 1rem 1rem 1rem 45px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: var(--color-white);
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-gold);
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 15px rgba(248, 156, 29, 0.1);
        }

        .form-control:focus + i.icon-input {
            color: var(--color-gold);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: var(--transition);
        }

        .toggle-password:hover {
            color: var(--color-white);
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--color-gold) 0%, #D68212 100%);
            border: none;
            border-radius: 6px;
            color: var(--color-white);
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(248, 156, 29, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(248, 156, 29, 0.4);
        }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.75rem;
        }

        .footer-text i {
            color: var(--color-gold);
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="brand-header">
            <h1>KARIBES</h1>
            <span>Portal Administrativo</span>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group">
                <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Nombre de Usuario" required autocomplete="off" value="<?php echo htmlspecialchars($usuario ?? ''); ?>">
                <i class="fa-solid fa-user icon-input"></i>
            </div>

            <div class="form-group">
                <input type="password" name="password" id="password" class="form-control" placeholder="Contraseña de Acceso" required>
                <i class="fa-solid fa-lock icon-input"></i>
                <i class="fa-regular fa-eye toggle-password" id="toggleBtn" onclick="togglePassword()"></i>
            </div>

            <button type="submit" class="btn-login">
                Iniciar Sesión <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        <div class="footer-text">
            <p>Acceso restringido a personal autorizado.</p>
            <p style="margin-top: 5px;"><i class="fa-solid fa-shield-halved"></i> Conexión Cifrada a Karibes DB</p>
        </div>
    </div>

    <script>
        // Lógica de UI para revelar/ocultar contraseña
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
    </script>
</body>
</html>