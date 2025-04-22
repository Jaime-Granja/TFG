<?php
session_start();
require 'conection.php';

// Verificamos que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión.");
}

// Este id de personaje después habrá que ponerlo bien, ahora está hardcodeado para testear
$characterId = 2;
$userId = $_SESSION['user_id'];

// Obtenemos los datos del personaje si pertenece al usuario
$select = $dbConection->prepare("SELECT * FROM Characters WHERE character_id = :id AND character_owner = :owner");
$select->execute([':id' => $characterId, ':owner' => $userId]);

if ($select->rowCount() === 0) {
    die("Personaje no encontrado o no te pertenece.");
}

$character = $select->fetch(PDO::FETCH_ASSOC);
$stats = json_decode($character['stats'], true);
?>

<h2>Editar personaje</h2>
<form action="editCharacter.php" method="POST">
    <label>Nombre:</label>
    <input type="text" name="character_name" value="<?php echo htmlspecialchars($character['character_name']); ?>" required><br>

    <label>Descripción:</label>
    <textarea name="character_desc"><?php echo htmlspecialchars($character['character_desc']); ?></textarea><br>

    <label>Especie:</label>
    <select name="specie">
        <?php
        $select = $dbConection->query("SELECT specie_id, specie_name FROM Species");
        while ($row = $select->fetch(PDO::FETCH_ASSOC)) {
            $selected = ($character['specie'] == $row['specie_id']) ? "selected" : "";
            echo "<option value='{$row['specie_id']}' $selected>{$row['specie_name']}</option>";
        }
        ?>
    </select><br>

    <label>Estadísticas:</label><br>
    <input type="number" name="strength" value="<?php echo $stats['strength']; ?>" placeholder="Strength" required>
    <input type="number" name="dexterity" value="<?php echo $stats['dexterity']; ?>" placeholder="Dexterity" required>
    <input type="number" name="constitution" value="<?php echo $stats['constitution']; ?>" placeholder="Constitution" required>
    <input type="number" name="intelligence" value="<?php echo $stats['intelligence']; ?>" placeholder="Intelligence" required>
    <input type="number" name="wisdom" value="<?php echo $stats['wisdom']; ?>" placeholder="Wisdom" required>
    <input type="number" name="charisma" value="<?php echo $stats['charisma']; ?>" placeholder="Charisma" required><br><br>

    <button type="submit">Guardar cambios</button>
</form>
