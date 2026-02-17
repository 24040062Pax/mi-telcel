<?php
session_start();

// Verificar si ya hay una sesión activa
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
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
    <style>
        /* Estilos para el campo de archivo */
        .file-input-container {
            margin-top: 10px;
        }
        
        .file-input-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px dashed #6B3F69;
            text-align: center;
            color: #6B3F69;
        }
        
        .file-input-info i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #6B3F69;
        }
        
        .file-input-info p {
            margin: 5px 0;
            font-size: 0.9rem;
        }
        
        .file-input-info small {
            color: #666;
            display: block;
            margin-top: 10px;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
            cursor: pointer;
        }
        
        input[type="file"]::-webkit-file-upload-button {
            background-color: #6B3F69;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        input[type="file"]::-webkit-file-upload-button:hover {
            background-color: #5a3360;
        }
    </style>
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar">
        <div class="navbar-logo">
            <img src="img/logo.png" alt="Logo Mi Telcel">
            <h1>Mi Telcel</h1>
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
                            echo "El número de celular debe tener exactamente 10 dígitos";
                        } elseif ($error === 'email_invalido') {
                            echo "Por favor, ingresa un correo electrónico válido";
                        } elseif ($error === 'contrasena_corta') {
                            echo "La contraseña debe tener al menos 6 caracteres";
                        } elseif ($error === 'celular_existe') {
                            echo "Ya existe un usuario con este número de celular";
                        } elseif ($error === 'email_existe') {
                            echo "Ya existe un usuario con este correo electrónico";
                        } elseif ($error === 'registro_error') {
                            echo "Error al registrar el usuario. Por favor, intenta nuevamente";
                        } elseif ($error === 'email_error') {
                            echo "Error al enviar el correo de confirmación, pero el registro se completó";
                        } elseif ($error === 'foto_error') {
                            echo "Error al subir la foto de perfil";
                        } elseif ($error === 'tipo_foto_invalido') {
                            echo "Tipo de archivo no válido. Solo se permiten JPG, PNG y GIF";
                        } elseif ($error === 'foto_grande') {
                            echo "La foto es demasiado grande (máximo 5MB)";
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="message success">
                        ¡Registro exitoso! Revisa tu correo electrónico para confirmar tus datos.
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="procesar_registro.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo:</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ingresa tu nombre completo" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="celular">Número de Celular:</label>
                        <input type="tel" id="celular" name="celular" placeholder="Ingresa tu número de celular (10 dígitos)" 
                               pattern="[0-9]{10}" maxlength="10" minlength="10" 
                               title="Debe ingresar exactamente 10 dígitos numéricos" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" placeholder="Ingresa tu correo electrónico" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña:</label>
                        <input type="password" id="contrasena" name="contrasena" 
                               placeholder="Crea una contraseña segura (mínimo 6 caracteres)" 
                               minlength="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="foto_perfil">Foto de Perfil (opcional):</label>
                        <div class="file-input-info">
                            <i class="fas fa-camera"></i>
                            <p>Sube una foto de perfil</p>
                            <small>Formatos permitidos: JPG, PNG, GIF (Máximo 5MB)</small>
                        </div>
                        <div class="file-input-container">
                            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/gif">
                        </div>
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
                <p>Recibe recomendaciones y contenido adaptado a tus intereses.</p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-bell"></i>
                <h3>Notificaciones</h3>
                <p>Mantente informado sobre novedades y promociones importantes.</p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-history"></i>
                <h3>Historial</h3>
                <p>Accede a tu historial de actividad en cualquier momento.</p>
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
