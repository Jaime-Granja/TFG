<?php
session_start();
require 'conection.php';
$msgPassword = "";
// Aquí comprobamos que el usuario tenga la sesión iniciada, por lo que pueda pasar...
if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}
$userId = $_SESSION["user_id"];
//Proceso para cambiar la contraseña
$msgPassword = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $currentPassword = $_POST['password'];
    $newPassword = $_POST['newPassword'];

    if (empty($currentPassword) || empty($newPassword)) {
        $_SESSION['msgPassword'] = "Todos los campos son obligatorios.";
        $_SESSION['msgPasswordType'] = "error";
        header("Location: user.php");
        exit;

    } else {
        try {
            $sql = "SELECT password FROM Users WHERE user_id = :user_id";
            $selectPassword = $dbConection->prepare($sql);
            $selectPassword->execute([':user_id' => $userId]);
            $user = $selectPassword->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($currentPassword, $user['password'])) {
                $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                $updateSql = "UPDATE Users SET password = :password WHERE user_id = :user_id";
                $updatePassword = $dbConection->prepare($updateSql);
                $updatePassword->execute([
                    ':password' => $newHashedPassword,
                    ':user_id' => $userId
                ]);
                $_SESSION['msgPassword'] = "Contraseña cambiada con éxito.";
                $_SESSION['msgPasswordType'] = "success";
                header("Location: user.php");
                exit;

            } else {
                $_SESSION['msgPassword'] = "Contraseña actual incorrecta.";
                $_SESSION['msgPasswordType'] = "error";
                header("Location: user.php");
                exit;

            }
        } catch (PDOException $e) {
            $_SESSION['msgPassword'] = "Error interno al cambiar la contraseña.";
            $_SESSION['msgPasswordType'] = "error";
            header("Location: user.php");
            exit;

            // Opcional: log error $e->getMessage() en un archivo
        }
    }
}

//Queremos sacar quién es el usuario loggeado.
$getUserData = $dbConection->prepare("SELECT username, email FROM Users WHERE user_id = :user_id");
$getUserData->bindParam(':user_id', $userId, PDO::PARAM_INT);
$getUserData->execute();
$loggedUserData = $getUserData->fetch(PDO::FETCH_ASSOC);

//Con esta otra query sacaremos la cantidad de campañas en las que está el usuario y cuál es su rol en estas.
$getRoleCounts = $dbConection->prepare("
    SELECT role, COUNT(*) as count 
    FROM Users_Campaigns 
    WHERE user_id = :user_id 
    GROUP BY role
");
$getRoleCounts->bindParam(':user_id', $userId, PDO::PARAM_INT);
$getRoleCounts->execute();
$roleCounts = $getRoleCounts->fetchAll(PDO::FETCH_KEY_PAIR); // ['Master' => 3, 'Player' => 5]

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Usuario: <?php echo $loggedUserData['username'] ?></title>
    <!-- <link rel="stylesheet" href="./styles.css"> -->
    <link rel="stylesheet" href="../front/src/styles/stylesUser.css" />
    <script src="../front/src/scripts/user.js"></script>
    <link rel="shortcut icon" href="../front/src/img/D20.png" />
</head>

<body>
    <?php
    if (isset($_SESSION['msgPassword'])) {
        $msgPassword = $_SESSION['msgPassword'];
        $msgPasswordType = $_SESSION['msgPasswordType'] ?? 'info';
        unset($_SESSION['msgPassword'], $_SESSION['msgPasswordType']);
    }    
    if (!empty($msgPassword)): ?>
        <div id="popup" class="popup <?php echo $msgPasswordType; ?>">
            <?php echo htmlspecialchars($msgPassword); ?>
        </div>
    <?php endif; ?>
    <div id="body">
        <h1>Página de Usuario de <?php echo $loggedUserData['username'] ?></h1>
        <div id="infoUsuario">
            <img id="profilePic" src="../front/src/img/user.png" />
            <div id="personalData">
                <h2 class="title">Datos Personales</h2>
                <p>Nombre Usuario</p>
                <p class="larger"><?php echo $loggedUserData['username'] ?></p>
                <!-- Debemos sacar esta info de la BBDD -->
                <p>Correo Electrónico</p>
                <p class="larger"><?php echo $loggedUserData['email'] ?></p>
                <!-- Debemos sacar esta info de la BBDD -->
                <div class="botones">
                    <button id="edit">Editar</button>
                    <button id="editPassword">Cambiar Contraseña</button>
                </div>
            </div>
            <div id="stats">
                <h2 class="title">Estadísticas</h2>
                <p>Campañas DM/Jugador</p>
                <p><?php echo ($roleCounts['Master'] ?? 0) . '/' . ($roleCounts['Player'] ?? 0); ?></p>
                <!-- Debemos sacar esta info de la BBDD -->
                <p>Nivel Promedio de PJs</p>
                <p>4</p>
                <!-- Debemos sacar esta info de la BBDD -->
            </div>
        </div>
        <div id="changeInfo">
            <form method="post" action="../../back/updateProfile.php">
                <!--En action habrá que poner el nombre del php pertinente-->
                <label for="email">EmailActual@hotmail.com</label>
                <!-- Debemos sacar esta info de la BBDD -->
                <input type="email" id="email" name="email" placeholder="Nuevo E-mail" />
                <label for="user">Usuario Actual</label>
                <!-- Debemos sacar esta info de la BBDD -->
                <input type="text" id="newName" name="user" placeholder="Nuevo Usuario" />
                <label for="pass">Contraseña</label>
                <!-- Debemos comprobar esta info de la BBDD -->
                <input type="password" id="newPass" name="pass" placeholder="Contraseña" required /><br />
                <button type="submit">Confirmar</button>
            </form>
        </div>
        <div id="changePassword">
            <form method="post" action="user.php">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Contraseña" required /><br />
                <!-- Debemos comprobar esta info de la BBDD -->
                <input type="password" id="newPassword" name="newPassword" placeholder="Nueva Contraseña" required />
                <button type="submit" name="change_password">Confirmar</button>
            </form>
        </div>

        <button id="goBackUser">Retroceder</button>
    </div>
</body>

</html>