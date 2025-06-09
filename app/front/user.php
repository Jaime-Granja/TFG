<?php
session_start();
require '../back/conection.php';
$msgPopUp = "";
// Aquí comprobamos que el usuario tenga la sesión iniciada, por lo que pueda pasar...
if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Debes iniciar sesión.");
}
$userId = $_SESSION["user_id"];
//Proceso para cambiar la contraseña
$msgPopUp = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    if ($action === 'changePassword') {
        $currentPassword = $_POST['password'];
        $newPassword = $_POST['newPassword'];

        if (empty($currentPassword) || empty($newPassword)) {
            $_SESSION['msgPopUp'] = "Todos los campos son obligatorios.";
            $_SESSION['msgPopUpType'] = "error";
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
                    $_SESSION['msgPopUp'] = "Contraseña cambiada con éxito.";
                    $_SESSION['msgPopUpType'] = "success";
                    header("Location: user.php");
                    exit;

                } else {
                    $_SESSION['msgPopUp'] = "Contraseña actual incorrecta.";
                    $_SESSION['msgPopUpType'] = "error";
                    header("Location: user.php");
                    exit;

                }
            } catch (PDOException $e) {
                $_SESSION['msgPopUp'] = "Error interno al cambiar la contraseña.";
                $_SESSION['msgPopUpType'] = "error";
                header("Location: user.php");
                exit;

                // Opcional: log error $e->getMessage() en un archivo
            }
        }
    } else if ($action === 'updateProfile') {
        $newEmail = trim($_POST['email']);
        $newUsername = trim($_POST['user']);
        $password = trim($_POST['password']);


        if (empty($newEmail) || empty($newUsername) || empty($password)) {
            die("Todos los campos son obligatorios.");
        }

        try {
            $select = "SELECT password FROM Users WHERE user_id = :user_id";
            $result = $dbConection->prepare($select);
            $result->execute([':user_id' => $userId]);
            $correctPassword = $result->fetch(PDO::FETCH_ASSOC);

            if ($correctPassword && password_verify($password, $correctPassword['password'])) {
                $sql = "UPDATE Users SET email = :email, username = :username WHERE user_id = :user_id";
                $stmt = $dbConection->prepare($sql);
                $stmt->execute([
                    ':email' => $newEmail,
                    ':username' => $newUsername,
                    ':user_id' => $userId
                ]);
                $_SESSION['msgPopUp'] = "Perfil actualizado correctamente.";
                $_SESSION['msgPopUpType'] = "success";
            } else {
                $_SESSION['msgPopUp'] = "La contraseña es incorrecta.";
                $_SESSION['msgPopUpType'] = "error";
            }



        } catch (PDOException $e) {
            $_SESSION['msgPopUp'] = "Error al actualizar perfil: " . $e->getMessage();
            $_SESSION['msgPopUpType'] = "error";
        }
    }
}

//Método para actualizar el perfil



//Queremos sacar quién es el usuario loggeado.
$getUserData = $dbConection->prepare("SELECT username, email FROM Users WHERE user_id = :user_id");
$getUserData->bindParam(':user_id', $userId, PDO::PARAM_INT);
$getUserData->execute();
$loggedUserData = $getUserData->fetch(PDO::FETCH_ASSOC);

