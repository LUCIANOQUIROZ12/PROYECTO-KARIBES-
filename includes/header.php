<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Karibes Resorts Internacional | Lujo y Exclusividad</title>
    <link rel="icon" type="image/png" href="images/logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ==========================================================================
           1. SISTEMA DE DISEÑO GLOBAL (Basado en Nuevo Logo Karibes)
           ========================================================================== */
        :root {
            /* Paleta extraída del Logo */
            --color-primary-dark: #0054A6; /* Azul profundo de la base y olas */
            --color-primary-light: #00AEEF; /* Cian/Celeste brillante de la "K" */
            --color-accent-orange: #F15A24; /* Naranja base del Sol y "Internacional" */
            --color-accent-yellow: #FFF200; /* Amarillo brillante del Sol */
            --color-accent-green: #39B54A;  /* Verde vibrante de la Palmera */
            
            /* Colores Base Premium */
            --color-bg-light: #FBFBF9;
            --color-bg-dark: #0A1118;
            --color-text-main: #2C3E50;
            --color-text-muted: #596A7B;
            --color-white: #FFFFFF;
            
            /* Gradientes Adaptados al Logo */
            --gradient-gold: linear-gradient(135deg, var(--color-accent-yellow) 0%, var(--color-accent-orange) 100%);
            --gradient-ocean: linear-gradient(135deg, var(--color-primary-dark) 0%, var(--color-primary-light) 100%);
            --gradient-palm: linear-gradient(135deg, #8CC63F 0%, var(--color-accent-green) 100%);
            
            /* Tipografías y Sombras */
            --font-serif: 'Playfair Display', serif;
            --font-sans: 'Montserrat', sans-serif;
            --transition-smooth: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            --shadow-soft: 0 10px 30px rgba(0, 84, 166, 0.1);
            --shadow-hover: 0 15px 40px rgba(241, 90, 36, 0.25);
        }

        /* ==========================================================================
           2. RESET Y ESTILOS BASE
           ========================================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
            overflow-x: hidden;
        }

        body {
            font-family: var(--font-sans);
            color: var(--color-text-main);
            background-color: var(--color-bg-light);
            line-height: 1.6;
            overflow-x: hidden;
            width: 100%;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-serif);
            color: var(--color-primary-dark);
            font-weight: 600;
        }

        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }
        img { max-width: 100%; height: auto; display: block; }

        /* Clases de Utilidad */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-padding { padding: 6rem 0; }
        .text-center { text-align: center; }

        /* Títulos de Sección */
        .section-header {
            margin-bottom: 4rem;
            text-align: center;
            position: relative;
        }

        .section-header h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--color-primary-dark);
        }

        .section-header p {
            font-size: 1.1rem;
            color: var(--color-text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        .section-header::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--gradient-gold);
            margin: 1.5rem auto 0;
        }

        /* Botones Premium */
        .btn {
            display: inline-block;
            padding: 1rem 2.5rem;
            font-family: var(--font-sans);
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: none;
            cursor: pointer;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-primary {
            background: var(--gradient-gold);
            color: var(--color-white);
            box-shadow: 0 4px 15px rgba(248, 156, 29, 0.3);
            border-radius: 2px;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: var(--color-primary-dark);
            z-index: -1;
            transition: var(--transition-smooth);
            transform: scaleX(0);
            transform-origin: right;
        }

        .btn-primary:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }

        .btn-primary:hover {
            color: var(--color-accent-yellow);
            box-shadow: 0 6px 20px rgba(0, 84, 166, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--color-primary-dark);
            border: 1px solid var(--color-primary-dark);
        }

        .btn-outline:hover {
            background: var(--color-primary-dark);
            color: var(--color-white);
        }

        /* ==========================================================================
           3. HEADER Y NAVEGACIÓN
           ========================================================================== */
        .header {
            position: fixed;
            top: 0; left: 0; width: 100%;
            padding: 1rem 0;
            z-index: 1000;
            transition: var(--transition-smooth);
            background: linear-gradient(to bottom, rgba(10, 17, 24, 0.7) 0%, rgba(0,0,0,0) 100%);
        }

        .header.scrolled {
            background: rgba(251, 251, 249, 0.98);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 0.5rem 0;
            box-shadow: 0 4px 20px rgba(0, 84, 166, 0.08);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        /* --- LOGO RESPONSIVO --- */
        .logo {
            display: flex;
            align-items: center;
            z-index: 1002;
            text-decoration: none;
            flex-shrink: 0;
        }

        .logo-img {
            height: 75px; 
            width: auto;
            max-width: 280px; 
            object-fit: contain;
            transition: var(--transition-smooth);
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.4));
        }

        .header.scrolled .logo-img {
            height: 55px;
            filter: none;
        }

        /* --- NAVEGACIÓN --- */
        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links li a:not(.btn-nav-login) {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--color-white);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            position: relative;
            transition: var(--transition-smooth);
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
            white-space: nowrap;
        }

        .header.scrolled .nav-links li a:not(.btn-nav-login) {
            color: var(--color-primary-dark);
            text-shadow: none;
        }

        .nav-links li a:not(.btn-nav-login)::after {
            content: '';
            position: absolute;
            bottom: -5px; left: 0; width: 0; height: 2px;
            background: var(--gradient-gold);
            transition: var(--transition-smooth);
        }

        .nav-links li a:not(.btn-nav-login):hover::after { width: 100%; }

        /* --- MAGIA UX: BOTÓN DEL PANEL ADMIN EN EL MENÚ --- */
        .btn-nav-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--color-white);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.4rem 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 30px;
            transition: var(--transition-smooth);
            backdrop-filter: blur(5px);
        }

        .btn-nav-login i {
            font-size: 0.9rem;
        }

        .header.scrolled .btn-nav-login {
            color: var(--color-primary-dark);
            border-color: var(--color-primary-dark);
        }

        .btn-nav-login:hover {
            background: var(--gradient-gold);
            border-color: transparent;
            color: var(--color-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(241, 90, 36, 0.3);
        }

        /* --- Hamburguesa interactiva --- */
        .mobile-menu-btn {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 28px; height: 20px;
            cursor: pointer;
            z-index: 1002;
        }

        .mobile-menu-btn span {
            display: block; width: 100%; height: 3px;
            background-color: var(--color-white);
            border-radius: 3px;
            transition: all 0.3s cubic-bezier(0.645, 0.045, 0.355, 1);
            transform-origin: center;
        }

        .header.scrolled .mobile-menu-btn span { background-color: var(--color-primary-dark); }
        
        .mobile-menu-btn.open span { background-color: var(--color-white) !important; }
        .mobile-menu-btn.open span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
        .mobile-menu-btn.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .mobile-menu-btn.open span:nth-child(3) { transform: translateY(-9px) rotate(-45deg); }

        /* ==========================================================================
           4. SECCIONES DEL CUERPO (Main)
           ========================================================================== */
        /* Hero */
        .hero {
            position: relative;
            height: 100vh;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: 1;
            animation: heroZoom 20s infinite alternate linear;
        }

        @keyframes heroZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        .hero-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(10, 17, 24, 0.4) 0%, rgba(0, 84, 166, 0.5) 100%);
            z-index: 2;
        }

        .hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            color: var(--color-white);
            max-width: 800px;
            padding: 0 2rem;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 1.2s ease forwards 0.5s;
        }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .hero-subtitle {
            font-family: var(--font-sans);
            font-size: 1rem;
            letter-spacing: 5px;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            color: var(--color-accent-yellow);
            display: block;
        }

        .hero-title {
            font-size: 4.5rem;
            line-height: 1.1;
            margin-bottom: 2rem;
            color: var(--color-white);
            text-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .hero-title span { font-style: italic; color: var(--color-accent-yellow); }

        /* Experiencia */
        .experience { background-color: var(--color-bg-light); }
        .experience-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
        .experience-images { position: relative; }
        .exp-img-main { width: 80%; border-radius: 4px; box-shadow: var(--shadow-soft); }
        .exp-img-secondary {
            position: absolute; bottom: -10%; right: 0; width: 50%;
            border: 10px solid var(--color-bg-light); border-radius: 4px;
            box-shadow: var(--shadow-soft); z-index: 2;
        }
        .experience-text h3 { font-size: 2.2rem; margin-bottom: 1.5rem; }
        .experience-text p { margin-bottom: 1.5rem; color: var(--color-text-muted); font-size: 1.05rem; }
        .features-list { margin: 2rem 0; }
        .features-list li { margin-bottom: 1rem; display: flex; align-items: center; font-weight: 500; }
        .features-list li i { color: var(--color-accent-orange); margin-right: 1rem; font-size: 1.2rem; }

        /* Suites */
        .suites { background-color: var(--color-white); }
        .suites-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; }
        .suite-card { background: var(--color-bg-light); border-radius: 8px; overflow: hidden; box-shadow: var(--shadow-soft); transition: var(--transition-smooth); position: relative; }
        .suite-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-hover); }
        .suite-img-wrapper { position: relative; height: 250px; overflow: hidden; }
        .suite-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
        .suite-card:hover .suite-img-wrapper img { transform: scale(1.1); }
        .suite-price { position: absolute; top: 20px; right: 20px; background: rgba(0, 84, 166, 0.9); color: var(--color-white); padding: 0.5rem 1rem; border-radius: 4px; font-weight: 600; font-family: var(--font-sans); backdrop-filter: blur(5px); }
        .suite-price span { font-size: 0.8rem; color: var(--color-accent-yellow); }
        .suite-content { padding: 2rem; }
        .suite-content h4 { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .suite-content p { color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; }
        .suite-amenities { display: flex; gap: 1.5rem; margin-bottom: 2rem; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 1rem; }
        .suite-amenities i { color: var(--color-primary-light); font-size: 1.2rem; position: relative; }
        .suite-card .btn { width: 100%; text-align: center; }

        /* Servicios */
        .services { background-color: var(--color-bg-dark); color: var(--color-white); }
        .services .section-header h2 { color: var(--color-white); }
        .services .section-header p { color: #AAB7C4; }
        .services-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .service-item { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 3rem 2rem; text-align: center; border-radius: 8px; transition: var(--transition-smooth); position: relative; overflow: hidden; }
        .service-item::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: var(--gradient-gold); transform: scaleX(0); transition: var(--transition-smooth); transform-origin: left; }
        .service-item:hover { transform: translateY(-5px); background: rgba(255,255,255,0.06); }
        .service-item:hover::before { transform: scaleX(1); }
        .service-icon { width: 80px; height: 80px; line-height: 80px; background: rgba(241, 90, 36, 0.1); color: var(--color-accent-orange); font-size: 2.5rem; border-radius: 50%; margin: 0 auto 1.5rem; transition: var(--transition-smooth); }
        .service-item:hover .service-icon { background: var(--gradient-gold); color: var(--color-white); }
        .service-item h4 { color: var(--color-white); font-size: 1.4rem; margin-bottom: 1rem; }
        .service-item p { color: #AAB7C4; font-size: 0.95rem; }
        .vip-transport { margin-top: 4rem; background: var(--gradient-ocean); border-radius: 8px; padding: 4rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 20px 40px rgba(0,0,0,0.4); position: relative; overflow: hidden; }
        .vip-transport::after { content: '\f5b0'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; right: -5%; top: 50%; transform: translateY(-50%); font-size: 15rem; color: rgba(255,255,255,0.05); pointer-events: none; }
        .vip-text { max-width: 60%; }
        .vip-text h3 { color: var(--color-white); font-size: 2rem; margin-bottom: 1rem; }
        .vip-text p { color: rgba(255,255,255,0.8); font-size: 1.1rem; }

        /* Testimonios */
        .testimonials { background-color: var(--color-bg-light); text-align: center; }
        .testimonial-slider { max-width: 800px; margin: 0 auto; position: relative; }
        .testimonial-item { display: none; padding: 2rem; animation: fadeIn 0.8s ease; }
        .testimonial-item.active { display: block; }
        .quote-icon { color: var(--color-accent-yellow); font-size: 3rem; opacity: 0.3; margin-bottom: 1rem; }
        .testimonial-text { font-family: var(--font-serif); font-size: 1.5rem; font-style: italic; color: var(--color-primary-dark); margin-bottom: 2rem; line-height: 1.8; }
        .testimonial-author h5 { font-family: var(--font-sans); font-size: 1.1rem; color: var(--color-text-main); text-transform: uppercase; letter-spacing: 1px; }
        .testimonial-author span { font-size: 0.85rem; color: var(--color-text-muted); }
        .slider-controls { margin-top: 2rem; display: flex; justify-content: center; gap: 1rem; }
        .slider-dot { width: 12px; height: 12px; border-radius: 50%; background: #D1D8E0; cursor: pointer; transition: var(--transition-smooth); }
        .slider-dot.active { background: var(--color-accent-orange); transform: scale(1.3); }

        /* Contacto */
        .contact { background-color: var(--color-white); }
        .contact-wrapper { display: flex; gap: 4rem; background: var(--color-bg-light); border-radius: 12px; box-shadow: var(--shadow-soft); overflow: hidden; }
        .contact-info { flex: 1; background: var(--color-primary-dark); color: var(--color-white); padding: 4rem; position: relative; }
        .contact-info h3 { color: var(--color-white); font-size: 2rem; margin-bottom: 2rem; }
        .contact-detail { display: flex; align-items: flex-start; margin-bottom: 2rem; }
        .contact-detail i { color: var(--color-accent-yellow); font-size: 1.5rem; margin-right: 1.5rem; margin-top: 0.2rem; }
        .contact-form { flex: 1; padding: 4rem 4rem 4rem 0; }
        .form-group { margin-bottom: 1.5rem; position: relative; }
        .contact-form .form-control { width: 100%; padding: 1rem 0; border: none; border-bottom: 1px solid #D1D8E0; background: transparent; font-family: var(--font-sans); font-size: 1rem; color: var(--color-text-main); transition: var(--transition-smooth); }
        .contact-form .form-control:focus { outline: none; border-bottom-color: var(--color-primary-light); }
        
        /* Footer & WhatsApp */
        .footer { background-color: var(--color-bg-dark); color: rgba(255,255,255,0.6); padding: 4rem 0 2rem; border-top: 4px solid var(--color-accent-orange); }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 2rem; margin-bottom: 3rem; }
        .footer-col h4 { color: var(--color-white); font-family: var(--font-sans); font-size: 1.1rem; margin-bottom: 1.5rem; position: relative; }
        .footer-col h4::after { content: ''; position: absolute; left: 0; bottom: -5px; width: 30px; height: 2px; background: var(--color-accent-green); }
        .social-links { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .social-links a { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.05); color: var(--color-white); transition: var(--transition-smooth); }
        .whatsapp-btn { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background-color: #25D366; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); z-index: 9999; animation: vibrate 5s infinite; transition: var(--transition-smooth); }

        /* ==========================================================================
           5. RESPONSIVE DESIGN & ANTI-COLISIÓN (Media Queries)
           ========================================================================== */
        
        /* Laptops pequeñas */
        @media screen and (min-width: 992px) and (max-width: 1200px) {
            .nav-container { gap: 1rem; }
            .nav-links { gap: 1.2rem; }
            .nav-links li a:not(.btn-nav-login) { font-size: 0.75rem; letter-spacing: 1px; }
            .btn-nav-login { padding: 0.3rem 0.8rem; font-size: 0.75rem; }
            .logo-img { height: 60px; max-width: 200px; }
            .header.scrolled .logo-img { height: 50px; }
        }

        /* Tablets y móviles grandes */
        @media screen and (max-width: 991px) {
            .logo-img { height: 55px; max-width: 190px; }
            .header.scrolled .logo-img { height: 45px; }

            .nav-links {
                display: flex; position: fixed; top: 0; right: -100%;
                width: 80%; max-width: 400px; height: 100vh;
                background: rgba(0, 84, 166, 0.98); 
                backdrop-filter: blur(15px);
                flex-direction: column; justify-content: center; align-items: center;
                gap: 2rem; transition: cubic-bezier(0.4, 0, 0.2, 1) 0.4s;
                box-shadow: -10px 0 30px rgba(0,0,0,0.3); z-index: 1001;
            }
            .nav-links.active { right: 0; }
            .nav-links li a:not(.btn-nav-login) { color: var(--color-white) !important; font-size: 1.2rem; text-shadow: none !important; }
            
            .btn-nav-login {
                margin-top: 1rem;
                padding: 0.8rem 2rem;
                font-size: 1rem;
                color: var(--color-white) !important;
                border: 2px solid var(--color-accent-orange);
            }
            .header.scrolled .btn-nav-login {
                color: var(--color-white);
                border-color: var(--color-accent-orange);
            }

            .mobile-menu-btn { display: flex; }

            /* Ajustes Cuerpo */
            .hero-title { font-size: 3.5rem; }
            .experience-grid { gap: 2rem; }
            .services-grid { grid-template-columns: repeat(2, 1fr); }
            .contact-wrapper { flex-direction: column; gap: 0; }
            .contact-form { padding: 3rem; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }

        /* Móviles pequeños */
        @media screen and (max-width: 768px) {
            .hero-title { font-size: 2.8rem; }
            .experience-grid { grid-template-columns: 1fr; }
            .exp-img-secondary { display: none; }
            .exp-img-main { width: 100%; }
            .services-grid { grid-template-columns: 1fr; }
            .vip-transport { flex-direction: column; text-align: center; padding: 2rem; }
            .vip-text { max-width: 100%; margin-bottom: 2rem; }
            .vip-transport::after { display: none; }
            .footer-grid { grid-template-columns: 1fr; text-align: center; }
            .social-links { justify-content: center; }
            .footer-col h4::after { left: 50%; transform: translateX(-50%); }
        }
    </style>
</head>
<body>

    <header class="header" id="header">
        <div class="container nav-container">
            
            <a href="index.php" class="logo" aria-label="Ir al inicio">
                <img src="images/logo.png" alt="Karibes Resorts Internacional" class="logo-img">
            </a>
            
            <nav role="navigation">
                <ul class="nav-links" id="nav-links">
                    <li><a href="index.php#hero">Inicio</a></li>
                    <li><a href="index.php#nosotros">Experiencia</a></li>
                    <li><a href="index.php#suites">Suites</a></li>
                    <li><a href="index.php#servicios">Servicios VIP</a></li>
                    <li><a href="index.php#contacto">Contacto</a></li>
                    
                    <li>
                        <a href="admin/login.php" class="btn-nav-login" title="Acceso Exclusivo para Staff">
                            <i class="fa-solid fa-user-lock"></i> ADMIN
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>