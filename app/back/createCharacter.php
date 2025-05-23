<?php
session_start();
require 'conection.php';

if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión.");
}

/*
Usamos el IF para asegurarnos de que lel método http usado en la petición sea "POST". Por ejemplo, si 
un usuario entrase a "register.php" escribiendo la ruta del archivo en el navegador le daría el error del 
else.
*/  
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lo primero que hacemos es recoger lo s datos del formulario
    $name = trim($_POST['character_name']);
    $desc = trim($_POST['character_desc']);
    $owner = $_SESSION['user_id'];
    $specie = (int)$_POST['specie'];
    $classId = (int)$_POST['class_level'];

    // Aquí creamos el JSON con la clase inicial y su nivel (1)
    $classLevels = json_encode([$classId => 1], JSON_UNESCAPED_UNICODE); 

    // Aquí creamos el JSON con las estadísticas
    $stats = json_encode([
        "strength" => (int)$_POST["strength"],
        "dexterity" => (int)$_POST["dexterity"],
        "constitution" => (int)$_POST["constitution"],
        "intelligence" => (int)$_POST["intelligence"],
        "wisdom" => (int)$_POST["wisdom"],
        "charisma" => (int)$_POST["charisma"]
    ], JSON_UNESCAPED_UNICODE);

    try {

        // Preparamos la inserción a la base de datos
        $insert = $dbConection->prepare("
            INSERT INTO Characters (character_name, character_desc, character_owner, specie, pb, stats, class_levels)
            VALUES (:name, :desc, :owner, :specie, 2, :stats, :classLevels)
        ");

        // Y, por último, ejecutamos la inserción poniendo todas las variables correspondientes
        $insert->execute([
            ':name' => $name,
            ':desc' => $desc,
            ':owner' => $owner,
            ':specie' => $specie,
            ':stats' => $stats,
            ':classLevels' => $classLevels
        ]);

        echo "Personaje creado con éxito :).";
    } catch (PDOException $e) {
        echo "Error al crear personaje: " . $e->getMessage() . " Eres un liante macho.";
    }
} else {
    echo "Acceso denegado. Tira pa casa pringao.";
}
?>
