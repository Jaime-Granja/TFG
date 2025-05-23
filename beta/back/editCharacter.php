<?php
session_start();
require 'conection.php';

// Como siempre, verificamos que el usuario haya iniciado sesión
if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión.");
}

// También, como siempre, nos aseguramos de que el script sólo se ejecute si el método http es post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $characterId = (int) $_POST['character_id'];
    $userId = $_SESSION['user_id'];
    echo $characterId;
    // Comprobamos que el personaje pertenece al usuario
    $check = $dbConection->prepare("SELECT * FROM Characters WHERE character_id = :id AND character_owner = :owner");
    $check->execute([':id' => $characterId, ':owner' => $userId]);

    // Si por cualquier motivo el personaje no le pertenece al usuario mandamos un mensaje de error
    if ($check->rowCount() === 0) {
        die("No tienes permiso para editar este personaje.");
    } else { // En el caso normal, editaremos el personaje
        // Recogemos los datos del formulario
        $name = trim($_POST['character_name']);
        $desc = trim($_POST['character_desc']);
        $specie = (int) $_POST['specie'];

        $stats = json_encode([
            "strength" => (int) $_POST["strength"],
            "dexterity" => (int) $_POST["dexterity"],
            "constitution" => (int) $_POST["constitution"],
            "intelligence" => (int) $_POST["intelligence"],
            "wisdom" => (int) $_POST["wisdom"],
            "charisma" => (int) $_POST["charisma"]
        ], JSON_UNESCAPED_UNICODE);

        try {
            // Actualizamos los datos
            $update = $dbConection->prepare("
            UPDATE Characters 
            SET character_name = :name,
                character_desc = :desc,
                specie = :specie,
                stats = :stats
            WHERE character_id = :id
        ");

            $update->execute([
                ':name' => $name,
                ':desc'  => $desc,
                ':specie' => $specie,
                ':stats' => $stats,
                ':id' => $characterId
            ]);

            echo "Personaje actualizado con éxito :D.";
            
            // DESCOMENTAR EL HEADER DESPUÉS DE PROBAR EL SCRIPT
            // header("Location: ../front/public/home.html");

        } catch (PDOException $e) {
            echo "Error al actualizar personaje: " . $e->getMessage();
        }
    }
} else {
    echo "Acceso denegado. Tira pa casa pringao.";
}
?>