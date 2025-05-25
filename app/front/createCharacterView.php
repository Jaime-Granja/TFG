<?php
session_start();
require '../back/conection.php';

if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión.");
}

if (isset($_POST['createCharacter'])) {
    // Lo primero que hacemos es recoger lo s datos del formulario
    $name = trim($_POST['character_name']);
    $desc = trim($_POST['character_desc']);
    $owner = $_SESSION['user_id'];
    $specie = (int) $_POST['specie'];
    $classId = (int) $_POST['class_level'];

    // Aquí creamos el JSON con la clase inicial y su nivel (1)
    $classLevels = json_encode([$classId => 1], JSON_UNESCAPED_UNICODE);

    // Aquí creamos el JSON con las estadísticas
    $stats = json_encode([
        "strength" => (int) $_POST["strength"],
        "dexterity" => (int) $_POST["dexterity"],
        "constitution" => (int) $_POST["constitution"],
        "intelligence" => (int) $_POST["intelligence"],
        "wisdom" => (int) $_POST["wisdom"],
        "charisma" => (int) $_POST["charisma"]
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
}

?>

<!-- Esta página es una página demo que sólo sirve para probar un script :) -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/styles/stylesCreateCharacter.css" />
    <!-- <script src="../src/scripts/createCharacterView.js"></script>  Si no tenemos compra de puntos, no es necesario.-->
    <title>Document</title>
</head>

<body>
    <div id="container">
        <form id="formulario" method="POST">
            <label>Nombre del personaje:</label>
            <input type="text" name="character_name" required>

            <label>Descripción:</label>
            <textarea name="character_desc"></textarea>

            <label>Especie:</label>
            <select name="specie" required>
                <?php
                require '../back/conection.php';
                $select = $dbConection->query("SELECT specie_id, specie_name FROM Species");
                while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['specie_id']}'>{$row['specie_name']}</option>";
                }
                ?>
            </select>

            <label>Clase:</label>
            <select name="class_level" required>
                <?php
                $select = $dbConection->query("SELECT class_id, class_name FROM Classes");
                while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['class_id']}'>{$row['class_name']}</option>";
                }
                ?>
            </select>
            <label id="stats">Puntos de estadística:</label>
            <input type="number" class="stat" min="8" max="15" name="strength" placeholder="Strength" required>
            <input type="number" class="stat" min="8" max="15" name="dexterity" placeholder="Dexterity" required>
            <input type="number" class="stat" min="8" max="15" name="constitution" placeholder="Constitution" required>
            <input type="number" class="stat" min="8" max="15" name="intelligence" placeholder="Intelligence" required>
            <input type="number" class="stat" min="8" max="15" name="wisdom" placeholder="Wisdom" required>
            <input type="number" class="stat" min="8" max="15" name="charisma" placeholder="Charisma" required>
            <!-- <p>Puntos Restantes: <span id="remaining">27</span> </p>  Si habilitamos compra de puntos, esto estará por aquí.-->
            <button type="submit" name="createCharacter">Crear personaje</button>
        </form>
    </div>
</body>

</html>