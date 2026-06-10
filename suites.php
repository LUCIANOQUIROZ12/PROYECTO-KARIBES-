<?php
// 1. Inicializar Conexión
require_once 'config/conexion.php';
$db = new Conexion();
$conn = $db->conectar();

// 2. MAGIA OMNICANAL: Procesar Reserva y Preparar WhatsApp
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_reserva_suite'])) {
    
    $suite_interes = trim($_POST['suite_interes']);
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $mensaje_usuario = trim($_POST['mensaje']);

    // Unimos la suite al mensaje para que aparezca en el Panel Admin (reportes.php)
    $mensaje_db = "[Interesado en: " . $suite_interes . "]\n\n" . $mensaje_usuario;

    try {
        $stmtInsert = $conn->prepare("INSERT INTO reservas (nombre, email, telefono, checkin, checkout, mensaje, estado_reserva) VALUES (:nombre, :email, :telefono, :checkin, :checkout, :mensaje, 'Nueva')");
        $stmtInsert->bindParam(':nombre', $nombre);
        $stmtInsert->bindParam(':email', $email);
        $stmtInsert->bindParam(':telefono', $telefono);
        $stmtInsert->bindParam(':checkin', $checkin);
        $stmtInsert->bindParam(':checkout', $checkout);
        $stmtInsert->bindParam(':mensaje', $mensaje_db);
        
        if($stmtInsert->execute()){
            // ==============================================================
            // CONFIGURACIÓN DE WHATSAPP (Cambia este número por el tuyo)
            // ==============================================================
            $numero_whatsapp = "51999999999"; // Ej: Código de Perú (51) + Número
            
            // Construimos el texto predeterminado con saltos de línea codificados
            $texto_wa = "🌟 *Nueva Solicitud Karibes Resorts* 🌟\n\n";
            $texto_wa .= "*Suite:* " . $suite_interes . "\n";
            $texto_wa .= "*Huésped:* " . $nombre . "\n";
            $texto_wa .= "*Fechas:* " . date('d/m/Y', strtotime($checkin)) . " al " . date('d/m/Y', strtotime($checkout)) . "\n";
            $texto_wa .= "*Mensaje:* " . $mensaje_usuario;

            // Redirección segura (PRG) pasando la URL de WhatsApp codificada
            $url_wa = "https://api.whatsapp.com/send?phone=" . $numero_whatsapp . "&text=" . urlencode($texto_wa);
            
            header("Location: suites.php?reserva=success&wa=" . urlencode($url_wa));
            exit;
        }
    } catch(PDOException $e) {
        error_log("Error al guardar reserva de suite: " . $e->getMessage());
        header("Location: suites.php?reserva=error");
        exit;
    }
}

// 3. Traer TODAS las suites activas
try {
    $stmtSuites = $conn->query("SELECT * FROM suites WHERE estado = 1 ORDER BY orden ASC");
    $suites = $stmtSuites->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $suites = [];
}
?>

