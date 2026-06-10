<?php
// 1. Inicializar la sesión para poder destruirla
session_start();

// 2. Vaciar el array global de la sesión
$_SESSION = array();

// 3. Destruir la cookie de sesión en el navegador del usuario (Seguridad Extrema)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruir la sesión en el servidor
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando Sesión | Karibes Admin</title>
    
    <meta http-equiv="refresh" content="1.5;url=login.php">
    
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-dark);
            font-family: 'Montserrat', sans-serif;
            color: var(--color-white);
            overflow: hidden;
            position: relative;
        }

        /* Efecto de fondo sutil */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(8, 61, 107, 0.4) 0%, transparent 70%);
            z-index: 1;
        }

        .logout-container {
            position: relative;
            z-index: 2;
            text-align: center;
            animation: fadeOut 1.5s ease forwards;
        }

        @keyframes fadeOut {
            0% { opacity: 0; transform: translateY(20px); }
            20% { opacity: 1; transform: translateY(0); }
            80% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }

        .brand-header h1 {
            font-family: 'Playfair Display', serif;
            color: var(--color-white);
            font-size: 2.5rem;
            letter-spacing: 2px;
            margin-bottom: 0.5rem;
        }

        .brand-header span {
            color: var(--color-gold);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 4px;
            font-weight: 600;
            display: block;
            margin-bottom: 2rem;
        }

        .spinner-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .loader {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            border-top-color: var(--color-gold);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .msg {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <div class="logout-container">
        <div class="brand-header">
            <h1>KARIBES</h1>
            <span>Sistema Cerrado</span>
        </div>
        
        <div class="spinner-box">
            <div class="loader"></div>
            <p class="msg"><i class="fa-solid fa-lock"></i> Cerrando conexión de forma segura...</p>
        </div>
    </div>

</body>
</html>