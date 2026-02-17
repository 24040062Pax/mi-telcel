<?php
session_start();
require_once 'config/database.php';

// Verificar si ya hay una sesión activa
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Procesar inicio de sesión
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $celular = trim($_POST['celular']);
    $contrasena = $_POST['contrasena'];
    
    if (!empty($celular) && !empty($contrasena)) {
        try {
            $conexion = conectarDB();
            
            // Buscar usuario por celular 
            $stmt = $conexion->prepare("SELECT id, nombre, contraseña FROM usuarios WHERE celular = ?");
            $stmt->execute([$celular]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                // Verificar contraseña
                if (password_verify($contrasena, $usuario['contraseña'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = "Contraseña incorrecta";
                }
            } else {
                $error = "No existe un usuario con ese número de celular";
            }
            
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
            $error = "Error en el sistema. Por favor, intente más tarde.";
        }
    } else {
        $error = "Por favor, completa todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Mi Telcel</title>
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
        <a href="https://24040062pax.github.io/portafolio-fw/" class="btn-portafolio">Volver al Portafolio</a>
    </nav>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Panel de imagen -->
        <div class="image-panel">
            <div class="placeholder-image">
                <img src="img/decoracion.png" alt="Decoración de bienvenida" class="decoracion-img">
                <h3>Bienvenido de nuevo</h3>
                <p>Inicia sesión para acceder a tu cuenta y disfrutar de todos nuestros servicios.</p>
            </div>
        </div>

        <!-- Panel del formulario -->
        <div class="form-panel">
            <div class="form-card">
                <h2>Iniciar Sesión</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="message error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="celular">Número de Celular:</label>
                        <input type="tel" id="celular" name="celular" placeholder="Ingresa tu número de celular" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrasena">Contraseña:</label>
                        <input type="password" id="contrasena" name="contrasena" placeholder="Ingresa tu contraseña" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </form>
                
                <a href="registro.php" class="btn btn-secondary">¿No tienes cuenta? Regístrate</a>
            </div>
        </div>
    </div>

    <!-- Sección de información -->
    <section class="info-section">
        <h2>Conoce nuestras herramientas de autogestión</h2>
        <div class="info-cards">
            <div class="info-card">
                <i class="fas fa-mobile-alt"></i>
                <h3>Consultar</h3>
                <p>Realiza consultas y administra los servicios de tu línea.</p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-headset"></i>
                <h3>Operaciones</h3>
                <p>Realiza recargas, compra paquetes y paga tu factura.</p>
            </div>
            
            <div class="info-card">
                <i class="fas fa-bolt"></i>
                <h3>Rapidez</h3>
                <p>Ahorra tiempo realizando trámites en línea.</p>
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

