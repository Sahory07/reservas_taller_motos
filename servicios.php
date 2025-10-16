<?php
require_once 'config/database.php';

// Obtener todos los servicios activos
$db = new Database();
$db->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre");
$servicios = $db->resultset();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoBlook - Nuestros Servicios</title>
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
            color: #2c3e50;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
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

        .btn-nav {
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary-nav {
            background: #667eea;
            color: white;
        }

        .btn-primary-nav:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-secondary-nav {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary-nav:hover {
            background: #667eea;
            color: white;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header Section */
        .services-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .services-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .services-subtitle {
            font-size: 1.2rem;
            color: #718096;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .btn-cta {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .service-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--service-color);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .service-card.mantenimiento { --service-color: #4ecdc4; }
        .service-card.reparacion { --service-color: #ff6b6b; }
        .service-card.revision { --service-color: #feca57; }
        .service-card.frenos { --service-color: #48bb78; }
        .service-card.electrico { --service-color: #9f7aea; }
        .service-card.suspension { --service-color: #ed8936; }
        .service-card.carroceria { --service-color: #38b2ac; }
        .service-card.otro { --service-color: #718096; }

        .service-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            background: var(--service-color);
        }

        .service-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .service-description {
            color: #718096;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .service-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }

        .service-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #48bb78;
        }

        .service-duration {
            color: #718096;
            font-size: 0.9rem;
        }

        .btn-reserve {
            background: var(--service-color);
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-reserve:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }

        /* Info Section */
        .info-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .info-item {
            padding: 1.5rem;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin: 0 auto 1rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
        }

        .info-item h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .info-item p {
            color: #718096;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .services-title {
                font-size: 2.5rem;
            }
            
            .nav-buttons {
                flex-direction: column;
                gap: 0.5rem;
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

        .service-card {
            animation: fadeInUp 0.6s ease;
        }

        .service-card:nth-child(even) {
            animation-delay: 0.1s;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="nav-container">
            <div class="logo">MotoBlook</div>
            <div class="nav-buttons">
                <a href="index.php" class="btn-nav btn-secondary-nav">Inicio</a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="btn-nav btn-primary-nav">Panel Admin</a>
                    <?php else: ?>
                        <a href="cliente/dashboard.php" class="btn-nav btn-primary-nav">Mis Reservas</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn-nav btn-secondary-nav">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="login.php" class="btn-nav btn-primary-nav">Iniciar Sesión</a>
                    <a href="register.php" class="btn-nav btn-secondary-nav">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Services Header -->
        <section class="services-header">
            <h1 class="services-title">Nuestros Servicios</h1>
            <p class="services-subtitle">
                Ofrecemos una amplia gama de servicios especializados para el mantenimiento y reparación de tu motocicleta con la más alta calidad y profesionalismo.
            </p>
            <?php if (isLoggedIn()): ?>
                <a href="reserva.php" class="btn-cta">
                    <i class="fas fa-calendar-plus"></i>
                    Reservar Servicio
                </a>
            <?php else: ?>
                <a href="register.php" class="btn-cta">
                    <i class="fas fa-user-plus"></i>
                    Registrarse para Reservar
                </a>
            <?php endif; ?>
        </section>

        <!-- Services Grid -->
        <div class="services-grid">
            <?php 
            $iconos = [
                'Mantenimiento General' => 'fas fa-tools',
                'Reparación Motor' => 'fas fa-cog',
                'Revisión Técnica' => 'fas fa-clipboard-check',
                'Servicio de Frenos' => 'fas fa-stop-circle',
                'Sistema Eléctrico' => 'fas fa-bolt',
                'Suspensión' => 'fas fa-compress-arrows-alt',
                'Carrocería' => 'fas fa-paint-brush',
                'Otro' => 'fas fa-wrench'
            ];
            
            $clases = [
                'Mantenimiento General' => 'mantenimiento',
                'Reparación Motor' => 'reparacion',
                'Revisión Técnica' => 'revision',
                'Servicio de Frenos' => 'frenos',
                'Sistema Eléctrico' => 'electrico',
                'Suspensión' => 'suspension',
                'Carrocería' => 'carroceria',
                'Otro' => 'otro'
            ];

            foreach ($servicios as $servicio): 
                $clase = $clases[$servicio['nombre']] ?? 'otro';
                $icono = $iconos[$servicio['nombre']] ?? 'fas fa-wrench';
            ?>
            <div class="service-card <?php echo $clase; ?>">
                <div class="service-icon">
                    <i class="<?php echo $icono; ?>"></i>
                </div>
                <h3 class="service-name"><?php echo htmlspecialchars($servicio['nombre']); ?></h3>
                <p class="service-description">
                    <?php echo htmlspecialchars($servicio['descripcion'] ?: 'Servicio especializado para el mantenimiento óptimo de tu motocicleta.'); ?>
                </p>
                <div class="service-details">
                    <div>
                        <?php if ($servicio['precio'] > 0): ?>
                            <div class="service-price">$<?php echo number_format($servicio['precio'], 2); ?></div>
                        <?php else: ?>
                            <div class="service-price">Consultar</div>
                        <?php endif; ?>
                        <?php if ($servicio['duracion_estimada']): ?>
                            <div class="service-duration"><?php echo $servicio['duracion_estimada']; ?> min aprox.</div>
                        <?php endif; ?>
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <a href="reserva.php" class="btn-reserve">
                            <i class="fas fa-calendar-plus"></i>
                            Reservar
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn-reserve">
                            <i class="fas fa-sign-in-alt"></i>
                            Iniciar Sesión
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Info Section -->
        <section class="info-section">
            <h2 style="font-size: 2rem; color: #2c3e50; margin-bottom: 1rem;">¿Por qué elegirnos?</h2>
            <p style="color: #718096; margin-bottom: 2rem;">Somos el taller de confianza para miles de motociclistas</p>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h3>Mecánicos Especializados</h3>
                    <p>Nuestro equipo cuenta con más de 15 años de experiencia en todas las marcas y modelos.</p>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3>Tecnología Avanzada</h3>
                    <p>Utilizamos herramientas y equipos de última generación para diagnósticos precisos.</p>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Garantía Total</h3>
                    <p>Todos nuestros trabajos incluyen garantía completa y repuestos originales.</p>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Servicio Rápido</h3>
                    <p>Cumplimos con los tiempos estimados sin comprometer la calidad del servicio.</p>
                </div>
            </div>
        </section>
    </div>

    <script>
        // Animación suave al hacer scroll
        window.addEventListener('scroll', function() {
            const cards = document.querySelectorAll('.service-card');
            cards.forEach(card => {
                const rect = card.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }
            });
        });

        // Efecto de hover mejorado
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>