<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'agrotracker');



// Enable error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    mysqli_set_charset($conexion, "utf8mb4");
} catch (Exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Error de conexión al servidor de base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['nombre_usuario', 'correo', 'contraseña', 'nombres', 'apellidos'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        die("Error: Faltan campos obligatorios para el registro.");
    }
    
    $nombre_usuario = trim($_POST['nombre_usuario']);
    $correo         = trim($_POST['correo']);
    $contraseña     = $_POST['contraseña'];
    $nombres        = trim($_POST['nombres']);
    $apellidos      = trim($_POST['apellidos']);
    $telefono       = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $departamento   = isset($_POST['departamento']) ? trim($_POST['departamento']) : null;
    $municipio      = isset($_POST['municipio']) ? trim($_POST['municipio']) : null;
    $direccion      = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;
    
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die("Error: El formato del correo electrónico no es válido.");
    }

    mysqli_begin_transaction($conexion);
    

    try {
    
        // A. RE-SECUENCIAR AUTOMÁTICAMENTE TODOS LOS IDs EXISTENTES
        // Esto elimina cualquier gap en los IDs (por ejemplo, si se eliminaron usuarios intermedios)
        // Como 'perfiles_usuario' tiene ON UPDATE CASCADE, sus IDs se actualizarán automáticamente.
        mysqli_query($conexion, "SET @count = 0");
        mysqli_query($conexion, "UPDATE usuarios SET id_usuario = (@count:= @count + 1) ORDER BY id_usuario ASC");
        mysqli_query($conexion, "ALTER TABLE usuarios AUTO_INCREMENT = 1");

         mysqli_query($conexion, "SET @count = 0");
        mysqli_query($conexion, "UPDATE perfiles_usuario SET id_perfil = (@count:= @count + 1) ORDER BY id_usuario ASC");
        mysqli_query($conexion, "ALTER TABLE perfiles_usuario AUTO_INCREMENT = 1");
        
        // B. Comprobar si el nombre de usuario o correo ya existen
         $sqlCheck = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? OR correo = ? LIMIT 1";
        $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, "ss", $nombre_usuario, $correo);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_store_result($stmtCheck);
        
        if (mysqli_stmt_num_rows($stmtCheck) > 0) {
            mysqli_stmt_close($stmtCheck);
            throw new Exception("El usuario o el correo electrónico ya están registrados.");
        }
        mysqli_stmt_close($stmtCheck);

        // Hash password safely
        $passwordHash = password_hash($contraseña, PASSWORD_DEFAULT);

        // Insert into usuarios
        
        $sqlUsuarios = "INSERT INTO usuarios (nombre_usuario, correo, contraseña) VALUES (?, ?, ?)";
        $stmtUsuarios = mysqli_prepare($conexion, $sqlUsuarios);
        mysqli_stmt_bind_param($stmtUsuarios, "sss", $nombre_usuario, $correo, $passwordHash);
        mysqli_stmt_execute($stmtUsuarios);
        
        $id_usuario = mysqli_insert_id($conexion);
        mysqli_stmt_close($stmtUsuarios);

        // Insert into perfiles_usuario
        $sqlPerfil = "INSERT INTO perfiles_usuario (id_usuario, nombres, apellidos, telefono, departamento, municipio, direccion) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtPerfil = mysqli_prepare($conexion, $sqlPerfil);
        mysqli_stmt_bind_param($stmtPerfil, "issssss", $id_usuario, $nombres, $apellidos, $telefono, $departamento, $municipio, $direccion);
        mysqli_stmt_execute($stmtPerfil);
        mysqli_stmt_close($stmtPerfil);

        mysqli_commit($conexion);
        echo "Usuario y perfil registrados correctamente.";

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        error_log("Registration error: " . $e->getMessage());
        echo "Error al registrar el usuario: " . $e->getMessage();
    }
} 
else {
    echo "Método de solicitud no permitido.";
}

mysqli_close($conexion);
?>