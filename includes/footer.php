<footer class="footer">
        <div class="container">
            <!-- Sección Superior: Newsletter VIP -->
            <div class="footer-newsletter" style="text-align: center; margin-bottom: 4rem; padding-bottom: 3rem; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <h3 style="color: var(--color-white); font-family: var(--font-serif); font-size: 1.8rem; margin-bottom: 1rem;">Únase al Círculo Karibes</h3>
                <p style="color: rgba(255,255,255,0.7); margin-bottom: 2rem; font-size: 0.95rem;">Suscríbase para recibir invitaciones a eventos privados y beneficios exclusivos.</p>
                <form action="#" method="POST" style="max-width: 500px; margin: 0 auto; display: flex; gap: 1rem;">
                    <input type="email" placeholder="Su correo electrónico corporativo o personal" required 
                           style="flex: 1; padding: 1rem 1.5rem; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white; border-radius: 4px; font-family: var(--font-sans);">
                    <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem;">Suscribir</button>
                </form>
            </div>

            <!-- Grid Principal del Footer -->
            <div class="footer-grid">
                <!-- Columna 1: Branding -->
                <div class="footer-col footer-logo">
                    <a href="index.php" class="logo" aria-label="Ir al inicio de Karibes Resorts">
                        <span class="logo-title">KARIBES</span>
                        <span class="logo-subtitle" style="color: rgba(255,255,255,0.5);">Resorts Internacional</span>
                    </a>
                    <p style="margin-top: 1rem; font-size: 0.9rem; line-height: 1.8;">El epítome del lujo caribeño. Redefiniendo la hospitalidad de clase mundial con servicios incomparables y paisajes majestuosos.</p>
                    <div style="margin-top: 1.5rem; display: flex; gap: 10px; align-items: center;">
                        <i class="fa-solid fa-star" style="color: var(--color-accent-yellow); font-size: 0.8rem;"></i>
                        <i class="fa-solid fa-star" style="color: var(--color-accent-yellow); font-size: 0.8rem;"></i>
                        <i class="fa-solid fa-star" style="color: var(--color-accent-yellow); font-size: 0.8rem;"></i>
                        <i class="fa-solid fa-star" style="color: var(--color-accent-yellow); font-size: 0.8rem;"></i>
                        <i class="fa-solid fa-star" style="color: var(--color-accent-yellow); font-size: 0.8rem;"></i>
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-left: 5px;">5-Star Diamond Award</span>
                    </div>
                </div>
                
                <!-- Columna 2: Navegación -->
                <nav class="footer-col" aria-label="Navegación del pie de página">
                    <h4>Descubrir</h4>
                    <ul class="footer-links">
                        <li><a href="#hero">Inicio</a></li>
                        <li><a href="#nosotros">La Experiencia Karibes</a></li>
                        <li><a href="#suites">Alojamiento y Villas</a></li>
                        <li><a href="#servicios">Servicios VIP</a></li>
                        <li><a href="#testimonios">Reseñas de Huéspedes</a></li>
                    </ul>
                </nav>
                
                <!-- Columna 3: Legal y Soporte -->
                <nav class="footer-col" aria-label="Enlaces legales">
                    <h4>Legal & Privacidad</h4>
                    <ul class="footer-links">
                        <li><a href="#">Aviso de Privacidad</a></li>
                        <li><a href="#">Términos y Condiciones</a></li>
                        <li><a href="#">Política de Cancelación</a></li>
                        <li><a href="#">Prensa y Medios</a></li>
                        <li><a href="#">Bolsa de Trabajo</a></li>
                    </ul>
                </nav>
                
                <!-- Columna 4: Redes Sociales -->
                <div class="footer-col">
                    <h4>Conecte con Nosotros</h4>
                    <p style="font-size: 0.85rem; margin-bottom: 1rem; color: rgba(255,255,255,0.7);">Síganos para inspirarse con nuestras vistas diarias.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Instagram de Karibes Resort" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" aria-label="Facebook de Karibes Resort" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter de Karibes Resort" target="_blank"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn corporativo" target="_blank"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Derechos de Autor -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Karibes Resorts Internacional. Todos los derechos reservados. | Diseño Exclusivo</p>
            </div>
        </div>
    </footer>

    <!-- Botón Flotante WhatsApp -->
    <a href="https://wa.me/1234567890" target="_blank" class="whatsapp-btn" title="Contactar Concierge VIP" aria-label="Chatear con nuestro Concierge por WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <!-- ==========================================================================
         SCRIPTS PRINCIPALES DE INTERACCIÓN (UX)
         ========================================================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            /* 1. COMPORTAMIENTO DEL HEADER AL HACER SCROLL (Glassmorphism) */
            const header = document.getElementById('header');
            const handleScroll = () => {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            };
            window.addEventListener('scroll', handleScroll, { passive: true });
            // Comprobación inicial por si se recarga a mitad de página
            handleScroll();

            /* 2. MENÚ MÓVIL (Interacción Hamburguesa a 'X') */
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const navLinks = document.getElementById('nav-links');
            const body = document.body;
            
            if(mobileBtn && navLinks) {
                mobileBtn.addEventListener('click', () => {
                    mobileBtn.classList.toggle('open');
                    navLinks.classList.toggle('active');
                    // Evitar scroll en el fondo cuando el menú está abierto
                    body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
                });

                // Cerrar menú al hacer clic en un enlace (Mobile)
                const links = document.querySelectorAll('.nav-links li a');
                links.forEach(link => {
                    link.addEventListener('click', () => {
                        mobileBtn.classList.remove('open');
                        navLinks.classList.remove('active');
                        body.style.overflow = ''; // Restaurar scroll
                    });
                });
            }

            /* 3. LÓGICA DEL SLIDER DE TESTIMONIOS (Auto-play y Controles) */
            let currentTestimonial = 1;
            const totalTestimonials = 3; // Modificar si se añaden más testimonios
            const dots = document.querySelectorAll('.slider-dot');
            let sliderInterval;

            window.showTestimonial = function(index) {
                // Ocultar todos los elementos y desactivar puntos
                for(let i=1; i<=totalTestimonials; i++) {
                    const el = document.getElementById(`testimonio-${i}`);
                    if(el) el.classList.remove('active');
                }
                dots.forEach(dot => dot.classList.remove('active'));
                
                // Mostrar seleccionado
                const selectedEl = document.getElementById(`testimonio-${index}`);
                if(selectedEl) {
                    selectedEl.classList.add('active');
                    if(dots[index-1]) dots[index-1].classList.add('active');
                    currentTestimonial = index;
                }

                // Reiniciar el temporizador para evitar saltos bruscos si el usuario hace clic
                resetInterval();
            };

            const startInterval = () => {
                sliderInterval = setInterval(() => {
                    let next = currentTestimonial + 1;
                    if(next > totalTestimonials) next = 1;
                    showTestimonial(next);
                }, 6000);
            };

            const resetInterval = () => {
                clearInterval(sliderInterval);
                startInterval();
            };

            // Iniciar auto-play
            if(document.querySelector('.testimonial-slider')) {
                startInterval();
            }

            /* 4. PREVENCIÓN DE FORMULARIOS PARA DEMO (Validación Frontend básica) */
            const form = document.getElementById('bookingForm');
            if(form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    // Aquí iría la llamada AJAX/Fetch al backend
                    alert('Gracias por su solicitud. Nuestro Concierge VIP se pondrá en contacto con usted a la brevedad para coordinar los detalles de su estadía exclusiva.');
                    form.reset();
                });
            }
        });
    </script>
</body>
</html>