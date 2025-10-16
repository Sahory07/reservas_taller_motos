<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoBlook - El taller más confiable de la ciudad</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            line-height: 1.6;
            color: #2a3e50;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-style: italic;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn-login, .btn-register {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-login {
            background: #667eea;
            color: white;
        }

        .btn-login:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-register {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-register:hover {
            background: #667eea;
            color: white;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(0,0,0,0.7), rgba(0,0,0,0.5)),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect width="1200" height="600" fill="%23333"/><ellipse cx="200" cy="300" rx="80" ry="40" fill="%23ff6b6b" opacity="0.3"/><ellipse cx="1000" cy="200" rx="100" ry="50" fill="%234ecdc4" opacity="0.3"/><path d="M0 400c100-20 200 20 300-10s200-50 300-20 200 30 300 0 200-40 300-10v230H0z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
            background-position: center;
            padding: 4rem 2rem;
            text-align: center;
            color: white;
            border-radius: 20px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 3rem;
            opacity: 0.95;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary, .btn-secondary {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        /* Footer Info */
        .footer-info {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            color: #ff6b6b;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .footer-section p, .footer-section li {
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
        }

        .contact-item i {
            color: #4ecdc4;
            width: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .footer-info {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-section {
            animation: fadeInUp 1s ease;
        }

        .footer-info {
            animation: fadeInUp 1s ease 0.3s both;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <div class="logo">MotoBlook</div>
            <div class="nav-buttons">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="btn-login">Panel Admin</a>
                    <?php else: ?>
                        <a href="cliente/dashboard.php" class="btn-login">Mis Reservas</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn-register">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Iniciar Sesión</a>
                    <a href="register.php" class="btn-register">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">Reserva tu Servicio en MotoBlook</h1>
                <p class="hero-subtitle">
                    El taller más confiable de la ciudad. Servicio profesional, tecnología 
                    avanzada y mecánicos especializados para tu moto.
                </p>
                <div class="hero-buttons">
                    <?php if (isLoggedIn()): ?>
                        <a href="reserva.php" class="btn-primary">
                            <i class="fas fa-calendar-plus"></i>
                            Hacer Reserva
                        </a>
                        <a href="servicios.php" class="btn-secondary">
                            <i class="fas fa-tools"></i>
                            Ver Servicios
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Hacer Reserva
                        </a>
                        <a href="servicios.php" class="btn-secondary">
                            <i class="fas fa-tools"></i>
                            Ver Servicios
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Footer Info -->
        <div class="footer-info">
            <div class="footer-section">
                <h3><i class="fas fa-motorcycle"></i> MotoBlook</h3>
                <p>El taller líder en servicios especializados para motocicletas</p>
            </div>
            
            <div class="footer-section">
                <h3>Contacto</h3>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Av. trinidad</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+159 75009026</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>Helenrivero@.com</span>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Horarios</h3>
                <p><strong>Lunes - Viernes:</strong> 8:00 - 18:00</p>
                <p><strong>Sábados:</strong> 8:00 - 14:00</p>
                <p><strong>Domingos:</strong> Cerrado</p>
            </div>
        </div>
    </div>

    <script>
        // Smooth scrolling para los enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>