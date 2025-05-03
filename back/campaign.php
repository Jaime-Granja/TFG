<?php
session_start();
require 'conection.php';

// Aquí comprobamos que el usuario tenga la sesión iniciada, por lo que pueda pasar...
if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}
$userId = $_SESSION["user_id"];
$campaignId = isset($_GET['id']) ? intval($_GET['id']) : null;

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
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Document</title>
        <link rel="stylesheet" href="../front/src/styles/stylesCampaign.css" />
        <script src="../front/src/scripts/campaign.js"></script>
        <link rel="shortcut icon" href="../front/src/img/D20.png" />
    </head>

    <body>
        <div id="contenedor">
            <!-- Al llegar a esta página, hay que revisar el id del usuario y así mostrarle su información-->
            <?php
            if ($campaign) {
                ?>
                <div campaignInfo>
                    <button id="campaignDelete">Eliminar Campaña</button>
                    <button id="campaignButton">Editar</button>
                    <h1 id="campaignName" class="title"><?php echo $campaign['campaign_name'] ?></h1>
                    <!-- $campaignName de la base de datos-->
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
                        <div id="journalPage">Espacio para Diario de Campaña</div>
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
                    <div id="campaignForm">
                        <form method="POST" action=""> <!--Aquí tiene que ir el php para editar campañas-->
                            <label for="campaignName">Indique el nuevo nombre de la campaña:</label>
                            <input type="text" name="campaignName" placeholder="<?php echo $campaign['campaign_name'] ?> "
                                required />

                            <label for="description">Edite la descripción de la campaña:</label>
                            <textarea name="description" placeholder="<?php echo $campaign['campaign_desc'] ?>"
                                required></textarea>


                            <button type="submit">Editar Campaña</button>
                        </form>
                    </div>
                </div>

            </div>
            <div id="margin">
                <img id="menuHamburguesa" src="../front/src/img/menu.png" />
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