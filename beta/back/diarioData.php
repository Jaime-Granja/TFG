<?php
session_start();
require 'conection.php';

if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}

if (!isset($_SESSION["campaign_id"])) {
    die("No tienes ninguna campaña activa.");
}
//campaña actual en la que esta el usuario
$campaignId = $_SESSION["campaign_id"];
//visualizar entradas
try {
    $select = $dbConection->prepare("SELECT title, content, created_at FROM campaign_diary WHERE campaign_id = :campaignId ORDER BY created_at DESC");
    $select->execute([':campaignId' => $campaignId]);
    $entries = $select->fetchAll(PDO::FETCH_ASSOC);
  //devuelve los datos en Json
    header('Content-Type: application/json');
    echo json_encode($entries);
} catch (PDOException $e) {
    echo "Error al obtener entradas del diario: " . $e->getMessage();
}
?>
