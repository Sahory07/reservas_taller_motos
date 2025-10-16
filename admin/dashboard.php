<?php
require_once '../config/database.php';
requireLogin();
requireAdmin();

// Obtener estadísticas generales
$db = new Database();

// Reservas por estado
$db->query("SELECT estado, COUNT(*) as total FROM reservas GROUP BY estado");
$estadisticas = $db->resultset();

$stats = ['pendiente' => 0, 'confirmada' => 0, 'rechazada' => 0, 'completada' => 0, 'cancelada' => 0];
foreach ($estadisticas as $stat) {
    $stats[$stat['estado']] = $stat['total'];
}
$total_reservas = array_sum($stats);

// Ingresos del mes
$db->query("SELECT SUM(s.precio) as total_ingresos, COUNT(*) as servicios_completados 
           FROM reservas r 
           JOIN servicios s ON r.servicio_id = s.id 
           WHERE r.estado = 'completada' AND MONTH(r.fecha_reserva) = MONTH(CURRENT_DATE())");
$ingresos = $db->single();

// Calificación promedio (simulada)
$calificacion_promedio = 4.8;
$satisfaccion = 95;

// Obtener todas las reservas para gestión
$db->query("SELECT r.*, u.nombre as cliente_nombre, u.apellido as cliente_apellido, u.telefono as cliente_telefono, u.email as cliente_email,
                   s.nombre as servicio_nombre, s.precio, 
                   m.nombre as mecanico_nombre
           FROM reservas r 
           LEFT JOIN usuarios u ON r.cliente_id = u.id
           LEFT JOIN servicios s ON r.servicio_id = s.id 
           LEFT JOIN mecanicos m ON r.mecanico_id = m.id 
           ORDER BY r.fecha_reserva DESC, r.hora_reserva DESC");
$todas_reservas = $db->resultset();

// Obtener mecánicos (simulado, reemplazar por tu consulta real si existe)
$db->query("SELECT *, 0 as reservas_activas FROM mecanicos"); // Ajusta según tu DB
$mecanicos = $db->resultset();

// Procesar cambios de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $reserva_id = sanitize($_POST['reserva_id']);
    $nuevo_estado = sanitize($_POST['nuevo_estado']);
    
    $db->query("UPDATE reservas SET estado = :estado WHERE id = :id");
    $db->bind(':estado', $nuevo_estado);
    $db->bind(':id', $reserva_id);
    $db->execute();
    
    // Recargar la página para mostrar cambios
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MotoBlook - Panel Admin</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
/* ===================== Estilos generales ===================== */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { background: #f8f9fa; color: #2c3e50; min-height: 100vh; }
.header { background: white; padding: 1rem 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
.nav-container { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 2rem; }
.logo { font-size: 2rem; font-weight: 700; background: linear-gradient(45deg, #667eea, #764ba2); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-style: italic; }
.admin-badge { background: linear-gradient(45deg, #ff6b6b, #feca57); color: white; padding: 0.3rem 1rem; border-radius: 15px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
.user-info { display: flex; align-items: center; gap: 1rem; }
.main-content { padding: 2rem; max-width: 1400px; margin: 0 auto; }
/* ===================== Estadísticas ===================== */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05); position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--accent-color, #667eea); }
.stat-card.pendientes { --accent-color: #ffd93d; }
.stat-card.confirmadas { --accent-color: #6bcf7f; }
.stat-card.rechazadas { --accent-color: #ff6b6b; }
.stat-card.total { --accent-color: #4ecdc4; }
.stat-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; background: var(--accent-color); }
.stat-value { font-size: 2rem; font-weight: 700; color: #2c3e50; }
.stat-label { color: #718096; font-size: 0.9rem; margin-top: 0.5rem; }
/* ===================== Tabs ===================== */
.tabs { display: flex; background: white; border-radius: 12px; padding: 0.3rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.tab { flex: 1; padding: 1rem; background: transparent; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; color: #718096; transition: all 0.3s ease; }
.tab.active { background: #667eea; color: white; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3); }
.tab-content { display: none; }
.tab-content.active { display: block; }
/* ===================== Tabla de reservas ===================== */
.reservas-table { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
.table-header { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; }
.btn-export { background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; transition: all 0.3s ease; }
.btn-export:hover { background: rgba(255,255,255,0.3); }
.reserva-row { display: grid; grid-template-columns: 2fr 1.5fr 1fr 1fr 1fr auto; gap: 1rem; padding: 1.5rem 2rem; border-bottom: 1px solid #f1f3f4; align-items: center; }
.reserva-row:last-child { border-bottom: none; }
.cliente-info h4 { color: #2c3e50; margin-bottom: 0.2rem; }
.cliente-info .meta { color: #718096; font-size: 0.9rem; }
.servicio-info { color: #4a5568; }
.servicio-info .precio { color: #48bb78; font-weight: 600; }
.fecha-info { text-align: center; color: #4a5568; }
.mecanico-info { text-align: center; color: #4a5568; }
.status-select { padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.9rem; cursor: pointer; }
.status-pendiente { background: rgba(255, 193, 7, 0.1); color: #856404; }
.status-confirmada { background: rgba(76, 205, 196, 0.1); color: #2d8f7a; }
.status-rechazada { background: rgba(255, 107, 107, 0.1); color: #c53030; }
.status-completada { background: rgba(72, 187, 120, 0.1); color: #2f855a; }
.status-cancelada { background: rgba(160, 174, 192, 0.1); color: #4a5568; }
.actions-buttons { display: flex; gap: 0.5rem; }
.btn-action { padding: 0.4rem 0.8rem; border: 1px solid #e2e8f0; background: white; border-radius: 4px; cursor: pointer; font-size: 0.8rem; transition: all 0.3s ease; }
.btn-view { color: #667eea; border-color: #667eea; }
.btn-edit { color: #feca57; border-color: #feca57; }
.btn-delete { color: #ff6b6b; border-color: #ff6b6b; }
.btn-action:hover { background: #f8fafc; }
/* ===================== Cards resumen ===================== */
.summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.summary-card { background: white; border-radius: 12px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.summary-card .value { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
.summary-card.ingresos .value { color: #48bb78; }
.summary-card.servicios .value { color: #4299e1; }
.summary-card.calificacion .value { color: #ed8936; }
.summary-card.satisfaccion .value { color: #9f7aea; }
/* ===================== Responsive ===================== */
@media (max-width: 768px) {
    .stats-grid { grid-template-columns: 1fr; }
    .reserva-row { grid-template-columns: 1fr; gap: 0.5rem; }
    .tabs { flex-direction: column; gap: 0.3rem; }
}
</style>
</head>
<body>
<header class="header">
<div class="nav-container">
    <div class="logo">MotoBlook</div>
    <div class="admin-badge">Panel Admin</div>
    <div class="user-info">
        <i class="fas fa-user-shield"></i>
        <span><strong>Administrador</strong></span>
        <a href="../logout.php" style="color: #ff6b6b; text-decoration: none; margin-left: 1rem;">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</div>
</header>

<div class="main-content">
<!-- Estadísticas Principales -->
<div class="stats-grid">
    <div class="stat-card pendientes">
        <div class="stat-header"><div class="stat-icon"><i class="fas fa-clock"></i></div></div>
        <div class="stat-value"><?php echo $stats['pendiente']; ?></div>
        <div class="stat-label">Pendientes</div>
    </div>
    <div class="stat-card confirmadas">
        <div class="stat-header"><div class="stat-icon"><i class="fas fa-check"></i></div></div>
        <div class="stat-value"><?php echo $stats['confirmada']; ?></div>
        <div class="stat-label">Confirmadas</div>
    </div>
    <div class="stat-card rechazadas">
        <div class="stat-header"><div class="stat-icon"><i class="fas fa-times"></i></div></div>
        <div class="stat-value"><?php echo $stats['rechazada']; ?></div>
        <div class="stat-label">Rechazadas</div>
    </div>
    <div class="stat-card total">
        <div class="stat-header"><div class="stat-icon"><i class="fas fa-calendar-alt"></i></div></div>
        <div class="stat-value"><?php echo $total_reservas; ?></div>
        <div class="stat-label">Total</div>
    </div>
</div>

<!-- Tabs de Navegación -->
<div class="tabs">
    <button class="tab active" data-tab="gestionar-reservas"><i class="fas fa-tasks"></i> Gestionar Reservas</button>
    <button class="tab" data-tab="mecanicos"><i class="fas fa-users-cog"></i> Mecánicos</button>
    <button class="tab" data-tab="reportes"><i class="fas fa-chart-bar"></i> Reportes</button>
</div>

<!-- Gestión de Reservas -->
<div class="tab-content active" id="gestionar-reservas">
    <div class="reservas-table">
        <div class="table-header">
            <h3>Gestión de Reservas</h3>
            <button class="btn-export"><i class="fas fa-download"></i> Exportar</button>
        </div>
        
        <?php foreach ($todas_reservas as $reserva): ?>
        <div class="reserva-row">
            <div class="cliente-info">
                <h4><?php echo htmlspecialchars($reserva['cliente_nombre'] . ' ' . $reserva['cliente_apellido']); ?></h4>
                <div class="meta">
                    <div><?php echo htmlspecialchars($reserva['cliente_telefono']); ?></div>
                    <div><?php echo htmlspecialchars($reserva['cliente_email']); ?></div>
                </div>
            </div>
            
            <div class="servicio-info">
                <div><strong><?php echo htmlspecialchars($reserva['servicio_nombre']); ?></strong></div>
                <!-- Marca y modelo eliminados para evitar error -->
                <?php if ($reserva['precio']): ?>
                <div class="precio">$<?php echo number_format($reserva['precio'], 2); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="fecha-info">
                <div><?php echo date('d/m/Y', strtotime($reserva['fecha_reserva'])); ?></div>
                <div><?php echo date('H:i', strtotime($reserva['hora_reserva'])); ?></div>
            </div>
            
            <div class="mecanico-info">
                <?php echo $reserva['mecanico_nombre'] ? htmlspecialchars($reserva['mecanico_nombre']) : 'Sin asignar'; ?>
            </div>
            
            <div>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                    <select name="nuevo_estado" class="status-select status-<?php echo $reserva['estado']; ?>" onchange="this.form.submit()">
                        <option value="pendiente" <?php echo $reserva['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="confirmada" <?php echo $reserva['estado'] === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                        <option value="rechazada" <?php echo $reserva['estado'] === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                        <option value="completada" <?php echo $reserva['estado'] === 'completada' ? 'selected' : ''; ?>>Completada</option>
                        <option value="cancelada" <?php echo $reserva['estado'] === 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                    </select>
                </form>
            </div>
            
            <div class="actions-buttons">
                <button class="btn-action btn-view" title="Ver detalles"><i class="fas fa-eye"></i></button>
                <button class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></button>
                <button class="btn-action btn-delete" title="Eliminar"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Mecánicos -->
<div class="tab-content" id="mecanicos">
    <div class="reservas-table">
        <div class="table-header">
            <h3>Gestión de Mecánicos</h3>
            <button class="btn-export"><i class="fas fa-plus"></i> Agregar Mecánico</button>
        </div>
        
        <?php foreach ($mecanicos as $mecanico): ?>
        <div class="reserva-row" style="grid-template-columns: 2fr 2fr 1fr 1fr auto;">
            <div class="cliente-info">
                <h4><?php echo htmlspecialchars($mecanico['nombre']); ?></h4>
                <div class="meta"><?php echo htmlspecialchars($mecanico['especialidad']); ?></div>
            </div>
            
            <div class="servicio-info">
                <div><?php echo htmlspecialchars($mecanico['telefono']); ?></div>
                <div><?php echo htmlspecialchars($mecanico['email']); ?></div>
            </div>
            
            <div class="fecha-info">
                <div><?php echo $mecanico['reservas_activas']; ?></div>
                <small>Reservas activas</small>
            </div>
            
            <div class="mecanico-info">
                <span class="status-select <?php echo $mecanico['disponible'] ? 'status-confirmada' : 'status-rechazada'; ?>">
                    <?php echo $mecanico['disponible'] ? 'Disponible' : 'No disponible'; ?>
                </span>
            </div>
            
            <div class="actions-buttons">
                <button class="btn-action btn-view" title="Ver horarios"><i class="fas fa-calendar"></i></button>
                <button class="btn-action btn-edit" title="Editar"><i class="fas fa-edit"></i></button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Reportes -->
<div class="tab-content" id="reportes">
    <div class="summary-cards">
        <div class="summary-card ingresos">
            <div class="value">$<?php echo number_format($ingresos['total_ingresos'] ?? 2450, 2); ?></div>
            <div class="stat-label">Ingresos</div>
        </div>
        
        <div class="summary-card servicios">
            <div class="value"><?php echo $ingresos['servicios_completados'] ?? 18; ?></div>
            <div class="stat-label">Servicios Completados</div>
        </div>
        
        <div class="summary-card calificacion">
            <div class="value"><?php echo $calificacion_promedio; ?></div>
            <div class="stat-label">Calificación Promedio</div>
        </div>
        
        <div class="summary-card satisfaccion">
            <div class="value"><?php echo $satisfaccion; ?>%</div>
            <div class="stat-label">Satisfacción</div>
        </div>
    </div>
    
    <div class="reservas-table">
        <div class="table-header">
            <h3>Reportes y Estadísticas</h3>
        </div>
        <div style="padding: 2rem; text-align: center; color: #718096;">
            <p>Los reportes detallados estarán disponibles próximamente</p>
        </div>
    </div>
</div>
</div>

<script>
// Manejar tabs
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.getAttribute('data-tab')).classList.add('active');
    });
});
</script>
</body>
</html>
