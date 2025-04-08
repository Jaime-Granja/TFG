<?php 
session_start();
require 'conection.php';

if( !isset($_SESSION["user_id"])){
die("acceso denegado, inicia sesion");
}  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campaignName = trim($_POST["campaignName"]);
    $description = trim($_POST["description"]);
    $userId = $_SESSION["user_id"];

  
    if (empty($campaignName) || empty($description)) {
        die("Todos los campos son obligatorios.");
    }

    try {
        // Obtener la campaña que el usuario ha creado
        $select = $dbConection->prepare("SELECT campaign_id FROM Campaigns WHERE creator_id = :userId");
        $select->execute([':userId' => $userId]);
        $campaign = $select->fetch(PDO::FETCH_ASSOC);

        if ($campaign) {
            $campaignId = $campaign["campaign_id"];

            // Actualizar la campaña
            $update = $dbConection->prepare("UPDATE Campaigns SET campaign_name = :campaignName, campaign_desc = :description WHERE campaign_id = :campaignId");
            $update->execute([
                ':campaignName' => $campaignName,
                ':description' => $description,
                ':campaignId' => $campaignId
            ]);

            echo "Campaña actualizada con éxito.";
        } else {
            echo "No tienes campañas creadas.";
        }
    } catch (PDOException $e) {
        echo "Error al editar la campaña: " . $e->getMessage();
    }
} else {
    echo "Acceso denegado.";
}
?>