<?php
require_once '../config/database.php';
requireLogin();

// Obtener reservas del cliente
$db = new Database();
$db->query("SELECT r.*, s.nombre as servicio_nombre, s.precio, m.nombre as mecanico_nombre, m.especialidad 
           FROM reservas r 
           LEFT JOIN servicios s ON r.servicio_id = s.id 
           LEFT JOIN mecanicos m ON r.mecanico_id = m.id 
           WHERE r.cliente_id = :cliente_id 
           ORDER BY r.fecha_reserva DESC, r.hora_reserva DESC");
$db->bind(':cliente_id', $_SESSION['usuario_id']);
$reservas = $db->resultset();

// Obtener estadísticas
$db->query("SELECT estado, COUNT(*) as total FROM reservas WHERE cliente_id = :cliente_id GROUP BY estado");
$db->bind(':cliente_id', $_SESSION['usuario_id']);
$estadisticas = $db->resultset();

$stats = [];
foreach ($estadisticas as $stat) {
    $stats[$stat['estado']] = $stat['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoService - Panel Cliente</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #2c3e50;
        }

        .header {
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .main-content {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .welcome-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: #718096;
            margin-bottom: 2rem;
        }

        .tabs {
            display: flex;
            background: #f8fafc;
            border-radius: 12px;
            padding: 0.3rem;
            margin-bottom: 2rem;
        }

        .tab {
            flex: 1;
            padding: 1rem;
            background: transparent;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #718096;
            transition: all 0.3s ease;
        }

        .tab.active {
            background: #667eea;
            color: white;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .btn-new-reservation {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            float: right;
            margin-bottom: 2rem;
        }

        .btn-new-reservation:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .reservas-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .reservas-title {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 1.3rem;
            color: #2c3e50;
        }

        .reserva-card {
            padding: 2rem;
            border-bottom: 1px solid #f1f3f4;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 2rem;
            align-items: center;
        }

        .reserva-card:last-child {
            border-bottom: none;
        }

        .reserva-info {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1.5rem;
            align-items: center;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-confirmada {
            background: rgba(76, 205, 196, 0.2);
            color: #2d8f7a;
        }

        .status-pendiente {
            background: rgba(255, 193, 7, 0.2);
            color: #856404;
        }

        .status-rechazada {
            background: rgba(255, 107, 107, 0.2);
            color: #c53030;
        }

        .status-completada {
            background: rgba(72, 187, 120, 0.2);
            color: #2f855a;
        }

        .reserva-details h4 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .reserva-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: #718096;
            font-size: 0.9rem;
        }

        .reserva-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .reserva-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #718096;
        }

        .btn-action:hover {
            background: #f8fafc;
            color: #4a5568;
        }

        .btn-edit {
            color: #667eea;
            border-color: #667eea;
        }

        .btn-delete {
            color: #ff6b6b;
            border-color: #ff6b6b;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #718096;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .reserva-card {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .reserva-info {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .tabs {
                flex-direction: column;
                gap: 0.3rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">MotoService</div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
                </div>
                <span><strong><?php echo $_SESSION['nombre']; ?></strong></span>
                <a href="../logout.php" style="color: #ff6b6b; text-decoration: none; margin-left: 1rem;">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="main-content">
        <div class="welcome-section">
            <h1 class="welcome-title">¡Bienvenido, <?php echo $_SESSION['nombre']; ?>!</h1>
            <p class="welcome-subtitle">Gestiona tus reservas y revisa el historial de servicios</p>
            
            <a href="../reserva.php" class="btn-new-reservation">
                <i class="fas fa-plus"></i>
                Nueva Reserva
            </a>
            <div style="clear: both;"></div>

            <div class="tabs">
                <button class="tab active" data-tab="mis-reservas">
                    <i class="fas fa-calendar-check"></i> Mis Reservas
                </button>
                <button class="tab" data-tab="nueva-reserva">
                    <i class="fas fa-plus-circle"></i> Nueva Reserva
                </button>
                <button class="tab" data-tab="historial">
                    <i class="fas fa-history"></i> Historial
                </button>
            </div>
        </div>

        <!-- Mis Reservas -->
        <div class="tab-content active" id="mis-reservas">
            <div class="reservas-section">
                <div class="reservas-title">Reservas Activas</div>
                
                <?php 
                $reservas_activas = array_filter($reservas, function($r) { 
                    return in_array($r['estado'], ['pendiente', 'confirmada']); 
                });
                
                if (empty($reservas_activas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No tienes reservas activas</h3>
                        <p>Haz tu primera reserva para comenzar</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reservas_activas as $reserva): ?>
                        <div class="reserva-card">
                            <div class="reserva-info">
                                <div class="status-badge status-<?php echo $reserva['estado']; ?>">
                                    <?php echo ucfirst($reserva['estado']); ?>
                                </div>
                                
                                <div class="reserva-details">
                                    <h4><?php echo htmlspecialchars($reserva['servicio_nombre']); ?></h4>
                                    <div class="reserva-meta">
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($reserva['fecha_reserva'])); ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('H:i', strtotime($reserva['hora_reserva'])); ?>
                                        </span>
                                        <?php if ($reserva['mecanico_nombre']): ?>
                                        <span>
                                            <i class="fas fa-user-cog"></i>
                                            <?php echo htmlspecialchars($reserva['mecanico_nombre']); ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($reserva['marca_moto']): ?>
                                        <span>
                                            <i class="fas fa-motorcycle"></i>
                                            <?php echo htmlspecialchars($reserva['marca_moto'] . ' ' . $reserva['modelo_moto']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="reserva-actions">
                                <button class="btn-action btn-edit" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($reserva['estado'] === 'pendiente'): ?>
                                <button class="btn-action btn-delete" title="Cancelar reserva">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Nueva Reserva -->
        <div class="tab-content" id="nueva-reserva">
            <div class="reservas-section">
                <div class="reservas-title">Crear Nueva Reserva</div>
                <div style="padding: 2rem; text-align: center;">
                    <p style="margin-bottom: 2rem; color: #718096;">Programa tu próximo servicio de mantenimiento</p>
                    <a href="../reserva.php" class="btn-new-reservation">
                        <i class="fas fa-calendar-plus"></i>
                        Hacer Nueva Reserva
                    </a>
                </div>
            </div>
        </div>

        <!-- Historial -->
        <div class="tab-content" id="historial">
            <div class="reservas-section">
                <div class="reservas-title">Historial de Servicios</div>
                
                <?php 
                $historial = array_filter($reservas, function($r) { 
                    return in_array($r['estado'], ['completada', 'rechazada']); 
                });
                
                if (empty($historial)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history"></i>
                        <h3>Sin historial de servicios</h3>
                        <p>Tus servicios completados aparecerán aquí</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($historial as $reserva): ?>
                        <div class="reserva-card">
                            <div class="reserva-info">
                                <div class="status-badge status-<?php echo $reserva['estado']; ?>">
                                    <?php echo ucfirst($reserva['estado']); ?>
                                </div>
                                
                                <div class="reserva-details">
                                    <h4><?php echo htmlspecialchars($reserva['servicio_nombre']); ?></h4>
                                    <div class="reserva-meta">
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($reserva['fecha_reserva'])); ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-user-cog"></i>
                                            <?php echo htmlspecialchars($reserva['mecanico_nombre']); ?>
                                        </span>
                                        <?php if ($reserva['marca_moto']): ?>
                                        <span>
                                            <i class="fas fa-motorcycle"></i>
                                            <?php echo htmlspecialchars($reserva['marca_moto'] . ' ' . $reserva['modelo_moto']); ?>
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Manejar tabs
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remover clase active de todos los tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Activar el tab clickeado
                this.classList.add('active');
                document.getElementById(this.getAttribute('data-tab')).classList.add('active');
            });
        });
    </script>
</body>
</html>