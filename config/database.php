<?php
// ==============================
// CONFIGURACIÓN DE LA BASE DE DATOS
// ==============================

// Datos de conexión para XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Por defecto en XAMPP no hay contraseña
define('DB_NAME', 'motobloock');

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh;
    private $stmt;
    private $error;

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = array(
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        );

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            error_log('Error de conexión: ' . $this->error);
            die('Error de conexión a la base de datos. Contacte al administrador.');
        }
    }

    // Preparar consulta
    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
        return $this;
    }

    // Vincular parámetros
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            if (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $type = PDO::PARAM_NULL;
            } else {
                $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    // Ejecutar consulta
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch(PDOException $e) {
            error_log('Error en ejecución: ' . $e->getMessage());
            throw $e;
        }
    }

    // Obtener múltiples registros
    public function resultset() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un solo registro
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Contar filas afectadas
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Último ID insertado
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    // Cerrar conexión
    public function closeConnection() {
        $this->dbh = null;
        $this->stmt = null;
    }

    // Transacciones
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    public function commit() {
        return $this->dbh->commit();
    }

    public function rollback() {
        return $this->dbh->rollback();
    }
}

// Función para crear instancia de DB
function conectarDB() {
    return new Database();
}

// ==============================
// CONFIGURACIÓN DE SESIONES
// ==============================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_start();
}

// ==============================
// FUNCIONES DE UTILIDAD
// ==============================

// Sanitizar entrada
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Redirección segura
function redirect($url) {
    if (filter_var($url, FILTER_VALIDATE_URL) === false && !preg_match('/^[a-zA-Z0-9_\-\/\.]+\.php(\?.*)?$/', $url)) {
        $url = 'index.php';
    }
    header("Location: " . $url);
    exit();
}

// Validaciones de sesión
function isLoggedIn() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function isAdmin() {
    return isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('index.php');
    }
}

// Seguridad de sesión
function regenerateSession() {
    session_regenerate_id(true);
}

function destroySession() {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// ==============================
// CONFIGURACIÓN ADICIONAL
// ==============================

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Manejo de errores según entorno
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

?>
