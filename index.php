<?php
// 1. Inicializar Conexión
require_once 'config/conexion.php';
$db = new Conexion();
$conn = $db->conectar();

// 2. MAGIA: PROCESAR EL FORMULARIO DE RESERVA (Patrón PRG para evitar reenvío con F5)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_reserva'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $mensaje = trim($_POST['mensaje']);

    try {
        $stmtInsert = $conn->prepare("INSERT INTO reservas (nombre, email, telefono, checkin, checkout, mensaje, estado_reserva) VALUES (:nombre, :email, :telefono, :checkin, :checkout, :mensaje, 'Nueva')");
        $stmtInsert->bindParam(':nombre', $nombre);
        $stmtInsert->bindParam(':email', $email);
        $stmtInsert->bindParam(':telefono', $telefono);
        $stmtInsert->bindParam(':checkin', $checkin);
        $stmtInsert->bindParam(':checkout', $checkout);
        $stmtInsert->bindParam(':mensaje', $mensaje);
        
        if($stmtInsert->execute()){
            // Redirección inmediata para limpiar el método POST del navegador
            header("Location: index.php?reserva=success#contacto");
            exit;
        }
    } catch(PDOException $e) {
        error_log("Error al guardar reserva: " . $e->getMessage());
        header("Location: index.php?reserva=error#contacto");
        exit;
    }
}

// 3. MAGIA: PROCESAR NUEVO TESTIMONIO (Patrón PRG para evitar reenvío con F5)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_testimonio'])) {
    $nombre_cliente = trim($_POST['nombre_cliente']);
    $origen = trim($_POST['origen']);
    $comentario = trim($_POST['comentario']);

    try {
        // Se guarda con estado = 0 para requerir aprobación del Admin
        $stmtTest = $conn->prepare("INSERT INTO testimonios (nombre_cliente, origen, comentario, estado) VALUES (:nombre, :origen, :comentario, 0)");
        $stmtTest->bindParam(':nombre', $nombre_cliente);
        $stmtTest->bindParam(':origen', $origen);
        $stmtTest->bindParam(':comentario', $comentario);
        
        if($stmtTest->execute()){
            // Redirección inmediata para limpiar el método POST del navegador
            header("Location: index.php?testimonio=success#testimonios");
            exit;
        }
    } catch(PDOException $e) {
        error_log("Error al guardar testimonio: " . $e->getMessage());
        header("Location: index.php?testimonio=error#testimonios");
        exit;
    }
}

// 4. CAPTURAR LOS ESTADOS ENVIADOS POR URL (GET) PARA DISPARAR LAS ALERTAS
$alertaReserva = $_GET['reserva'] ?? '';
$alertaTestimonio = $_GET['testimonio'] ?? '';

// 5. CONSULTAS DINÁMICAS FRONT-END
try {
    $stmtHero = $conn->query("SELECT * FROM configuracion_hero LIMIT 1");
    $hero = $stmtHero->fetch(PDO::FETCH_ASSOC);

    $stmtNosotros = $conn->query("SELECT * FROM configuracion_nosotros LIMIT 1");
    $nosotros = $stmtNosotros->fetch(PDO::FETCH_ASSOC);

    $stmtSuites = $conn->query("SELECT * FROM suites WHERE estado = 1 ORDER BY orden ASC LIMIT 3");
    $suites = $stmtSuites->fetchAll(PDO::FETCH_ASSOC);

    $stmtServicios = $conn->query("SELECT * FROM servicios WHERE estado = 1 ORDER BY orden ASC LIMIT 8");
    $servicios = $stmtServicios->fetchAll(PDO::FETCH_ASSOC);

    $stmtTestimonios = $conn->query("SELECT * FROM testimonios WHERE estado = 1 ORDER BY creado_en DESC LIMIT 3");
    $testimonios = $stmtTestimonios->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $hero = $nosotros = false;
    $suites = $servicios = $testimonios = [];
}
?>

