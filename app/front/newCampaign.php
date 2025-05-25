<?php
session_start();
require '../back/conection.php';

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


if (isset($_POST['createCampaign'])) {
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
}

if (isset($_POST['joinCampaign'])) {
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
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <title>Nueva Campaña</title>
    <link rel="stylesheet" href="../src/styles/stylesNewCampaign.css" />
    <script src="../src/scripts/newCampaign.js"></script>
    <link rel="shortcut icon" href="../src/img/D20.png" />
  </head>
  <body>
    <div id="contenedor">
      <h2>¿Quieres crear una campaña nueva o unirte a una existente?</h2>
      <div class="botones">
        <button id="create">Crear Campaña Nueva</button>
        <button id="join">Unirme a una Campaña</button>
      </div>
      <div id="createCampaign">
        <form  method="POST">
          <label for="campaignName">Indique el nombre de la campaña:</label>
          <input type="text" name="campaignName" placeholder="Nombre de la Campaña" required />

          <label for="description">Descripción:</label>
          <textarea name="description" placeholder="Breve descripción de la trama, temas o ambientación." required></textarea>

          <button type="submit" name="createCampaign">Crear Campaña</button>
        </form>
      </div>
      <div id="joinCampaign">
        <form method="POST">
          <label for="inviteCode">Introduzca el código generado por su Director de Juego:</label>
          <input type="text" name="inviteCode" placeholder="Código de invitación" required />
          <button type="submit" name="joinCampaign">Enviar</button>
        </form>
      </div>
    </div>
    <button id="goBackButton">Volver</button>
  </body>
</html>
