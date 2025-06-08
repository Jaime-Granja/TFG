<?php
session_start();
require 'conection.php';

$userId = $_SESSION['user_id'];
echo "Usuario ID: $userId";
$characterId = $_POST['character_id'];
echo "Personaje ID: $characterId";
$campaignId = $_POST['campaign_id'];
echo "Campaña ID: $campaignId";
// Validar que la ficha pertenece al usuario
$select = $dbConection->prepare("SELECT * FROM Characters WHERE character_id = :character_id AND character_owner = :character_owner");
$select->execute([
    ':character_id' => $characterId, 
    ':character_owner' => $userId
]);

if ($select->rowCount() === 0) {
    die("Ficha no válida.");
}

// Actualizar Users_Campaigns_Characters
$update = $dbConection->prepare("UPDATE Users_Campaigns_Characters 
                       SET character_id = :character_id 
                       WHERE user_id = :user_id AND campaign_id = :campaign_id");
$update->execute([
    ':character_id' => $characterId,
    ':user_id' => $userId,
    ':campaign_id' => $campaignId
]);

header("Location: ../front/campaign.php?id=$campaignId");
exit;