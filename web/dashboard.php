<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - AgroTracker</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <header class="dashboard-header">
        <h1>Bienvenid@ 
        
    </header><a href="logout.php" class="btn logout">Cerrar sesión</a>

    <main class="dashboard-main">
        <p>Panel principal de AgroTracker 🌱</p>

        <ul class="dashboard-menu">
            <li><a href="agregar_cultivo.php">Registrar cultivo</a></li>
            <li><a href="cultivos.php">Mis cultivos</a></li>
            <li><a href="actividades.php">Mis actividades</a></li>
            <li><a href="interpretacion.php">Interpretacion de analisis de suelos</a></li>
        </ul>
    </main>
</body>
</html>


