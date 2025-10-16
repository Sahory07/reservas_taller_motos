<?php
require_once 'config/database.php';
requireLogin();

$error = '';
$success = '';

// Obtener servicios y mecánicos
$db = new Database();
$db->query("SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre");
$servicios = $db->resultset();

$db->query("SELECT * FROM mecanicos WHERE disponible = 1 ORDER BY nombre");
$mecanicos = $db->resultset();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $servicio_id = sanitize($_POST['servicio_id']);
    $mecanico_id = sanitize($_POST['mecanico_id']);
    $fecha_reserva = sanitize($_POST['fecha_reserva']);
    $hora_reserva = sanitize($_POST['hora_reserva']);
    $marca_moto = sanitize($_POST['marca_moto']);
    $modelo_moto = sanitize($_POST['modelo_moto']);
    $anio_moto = sanitize($_POST['año_moto']); // Corregido variable
    $comentarios = sanitize($_POST['comentarios']);
    
    if (!empty($servicio_id) && !empty($fecha_reserva) && !empty($hora_reserva)) {
        // Verificar disponibilidad
        $db->query("SELECT id FROM reservas WHERE fecha_reserva = :fecha AND hora_reserva = :hora AND mecanico_id = :mecanico AND estado IN ('pendiente', 'confirmada')");
        $db->bind(':fecha', $fecha_reserva);
        $db->bind(':hora', $hora_reserva);
        $db->bind(':mecanico', $mecanico_id);
        
        if ($db->single()) {
            $error = 'Este horario ya está ocupado. Por favor selecciona otro.';
        } else {
            // Crear reserva
            $db->query("INSERT INTO reservas (cliente_id, mecanico_id, servicio_id, marca_moto, modelo_moto, anio_moto, fecha_reserva, hora_reserva, comentarios, estado) VALUES (:cliente_id, :mecanico_id, :servicio_id, :marca_moto, :modelo_moto, :anio_moto, :fecha_reserva, :hora_reserva, :comentarios, 'pendiente')");
            $db->bind(':cliente_id', $_SESSION['usuario_id']);
            $db->bind(':mecanico_id', $mecanico_id);
            $db->bind(':servicio_id', $servicio_id);
            $db->bind(':marca_moto', $marca_moto);
            $db->bind(':modelo_moto', $modelo_moto);
            $db->bind(':anio_moto', $anio_moto); // Corregido bind
            $db->bind(':fecha_reserva', $fecha_reserva);
            $db->bind(':hora_reserva', $hora_reserva);
            $db->bind(':comentarios', $comentarios);
            
            if ($db->execute()) {
                $success = 'Reserva creada exitosamente. Te contactaremos pronto para confirmarla.';
            } else {
                $error = 'Error al crear la reserva. Intenta nuevamente.';
            }
        }
    } else {
        $error = 'Por favor complete todos los campos obligatorios.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoBlook - Hacer Reserva</title>
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
            color: #2c3e50;
        }

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

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #4a5568;
        }

        .main-content {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .reserva-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .reserva-title {
            text-align: center;
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .reserva-subtitle {
            text-align: center;
            color: #718096;
            margin-bottom: 2rem;
        }

        .section {
            margin-bottom: 2rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.2rem;
            color: #667eea;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .service-option {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .service-option:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .service-option.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .service-option input {
            display: none;
        }

        .days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .day-option {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.8rem 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.9rem;
        }

        .day-option.available {
            background: rgba(76, 205, 196, 0.1);
            border-color: #4ecdc4;
        }

        .day-option.unavailable {
            background: rgba(255, 107, 107, 0.1);
            border-color: #ff6b6b;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .time-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 0.5rem;
        }

        .time-option {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.9rem;
        }

        .time-option:hover {
            border-color: #667eea;
        }

        .time-option.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .time-option input {
            display: none;
        }

        .mecanico-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .mecanico-card {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .mecanico-card:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .mecanico-card.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .mecanico-card input {
            display: none;
        }

        .mecanico-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.5rem;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 1.2rem;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .error-message, .success-message {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .error-message {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .success-message {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .service-grid, .mecanico-grid {
                grid-template-columns: 1fr;
            }
            
            .days-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">MotoBlook</div>
            <div class="user-info">
                <i class="fas fa-user"></i>
                <span><?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?></span>
                <a href="cliente/dashboard.php" style="color: #667eea; text-decoration: none; margin-left: 1rem;">Dashboard</a>
                <a href="logout.php" style="color: #ff6b6b; text-decoration: none; margin-left: 1rem;">Salir</a>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="reserva-container">
            <h2 class="reserva-title">Hacer Reserva</h2>
            <p class="reserva-subtitle">Programa tu servicio de motocicleta</p>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Tipo de Servicio -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-tools"></i>
                        Tipo de Servicio
                    </div>
                    <div class="service-grid">
                        <?php foreach ($servicios as $servicio): ?>
                            <label class="service-option">
                                <input type="radio" name="servicio_id" value="<?php echo $servicio['id']; ?>" required>
                                <strong><?php echo htmlspecialchars($servicio['nombre']); ?></strong>
                                <?php if ($servicio['precio'] > 0): ?>
                                    <br><small>$<?php echo number_format($servicio['precio'], 2); ?></small>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Días Disponibles -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Días Disponibles
                    </div>
                    <div class="days-grid">
                        <?php
                        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                        $disponibilidad = ['available', 'available', 'available', 'available', 'available', 'available', 'unavailable'];
                        for ($i = 0; $i < 7; $i++):
                        ?>
                            <div class="day-option <?php echo $disponibilidad[$i]; ?>">
                                <div><?php echo $dias[$i]; ?></div>
                                <div style="font-size: 0.8em; margin-top: 0.2rem;">
                                    <?php echo $disponibilidad[$i] === 'available' ? 'Disponible' : 'No Disponible'; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Fecha y Hora -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-clock"></i>
                        Fecha Preferida y Hora Preferida
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_reserva">Fecha Preferida</label>
                            <input type="date" id="fecha_reserva" name="fecha_reserva" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                    </div>
                    
                    <div class="time-grid">
                        <?php
                        $horarios = ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];
                        foreach ($horarios as $hora):
                        ?>
                            <label class="time-option">
                                <input type="radio" name="hora_reserva" value="<?php echo $hora; ?>" required>
                                <?php echo $hora; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Información de la Moto -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-motorcycle"></i>
                        Información de tu Moto
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="marca_moto">Marca</label>
                            <input type="text" id="marca_moto" name="marca_moto" placeholder="Honda, Yamaha, etc.">
                        </div>
                        <div class="form-group">
                            <label for="modelo_moto">Modelo</label>
                            <input type="text" id="modelo_moto" name="modelo_moto" placeholder="CB600F, MT-07, etc.">
                        </div>
                        <div class="form-group">
                            <label for="año_moto">Año</label>
                            <input type="number" id="año_moto" name="año_moto" min="1990" max="<?php echo date('Y'); ?>" placeholder="2020">
                        </div>
                    </div>
                </div>

                <!-- Mecánico Preferido -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-user-cog"></i>
                        Mecánico Preferido
                    </div>
                    <div class="mecanico-grid">
                        <?php foreach ($mecanicos as $mecanico): ?>
                            <label class="mecanico-card">
                                <input type="radio" name="mecanico_id" value="<?php echo $mecanico['id']; ?>">
                                <div class="mecanico-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <strong><?php echo htmlspecialchars($mecanico['nombre']); ?></strong>
                                <br><small><?php echo htmlspecialchars($mecanico['especialidad']); ?></small>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Comentarios Adicionales -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-comment"></i>
                        Comentarios Adicionales (Opcional)
                    </div>
                    <div class="form-group">
                        <textarea 
                            name="comentarios" 
                            rows="4" 
                            placeholder="Describe cualquier problema específico o requerimiento especial..."
                            maxlength="500"
                        ></textarea>
                        <small style="color: #718096;">0/500 caracteres</small>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Enviar Reserva
                </button>
            </form>
        </div>
    </div>

    <script>
        // Manejar selección de opciones de servicio
        document.querySelectorAll('.service-option input').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelectorAll('.service-option').forEach(option => {
                    option.classList.remove('selected');
                });
                if (this.checked) {
                    this.parentElement.classList.add('selected');
                }
            });
        });

        // Manejar selección de mecánico
        document.querySelectorAll('.mecanico-card input').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelectorAll('.mecanico-card').forEach(card => {
                    card.classList.remove('selected');
                });
                if (this.checked) {
                    this.parentElement.classList.add('selected');
                }
            });
        });

        // Manejar selección de hora
        document.querySelectorAll('.time-option input').forEach(input => {
            input.addEventListener('change', function() {
                document.querySelectorAll('.time-option').forEach(option => {
                    option.classList.remove('selected');
                });
                if (this.checked) {
                    this.parentElement.classList.add('selected');
                }
            });
        });

        // Contador de caracteres para comentarios
        document.querySelector('textarea[name="comentarios"]').addEventListener('input', function() {
            const count = this.value.length;
            const counter = this.parentElement.querySelector('small');
            counter.textContent = count + "/500 caracteres";
            
            if (count > 450) {
                counter.style.color = '#ff6b6b';
            } else {
                counter.style.color = '#718096';
            }
        });

        // Validar fecha mínima
        document.getElementById('fecha_reserva').min = new Date(Date.now() + 86400000).toISOString().split('T')[0];
    </script>
</body>
</html>
