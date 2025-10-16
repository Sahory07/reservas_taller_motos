<?php
require_once 'config/database.php';

// Destruir la sesión
session_destroy();

// Redirigir a la página principal
redirect('index.php');
?>