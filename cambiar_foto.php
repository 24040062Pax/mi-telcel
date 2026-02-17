<?php
session_start();
require_once 'config/database.php';

// Verificar si hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$error = '';

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $archivo = $_FILES['foto_perfil'];
    
    // Validaciones
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $error = "Error al subir el archivo";
    } elseif (!in_array($archivo['type'], $permitidos)) {
        $error = "Tipo de archivo no permitido. Solo JPG, PNG y GIF";
    } elseif ($archivo['size'] > $max_size) {
        $error = "El archivo es demasiado grande (máximo 5MB)";
    } else {
        try {
            $conexion = conectarDB();
            
            // Leer el contenido del archivo
            $imagen_binaria = file_get_contents($archivo['tmp_name']);
            
            // Actualizar base de datos
            $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
            $stmt->execute([$imagen_binaria, $usuario_id]);
            
            header('Location: dashboard.php?success=foto_actualizada');
            exit();
            
        } catch (PDOException $e) {
            error_log("Error al actualizar foto: " . $e->getMessage());
            $error = "Error en la base de datos";
        }
    }
}

// Obtener datos del usuario
try {
    $conexion = conectarDB();
    $stmt = $conexion->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Foto - Mi Telcel</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cambiar-foto-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .foto-actual {
            margin-bottom: 2rem;
        }
        
        .foto-actual img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 4px solid #6B3F69;
            object-fit: cover;
        }
        
        .info-formatos {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            color: #666;
        }
        
        .btn-cancelar {
            background-color: #6c757d;
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 1rem;
        }
        
        .btn-cancelar:hover {
            background-color: #5a6268;
        }
        
        .botones {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
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
                <span class="user-name-navbar"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
            </div>
            <a href="cerrar_sesion.php" class="btn-logout-navbar" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i>
                <span class="logout-text">Salir</span>
            </a>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Cambiar Foto de Perfil</h1>
            <p>Selecciona una nueva foto para tu perfil</p>
        </div>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="cambiar-foto-container">
            <div class="foto-actual">
                <img src="ver_foto.php?id=<?php echo $usuario_id; ?>&t=<?php echo time(); ?>" 
                     alt="Foto actual">
                <p style="margin-top: 1rem; color: #666;">Foto actual</p>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="foto_perfil">Seleccionar nueva foto:</label>
                    <input type="file" id="foto_perfil" name="foto_perfil" 
                           accept="image/jpeg,image/png,image/gif" required>
                </div>
                
                <div class="info-formatos">
                    <i class="fas fa-info-circle"></i>
                    Formatos permitidos: JPG, PNG, GIF (Máximo 5MB)
                </div>
                
                <div class="botones">
                    <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.8rem 2rem;">
                        <i class="fas fa-upload"></i> Subir Foto
                    </button>
                    <a href="dashboard.php" class="btn-cancelar">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; 2026 Mi Telcel. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>