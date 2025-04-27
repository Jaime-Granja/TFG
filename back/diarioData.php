<?php
session_start();
require 'conection.php';

if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado, inicia sesión.");
}

if (!isset($_SESSION["campaign_id"])) {
    die("Acceso denegado, no tienes una campaña activa.");
}

$campaignId = $_SESSION["campaign_id"];

try {
    $select = $dbConection->prepare("SELECT title, content, created_at 
                                     FROM campaign_diary 
                                     WHERE campaign_id = :campaignId 
                                     ORDER BY created_at DESC");
    $select->execute([
        ':campaignId' => $campaignId
    ]);

    $entradas = $select->fetchAll(PDO::FETCH_ASSOC);

    foreach ($entradas as $entrada) {
        echo "<div class='diary-entry'>";
        echo "<h3>" . htmlspecialchars($entrada["title"]) . "</h3>"; 
        echo "<p>" . nl2br(htmlspecialchars($entrada["content"])) . "</p>";
        echo "<small>Publicado el: " . htmlspecialchars($entrada["created_at"]) . "</small>";
        echo "</div><hr>";
    }
} catch (PDOException $e) {
    echo "Error al cargar las entradas: " . $e->getMessage();
}
?>
