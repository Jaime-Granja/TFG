<?php
session_start();
require 'conection.php';
$userId = $_SESSION["user_id"];
$selectUser = $dbConection->prepare("SELECT username FROM users WHERE user_id = :userId");
$selectUser->bindParam(':userId', $userId, PDO::PARAM_INT);
$selectUser->execute();
$user = $selectUser->fetch(PDO::FETCH_ASSOC);
$selectCampaigns = $dbConection->prepare("SELECT c.campaign_id, c.campaign_name, c.campaign_desc FROM campaigns c JOIN users_campaigns uc ON c.campaign_id = uc.campaign_id WHERE uc.user_id = :userId");
$selectCampaigns->bindParam(':userId', $userId, PDO::PARAM_INT);
$selectCampaigns->execute();
$campaigns = $selectCampaigns->fetchAll(PDO::FETCH_ASSOC);



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../front/src/styles/styles.css">
    <link rel="stylesheet" href="../front/src/styles/stylesHome.css">
    <script src="../front/src/scripts/home.js"></script>
    <link rel="shortcut icon" href="../front/src/img/D20.png" />
</head>

<body>
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
            <div class="sheet">1<br>Integer
                pellentesque ante nec sapien condimentum, eu ornare eros pellentesque. Donec congue posuere quam, sed
                semper
                est aliquam ac. Sed vitae ligula ut turpis ullamcorper cursus in quis dui. Nulla in pretium velit. Sed
                semper mauris eget
                lectus egestas auctor. Nullam maximus eleifend dignissim. Donec sit amet sapien eget mi hendrerit
                pretium.
                Sed mattis, massa sodales pharetra gravida, leo enim venenatis nibh, non scelerisque sem felis id massa.
                Fusce tempus lorem non porttitor congue. <br>
                <button class="mas">+</button>
            </div>
            <div class="sheet">2<br>Integer
                pellentesque ante nec sapien condimentum, eu ornare eros pellentesque. Donec congue posuere quam, sed
                semper
                est aliquam ac. Sed vitae ligula ut turpis ullamcorper cursus in quis dui. Nulla in pretium velit. Sed
                semper mauris eget
                lectus egestas auctor. Nullam maximus eleifend dignissim. Donec sit amet sapien eget mi hendrerit
                pretium.
                Sed mattis, massa sodales pharetra gravida, leo enim venenatis nibh, non scelerisque sem felis id massa.
                Fusce tempus lorem non porttitor congue.<br>
                <button class="mas">+</button>
            </div>
            <div class="sheet">3<br>Integer
                pellentesque ante nec sapien condimentum, eu ornare eros pellentesque. Donec congue posuere quam, sed
                semper
                est aliquam ac. Sed vitae ligula ut turpis ullamcorper cursus in quis dui. Nulla in pretium velit. Sed
                semper mauris eget
                lectus egestas auctor. Nullam maximus eleifend dignissim. Donec sit amet sapien eget mi hendrerit
                pretium.
                Sed mattis, massa sodales pharetra gravida, leo enim venenatis nibh, non scelerisque sem felis id massa.
                Fusce tempus lorem non porttitor congue.<br>
                <button class="mas">+</button>
            </div>
        </div>
    </div>
    <button id="logOut">Cerrar Sesión</button>
</body>

</html>