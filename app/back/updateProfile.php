<?php
session_start();
require 'conection.php'; // Asegúrate de que el nombre del archivo sea correcto

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['user_id'];
    $newEmail = trim($_POST['email']);
    $newUsername = trim($_POST['user']);
    $password = trim($_POST['password']);


    if (empty($newEmail) || empty($newUsername) || empty($password)) {
        die("Todos los campos son obligatorios.");
    }

    try {
        $select = "SELECT password FROM Users WHERE user_id = :user_id";
        $result = $dbConection->prepare($select);
        $result ->execute([':user_id' => $userId]);
        $correctPassword = $result->fetch(PDO::FETCH_ASSOC);

        if ($correctPassword && password_verify($password, $correctPassword['password']) ) {
            $sql = "UPDATE Users SET email = :email, username = :username WHERE user_id = :user_id";
            $stmt = $dbConection->prepare($sql);
            $stmt->execute([
            ':email' => $newEmail,
            ':username' => $newUsername,
            ':user_id' => $userId
            ]);
            echo "Perfil actualizado correctamente.";
        } else {
            echo "La contraseña es incorrecta.";
        }
        

        
    } catch (PDOException $e) {
        echo "Error al actualizar perfil: " . $e->getMessage();
    }
}
?>
