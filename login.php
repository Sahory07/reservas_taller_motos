<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $tipo_usuario = sanitize($_POST['tipo_usuario']);
    
    if (!empty($email) && !empty($password)) {
        $db = new Database();
        $db->query("SELECT * FROM usuarios WHERE email = :email AND tipo_usuario = :tipo_usuario AND activo = 1");
        $db->bind(':email', $email);
        $db->bind(':tipo_usuario', $tipo_usuario);
        
        $usuario = $db->single();
        
        if ($usuario && md5($password) === $usuario['password']) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
            
            if ($tipo_usuario === 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('cliente/dashboard.php');
            }
        } else {
            $error = 'Credenciales incorrectas o usuario inactivo';
        }
    } else {
        $error = 'Por favor complete todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoBlook - Iniciar Sesión</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-style: italic;
            margin-bottom: 1rem;
        }

        .login-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .login-subtitle {
            color: #718096;
            margin-bottom: 2rem;
        }

        .user-type-selector {
            display: flex;
            background: #f7fafc;
            border-radius: 12px;
            padding: 0.3rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .user-type-option {
            flex: 1;
            padding: 0.8rem;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #718096;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .user-type-option.active {
            background: #667eea;
            color: white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .user-type-option i {
            margin-right: 0.5rem;
        }

        .form-group {
            text-align: left;
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .register-link {
            color: #718096;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .demo-credentials {
            background: #f7fafc;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 2rem;
            text-align: left;
            font-size: 0.9rem;
            color: #4a5568;
        }

        .demo-credentials h4 {
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: 1px solid #feb2b2;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">MotoBlook</div>
        <h2 class="login-title">Iniciar Sesión</h2>
        <p class="login-subtitle">Accede a tu cuenta para gestionar tus reservas</p>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Tipo de Usuario</label>
                <div class="user-type-selector">
                    <button type="button" class="user-type-option active" data-type="cliente">
                        <i class="fas fa-user"></i> Cliente
                    </button>
                    <button type="button" class="user-type-option" data-type="admin">
                        <i class="fas fa-user-shield"></i> Admin
                    </button>
                </div>
                <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="cliente">
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="tu@email.com" 
                    required
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                >
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </button>
        </form>

        <p class="register-link">
            ¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>
        </p>
        

    
    </div>

    <script>
        // Manejar selector de tipo de usuario
        document.querySelectorAll('.user-type-option').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.user-type-option').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                document.getElementById('tipo_usuario').value = this.getAttribute('data-type');
            });
        });
    </script>
</body>
</html>
