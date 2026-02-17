<?php
session_start();
require_once 'config/database.php';
require_once 'config/phpmailer_config.php';

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar datos
    $nombre = trim($_POST['nombre']);
    $celular = trim($_POST['celular']);
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    
    // Array para almacenar errores
    $errores = [];
    
    // Validaciones básicas
    if (empty($nombre) || empty($celular) || empty($correo) || empty($contrasena)) {
        $errores[] = 'campos_vacios';
    }
    
    // Validar celular
    if (!preg_match('/^[0-9]{10}$/', $celular)) {
        $errores[] = 'celular_invalido';
    }
    
    // Validar correo electrónico
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'email_invalido';
    }
    
    // Validar contraseña
    if (strlen($contrasena) < 6) {
        $errores[] = 'contrasena_corta';
    }
    
    // Procesar la foto si se subió
    $foto_binaria = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_NO_FILE) {
        $archivo = $_FILES['foto_perfil'];
        
        // Validar que no haya error en la subida
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            $errores[] = 'foto_error';
        }
        
        // Validar tipo de archivo
        $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($archivo['type'], $permitidos)) {
            $errores[] = 'tipo_foto_invalido';
        }
        
        // Validar tamaño (5MB máximo)
        $max_size = 5 * 1024 * 1024;
        if ($archivo['size'] > $max_size) {
            $errores[] = 'foto_grande';
        }
        
        // Si no hay errores, leer la imagen
        if (empty($errores)) {
            $foto_binaria = file_get_contents($archivo['tmp_name']);
        }
    }
    
    // Si hay errores, redirigir con el primer error
    if (!empty($errores)) {
        header('Location: registro.php?error=' . $errores[0]);
        exit();
    }
    
    try {
        $conexion = conectarDB();
        
        // Verificar si el celular ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE celular = ?");
        $stmt->execute([$celular]);
        
        if ($stmt->fetch()) {
            header('Location: registro.php?error=celular_existe');
            exit();
        }
        
        // Verificar si el correo ya existe
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        
        if ($stmt->fetch()) {
            header('Location: registro.php?error=email_existe');
            exit();
        }
        
        // Hash de la contraseña
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario con foto si se proporcionó
        if ($foto_binaria) {
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, celular, correo, foto_perfil, contraseña) VALUES (?, ?, ?, ?, ?)");
            $resultado = $stmt->execute([$nombre, $celular, $correo, $foto_binaria, $contrasena_hash]);
        } else {
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, celular, correo, contraseña) VALUES (?, ?, ?, ?)");
            $resultado = $stmt->execute([$nombre, $celular, $correo, $contrasena_hash]);
        }
        
        if ($resultado) {
            $usuario_id = $conexion->lastInsertId();
            
            // Enviar correo de confirmación
            $email_enviado = enviarCorreoConfirmacion($nombre, $celular, $correo);
            
            if ($email_enviado) {
                header('Location: registro.php?success=true');
            } else {
                header('Location: registro.php?error=email_error');
            }
            exit();
        } else {
            header('Location: registro.php?error=registro_error');
            exit();
        }
        
    } catch (PDOException $e) {
        error_log("Error en registro: " . $e->getMessage());
        header('Location: registro.php?error=registro_error');
        exit();
    }
    
} else {
    header('Location: registro.php');
    exit();
}

// Función para enviar correo de confirmación
function enviarCorreoConfirmacion($nombre, $celular, $correo) {
    try {
        require __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require __DIR__ . '/PHPMailer/src/SMTP.php';
        require __DIR__ . '/PHPMailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail = configurarPHPMailer($mail);
        
        $mail->addAddress($correo, $nombre);
        $mail->Subject = 'Confirmación de Registro - Mi Telcel';
        
        $mail->Body = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Confirmación de Registro</title>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .header { background-color: #6B3F69; color: white; padding: 20px; text-align: center; }
                .content { padding: 30px; }
                .footer { text-align: center; color: #777; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>¡Bienvenido a Mi Telcel!</h1>
            </div>
            <div class='content'>
                <h2>Hola $nombre,</h2>
                <p>Gracias por registrarte. Tu cuenta ha sido creada exitosamente.</p>
                <p><strong>Celular:</strong> $celular</p>
                <p><strong>Correo:</strong> $correo</p>
                <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
            </div>
            <div class='footer'>
                <p>&copy; 2026 Mi Telcel. Todos los derechos reservados.</p>
            </div>
        </body>
        </html>
        ";
        
        $mail->isHTML(true);
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}
?>
