<?php
/**
 * Sistema de Conexión a Base de Datos - Karibes Resorts Internacional
 * Nivel: Producción / Alta Seguridad
 * Patrón: PDO (PHP Data Objects) Orientado a Objetos
 */

class Conexion {
    // Credenciales de acceso
    private $host = "localhost"; // Normalmente es localhost en cPanel/Plesk
    private $db_name = "karibesresort_sistemaDB";
    private $username = "karibesresort_user";
    private $password = "Zo@jjt00*yKndMdf";
    
    // Propiedad para almacenar la instancia de la conexión
    public $conn;

    /**
     * Establece y retorna la conexión a la base de datos
     * @return PDO|null
     */
    public function conectar() {
        $this->conn = null;

        try {
            // Configuración del DSN con soporte total para caracteres internacionales (utf8mb4)
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            
            // Matriz de opciones avanzadas de PDO
            $opciones = [
                // Lanzar excepciones reales en caso de error para poder capturarlas con Try/Catch
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
                // Retornar resultados como arrays asociativos por defecto (más limpio y rápido)
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
                // Desactivar emulación para forzar a MySQL a preparar las sentencias (Máxima seguridad)
                PDO::ATTR_EMULATE_PREPARES   => false,                  
            ];

            // Instanciamos la conexión
            $this->conn = new PDO($dsn, $this->username, $this->password, $opciones);
            
        } catch(PDOException $exception) {
            // Guardamos el error real en los logs del servidor de forma invisible al público
            error_log("Fallo crítico en DB Karibes: " . $exception->getMessage());
            
            // Detenemos la ejecución y mostramos un mensaje amigable y seguro
            die("<strong>Sistema Karibes:</strong> En este momento estamos realizando un mantenimiento en nuestros servidores de datos. Por favor, intente recargar la página en unos minutos.");
        }

        return $this->conn;
    }
}
?>