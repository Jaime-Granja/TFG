<?php
session_start();
require 'conection.php'; 

if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}

$userId = $_SESSION["user_id"];
$message = "";

// --- Eliminar campaña ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_campaign'])) {
    try {
        $select = $dbConection->prepare("SELECT campaign_id FROM Campaigns WHERE creator_id = :userId");
        $select->execute([':userId' => $userId]);
        $campaign = $select->fetch(PDO::FETCH_ASSOC);

        if ($campaign) {
            $campaignId = $campaign["campaign_id"];
            $deleteUsers = $dbConection->prepare("DELETE FROM Users_Campaigns WHERE campaign_id = :campaignId");
            $deleteUsers->execute([':campaignId' => $campaignId]);

            $deleteCampaign = $dbConection->prepare("DELETE FROM Campaigns WHERE campaign_id = :campaignId");
            $deleteCampaign->execute([':campaignId' => $campaignId]);

            $message = "Campaña eliminada con éxito.";
        } else {
            $message = "No tienes campañas creadas.";
        }
    } catch (PDOException $e) {
        $message = "Error al eliminar la campaña: " . $e->getMessage();
    }
}

// --- Editar campaña ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_campaign'])) {
    $campaignName = trim($_POST["campaignName"]);
    $description = trim($_POST["description"]);

    if (empty($campaignName) || empty($description)) {
        $message = "Todos los campos son obligatorios.";
    } else {
        try {
            $select = $dbConection->prepare("SELECT campaign_id FROM Campaigns WHERE creator_id = :userId");
            $select->execute([':userId' => $userId]);
            $campaign = $select->fetch(PDO::FETCH_ASSOC);

            if ($campaign) {
                $campaignId = $campaign["campaign_id"];

                $update = $dbConection->prepare("UPDATE Campaigns SET campaign_name = :campaignName, campaign_desc = :description WHERE campaign_id = :campaignId");
                $update->execute([
                    ':campaignName' => $campaignName,
                    ':description' => $description,
                    ':campaignId' => $campaignId
                ]);

                $message = "Campaña actualizada con éxito.";
            } else {
                $message = "No tienes campañas creadas.";
            }
        } catch (PDOException $e) {
            $message = "Error al editar la campaña: " . $e->getMessage();
        }
    }
}

// --- Insertar nueva entrada en el diario ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['entrada'])) {
    $entrada = trim($_POST['entrada']);
    $authorId = $_SESSION["user_id"];
    $campaignId = $_SESSION["campaign_id"];

    if (empty($entrada)) {
        die("La entrada no puede estar vacía.");
    }

    // Insertamos la nueva entrada en la base de datos
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
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Campaign</title>
  <link rel="stylesheet" href="src/styles/stylesCampaign.css" />
  <script src="src/scripts/campaign.js"></script>
  <link rel="shortcut icon" href="src/img/D20.png" />
</head>

<body>
  <div id="contenedor">
    <?php if ($message): ?>
      <p style="color: green; font-weight: bold;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div campaignInfo>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="delete_campaign" value="1">
        <button type="submit" id="campaignDelete">Eliminar Campaña</button>
      </form>

      <button id="campaignButton" onclick="document.getElementById('campaignForm').style.display='flex'">
        Editar
      </button>

      <h1 id="campaignName" class="title">Nombre de la Campaña</h1>
      <div id="campaignDescription">
        <h2>Pequeña Descripción de la Campaña.</h2>
        Lorem ipsum dolor sit amet, consectetur adipiscing elit...
      </div>
    </div>

    <div id="contenido">
      <div id="sheet">
        <h2 id="sheetTitle">Ficha de X</h2>
        <button id="sheetButton">Editar</button><br />
        <div id="sheetPage">Espacio para la Ficha</div>
      </div>

      <div id="journal">
        <h2 id="journalTitle">Diario de Campaña</h2>
        <button id="journalButton" onclick="document.getElementById('newEntryForm').style.display='block'">Añadir Nueva Entrada</button><br />

        <!-- Formulario para añadir una nueva entrada -->
        <div id="newEntryForm" style="display:none;">
          <form method="POST" action="campaign.php">
            <label for="entrada">Nueva entrada al diario:</label><br />
            <textarea name="entrada" required></textarea><br />
            <button type="submit">Añadir entrada</button>
          </form>
        </div>

        <div id="journalPage">
          <?php
          try {
              // Obtener las entradas desde la base de datos
              $select = $dbConection->prepare("SELECT title, content, created_at FROM campaign_diary WHERE campaign_id = :campaignId ORDER BY created_at DESC");
              $select->execute([':campaignId' => $_SESSION["campaign_id"]]);
              $entries = $select->fetchAll(PDO::FETCH_ASSOC);

              // Mostrar las entradas
              if ($entries) {
                  echo "<h3>Entradas del Diario:</h3>";
                  foreach ($entries as $entry) {
                      echo "<div class='entry'>";
                      echo "<h3>" . htmlspecialchars($entry['title']) . "</h3>";
                      echo "<p>" . nl2br(htmlspecialchars($entry['content'])) . "</p>";
                      echo "<small>" . $entry['created_at'] . "</small>";
                      echo "</div><br />";
                  }
              } else {
                  echo "<p>No hay entradas en el diario aún.</p>";
              }
          } catch (PDOException $e) {
              echo "Error al obtener entradas del diario: " . $e->getMessage();
          }
          ?>
        </div>
      </div>

      <div id="participants">
        <h2 id="participantsTittle">Participantes</h2>
        <div class="participant"><img class="profilePic" src="../src/img/user.png" />Jorge</div>
        <div class="participant"><img class="profilePic" src="../src/img/user.png" />Jaime</div>
        <div class="participant"><img class="profilePic" src="../src/img/user.png" />Alex</div>
        <div class="participant"><img class="profilePic" src="../src/img/user.png" />Victor</div>
        <div class="participant"><img class="profilePic" src="../src/img/user.png" />Brutalitops</div>
      </div>
    </div>

    <div id="campaignForm" style="display:none;">
      <form method="POST">
        <input type="hidden" name="edit_campaign" value="1" />
        <label for="campaignName">Indique el nuevo nombre de la campaña:</label>
        <input type="text" name="campaignName" placeholder="" required />

        <label for="description">Edite la descripción de la campaña:</label>
        <textarea name="description" placeholder="" required></textarea>

        <button type="submit">Editar Campaña</button>
      </form>
    </div>
  </div>

  <div id="margin">
    <img id="menuHamburguesa" src="../src/img/menu.png" />
    <div id="menuHamburguesaBotones">
      <button id="userProfile">Perfil de Usuario</button>
      <button id="goBack">Retroceder</button>
      <button id="logOut">Cerrar Sesión</button>
    </div>
  </div>
</body>
</html>
