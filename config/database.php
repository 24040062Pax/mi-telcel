<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_mitelcel');

// Función para conectar a la base de datos usando PDO
function conectarDB() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        $conexion = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        return $conexion;
        
    } catch (PDOException $e) {
        error_log("Error de conexión PDO: " . $e->getMessage());
        die("Error de conexión a la base de datos. Por favor, intente más tarde.");
    }
}
?>
