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
    
    // Validaciones
    if (empty($nombre) || empty($celular) || empty($correo) || empty($contrasena)) {
        header('Location: registro.php?error=campos_vacios');
        exit();
    }
    
    // Validar celular 
    if (strlen($celular) < 10 || !is_numeric($celular)) {
        header('Location: registro.php?error=celular_invalido');
        exit();
    }
    
    // Validar correo electrónico
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header('Location: registro.php?error=email_invalido');
        exit();
    }
    
    // Conectar a la base de datos
    $conexion = conectarDB();
    
    // Verificar si el celular ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE celular = ?");
    $stmt->bind_param("s", $celular);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conexion->close();
        header('Location: registro.php?error=celular_existe');
        exit();
    }
    
    // Verificar si el correo ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conexion->close();
        header('Location: registro.php?error=email_existe');
        exit();
    }
    
    // Hash de la contraseña
    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
    
    // Insertar nuevo usuario
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, celular, correo, contraseña) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $celular, $correo, $contrasena_hash);
    
    if ($stmt->execute()) {
        $usuario_id = $stmt->insert_id;
        
        // Enviar correo de confirmación
        $email_enviado = enviarCorreoConfirmacion($nombre, $celular, $correo);
        
        $stmt->close();
        $conexion->close();
        
        if ($email_enviado) {
            header('Location: registro.php?success=true');
        } else {
            header('Location: registro.php?error=email_error');
        }
        exit();
    } else {
        $stmt->close();
        $conexion->close();
        header('Location: registro.php?error=registro_error');
        exit();
    }
} else {
    // Si no es POST, redirigir al registro
    header('Location: registro.php');
    exit();
}

// Función para enviar correo de confirmación
function enviarCorreoConfirmacion($nombre, $celular, $correo) {
    try {
        // Cargar PHPMailer
        require __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require __DIR__ . '/PHPMailer/src/SMTP.php';
        require __DIR__ . '/PHPMailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail = configurarPHPMailer($mail);
        
        // Destinatario
        $mail->addAddress($correo, $nombre);
        
        // Asunto y cuerpo del mensaje
        $mail->Subject = 'Confirmación de Registro - Sistema de Usuarios';
        
        $mail->Body = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmación de Registro</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #6B3F69; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px; }
                .footer { text-align: center; margin-top: 30px; color: #777; font-size: 0.9em; }
                .data-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
                .data-table th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>¡Bienvenido a Mi Telcel!</h1>
                </div>
                <div class='content'>
                    <h2>Hola $nombre,</h2>
                    <p>Gracias por registrarte en nuestro sistema. Tu cuenta ha sido creada exitosamente.</p>
                    
                    <h3>Tus datos de registro:</h3>
                    <table class='data-table'>
                        <tr>
                            <th>Campo</th>
                            <th>Valor</th>
                        </tr>
                        <tr>
                            <td><strong>Nombre:</strong></td>
                            <td>$nombre</td>
                        </tr>
                        <tr>
                            <td><strong>Celular:</strong></td>
                            <td>$celular</td>
                        </tr>
                        <tr>
                            <td><strong>Correo Electrónico:</strong></td>
                            <td>$correo</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Registro:</strong></td>
                            <td>" . date('d/m/Y H:i:s') . "</td>
                        </tr>
                    </table>
                    
                    <p>Para iniciar sesión y visita nuestro sitio web</p>
                    
                    <div class='footer'>
                        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                        <p>&copy; " . date('Y') . " Mi Telcel. Todos los derechos reservados.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Configurar como HTML
        $mail->isHTML(true);
        
        // Enviar correo
        return $mail->send();
        
    } catch (Exception $e) {
        // En caso de error, puedes registrar el error en un log
        error_log("Error al enviar correo: " . $mail->ErrorInfo);
        return false;
    }
}
?>