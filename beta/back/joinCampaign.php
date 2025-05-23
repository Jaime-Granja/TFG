<?php
session_start();
require 'conection.php'; 

// Aquí comprobamos que el usuario tenga la sesión iniciada, por lo que pueda pasar...
if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inviteCode = trim($_POST["inviteCode"]);
    $userId = $_SESSION["user_id"];

    /* 
    Aquí lo que hacemos es comprobar que exista una campaña con el código de invitación que está poniendo el usuario y a la vez
    cogemos el id de la campaña para usarlo después
    */
    $select = $dbConection->prepare("SELECT campaign_id FROM Campaigns WHERE invite_code = :inviteCode");
    $select->execute([':inviteCode' => $inviteCode]);
    $campaign = $select->fetch(PDO::FETCH_ASSOC);

    if ($campaign) {
        $campaignId = $campaign["campaign_id"];

        // Insertamos los datos en la tabla usuarios_campañas para añadir al usuario a la campaña
        $insert = $dbConection->prepare("INSERT INTO Users_Campaigns (user_id, campaign_id, role) VALUES (:userId, :campaignId, 'Player')");
        $insert->execute([
            ':userId' => $userId,
            ':campaignId' => $campaignId
        ]);

        echo "Te has unido a la campaña.";
    } else {
        echo "Código de invitación incorrecto.";
    }
} else {
    echo "Acceso denegado. Tira pa casa pringao.";
}
?>