<?php include 'includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main>
    <section class="hero" id="hero">
        <div class="hero-bg" style="background-image: url('<?php echo !empty($hero['imagen_fondo']) ? htmlspecialchars($hero['imagen_fondo']) : 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1920&q=80'; ?>');"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <span class="hero-subtitle"><?php echo htmlspecialchars($hero['subtitulo'] ?? 'El paraíso a tu medida'); ?></span>
            <h1 class="hero-title"><?php echo !empty($hero['titulo_html']) ? $hero['titulo_html'] : 'Redescubre el <span>Lujo</span> en el Caribe'; ?></h1>
            <p style="font-size: 1.2rem; margin-bottom: 2.5rem; font-weight: 300;">
                <?php echo htmlspecialchars($hero['descripcion'] ?? 'Un santuario de exclusividad donde el océano se encuentra con la sofisticación absoluta.'); ?>
            </p>
            <a href="#suites" class="btn btn-primary"><?php echo htmlspecialchars($hero['texto_boton'] ?? 'Reservar Ahora'); ?></a>
        </div>
    </section>

    <section class="experience section-padding" id="nosotros">
        <div class="container">
            <div class="experience-grid">
                <div class="experience-images">
                    <img src="<?php echo !empty($nosotros['imagen_principal']) ? htmlspecialchars($nosotros['imagen_principal']) : 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80'; ?>" alt="Lobby del Resort" class="exp-img-main">
                    <img src="<?php echo !empty($nosotros['imagen_secundaria']) ? htmlspecialchars($nosotros['imagen_secundaria']) : 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=600&q=80'; ?>" alt="Vista al Mar" class="exp-img-secondary">
                </div>
                <div class="experience-text">
                    <div class="section-header" style="text-align: left; margin-bottom: 2rem;">
                        <h2><?php echo htmlspecialchars($nosotros['titulo'] ?? 'La Experiencia Karibes'); ?></h2>
                    </div>
                    <p><?php echo htmlspecialchars($nosotros['parrafo_1'] ?? 'Diseñado para los viajeros más exigentes del mundo, Karibes Resorts Internacional redefine el concepto de hospitalidad. Cada detalle de nuestra arquitectura e interiorismo ha sido curado para fundirse armoniosamente con la naturaleza caribeña.'); ?></p>
                    <p><?php echo htmlspecialchars($nosotros['parrafo_2'] ?? 'Despierta con el sonido de las olas, relájate en nuestras piscinas infinitas y déjate consentir por un servicio de mayordomía personalizado disponible 24/7.'); ?></p>
                    <ul class="features-list">
                        <li><i class="fa-solid fa-crown"></i> Privacidad y exclusividad absoluta.</li>
                        <li><i class="fa-solid fa-martini-glass-citrus"></i> Alta gastronomía internacional.</li>
                        <li><i class="fa-solid fa-spa"></i> Bienestar y relajación de clase mundial.</li>
                    </ul>
                    <a href="#servicios" class="btn btn-outline">Descubrir Más</a>
                </div>
            </div>
        </div>
    </section>

    <section class="suites section-padding" id="suites">
        <div class="container">
            <div class="section-header">
                <h2>Suites & Villas</h2>
                <p>Espacios diseñados para el descanso supremo, combinando vistas panorámicas con amenidades de ultra-lujo.</p>
            </div>
            <div class="suites-grid">
                <?php if(count($suites) > 0): ?>
                    <?php foreach($suites as $suite): ?>
                        <div class="suite-card">
                            <div class="suite-img-wrapper">
                                <img src="<?php echo htmlspecialchars($suite['imagen']); ?>" alt="<?php echo htmlspecialchars($suite['nombre']); ?>">
                                <div class="suite-price">S/ <?php echo number_format($suite['precio_noche'], 2); ?> <span>/ noche</span></div>
                            </div>
                            <div class="suite-content">
                                <h4><?php echo htmlspecialchars($suite['nombre']); ?></h4>
                                <p><?php echo htmlspecialchars($suite['descripcion_corta']); ?></p>
                                <div class="suite-amenities">
                                    <?php 
                                        $amenidades = json_decode($suite['amenidades_json'], true);
                                        if(is_array($amenidades)) {
                                            foreach($amenidades as $icono) {
                                                echo "<i class='".htmlspecialchars($icono)."'></i>";
                                            }
                                        }
                                    ?>
                                </div>
                                <a href="#contacto" class="btn btn-outline">Reservar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="suite-card">
                        <div class="suite-img-wrapper">
                            <img src="https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=800&q=80" alt="Suite Muestra">
                            <div class="suite-price">S/ 1,200.00 <span>/ noche</span></div>
                        </div>
                        <div class="suite-content">
                            <h4>Suite Presidencial Oceanfront</h4>
                            <p>Más de 200m² de puro lujo con terraza privada, jacuzzi infinito y vistas ininterrumpidas al Mar Caribe.</p>
                            <div class="suite-amenities">
                                <i class="fa-solid fa-wifi"></i>
                                <i class="fa-solid fa-tv"></i>
                                <i class="fa-solid fa-wind"></i>
                                <i class="fa-solid fa-bell-concierge"></i>
                            </div>
                            <a href="#contacto" class="btn btn-outline">Reservar</a>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            <div style="text-align: center; margin-top: 3.5rem;">
                <a href="suites.php" class="btn btn-outline" style="border-radius: 6px;">
                    <i class="fa-solid fa-gem"></i> Ver Todos los Planes
                </a>
            </div>
        </div>
    </section>

    <section class="services section-padding" id="servicios">
        <div class="container">
            <div class="section-header">
                <h2 style="color:var(--color-white);">Servicios Exclusivos</h2>
                <p style="color:rgba(255,255,255,0.7);">Elevamos su estadía a un nivel de perfección sin precedentes.</p>
            </div>
            <div class="services-grid">
                <?php if(count($servicios) > 0): ?>
                    <?php foreach($servicios as $servicio): ?>
                        <div class="service-item">
                            <div class="service-icon"><i class="<?php echo htmlspecialchars($servicio['icono_fontawesome']); ?>"></i></div>
                            <h4><?php echo htmlspecialchars($servicio['titulo']); ?></h4>
                            <p><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="service-item">
                        <div class="service-icon"><i class="fa-solid fa-spa"></i></div>
                        <h4>Karibes Zen Spa</h4>
                        <p>Tratamientos holísticos ancestrales, hidroterapia y masajes con piedras calientes en pabellones frente al mar.</p>
                    </div>
                    <div class="service-item">
                        <div class="service-icon"><i class="fa-solid fa-utensils"></i></div>
                        <h4>Alta Gastronomía</h4>
                        <p>5 restaurantes de especialidad con chefs galardonados con estrellas Michelin. Sabores del mundo con ingredientes locales.</p>
                    </div>
                    <div class="service-item">
                        <div class="service-icon"><i class="fa-solid fa-ship"></i></div>
                        <h4>Yates Privados</h4>
                        <p>Flota de yates de lujo a su disposición para excursiones al atardecer, buceo privado o cenas románticas en alta mar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="testimonials section-padding" id="testimonios">
        <div class="container">
            <div class="section-header">
                <h2>Voces de la Excelencia</h2>
                <p>Lo que dicen nuestros huéspedes más distinguidos.</p>
            </div>
            
            <div class="testimonial-slider">
                <i class="fa-solid fa-quote-right quote-icon"></i>
                
                <?php if(count($testimonios) > 0): ?>
                    <?php $i = 1; foreach($testimonios as $testimonio): ?>
                        <div class="testimonial-item <?php echo $i === 1 ? 'active' : ''; ?>" id="testimonio-<?php echo $i; ?>">
                            <p class="testimonial-text">"<?php echo htmlspecialchars($testimonio['comentario']); ?>"</p>
                            <div class="testimonial-author">
                                <h5><?php echo htmlspecialchars($testimonio['nombre_cliente']); ?></h5>
                                <span><?php echo htmlspecialchars($testimonio['origen']); ?></span>
                            </div>
                        </div>
                    <?php $i++; endforeach; ?>
                    
                    <div class="slider-controls">
                        <?php for($x = 1; $x <= count($testimonios); $x++): ?>
                            <div class="slider-dot <?php echo $x === 1 ? 'active' : ''; ?>" onclick="showTestimonial(<?php echo $x; ?>)"></div>
                        <?php endfor; ?>
                    </div>
                <?php else: ?>
                    <div class="testimonial-item active" id="testimonio-1">
                        <p class="testimonial-text">"He visitado resorts de lujo en todo el mundo, pero la atención al detalle y la privacidad en Karibes no tiene igual. Simplemente espectacular."</p>
                        <div class="testimonial-author">
                            <h5>Elena R. Visconti</h5>
                            <span>Huésped Frecuente, Milán</span>
                        </div>
                    </div>
                    <div class="slider-controls">
                        <div class="slider-dot active" onclick="showTestimonial(1)"></div>
                    </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin-top: 3rem;">
                <button onclick="openReviewModal()" class="btn btn-outline" style="border-radius: 6px;">
                    <i class="fa-solid fa-pen-nib"></i> Dejar mi Experiencia
                </button>
            </div>
        </div>
    </section>

    <section class="contact section-padding" id="contacto">
        <div class="container">
            <div class="section-header">
                <h2>Reserve su Estancia</h2>
                <p>Permítanos diseñar una experiencia inolvidable a su medida.</p>
            </div>
            <div class="contact-wrapper">
                <div class="contact-info" style="background: var(--gradient-ocean);">
                    <h3 style="color: white;">Contacto Directo</h3>
                    <div class="contact-detail">
                        <i class="fa-solid fa-location-dot" style="color: var(--color-accent-yellow);"></i>
                        <div>
                            <h5 style="color: white;">Ubicación Exclusiva</h5>
                            <p style="color: rgba(255,255,255,0.8);">Boulevard del Paraíso Km 12</p>
                        </div>
                    </div>
                    <div class="contact-detail">
                        <i class="fa-solid fa-phone" style="color: var(--color-accent-yellow);"></i>
                        <div>
                            <h5 style="color: white;">Línea Concierge 24/7</h5>
                            <p style="color: rgba(255,255,255,0.8);">+1 (800) 555-KARIBE</p>
                        </div>
                    </div>
                </div>

                <div class="contact-form">
                    <form action="index.php#contacto" method="POST" id="bookingForm">
                        <input type="hidden" name="form_reserva" value="1">
                        <div class="form-group">
                            <input type="text" class="form-control" name="nombre" placeholder="Nombre Completo" required>
                        </div>
                        <div class="form-group" style="display: flex; gap: 1rem;">
                            <div style="flex: 1;"><input type="email" class="form-control" name="email" placeholder="Correo Electrónico" required></div>
                            <div style="flex: 1;"><input type="tel" class="form-control" name="telefono" placeholder="Teléfono / WhatsApp" required></div>
                        </div>
                        <div class="form-group" style="display: flex; gap: 1rem;">
                            <div style="flex: 1;">
                                <label style="font-size: 0.8rem; color: var(--color-primary-dark); font-weight: 600;">Check-in</label>
                                <input type="date" class="form-control" name="checkin" required>
                            </div>
                            <div style="flex: 1;">
                                <label style="font-size: 0.8rem; color: var(--color-primary-dark); font-weight: 600;">Check-out</label>
                                <input type="date" class="form-control" name="checkout" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" name="mensaje" placeholder="Requerimientos Especiales (Ej. Traslado VIP, Alergias, etc.)" required style="height: 100px;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; border-radius: 6px;">Solicitar Reserva</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <style>
        .review-modal-overlay {
            position: fixed; inset: 0; background: rgba(10, 17, 24, 0.85); backdrop-filter: blur(8px);
            z-index: 9999; display: flex; align-items: center; justify-content: center;
            opacity: 0; visibility: hidden; transition: all 0.4s ease; padding: 1rem;
        }
        .review-modal-overlay.active { opacity: 1; visibility: visible; }
        
        .review-modal-content {
            background: var(--color-white); width: 100%; max-width: 500px; border-radius: 12px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5); transform: translateY(30px); opacity: 0;
            transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1); position: relative; overflow: hidden;
        }
        .review-modal-overlay.active .review-modal-content { transform: translateY(0); opacity: 1; }

        .review-modal-content::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px;
            background: var(--gradient-gold);
        }

        .review-close-btn {
            position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.05); border: none;
            width: 35px; height: 35px; border-radius: 50%; color: var(--color-text-muted);
            cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center;
        }
        .review-close-btn:hover { background: var(--color-danger, #E74C3C); color: white; transform: rotate(90deg);}

        .review-modal-header { padding: 2.5rem 2.5rem 1rem; text-align: center; }
        .review-modal-header h3 { font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-primary-dark); margin-bottom: 0.5rem; }
        .review-modal-header p { font-size: 0.9rem; color: var(--color-text-muted); }
        .review-stars { color: var(--color-accent-yellow); font-size: 1.2rem; margin-top: 10px; letter-spacing: 2px;}

        .review-modal-body { padding: 0 2.5rem 2.5rem; }
        
        /* Forzamos el color y diseño independiente de los marcadores (Placeholders) */
        .review-modal-body .form-control { 
            background: #F8FAFC; 
            border: 1px solid #D1D8E0; 
            border-radius: 6px; 
            padding: 1rem 1.2rem; 
            margin-bottom: 1.2rem; 
            color: var(--color-text-main) !important;
            width: 100%;
            font-family: var(--font-sans);
        }
        
        .review-modal-body .form-control::placeholder {
            color: #8798A5 !important;
            font-weight: 500;
        }

        .review-modal-body .form-control:focus { 
            border-color: var(--color-accent-orange); 
            background: white; 
            box-shadow: 0 0 10px rgba(241, 90, 36, 0.15);
            outline: none;
        }

        .btn-modal-submit {
            width: 100%; 
            padding: 1rem;
            border-radius: 6px; 
            background: var(--gradient-gold); 
            color: var(--color-primary-dark);
            border: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(241, 90, 36, 0.2);
            margin-top: 0.5rem;
        }
        .btn-modal-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(241, 90, 36, 0.35);
        }
    </style>

    <div class="review-modal-overlay" id="reviewModal">
        <div class="review-modal-content">
            <button type="button" class="review-close-btn" onclick="closeReviewModal()"><i class="fa-solid fa-xmark"></i></button>
            <div class="review-modal-header">
                <h3>Su Experiencia Karibes</h3>
                <p>Nuestra mayor recompensa es su satisfacción.</p>
                <div class="review-stars">
                    <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
                </div>
            </div>
            <div class="review-modal-body">
                <form action="index.php" method="POST">
                    <input type="hidden" name="form_testimonio" value="1">
                    <div class="form-group">
                        <input type="text" class="form-control" name="nombre_cliente" placeholder="Su Nombre Completo" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="origen" placeholder="Lugar de procedencia (Ej. Lima, Madrid)" required>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" name="comentario" placeholder="¿Qué fue lo que más disfrutó de su estancia?" required style="height: 100px; resize:none;"></textarea>
                    </div>
                    <p style="font-size: 0.75rem; color: var(--color-text-muted); text-align: center; margin-bottom: 1.5rem;">
                        <i class="fa-solid fa-shield-halved"></i> Su testimonio será revisado por nuestro equipo de Concierge antes de ser publicado.
                    </p>
                    <button type="submit" class="btn-modal-submit">Enviar Testimonio</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openReviewModal() {
            document.getElementById('reviewModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeReviewModal() {
            document.getElementById('reviewModal').classList.remove('active');
            document.body.style.overflow = '';
        }
        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if(e.target === this) closeReviewModal();
        });
    </script>
</main>

<?php include 'includes/footer.php'; ?>

<?php if ($alertaReserva == 'success'): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: '¡Solicitud Recibida!',
            text: 'Gracias por elegir Karibes. Nuestro Concierge VIP se pondrá en contacto con usted a la brevedad.',
            icon: 'success',
            confirmButtonColor: '#0054A6'
        });
    });
</script>
<?php elseif ($alertaReserva == 'error'): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({ title: 'Error de Sistema', text: 'No pudimos procesar la reserva. Intente de nuevo.', icon: 'error', confirmButtonColor: '#F15A24' });
    });
</script>
<?php endif; ?>

<?php if ($alertaTestimonio == 'success'): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: '¡Gracias por sus palabras!',
            text: 'Su experiencia ha sido enviada con éxito. Nuestro equipo la revisará y la publicará en el muro de la excelencia muy pronto.',
            icon: 'success',
            confirmButtonColor: '#0054A6'
        });
    });
</script>
<?php elseif ($alertaTestimonio == 'error'): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({ title: 'Error', text: 'Ocurrió un problema al enviar su reseña.', icon: 'error', confirmButtonColor: '#F15A24' });
    });
</script>
<?php endif; ?>