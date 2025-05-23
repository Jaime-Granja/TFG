<!-- Esta página es una página demo que sólo sirve para probar un script :) -->
<form action="createCharacter.php" method="POST">
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

    <label>Puntos de estadística:</label>
    <input type="number" name="strength" placeholder="Strength" required>
    <input type="number" name="dexterity" placeholder="Dexterity" required>
    <input type="number" name="constitution" placeholder="Constitution" required>
    <input type="number" name="intelligence" placeholder="Intelligence" required>
    <input type="number" name="wisdom" placeholder="Wisdom" required>
    <input type="number" name="charisma" placeholder="Charisma" required>

    <button type="submit">Crear personaje</button>
</form>