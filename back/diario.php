<?php
session_start();
require 'conection.php'; 

if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado, inicia sesión.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entrada = trim($_POST["entrada"] ?? '');

    $userId = $_SESSION["user_id"];
    $campaignId = $_SESSION["campaign_id"]; 

    if (empty($entrada)) {
        die("El contenido de la entrada no puede estar vacío.");
    }

    try {
        $insert = $dbConection->prepare("INSERT INTO campaign_diary (campaign_id, author_id, title, content, created_at) 
                                         VALUES (:campaignId, :authorId, '', :content, NOW())");
        $insert->execute([
            ':campaignId' => $campaignId,
            ':authorId' => $userId,
            ':content' => $entrada
        ]);

        echo "Entrada añadida correctamente.";
    } catch (PDOException $e) {
        echo "Error al guardar la entrada: " . $e->getMessage();
    }
} else {
    echo "Acceso denegado.";
}
?>
