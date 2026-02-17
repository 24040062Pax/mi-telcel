<?php
session_start();
require_once 'config/database.php';

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$error_foto = '';
$success_foto = '';
$usuario = null; // Declarar variable fuera del try

// Procesar cambio de foto si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $archivo = $_FILES['foto_perfil'];
    
    // Validaciones
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $error_foto = "Error al subir el archivo";
    } elseif (!in_array($archivo['type'], $permitidos)) {
        $error_foto = "Tipo de archivo no permitido. Solo JPG, PNG y GIF";
    } elseif ($archivo['size'] > $max_size) {
        $error_foto = "El archivo es demasiado grande (máximo 5MB)";
    } else {
        try {
            $conexion = conectarDB();
            
            // Leer el contenido del archivo
            $imagen_binaria = file_get_contents($archivo['tmp_name']);
            
            // Actualizar base de datos
            $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
            $stmt->execute([$imagen_binaria, $usuario_id]);
            
            $success_foto = "Foto de perfil actualizada correctamente";
            
        } catch (PDOException $e) {
            error_log("Error al actualizar foto: " . $e->getMessage());
            $error_foto = "Error en la base de datos";
        }
    }
}

try {
    $conexion = conectarDB();
    
    // Obtener datos actualizados del usuario
    $stmt = $conexion->prepare("SELECT nombre, celular, correo, foto_perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        session_destroy();
        header('Location: index.php');
        exit();
    }
    
} catch (PDOException $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    $error = "Error al cargar los datos";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mi Telcel</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos para el modal/cambio de foto */
        .cambio-foto-form {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
        
        .cambio-foto-form h4 {
            color: #6B3F69;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .file-input-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }
        
        .file-input-wrapper input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: white;
        }
        
        .file-input-wrapper input[type="file"]::-webkit-file-upload-button {
            background-color: #6B3F69;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .file-input-wrapper small {
            display: block;
            color: #666;
            margin-top: 5px;
        }
        
        .btn-subir-foto {
            background-color: #6B3F69;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        
        .btn-subir-foto:hover {
            background-color: #5a3360;
        }
        
        .btn-cancelar {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            margin-left: 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-cancelar:hover {
            background-color: #5a6268;
        }
        
        .foto-actual {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .foto-actual img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid #6B3F69;
            object-fit: cover;
        }
        
        /* Ocultar formulario por defecto */
        #formCambioFoto {
            display: none;
        }
        
        #formCambioFoto.mostrar {
            display: block;
        }
        
        .btn-cambiar-foto {
            background-color: #6B3F69;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-cambiar-foto:hover {
            background-color: #5a3360;
        }
        
        /* Estilos para información no editable */
        .info-estatica {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #6B3F69;
        }
        
        .info-estatica p {
            margin: 0.5rem 0;
            color: #333;
        }
        
        .info-estatica strong {
            color: #6B3F69;
            min-width: 120px;
            display: inline-block;
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
        
        <div class="navbar-right">
            <div class="user-info-navbar">
                <img src="ver_foto.php?id=<?php echo $usuario_id; ?>" 
                     alt="Foto de perfil" class="user-avatar">
                <span class="user-name-navbar">
                    <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                </span>
            </div>
            <a href="cerrar_sesion.php" class="btn-logout-navbar" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i>
                <span class="logout-text">Salir</span>
            </a>
        </div>
    </nav>

    <!-- Contenedor principal -->
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h1>
            <p>Aquí puedes ver tu información personal</p>
        </div>

        <?php if ($success_foto): ?>
            <div class="message success">
                <?php echo htmlspecialchars($success_foto); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_foto): ?>
            <div class="message error">
                <?php echo htmlspecialchars($error_foto); ?>
            </div>
        <?php endif; ?>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar-container">
                    <div class="profile-avatar">
                        <img src="ver_foto.php?id=<?php echo $usuario_id; ?>&t=<?php echo time(); ?>" 
                             alt="Foto de perfil" id="profileImage">
                    </div>
                    
                    <button class="btn-cambiar-foto" onclick="toggleFormulario()">
                        <i class="fas fa-camera"></i>
                        Cambiar foto
                    </button>
                </div>
                
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h2>
                </div>
            </div>
            
            <!-- Formulario para cambiar foto (oculto inicialmente) -->
            <div id="formCambioFoto" class="cambio-foto-form">
                <h4><i class="fas fa-camera"></i> Cambiar foto de perfil</h4>
                
                <div class="foto-actual">
                    <img src="ver_foto.php?id=<?php echo $usuario_id; ?>&t=<?php echo time(); ?>" 
                         alt="Foto actual">
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="file-input-wrapper">
                        <input type="file" id="foto_perfil" name="foto_perfil" 
                               accept="image/jpeg,image/png,image/gif" required>
                        <small>Formatos permitidos: JPG, PNG, GIF (Máximo 5MB)</small>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn-subir-foto">
                            <i class="fas fa-upload"></i> Subir foto
                        </button>
                        <button type="button" class="btn-cancelar" onclick="toggleFormulario()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="profile-details">
                <h3>Información Personal</h3>
                
                <div class="info-estatica">
                    <p>
                        <strong><i class="fas fa-envelope"></i> Correo:</strong>
                        <?php echo htmlspecialchars($usuario['correo'] ?? 'No disponible'); ?>
                    </p>
                    <p>
                        <strong><i class="fas fa-phone"></i> Teléfono:</strong>
                        <?php echo htmlspecialchars($usuario['celular'] ?? 'No disponible'); ?>
                    </p>
                    <p>
                        <strong><i class="fas fa-calendar"></i> Miembro desde:</strong>
                        <?php echo date('d/m/Y'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

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

    <script>
        function toggleFormulario() {
            var form = document.getElementById('formCambioFoto');
            form.classList.toggle('mostrar');
        }
    </script>
</body>
</html>