<?php
session_start(); 
require 'conection.php'; 

/*
Usamos el IF para asegurarnos de que lel método http usado en la petición sea "POST". Por ejemplo, si 
un usuario entrase a "register.php" escribiendo la ruta del archivo en el navegador le daría el error del 
else.
*/  
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Validamos que los campos no estén vacíos
    if (empty($username) || empty($password)) {
        die("Usuario y contraseña son obligatorios.");
    }

    // Verificamos que el usuario exista y que la contraseña sea correcta
    try {
        $select = $dbConection->prepare("SELECT user_id, username, password FROM Users WHERE username = :username");
        $select->execute([':username' => $username]);
        $user = $select->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            // Iniciamos la sesión y guardamos los datos
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            header('Location: home.php');
            ?>
            <form action="joinCampaign.php" method="POST">
                <input type="text" name="inviteCode" placeholder="Código de invitación" required>
                <button type="submit">Unirse</button>
            </form>
            <?php
        } else {
            echo "Usuario o contraseña incorrectos.";
            header('Location: ../front/public/index.html');
        }
    } catch (PDOException $e) {
        echo "Error al iniciar sesión: " . $e->getMessage();
    }
} else {
    echo "Acceso denegado. Tira pa casa pringao.";
}
?>
