<?php
// Configuración de conexión a la base de datos para XAMPP
$servidor = "localhost";
$usuario = "root";
$password = ""; // En XAMPP por defecto no hay contraseña
$base_datos = "motobloock";

// Crear conexión usando MySQLi
$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Configurar charset para caracteres especiales
$conexion->set_charset("utf8");

// Función alternativa usando PDO (más moderna)
function conectarPDO() {
    $servidor = "localhost";
    $usuario = "root";
    $password = "";
    $base_datos = "motobloock";
    
    try {
        $pdo = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Error de conexión PDO: " . $e->getMessage());
    }
}

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Funciones de utilidad
function limpiar_datos($datos) {
    global $conexion;
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos);
    $datos = $conexion->real_escape_string($datos);
    return $datos;
}

function redireccionar($url) {
    header("Location: $url");
    exit();
}

function usuario_logueado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function es_admin() {
    return isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
}

function verificar_login() {
    if (!usuario_logueado()) {
        redireccionar('login.php');
    }
}

function verificar_admin() {
    if (!es_admin()) {
        redireccionar('index.php');
    }
}
?>