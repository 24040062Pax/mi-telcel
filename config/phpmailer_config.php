<?php
// Configuración de PHPMailer
define('SMTP_HOST', 'smtp.gmail.com'); // Servidor SMTP
define('SMTP_PORT', 587); // Puerto SMTP
define('SMTP_USER', 'delimonpay142@gmail.com'); // 
define('SMTP_PASS', 'npca ddtf zaiw komz'); // CONTRASEÑA DE APLICACIÓN
define('SMTP_FROM_EMAIL', 'delimonpay142@gmail.com'); // CORREO REMITENTE
define('SMTP_FROM_NAME', 'Mi Telcel'); // NOMBRE REMITENTE

// Función para configurar PHPMailer
function configurarPHPMailer($mail) {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = 'tls';
    $mail->Port = SMTP_PORT;
    
    // Configuración del remitente
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    
    return $mail;
}
?>