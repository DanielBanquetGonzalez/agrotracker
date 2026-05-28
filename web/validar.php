<?php
session_start();
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'agrotracker');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    mysqli_set_charset($conexion, "utf8mb4");
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Error de conexión al servidor de base de datos.");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['correo']) || !isset($_POST['contrasena']) || trim($_POST['correo']) === '' || trim($_POST['contrasena']) === '') {
        die("Error: Correo y contraseña son campos obligatorios.");
    }
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die("Error: El formato del correo electrónico no es válido.");
    }
    try {
        $sql = "SELECT u.id_usuario, u.contraseña, p.nombres, p.apellidos 
                FROM usuarios u 
                LEFT JOIN perfiles_usuario p ON u.id_usuario = p.id_usuario 
                WHERE u.correo = ? LIMIT 1";
        
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "s", $correo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $id_usuario, $passwordHash, $nombres, $apellidos);
            mysqli_stmt_fetch($stmt);
            if (!empty($passwordHash) && password_verify($contrasena, $passwordHash)) {
                $_SESSION['id_usuario'] = $id_usuario;
                $_SESSION['correo'] = $correo;
                $_SESSION['nombres'] = $nombres;
                $_SESSION['apellidos'] = $apellidos;
                mysqli_stmt_close($stmt);
                mysqli_close($conexion);
                header("Location: dashboard.php");
                exit;
            }
        }
        
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        
        die("Error: Correo o contraseña incorrectos.");
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        die("Error al procesar el inicio de sesión.");
    }
} else {
    echo "Método de solicitud no permitido.";
}
mysqli_close($conexion);
?>