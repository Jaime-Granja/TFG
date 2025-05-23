<?php
session_start();
require 'conection.php'; 

// Aquí comprobamos que el usuario tenga la sesión iniciada, por lo que pueda pasar...
if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}

// Función para generar un código de invitación
function generateInviteCode($creatorId) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $random_part = substr(str_shuffle($characters), 0, 8);
    $code = $random_part . $creatorId;
    return str_shuffle($code); 
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campaignName = trim($_POST["campaignName"]);
    $description = trim($_POST["description"]);
    $creatorId = $_SESSION["user_id"]; 
    $inviteCode = generateInviteCode($creatorId);
    
    // Validamos los datos, por si acaso 
    if (empty($campaignName) || empty($description)) {
        die("Todos los campos son obligatorios.");
    }

    // Insertamos la campaña en la bbdd y además añadimos al creador a la tabla de usuarios_campañas
    try {
        $insert = $dbConection->prepare("INSERT INTO Campaigns (campaign_name, campaign_desc, creator_id, invite_code) VALUES (:campaignName, :description, :creatorId, :inviteCode)");
        $insert->execute([
            ':campaignName' => $campaignName,
            ':description' => $description,
            ':creatorId' => $creatorId,
            ':inviteCode' => $inviteCode
        ]);

        echo "Campaña creada con éxito. Código de invitación: " . $inviteCode;

        // El resto de código hasta el catch es para añadir al creador de la campaña a la tabla de usuarios_campañas
        $select = $dbConection->prepare("SELECT campaign_id FROM Campaigns WHERE invite_code = :inviteCode");
        $select->execute([':inviteCode' => $inviteCode]);
        $campaign = $select->fetch(PDO::FETCH_ASSOC);

        if ($campaign) {
            $campaignId = $campaign["campaign_id"];
    
            // Insertamos los datos en la tabla usuarios_campañas para añadir al usuario a la campaña
            $insert = $dbConection->prepare("INSERT INTO Users_Campaigns (user_id, campaign_id, role) VALUES (:creatorId, :campaignId, 'Master')");
            $insert->execute([
                ':creatorId' => $creatorId,
                ':campaignId' => $campaignId
            ]);
    
            echo "Te has unido a la campaña.";
        }
    } catch (PDOException $e) {
        echo "Error al crear campaña: " . $e->getMessage();
    }
} else {
    echo "Acceso denegado. Tira pa casa pringao.";
}
?>
