<?php
session_start();
require 'conection.php'; // Asegúrate de que el nombre del archivo sea correcto

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['user_id'];
    $newEmail = trim($_POST['email']);
    $newUsername = trim($_POST['user']);

    if (empty($newEmail) || empty($newUsername)) {
        die("Todos los campos son obligatorios.");
    }

    try {
        $sql = "UPDATE Users SET email = :email, username = :username WHERE user_id = :user_id";
        $stmt = $dbConection->prepare($sql);
        $stmt->execute([
            ':email' => $newEmail,
            ':username' => $newUsername,
            ':user_id' => $userId
        ]);

        echo "Perfil actualizado correctamente.";
    } catch (PDOException $e) {
        echo "Error al actualizar perfil: " . $e->getMessage();
    }
}
?>
