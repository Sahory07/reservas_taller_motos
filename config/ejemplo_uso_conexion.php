<?php
// Ejemplo de cómo usar el archivo conexion.php

// Incluir el archivo de conexión
require_once 'config/conexion.php';

// Ejemplo 1: Consulta simple con MySQLi
function obtener_usuarios() {
    global $conexion;
    
    $sql = "SELECT * FROM usuarios WHERE activo = 1";
    $resultado = $conexion->query($sql);
    
    if ($resultado->num_rows > 0) {
        $usuarios = [];
        while($fila = $resultado->fetch_assoc()) {
            $usuarios[] = $fila;
        }
        return $usuarios;
    }
    return [];
}

// Ejemplo 2: Insertar datos de forma segura
function crear_usuario($nombre, $apellido, $email, $password) {
    global $conexion;
    
    // Limpiar los datos de entrada
    $nombre = limpiar_datos($nombre);
    $apellido = limpiar_datos($apellido);
    $email = limpiar_datos($email);
    
    // Preparar la consulta (prepared statement para evitar SQL injection)
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, email, password, tipo_usuario) VALUES (?, ?, ?, ?, 'cliente')");
    $password_hash = md5($password);
    $stmt->bind_param("ssss", $nombre, $apellido, $email, $password_hash);
    
    if ($stmt->execute()) {
        return $conexion->insert_id;
    } else {
        return false;
    }
}

// Ejemplo 3: Actualizar datos
function actualizar_estado_reserva($reserva_id, $nuevo_estado) {
    global $conexion;
    
    $reserva_id = limpiar_datos($reserva_id);
    $nuevo_estado = limpiar_datos($nuevo_estado);
    
    $stmt = $conexion->prepare("UPDATE reservas SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_estado, $reserva_id);
    
    return $stmt->execute();
}

// Ejemplo 4: Login de usuario
function verificar_login($email, $password, $tipo_usuario) {
    global $conexion;
    
    $email = limpiar_datos($email);
    $tipo_usuario = limpiar_datos($tipo_usuario);
    $password_hash = md5($password);
    
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ? AND password = ? AND tipo_usuario = ? AND activo = 1");
    $stmt->bind_param("sss", $email, $password_hash, $tipo_usuario);
    $stmt->execute();
    
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows == 1) {
        return $resultado->fetch_assoc();
    }
    return false;
}

// Ejemplo 5: Obtener reservas con JOIN
function obtener_reservas_cliente($cliente_id) {
    global $conexion;
    
    $sql = "SELECT r.*, s.nombre as servicio_nombre, s.precio, m.nombre as mecanico_nombre 
            FROM reservas r 
            LEFT JOIN servicios s ON r.servicio_id = s.id 
            LEFT JOIN mecanicos m ON r.mecanico_id = m.id 
            WHERE r.cliente_id = ? 
            ORDER BY r.fecha_reserva DESC";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    
    $resultado = $stmt->get_result();
    
    $reservas = [];
    while($fila = $resultado->fetch_assoc()) {
        $reservas[] = $fila;
    }
    
    return $reservas;
}

// Ejemplo 6: Usando PDO (alternativa más moderna)
function obtener_servicios_pdo() {
    $pdo = conectarPDO();
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
        return [];
    }
}

// Función para cerrar la conexión (opcional, PHP lo hace automáticamente)
function cerrar_conexion() {
    global $conexion;
    $conexion->close();
}
?>