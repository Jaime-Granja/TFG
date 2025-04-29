<?php
session_start();
require 'conection.php'; 

if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}

if (!isset($_SESSION["campaign_id"])) {
    die("No tienes una campaña activa.");
}
//hacemos la entrada y validamos que no sea una vacia
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entrada = trim($_POST["entrada"]);
    $authorId = $_SESSION["user_id"];
    $campaignId = $_SESSION["campaign_id"];

    if (empty($entrada)) {
        die("La entrada no puede estar vacía.");
    }
 //la insertamos en campaign_diary
    try {
        $insert = $dbConection->prepare("INSERT INTO campaign_diary (campaign_id, author_id, title, content, created_at) 
                                         VALUES (:campaignId, :authorId, '', :content, NOW())");
        $insert->execute([
            ':campaignId' => $campaignId,
            ':authorId' => $authorId,
            ':content' => $entrada
        ]);

        echo "Entrada guardada con éxito.";
    } catch (PDOException $e) {
        echo "Error al guardar la entrada: " . $e->getMessage();
    }
} else {
    echo "Acceso denegado.";
}
?>
