# 🌴 Karibe'S Resort Internacional | Plataforma de Gestión y Reservas

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![SweetAlert2](https://img.shields.io/badge/SweetAlert2-UI%2FUX-FF6B6B?style=for-the-badge)
![Estado](https://img.shields.io/badge/Estado-Producci%C3%B3n-28A745?style=for-the-badge)

> Plataforma web Full-Stack para Karibe'S, el 1er resort climatizado del Perú. Desarrollada en PHP/PDO y MySQL. Integra un Front-End de ultra-lujo y un Back-End con RBAC para gestión omnicanal de reservas (WhatsApp), catálogo dinámico de suites y reportes automatizados. Alta seguridad y UX premium.

---

## ✨ Características Principales (Core Features)

### 1. Front-End Inmersivo (Orientado a Conversión)
- **Diseño Ultra-Premium:** Interfaz responsiva con efectos *Glassmorphism*, transiciones fluidas y una paleta de colores corporativa estrictamente calculada.
- **Catálogo Dinámico:** Visualización de suites y habitaciones en tiempo real conectada a la base de datos.
- **Social Proof Moderado:** Sistema de testimonios con modal de alta gama (los testimonios requieren aprobación del administrador antes de ser públicos).

### 2. Integración Omnicanal (WhatsApp & DB)
- **Cierre de Ventas en Caliente:** Al solicitar una reserva, el sistema registra el *lead* en la base de datos y genera un enlace pre-formateado hacia WhatsApp con los detalles exactos (fechas, suite de interés, nombre), evitando que el navegador bloquee la pestaña emergente.

### 3. Back-End Administrativo Avanzado
- **RBAC (Role-Based Access Control):** Separación de privilegios entre SuperAdmin, Recepción y Marketing.
- **Operaciones VIP:** Ficha de reserva optimizada para impresión (CSS `@media print`) con cálculo automático de noches de estadía y control rápido de estados.
- **Reportes Nativos:** Exportación de reservas a Microsoft Excel `.xls` mediante inyección directa de cabeceras HTTP, sin depender de librerías pesadas.
- **Gestión de Archivos:** Carga y previsualización en vivo de imágenes para el catálogo de suites.

---

## 🛡️ Arquitectura y Seguridad
Este proyecto fue construido siguiendo estrictos estándares de ingeniería de software:
* **Inyección SQL Cero:** Uso exclusivo de **PDO (PHP Data Objects)** con sentencias preparadas (`prepare`/`bindParam`) en absolutamente todas las consultas.
* **Patrón PRG (Post-Redirect-Get):** Implementado en los formularios del Front-End y Back-End para evitar la duplicación accidental de registros al presionar `F5`.
* **Seguridad de Credenciales:** Encriptación de contraseñas mediante algoritmo **BCRYPT** (`password_hash`).
* **Prevención de Session Hijacking:** Cierre de sesión absoluto con destrucción de variables, archivos de servidor y cookies de navegador (`setcookie` expiry).
* **Anti-Suicidio Digital:** Lógica que impide que el SuperAdmin degrade sus propios permisos por error durante la edición de su perfil.

---

Desarrollado por
[Luciano Quiroz Gonzales]
Arquitecto de Software & Desarrollador Full-Stack

Si este proyecto te resulta interesante o útil, no dudes en dejar una ⭐ estrella en el repositorio.