<?php include 'includes/header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main>
    <section style="position: relative; padding: 12rem 0 6rem; background-color: var(--color-primary-dark); text-align: center; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; opacity: 0.3;"></div>
        
        <div class="container" style="position: relative; z-index: 2;">
            <h1 style="color: var(--color-white); font-size: 3.5rem; margin-bottom: 1rem; text-shadow: 0 4px 15px rgba(0,0,0,0.5);">Colección de Suites</h1>
            <p style="color: var(--color-accent-yellow); font-size: 1.1rem; letter-spacing: 4px; text-transform: uppercase; font-weight: 600;">Descubra su refugio perfecto</p>
        </div>
    </section>

    <section class="suites section-padding" style="background-color: var(--color-bg-light);">
        <div class="container">
            <div class="suites-grid">
                <?php if(count($suites) > 0): ?>
                    <?php foreach($suites as $suite): ?>
                        <div class="suite-card" style="background: var(--color-white);">
                            <div class="suite-img-wrapper">
                                <img src="<?php echo htmlspecialchars($suite['imagen']); ?>" alt="<?php echo htmlspecialchars($suite['nombre']); ?>">
                                <div class="suite-price">S/ <?php echo number_format($suite['precio_noche'], 2); ?> <span>/ noche</span></div>
                            </div>
                            <div class="suite-content">
                                <h4><?php echo htmlspecialchars($suite['nombre']); ?></h4>
                                <p><?php echo mb_strimwidth(htmlspecialchars($suite['descripcion_corta']), 0, 80, "..."); ?></p>
                                
                                <div class="suite-amenities">
                                    <?php 
                                        $amenidades = json_decode($suite['amenidades_json'], true);
                                        if(is_array($amenidades)) {
                                            $iconos_preview = array_slice($amenidades, 0, 3);
                                            foreach($iconos_preview as $icono) {
                                                echo "<i class='".htmlspecialchars($icono)."'></i>";
                                            }
                                            if(count($amenidades) > 3) echo "<span style='font-size:0.8rem; color:var(--color-text-muted); font-weight:600;'>+" . (count($amenidades) - 3) . "</span>";
                                        }
                                    ?>
                                </div>
                                
                                <button type="button" class="btn btn-outline" style="width: 100%; border-radius: 4px;"
                                    data-nombre="<?php echo htmlspecialchars($suite['nombre'], ENT_QUOTES); ?>"
                                    data-precio="<?php echo number_format($suite['precio_noche'], 2); ?>"
                                    data-img="<?php echo htmlspecialchars($suite['imagen'], ENT_QUOTES); ?>"
                                    data-desc="<?php echo htmlspecialchars($suite['descripcion_corta'], ENT_QUOTES); ?>"
                                    data-amenidades="<?php echo htmlspecialchars($suite['amenidades_json'], ENT_QUOTES, 'UTF-8'); ?>"
                                    onclick="openSuiteModal(this)">
                                    <i class="fa-solid fa-eye"></i> Ver Detalles
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
                        <i class="fa-solid fa-bed" style="font-size: 3rem; color: var(--color-muted); margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3 style="color: var(--color-primary-dark);">Catálogo en Actualización</h3>
                        <p style="color: var(--color-text-muted);">Pronto revelaremos nuestras nuevas experiencias de alojamiento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <style>
        /* Overlay Compartido para Modales */
        .custom-modal-overlay {
            position: fixed; inset: 0; background: rgba(10, 17, 24, 0.9); backdrop-filter: blur(10px);
            z-index: 9999; display: flex; align-items: center; justify-content: center;
            opacity: 0; visibility: hidden; transition: all 0.4s ease; padding: 1rem;
        }
        .custom-modal-overlay.active { opacity: 1; visibility: visible; }
        
        /* Contenedor Modal de Detalles */
        .suite-details-content {
            background: var(--color-white); width: 100%; max-width: 900px; border-radius: 12px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5); transform: translateY(30px) scale(0.95); opacity: 0;
            transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1); position: relative; overflow: hidden;
            display: flex; flex-direction: row; min-height: 500px;
        }
        .custom-modal-overlay.active .suite-details-content { transform: translateY(0) scale(1); opacity: 1; }

        /* Contenedor Modal de Reserva */
        .suite-booking-content {
            background: var(--color-white); width: 100%; max-width: 600px; border-radius: 12px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5); transform: translateY(30px) scale(0.95); opacity: 0;
            transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1); position: relative; overflow: hidden;
        }
        .custom-modal-overlay.active .suite-booking-content { transform: translateY(0) scale(1); opacity: 1; }
        .suite-booking-content::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 6px; background: var(--gradient-gold); }

        .modal-close-btn {
            position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); border: none;
            width: 40px; height: 40px; border-radius: 50%; color: var(--color-primary-dark);
            cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center;
            z-index: 10; box-shadow: 0 4px 10px rgba(0,0,0,0.1); font-size: 1.2rem;
        }
        .modal-close-btn:hover { background: var(--color-accent-orange); color: white; transform: rotate(90deg);}

        /* Columnas del Modal de Detalles */
        .suite-modal-img { flex: 1.2; background-size: cover; background-position: center; background-color: #eee; position: relative; }
        .suite-modal-img::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to right, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0) 100%); }
        .suite-modal-info { flex: 1; padding: 3rem 2.5rem; display: flex; flex-direction: column; justify-content: center; }

        .suite-modal-title { font-family: var(--font-serif); font-size: 2rem; color: var(--color-primary-dark); margin-bottom: 0.5rem; line-height: 1.1; }
        .suite-modal-price { font-family: var(--font-sans); font-size: 1.5rem; font-weight: 700; color: var(--color-accent-orange); margin-bottom: 1.5rem; }
        .suite-modal-price span { font-size: 0.9rem; color: var(--color-text-muted); font-weight: 500; }
        .suite-modal-desc { color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.7; margin-bottom: 2rem; }
        
        .suite-modal-amenities-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; color: var(--color-primary-dark); font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;}
        .suite-modal-amenities { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem; }
        .amenity-badge { background: #F8FAFC; border: 1px solid #E2E8F0; padding: 8px 12px; border-radius: 6px; font-size: 0.8rem; color: var(--color-text-main); display: flex; align-items: center; gap: 8px; font-weight: 500;}
        .amenity-badge i { color: var(--color-primary-light); font-size: 1rem; }

        /* Botón de acción principal */
        .btn-action-luxe {
            width: 100%; padding: 1.2rem; border-radius: 6px; background: var(--gradient-gold); 
            color: var(--color-primary-dark); border: none; font-weight: 700; font-family: var(--font-sans);
            text-transform: uppercase; letter-spacing: 1px; cursor: pointer; text-align: center;
            transition: all 0.3s; box-shadow: 0 4px 15px rgba(241, 90, 36, 0.2); text-decoration: none; display: block; margin-top: 1rem;
        }
        .btn-action-luxe:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(241, 90, 36, 0.4); color: var(--color-primary-dark); }

        /* Estilos del Formulario dentro del Modal de Reserva */
        .booking-modal-header { padding: 2rem 2.5rem 1rem; text-align: center; }
        .booking-modal-header h3 { font-family: var(--font-serif); font-size: 1.8rem; color: var(--color-primary-dark); }
        .booking-modal-body { padding: 0 2.5rem 2rem; }
        
        .booking-modal-body .form-group { margin-bottom: 1rem; }
        .booking-modal-body label { display: block; font-size: 0.8rem; color: var(--color-primary-dark); font-weight: 600; margin-bottom: 0.3rem;}
        .booking-modal-body .form-control { 
            background: #F8FAFC; border: 1px solid #D1D8E0; border-radius: 6px; 
            padding: 0.8rem 1rem; width: 100%; color: var(--color-text-main); font-family: var(--font-sans);
        }
        .booking-modal-body .form-control:focus { border-color: var(--color-accent-orange); outline: none; background: #fff;}
        .booking-modal-body .form-control[readonly] { background: #E9ECEF; color: var(--color-primary-dark); font-weight: 700; border-color: transparent;}

        @media screen and (max-width: 768px) {
            .suite-details-content { flex-direction: column; max-height: 90vh; overflow-y: auto; }
            .suite-modal-img { min-height: 250px; flex: none; }
            .suite-modal-info { padding: 2rem; flex: none; }
            .booking-modal-body { padding: 0 1.5rem 1.5rem; }
            .booking-modal-header { padding: 1.5rem 1.5rem 1rem; }
        }
    </style>

    <div class="custom-modal-overlay" id="suiteModal" onclick="closeSuiteModal()">
        <div class="suite-details-content" onclick="event.stopPropagation()">
            <button type="button" class="modal-close-btn" onclick="closeSuiteModal()"><i class="fa-solid fa-xmark"></i></button>
            
            <div class="suite-modal-img" id="modSuiteImg"></div>
            
            <div class="suite-modal-info">
                <h3 class="suite-modal-title" id="modSuiteTitle">Nombre de la Suite</h3>
                <div class="suite-modal-price" id="modSuitePrice">S/ 0.00 <span>/ noche</span></div>
                
                <p class="suite-modal-desc" id="modSuiteDesc">Descripción completa de la suite.</p>
                
                <div class="suite-modal-amenities-title">Amenidades Incluidas</div>
                <div class="suite-modal-amenities" id="modSuiteAmenities">
                    </div>
                
                <button type="button" class="btn-action-luxe" onclick="openBookingModal()">
                    <i class="fa-regular fa-calendar-check"></i> Iniciar Reserva
                </button>
            </div>
        </div>
    </div>

    <div class="custom-modal-overlay" id="bookingModal" onclick="closeBookingModal()">
        <div class="suite-booking-content" onclick="event.stopPropagation()">
            <button type="button" class="modal-close-btn" style="background: rgba(0,0,0,0.05);" onclick="closeBookingModal()"><i class="fa-solid fa-xmark"></i></button>
            
            <div class="booking-modal-header">
                <h3>Solicitud de Reserva</h3>
            </div>
            
            <div class="booking-modal-body">
                <form action="suites.php" method="POST">
                    <input type="hidden" name="form_reserva_suite" value="1">
                    
                    <div class="form-group">
                        <label>Suite Seleccionada</label>
                        <input type="text" class="form-control" name="suite_interes" id="inputSuiteName" readonly>
                    </div>

                    <div class="form-group">
                        <input type="text" class="form-control" name="nombre" placeholder="Nombre Completo" required>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <input type="email" class="form-control" name="email" placeholder="Correo Electrónico" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <input type="tel" class="form-control" name="telefono" placeholder="Teléfono / WhatsApp" required>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group" style="flex: 1;">
                            <label>Check-in</label>
                            <input type="date" class="form-control" name="checkin" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Check-out</label>
                            <input type="date" class="form-control" name="checkout" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <textarea class="form-control" name="mensaje" placeholder="Requerimientos Especiales (Celebraciones, Traslados...)" required style="height: 80px; resize:none;"></textarea>
                    </div>
                    
                    <button type="submit" class="btn-action-luxe" style="margin-top: 0;">
                        Enviar a Concierge
                    </button>
                    <p style="text-align: center; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 10px;">
                        <i class="fa-brands fa-whatsapp" style="color: #25D366; font-size: 1rem; margin-right: 3px;"></i> Lo conectaremos vía WhatsApp al finalizar.
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Diccionario de amenidades
        const catalogoAmenidades = {
            'fa-solid fa-wifi': 'Wi-Fi Premium',
            'fa-solid fa-tv': 'Smart TV 85"',
            'fa-solid fa-wind': 'Climatización',
            'fa-solid fa-bell-concierge': 'Mayordomo',
            'fa-solid fa-water-ladder': 'Piscina Privada',
            'fa-solid fa-martini-glass': 'Minibar',
            'fa-solid fa-vault': 'Caja Fuerte',
            'fa-solid fa-bath': 'Bañera Romana',
            'fa-solid fa-snowflake': 'Aire Acond.'
        };

        // Variables globales para pasar datos entre modales
        let currentSuiteName = '';

        function openSuiteModal(btnElement) {
            // Extraer datos
            currentSuiteName = btnElement.getAttribute('data-nombre');
            const precio = btnElement.getAttribute('data-precio');
            const imgUrl = btnElement.getAttribute('data-img');
            const desc = btnElement.getAttribute('data-desc');
            const amenidadesStr = btnElement.getAttribute('data-amenidades');
            
            // Inyectar en modal de detalles
            document.getElementById('modSuiteTitle').innerText = currentSuiteName;
            document.getElementById('modSuitePrice').innerHTML = `S/ ${precio} <span>/ noche</span>`;
            document.getElementById('modSuiteImg').style.backgroundImage = `url('${imgUrl}')`;
            document.getElementById('modSuiteDesc').innerText = desc;
            
            // Procesar amenidades
            const amenitiesContainer = document.getElementById('modSuiteAmenities');
            amenitiesContainer.innerHTML = ''; 
            try {
                const arrAmenidades = JSON.parse(amenidadesStr);
                if (Array.isArray(arrAmenidades) && arrAmenidades.length > 0) {
                    arrAmenidades.forEach(iconClass => {
                        let textName = catalogoAmenidades[iconClass] || 'Exclusivo';
                        amenitiesContainer.innerHTML += `<div class="amenity-badge"><i class="${iconClass}"></i> ${textName}</div>`;
                    });
                }
            } catch (e) {}

            // Mostrar
            document.getElementById('suiteModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSuiteModal() {
            document.getElementById('suiteModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // ================= MAGIA UX: TRANSICIÓN DE MODALES =================
        function openBookingModal() {
            // Ocultamos el primero
            document.getElementById('suiteModal').classList.remove('active');
            
            // Inyectamos el nombre de la suite en el input de solo lectura
            document.getElementById('inputSuiteName').value = currentSuiteName;
            
            // Mostramos el formulario tras un leve retraso para fluidez
            setTimeout(() => {
                document.getElementById('bookingModal').classList.add('active');
            }, 300);
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').classList.remove('active');
            document.body.style.overflow = '';
        }
    </script>
</main>

<?php include 'includes/footer.php'; ?>

<?php if (isset($_GET['reserva']) && $_GET['reserva'] == 'success' && isset($_GET['wa'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({
            title: '¡Su solicitud está en el sistema!',
            text: 'Para asegurar su disponibilidad, haga clic en el botón de abajo para enviar los detalles directamente a nuestro Concierge VIP por WhatsApp.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#25D366', // Verde WhatsApp
            cancelButtonColor: '#0054A6', // Azul Karibes
            confirmButtonText: '<i class="fa-brands fa-whatsapp" style="font-size:1.2rem; margin-right:5px;"></i> Contactar Concierge',
            cancelButtonText: 'Cerrar ventana',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Al ser un clic real del usuario, el navegador NO bloquea la pestaña
                window.open('<?php echo urldecode($_GET['wa']); ?>', '_blank');
            }
            // Limpiar la URL para que no se repita al recargar
            window.history.replaceState(null, null, window.location.pathname);
        });
    });
</script>
<?php elseif (isset($_GET['reserva']) && $_GET['reserva'] == 'error'): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        Swal.fire({ title: 'Error', text: 'No pudimos procesar la reserva. Intente de nuevo.', icon: 'error', confirmButtonColor: '#F15A24' });
        window.history.replaceState(null, null, window.location.pathname);
    });
</script>
<?php endif; ?>