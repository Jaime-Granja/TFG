<?php
session_start();
require '../back/conection.php';

// Aquí comprobamos que el usuario tenga la sesión iniciada, por lo que pueda pasar...
if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}
$userId = $_SESSION["user_id"];
$campaignId = isset($_GET['id']) ? intval($_GET['id']) : null;
$message = "";

//Con este código determninamos si el acceso es desde newCampaign o no
$isFromNewCampaign = false;

if (!empty($_SESSION['fromNewCampaign'])) {
    $isFromNewCampaign = true;
    unset($_SESSION['fromNewCampaign']);
}
$alreadyJoined = false;

if (!empty($_SESSION['alreadyInCampaign'])) {
    $alreadyJoined = true;
    unset($_SESSION['alreadyInCampaign']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['campaignName'], $_POST['description'])) {
    $newName = trim($_POST['campaignName']);
    $newDesc = trim($_POST['description']);

    if ($newName === "" || $newDesc === "") {
        $_SESSION['message'] = "Por favor, completa todos los campos.";
        $_SESSION['messageType'] = "error";
    } else {
        try {
            $update = $dbConection->prepare("UPDATE Campaigns SET campaign_name = :name, campaign_desc = :desc WHERE campaign_id = :id");
            $update->bindParam(':name', $newName, PDO::PARAM_STR);
            $update->bindParam(':desc', $newDesc, PDO::PARAM_STR);
            $update->bindParam(':id', $campaignId, PDO::PARAM_INT);
            $update->execute();

            // Recarga los datos actualizados
            $select = $dbConection->prepare("SELECT campaign_name, campaign_desc FROM Campaigns WHERE campaign_id = :campaign_id");
            $select->bindParam(':campaign_id', $campaignId, PDO::PARAM_INT);
            $select->execute();
            $campaign = $select->fetch(PDO::FETCH_ASSOC);

            $_SESSION['message'] = "Campaña actualizada correctamente.";
            $_SESSION['messageType'] = "success";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error al actualizar la campaña: " . $e->getMessage();
            $_SESSION['messageType'] = "error";
        }
    }
}