//Con esta otra query sacaremos la cantidad de campañas en las que está el usuario y cuál es su rol en estas.
$getRoleCounts = $dbConection->prepare("
    SELECT role, COUNT(*) as count 
    FROM users_campaigns_characters 
    WHERE user_id = :user_id 
    GROUP BY role
");
$getRoleCounts->bindParam(':user_id', $userId, PDO::PARAM_INT);
$getRoleCounts->execute();
$roleCounts = $getRoleCounts->fetchAll(PDO::FETCH_KEY_PAIR); // ['Master' => 3, 'Player' => 5]

// ===== NIVEL PROMEDIO =====
$avgLevel = 0;
try {
    // Sacamos los datos de los niveles de los personajes del usuario 
    $select = $dbConection->prepare("SELECT class_levels FROM characters WHERE character_owner = :user_id");
    $select->execute([':user_id' => $userId]);
    $characters = $select->fetchAll(PDO::FETCH_COLUMN);

    // Creamos una variable para almacenar el nivel total y el número de personajes
    $totalLevel = 0;
    $characterCount = count($characters);

    // Iteramos sobre los personajes y sumamos sus niveles
    foreach ($characters as $classLevelsJson) {
        $classLevels = json_decode($classLevelsJson, true);

        if (is_array($classLevels)) {
            // Sumamos todos los niveles del personaje en sus distintas clases
            $levelSum = array_sum($classLevels);
            $totalLevel += $levelSum;
        }
    }

    // Hacemos la media 
    if ($characterCount > 0) {
        $avgLevel = round($totalLevel / $characterCount, 2);
    }
} catch (PDOException $e) {
    $avgLevel = 0;
}

//===== IMAGEN DE PERFIL =====
$profilePic = 'src/img/user.png'; // Imagen por defecto

if ($userId) {
    $select = $dbConection->prepare("SELECT profile_pic FROM users WHERE user_id = :id");
    $select->execute([':id' => $userId]);
    $result = $select->fetch(PDO::FETCH_ASSOC);

    if (!empty($result['profile_pic'])) {
        // Elimina cualquier '../' inicial para evitar rutas incorrectas
        $relativePath = ltrim($result['profile_pic'], '/');
        $absolutePath = realpath(__DIR__ . '/../' . $relativePath);

        if ($absolutePath && file_exists($absolutePath)) {
            $profilePic = $result['profile_pic'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BDD-Usuario: <?php echo htmlspecialchars($loggedUserData['username']) ?></title>
    <!-- <link rel="stylesheet" href="./styles.css"> -->
    <link rel="stylesheet" href="../src/styles/css/stylesUser.css" />
    <script src="../src/scripts/user.js"></script>
    <link rel="shortcut icon" href="../src/img/logo.png" />
</head>

<body>
    <?php
    if (isset($_SESSION['msgPopUp'])) {
        $msgPopUp = $_SESSION['msgPopUp'];
        $msgPopUpType = $_SESSION['msgPopUpType'] ?? 'info';
        unset($_SESSION['msgPopUp'], $_SESSION['msgPopUpType']);
    }
    if (!empty($msgPopUp)): ?>
        <div id="popup" class="popup <?php echo $msgPopUpType; ?>">
            <?php echo htmlspecialchars($msgPopUp); ?>
        </div> <?php
    endif;
    if (isset($_COOKIE['invalidPicture'])) {
            ?>
            <div id="popup" class="popup error">
                Formato de Imagen No Válido
            </div> <?php
            setcookie("invalidPicture", "", time() - 3600, "/");
        } else if (isset($_COOKIE['largePicture'])) {
            ?>
            <div id="popup" class="popup error">
                Imagen Demasiado Pesada
            </div> <?php
            setcookie("largePicture", "", time() - 3600, "/");

        } else if (isset($_COOKIE['correctUpload'])) {
            ?>
            <div id="popup" class="popup success">
                Imagen Subida Correctamente
            </div> <?php
            setcookie("largePicture", "", time() - 3600, "/");

        }
         ?>
    <div id="body">
        <h1>Página de Usuario de <?php echo htmlspecialchars($loggedUserData['username']) ?></h1>
        <div id="infoUsuario">
            <img id="profilePic" src="<?= htmlspecialchars("../" . $profilePic) ?>" alt="Profile picture" />
            <div id="personalData">
                <h2 class="title">Datos Personales</h2>
                <p>Nombre Usuario</p>
                <p class="larger"><?php echo htmlspecialchars($loggedUserData['username']) ?></p>
                <!-- Debemos sacar esta info de la BBDD -->
                <p>Correo Electrónico</p>
                <p class="larger"><?php echo htmlspecialchars($loggedUserData['email']) ?></p>
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
                <p><?= $avgLevel ?></p>
                <!-- Debemos sacar esta info de la BBDD -->
            </div>
        </div>
        <div id="changeInfo">
            <form method="post" action="user.php">
                <input type="hidden" name="action" value="updateProfile">
                <!--En action habrá que poner el nombre del php pertinente-->
                <label for="email"><?= htmlspecialchars($loggedUserData['email']) ?></label>
                <!-- Debemos sacar esta info de la BBDD -->
                <input type="email" id="email" name="email" placeholder="Nuevo E-mail" />
                <label for="user"> <?= htmlspecialchars($loggedUserData['username']) ?> </label>
                <!-- Debemos sacar esta info de la BBDD -->
                <input type="text" id="newName" name="user" placeholder="Nuevo Usuario" />
                <label for="pass">Contraseña</label>
                <!-- Debemos comprobar esta info de la BBDD -->
                <input type="password" id="newPass" name="password" placeholder="Contraseña" required /><br />
                <button type="submit">Confirmar</button>
            </form>
        </div>
        <div id="changePassword">
            <form method="post" action="user.php">
                <input type="hidden" name="action" value="changePassword">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Contraseña" required /><br />
                <!-- Debemos comprobar esta info de la BBDD -->
                <input type="password" id="newPassword" name="newPassword" placeholder="Nueva Contraseña" required />
                <button type="submit" name="change_password">Confirmar</button>
            </form>
        </div>

        <div id="uploadPic">
            <form action="../back/uploadImage.php" method="POST" enctype="multipart/form-data">
                <label for="user_photo">Subir foto de perfil:</label><br>
                <input type="file" name="user_photo" id="user_photo" accept="image/*" required>
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId) ?>">
                <input type="submit" name="upload_user_photo" value="Subir foto de perfil">
            </form>
        </div>


        <button id="goBackUser">Retroceder</button>
    </div>
</body>

</html>