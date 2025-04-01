<?php
session_start();
require 'conection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['password'];
    $newPassword = $_POST['newPassword'];

    if (empty($currentPassword) || empty($newPassword)) {
        die("Todos los campos son obligatorios.");
    }

    try {
        // Verificar contraseña actual
        $sql = "SELECT password FROM Users WHERE user_id = :user_id";
        $stmt = $dbConection->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($currentPassword, $user['password'])) {
            $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            
            // Actualizar contraseña
            $updateSql = "UPDATE Users SET password = :password WHERE user_id = :user_id";
            $updateStmt = $dbConection->prepare($updateSql);
            $updateStmt->execute([
                ':password' => $newHashedPassword,
                ':user_id' => $userId
            ]);

            echo "Contraseña cambiada con éxito.";
        } else {
            echo "Contraseña actual incorrecta.";
        }
    } catch (PDOException $e) {
        echo "Error al cambiar la contraseña: " . $e->getMessage();
    }
}
?>