// Añadir entrada al diario
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['entrada'])) {
    $entrada = trim($_POST['entrada']);
    $authorId = $_SESSION["user_id"];
    if ($campaignId === null || empty($entrada)) {
        $diaryMessage = "La entrada no puede estar vacía.";
    } else {
        try {
            $insert = $dbConection->prepare("INSERT INTO campaign_diary (campaign_id, author_id, title, content, created_at) 
                                             VALUES (:campaignId, :authorId, '', :content, NOW())");
           $insert->execute([
            ':campaignId' => $campaignId,
            ':authorId' => $authorId,
            ':content' => $entrada
            ]);

        // guardamos el mensaje en sesion
        $_SESSION['diaryMessage'] = "Entrada guardada con éxito.";

        // redirigimos para evitar reenvio duplicado al refrescar
         header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } catch (PDOException $e) {
            $diaryMessage = "Error al guardar entrada: " . $e->getMessage();
        }
    }
}

if (isset($_POST['campaignDelete'])) {
    try {
        // Borrar la relacion del usuario con la campaña
        $deleteUsers = $dbConection->prepare("DELETE FROM Users_Campaigns WHERE campaign_id = :campaignId");
        $deleteUsers->execute([':campaignId' => $campaignId]);
        // Borrar la campaña
        $deleteCampaign = $dbConection->prepare("DELETE FROM Campaigns WHERE campaign_id = :campaignId");
        $deleteCampaign->execute([':campaignId' => $campaignId]);
        setcookie("deletedCampaignMessage", "Campaña Eliminada Correctamente", time() + 5, "/");
        header("Location: home.php");

    } catch (PDOException $e) {
        echo "Error al eliminar la campaña: " . $e->getMessage();
    }
}



try {
    //Con esta select sacamos la información de la campaña
    $select = $dbConection->prepare("SELECT campaign_name, campaign_desc FROM Campaigns WHERE campaign_id = :campaign_id");
    $select->bindParam(':campaign_id', $campaignId, PDO::PARAM_INT);
    $select->execute();
    $campaign = $select->fetch(PDO::FETCH_ASSOC);
    //Con esta select sacamos los usuarios que están registrados en la campaña
    $selectPlayers = $dbConection->prepare("SELECT U.user_id, U.username, UC.role FROM Users U JOIN Users_Campaigns UC ON U.user_id = UC.user_id WHERE UC.campaign_id = :campaign_id");
    $selectPlayers->bindParam(':campaign_id', $campaignId, PDO::PARAM_INT);
    $selectPlayers->execute();
    $players = $selectPlayers->fetchAll(PDO::FETCH_ASSOC);
    //Ahora queremos sacar quién es el usuario loggeado.
    $getUserData = $dbConection->prepare("SELECT U.username, UC.role FROM Users U JOIN Users_Campaigns UC ON U.user_id = UC.user_id WHERE U.user_id = :user_id AND UC.campaign_id = :campaign_id");
    $getUserData->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $getUserData->bindParam(':campaign_id', $campaignId, PDO::PARAM_INT);
    $getUserData->execute();
    $loggedUserData = $getUserData->fetch(PDO::FETCH_ASSOC);

    //===== campaign Image =====
    if ($campaignId) {
        $select = $dbConection->prepare("SELECT campaign_pic FROM campaigns WHERE campaign_id = :id");
        $select->execute([':id' => $campaignId]);
        $result = $select->fetch(PDO::FETCH_ASSOC);

        if (!empty($result['campaign_pic'])) {
            // Elimina cualquier '../' inicial para evitar rutas incorrectas
            $relativePath = ltrim($result['campaign_pic'], '/');
            $absolutePath = realpath(__DIR__ . '/../' . $relativePath);

            if ($absolutePath && file_exists($absolutePath)) {
                $campaignPic = $result['campaign_pic'];
            }
        }
    }


    ?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>BDD-Campaing</title>
        <link rel="stylesheet" href="../src/styles/stylesCampaign.scss" />
        <script src="../src/scripts/campaign.js"></script>
        <link rel="shortcut icon" href="../src/img/logo.png" />
    </head>
    <!-- Comprobamos si el usuario viene de crear la campaña o unirse a ella por primera vez para generar el pop-up de bienvenida -->

    <body> <?php
    if ($isFromNewCampaign == true) {
        if ($loggedUserData['role'] == "Master") {
            ?>
                <div id="popup" class="popup">
                    Campaña Creada con Éxito
                </div> <?php
        } else if ($loggedUserData['role'] == "Player") {
            ?>
                    <div id="popup" class="popup">
                        Te has Unido a la Campaña con Éxito
                    </div> <?php
        }
    }
    if ($alreadyJoined == true) {
        ?>
            <div id="popup" class="popup">
                Ya Estabas Unido a Esta Campaña
            </div> <?php
    }
    // Intento de creación de pop-ups para Edición de Campañas
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $messageType = $_SESSION['messageType'] ?? 'info';
        unset($_SESSION['message'], $_SESSION['messageType']);
        if (!empty($message)): ?>
                <div id="popup" class="popup <?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif;
    }
    ?>
        <div id="contenedor">
            <!-- Al llegar a esta página, hay que revisar el id del usuario y así mostrarle su información-->
            <?php
            if ($campaign) {
                ?>
                <div campaignInfo>
                    <form id="deleteForm" method="POST"
                        onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta campaña? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="campaign_id" value="<?= $campaignId ?>">
                        <button type="submit" id="campaignDelete" name="campaignDelete">Eliminar Campaña</button>
                    </form>
                    <button id="campaignButton">Editar</button>
                    <h1 id="campaignName" class="title"><?php echo $campaign['campaign_name'] ?></h1>
                    <!-- $campaignName de la base de datos-->
                    <div>
                        <img id="campaignPic" src="<?= htmlspecialchars("../" . $campaignPic) ?>" alt="Profile picture" />
                    </div>
                    <div id="campaignDescription">
                        <h2>Pequeña Descripción de la Campaña </h2>
                        <!-- $description en la base de datos-->
                        <?php echo $campaign['campaign_desc'] ?>
                    </div>
                </div>
                <div id="contenido">
                    <div id="sheet">
                        <h2> <?php echo "Ficha de " . htmlspecialchars($loggedUserData['username']); ?></h2>
                        <button id="sheetButton">Editar</button><br />
                        <div id="sheetPage">Espacio para la Ficha</div>
                    </div>
                    <div id="journal">
                        <h2 id="journalTitle">Diario de Campaña</h2>
                        <button id="journalButton">Editar</button><br />
                        <div id="journalPage">
                            <?php if (isset($diaryMessage)) {
                                echo "<p style='color:green;font-weight:bold;'>$diaryMessage</p>";

                            } ?>

                            <form method="POST">
                                <label for="entrada">Nueva entrada al diario:</label><br />
                                <textarea name="entrada" required></textarea><br />
                                <button type="submit">Añadir entrada</button>
                            </form>
                            <hr />
                            <?php
                            try {
                                $selectEntries = $dbConection->prepare("SELECT title, content, created_at FROM campaign_diary WHERE campaign_id = :campaignId ORDER BY created_at DESC");
                                $selectEntries->execute([':campaignId' => $campaignId]);
                                $entries = $selectEntries->fetchAll(PDO::FETCH_ASSOC);

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
                                    echo "<p>No hay entradas aún.</p>";
                                }
                            } catch (PDOException $e) {
                                echo "<p>Error cargando entradas: " . $e->getMessage() . "</p>";
                            }
                            ?>
                        </div>
                    </div>
                    <div id="participants">
                        <h2 id="participantsTittle">Participantes</h2>
                        <?php
                        if ($players) {
                            foreach ($players as $player) {
                                $username = htmlspecialchars($player['username']);
                                $role = $player['role'];

                                echo "<div class='participant'>$username</div>";
                            }
                            echo "</div>";
                        } else {
                            echo "<p>Esta campaña todavía no tiene jugadores.</p>";
                        }

                        ?>
                    </div>

                    <div>
                        <form action="../back/uploadImage.php" method="POST" enctype="multipart/form-data">
                            <label for="campaign_photo">Subir imagen de personaje:</label><br>
                            <input type="file" name="campaign_photo" id="campaign_photo" accept="image/*" required>
                            <input type="hidden" name="campaign_id" value="<?= htmlspecialchars($campaignId) ?>">
                            <input type="submit" name="upload_campaign_photo" value="Subir imagen de personaje">
                        </form>
                    </div>

                    <div id="campaignForm">
                        <form method="POST" action="">
                            <label for="campaignName">Indique el nuevo nombre de la campaña:</label>
                            <input type="text" name="campaignName"
                                placeholder="<?php echo htmlspecialchars($campaign['campaign_name']); ?>" required />

                            <label for="description">Edite la descripción de la campaña:</label>
                            <textarea name="description"
                                placeholder="<?php echo htmlspecialchars($campaign['campaign_desc']); ?>" required></textarea>

                            <button type="submit">Editar Campaña</button>
                        </form>
                    </div>
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
            <?php
            } else {
                ?>
            <div>La campaña a la que intentas acceder no existe.</div><br>
            <button>Volver Atrás</button>
            <?php
            }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
</body>

</html>
