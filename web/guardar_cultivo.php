<?php
session_start();
require_once("../config/conexion.php");

// Protección: solo usuarios logueados
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$nombre_cultivo = $_POST['nombre_cultivo'];
$tipo_cultivo   = $_POST['tipo_cultivo'];
$ubicacion      = $_POST['ubicacion'];
$area_m2        = $_POST['area_m2'];
$fecha_siembra  = $_POST['fecha_siembra'];

$sql = "INSERT INTO cultivos
(id_usuario, nombre_cultivo, tipo_cultivo, ubicacion, area_m2, fecha_siembra)
VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param(
    "isssds",
    $id_usuario,
    $nombre_cultivo,
    $tipo_cultivo,
    $ubicacion,
    $area_m2,
    $fecha_siembra
);

$stmt->execute();

echo "Cultivo registrado correctamente";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <a href="agregar_cultivo.html">Registrar otro cultivo</a>
    <a href="dashboard.php">Regresar al dashboard</a>
</body>
</html>