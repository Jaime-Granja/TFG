<?php
session_start(); 
require 'conection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        die("Usuario y contraseña son obligatorios.");
    }

    try {
         // Busca el usuario por nombre de usuario
        $select = $dbConection->prepare("SELECT user_id, username, password FROM users WHERE username = :username");
        $select->execute([':username' => $username]);
        $user = $select->fetch(PDO::FETCH_ASSOC);
         // Verifica contraseña
        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];

             //sin esto no funciona bien diario porque al cerra la sesion no se recargaria el filtro de las anteriores entradas del diario 
            $stmt = $dbConection->prepare("SELECT campaign_id FROM users_campaigns WHERE user_id = :userId LIMIT 1");
            $stmt->execute([':userId' => $user["user_id"]]);
            $campaign = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($campaign) {
                $_SESSION["campaign_id"] = $campaign["campaign_id"];
            }

            header("Location: home.html");
            exit();
        } else {
            echo "Usuario o contraseña incorrectos.";
        }
    } catch (PDOException $e) {
        echo "Error al iniciar sesión: " . $e->getMessage();
    }
} else {
    echo "Acceso denegado. Tira pa casa pringao.";
}
?>

