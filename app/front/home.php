<?php
session_start();
require '../back/conection.php';
$userId = $_SESSION["user_id"];
$selectUser = $dbConection->prepare("SELECT username FROM users WHERE user_id = :userId");
$selectUser->bindParam(':userId', $userId, PDO::PARAM_INT);
$selectUser->execute();
$user = $selectUser->fetch(PDO::FETCH_ASSOC);
$selectCampaigns = $dbConection->prepare("SELECT c.campaign_id, c.campaign_name, c.campaign_desc, c.campaign_pic FROM campaigns c JOIN users_campaigns_characters uc ON c.campaign_id = uc.campaign_id WHERE uc.user_id = :userId");
$selectCampaigns->bindParam(':userId', $userId, PDO::PARAM_INT);
$selectCampaigns->execute();
$campaigns = $selectCampaigns->fetchAll(PDO::FETCH_ASSOC);

$selectCharacters = $dbConection->prepare("SELECT character_id, character_name, character_desc, character_pic FROM characters WHERE character_owner = :userId");
$selectCharacters->bindParam(':userId', $userId, PDO::PARAM_INT);
$selectCharacters->execute();
$characters = $selectCharacters->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDD-Home</title>
    <!-- <link rel="stylesheet" href="../src/styles/styles.css"> -->
    <link rel="stylesheet" href="../src/styles/stylesHome.css">
    <script src="../src/scripts/home.js"></script>
    <link rel="shortcut icon" href="../src/img/logo.png" />
</head>

<body> <?php
if (isset($_COOKIE['logInMessage'])) {
    ?>
        <div id="popup" class="popup">
            Sesión Iniciada Correctamente
        </div> <?php
        setcookie("logInMessage", "", time() - 3600, "/");
} else if (isset($_COOKIE['registerMessage'])) {
    ?>
            <div id="popup" class="popup">
                Usuario Registrado Correctamente
            </div> <?php
            setcookie("registerMessage", "", time() - 3600, "/");
} else if (isset($_COOKIE['deletedCharMessage'])) {
    ?>
                <div id="popup" class="popup">
                    Personaje Eliminado Correctamente
                </div> <?php
                setcookie("deletedCharMessage", "", time() - 3600, "/");
} else if (isset($_COOKIE['deletedCampaignMessage'])) {
    ?>
                    <div id="popup" class="popup">
                        Campaña Eliminada Correctamente
                    </div>
            <?php
            setcookie("deletedCampaignMessage", "", time() - 3600, "/");
}
?>
    <div id="margin">
        <img id="menuHamburguesa" src="../src/img/menu.png" />
        <div id="menuHamburguesaBotones">
            <button id="userProfile">Perfil de Usuario</button>
            <button id="logOut">Cerrar Sesión</button>
        </div>
    </div>
    <div id="body">
        <h1>Bienvenido, <?php echo htmlspecialchars($user['username']); ?></h1>
        <h2>Campañas</h2>
        <div class="newCampaign">
            <button id="newCampaignButton">Nueva Campaña</button>
        </div>
        <div id="campaigns-folder">
            <div id="campaigns">
                <?php if (count($campaigns) === 0): ?>
                    <p>No estás en ninguna campaña todavía.</p>
                <?php else: ?>
                    <?php foreach ($campaigns as $campaign): ?>
                        <div class="campaign">
                            <h3><?php echo htmlspecialchars($campaign['campaign_name']); ?></h3><br>
                            <?php if (!empty($campaign['campaign_pic'])): ?>
                                <img src="<?php echo htmlspecialchars("../" . $campaign['campaign_pic']); ?>" alt="Imagen de campaña"
                                    class="campaign-img">
                            <?php endif; ?>
                            <?php echo nl2br(htmlspecialchars($campaign['campaign_desc'])); ?><br>
                            <button class="mas" data-campaign-id="<?php echo $campaign['campaign_id']; ?>">+</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
        <h2>Fichas</h2>
        <div class="newSheet">
            <button id="createSheet">Crear Ficha</button>
        </div>
        <div id="sheets">
            <?php if (count($characters) === 0): ?>
                <p>No tienes ninguna ficha de personaje todavía.</p>
            <?php else: ?>
                <?php foreach ($characters as $character): ?>
                    <div class="sheet">
                        <h3><?php echo htmlspecialchars($character['character_name']); ?></h3><br>
                        <?php if (!empty($character['character_pic'])): ?>
                            <img src="<?php echo htmlspecialchars("../" . $character['character_pic']); ?>" alt="Imagen del personaje"
                                class="character-img">
                        <?php endif; ?>
                        <?php echo nl2br(htmlspecialchars($character['character_desc'])); ?><br>
                        <button class="mas" data-character-id="<?php echo $character['character_id']; ?>">+</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
