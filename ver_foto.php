<?php
require_once 'config/database.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        $conexion = conectarDB();
        $stmt = $conexion->prepare("SELECT foto_perfil, nombre FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        
        if ($usuario && !empty($usuario['foto_perfil'])) {
            // Tiene foto, mostrarla
            $foto = $usuario['foto_perfil'];
            
            // Determinar el tipo de imagen
            $finfo = finfo_open();
            $mime_type = finfo_buffer($finfo, $foto, FILEINFO_MIME_TYPE);
            finfo_close($finfo);
            
            header("Content-Type: $mime_type");
            header("Content-Length: " . strlen($foto));
            header("Cache-Control: public, max-age=86400");
            
            echo $foto;
            exit();
        } elseif ($usuario) {
            // No tiene foto, mostrar inicial
            $inicial = strtoupper(substr($usuario['nombre'], 0, 1));
            
            // Generar colores basados en el nombre para variar
            $colors = ['#6B3F69', '#4A2A48', '#8A5B88', '#5A3360', '#9B6B99'];
            $color_index = abs(crc32($usuario['nombre'])) % count($colors);
            $bg_color = $colors[$color_index];
            
            header("Content-Type: image/svg+xml");
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">';
            echo '<rect width="200" height="200" fill="' . $bg_color . '"/>';
            echo '<text x="100" y="140" font-size="100" text-anchor="middle" fill="#FFD700" font-family="Arial, Helvetica, sans-serif" dominant-baseline="middle">' . $inicial . '</text>';
            echo '</svg>';
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error al obtener foto: " . $e->getMessage());
    }
}

// Si todo falla, mostrar un avatar genérico
header("Content-Type: image/svg+xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">';
echo '<rect width="200" height="200" fill="#6B3F69"/>';
echo '<text x="100" y="140" font-size="80" text-anchor="middle" fill="#FFD700" font-family="Arial, Helvetica, sans-serif" dominant-baseline="middle">?</text>';
echo '</svg>';
?>