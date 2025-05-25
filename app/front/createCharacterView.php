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
        <form id="formulario" action="../back/createCharacter.php" method="POST">
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
            <button type="submit">Crear personaje</button>
        </form>
    </div>
</body>

</html>