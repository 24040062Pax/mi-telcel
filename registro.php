<?php
session_start();

// Verificar si ya hay una sesión activa
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php'); // Redirigir a dashboard si existe
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Mi Telcel</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Barra de navegación -->
     <nav class="navbar">
        <div class="navbar-logo">
            <img src="img/logo.png" alt="Logo Mi Telcel">
            <h1> Mi Telcel</h1>
        </div>
        <a href="portafolio.php" class="btn-portafolio">Regresar al Portafolio</a>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Panel de imagen -->
        <div class="image-panel">
            <div class="placeholder-image">
                <img src="img/decoracion.png" alt="Decoración de bienvenida" class="decoracion-img">
                <h3>Únete a nuestra comunidad</h3>
                <p>Crea una cuenta para acceder a contenido exclusivo y personalizar tu experiencia.</p>
            </div>
        </div>

        <!-- Panel del formulario -->
        <div class="form-panel">
            <div class="form-card">
                <h2>Crear Cuenta</h2>
                
                <?php if (isset($_GET['error'])): ?>
                    <div class="message error">
                        <?php 
                        $error = $_GET['error'];
                        if ($error === 'campos_vacios') {
                            echo "Por favor, completa todos los campos";
                        } elseif ($error === 'celular_invalido') {
                            echo "El número de celular debe tener al menos 10 dígitos";
                        } elseif ($error === 'email_invalido') {
                            echo "Por favor, ingresa un correo electrónico válido";
                        } elseif ($error === 'celular_existe') {
                            echo "Ya existe un usuario con este número de celular";
                        } elseif ($error === 'email_existe') {
                            echo "Ya existe un usuario con este correo electrónico";
                        } elseif ($error === 'registro_error') {
                            echo "Error al registrar el usuario. Por favor, intenta nuevamente";
                        } elseif ($error === 'email_error') {
                            echo "Error al enviar el correo de confirmación, pero el registro se completó";
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="message success">
                        ¡Registro exitoso! Revisa tu correo electrónico para confirmar tus datos.
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="procesar_registro.php">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo:</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ingresa tu nombre completo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="celular">Número de Celular:</label>
                        <input type="tel" id="celular" name="celular" placeholder="Ingresa tu número de celular (mínimo 10 dígitos)" minlength="10" required>
                        <small style="color: #666; font-size: 0.85rem;">Mínimo 10 dígitos</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" placeholder="Ingresa tu correo electrónico" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña:</label>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Crea una contraseña segura" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Registrarme</button>
                </form>
                
                <a href="index.php" class="btn btn-secondary">¿Ya tienes cuenta? Inicia Sesión</a>
            </div>
        </div>
    </div>

    <!-- Sección de información -->
    <section class="info-section">
        <h2>Beneficios de registrarte</h2>
        <div class="info-cards">
            <div class="info-card">
                <i class="fas fa-star"></i>
                <h3>Experiencia Personalizada</h3>
                <p>Recibe recomendaciones y contenido adaptado a tus intereses y preferencias.</p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-bell"></i>
                <h3>Notificaciones</h3>
                <p>Mantente informado sobre novedades, promociones y actualizaciones importantes.</p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-history"></i>
                <h3>Historial</h3>
                <p>Accede a tu historial de actividad y continúa donde lo dejaste en cualquier momento.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 Mi Telcel. Todos los derechos reservados.</p>
            <p>Desarrollado para nuestros usuarios</p>
            <div class="footer-links">
                <a href="#">Términos y condiciones</a>
                <a href="#">Política de privacidad</a>
                <a href="#">Contacto</a>
            </div>
        </div>
    </footer>
</body>
</html>