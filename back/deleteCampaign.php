<?php 
session_start();
require 'conection.php';

if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}

$userId = $_SESSION["user_id"];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Obtener la campaña que el usuario ha creado
        $select = $dbConection->prepare("SELECT campaign_id FROM Campaigns WHERE creator_id = :userId");
        $select->execute([':userId' => $userId]);
        $campaign = $select->fetch(PDO::FETCH_ASSOC);
    
        if ($campaign) {
            $campaignId = $campaign["campaign_id"];
    
            // Borrar la relacion del usuario con la campaña
            $deleteUsers = $dbConection->prepare("DELETE FROM Users_Campaigns WHERE campaign_id = :campaignId");
            $deleteUsers->execute([':campaignId' => $campaignId]);
    
            // Borrar la campaña
            $deleteCampaign = $dbConection->prepare("DELETE FROM Campaigns WHERE campaign_id = :campaignId");
            $deleteCampaign->execute([':campaignId' => $campaignId]);
    
            echo "Campaña eliminada con éxito.";
        } else {
            echo "No tienes campañas creadas.";
        }
    } catch (PDOException $e) {
        echo "Error al eliminar la campaña: " . $e->getMessage();
    }
}

?>