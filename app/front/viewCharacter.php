<?php
session_start();
require '../back/conection.php';

if (!isset($_SESSION['user_id'])) {
  die("You must be logged in.");
}
$userId = $_SESSION["user_id"];
$characterId = isset($_GET['id']) ? intval($_GET['id']) : null;
// HAY QUE CAMBIAR EL !== A === 
if ($_SERVER['REQUEST_METHOD'] === 'POST' || ($_SERVER['REQUEST_METHOD'] === 'GET' && $characterId !== null)) {

  try {
    // Get character data
    $select = $dbConection->prepare("SELECT * FROM characters WHERE character_id = :id");
    $select->execute([':id' => $characterId]);
    $character = $select->fetch(PDO::FETCH_ASSOC);

    if (!$character) {
      die("Character not found.");
    }

    // Decodificamos las estadísticas y los niveles de las clases
    $stats = json_decode($character['stats'], true);
    $classLevels = json_decode($character['class_levels'], true);

    // Obtenemos los datos de la especie, porque ahora sólo tenemos su id
    $specieSelect = $dbConection->prepare("SELECT * FROM species WHERE specie_id = :id");
    $specieSelect->execute([':id' => $character['specie']]);
    $specie = $specieSelect->fetch(PDO::FETCH_ASSOC);

    // Decodificamos las features y los traits de las especies
    $specieTraits = json_decode($specie['specie_traits'], true);
    $specieFeatures = json_decode($specie['specie_features'], true);

    /* 
    Obtenemos los nombres de las clases, porque ahora sólo tenemos su id. Como el usuario puede haber hecho
    una multiclase metemos los nombres en un array. Sí sólo hay una clase, el array no nos da ningún problema
    */
    $classList = [];
    foreach ($classLevels as $classId => $level) {
      $classSelect = $dbConection->prepare("SELECT class_name FROM classes WHERE class_id = :id");
      $classSelect->execute([':id' => $classId]);
      $className = $classSelect->fetchColumn() ?: "Unknown class";
      $classList[] = ['name' => $className, 'level' => $level];
    }

    // Función para calcular los modificadores de las estadísticas
    function calculateModifier($value)
    {
      $mod = floor(($value - 10) / 2);
      return ($mod > 0 ? '+' : '') . $mod;
    }

    // Traducimos las stats para que no aparezcan en inglés en la web
    $statTranslations = [
      'strength' => 'Fuerza',
      'dexterity' => 'Destreza',
      'constitution' => 'Constitución',
      'intelligence' => 'Inteligencia',
      'wisdom' => 'Sabiduría',
      'charisma' => 'Carisma'
    ];

    // Creamos un array asociativo con los totales y los modificadores de las estadísticas para luego
    $statsWithModifiers = [];

    /*
    Este bucle calcula y añade todos los datos de las estadísticas del personaje que necesitamos añadir al
    array que acabamos de crear. Lo hace sumando los bonificadores de la especie a las stats base y 
    calculando el modificador correspondiente para cada estadística.

    Lo primero que hace el bucle es recorrer el array $stats y, en cada iteración, pasar por el nombre y 
    el valor base de la estadística del pj.
    */
    foreach ($stats as $statName => $base) {
      // Primero verificamos que exista bono por la especie en la estadística para luego sumarlo. Si no hay suma 0
      $racialBonus = isset($specieTraits['ability_score'][$statName]) ? $specieTraits['ability_score'][$statName] : 0;
      $total = $base + $racialBonus;
      // Llamamos a la función que calcula el modificador metemos la respuesta en una variable
      $modifier = calculateModifier($total);

      /*
      Por último guardams los resultados en el array que creamos previamtente. Así luego podemos acceder a los datos
      de forma sencilla. La estructura sería así:
      [
        'strength' => ['total' => 16, 'modifier' => '+3'],
        'dexterity' => ['total' => 14, 'modifier' => '+2'],  
        etc
      ]
      */
      $statsWithModifiers[$statName] = [
        'total' => $total,
        'modifier' => $modifier
      ];
    }

    // ===== SAVING THROWS Y SPECIAL TRAITS =====

    // Función para calcular el bono de competencia según el nivel
    function getProficiencyBonus($level)
    {
      return 2 + floor(($level - 1) / 4);
    }

    // Obtenemos la primera clase del personaje porque es la que indica las competencias en salvaciones
    $firstClassId = key($classLevels);

    // Sacamos los traits de esa clase para sacar las competencias
    $classSelect = $dbConection->prepare("SELECT class_traits FROM classes WHERE class_id = :id");
    $classSelect->execute([':id' => $firstClassId]);
    $classTraitsJson = $classSelect->fetchColumn();

    $proficiencies = [];
    // Aquí sacamos las competencias de las tiradas de salvación 
    if ($classTraitsJson) {
      $classTraits = json_decode($classTraitsJson, true);
      if (is_array($classTraits) && isset($classTraits['SavingThrowProficiencies'])) {
        // Pasamos las competencias a minúsculas para quitarnos problemas después con la comparación
        $proficiencies = array_map('strtolower', $classTraits['SavingThrowProficiencies']);
      }
    }

    // Calculamos el bono de competencia usando la función creada previamente
    $totalLevel = array_sum($classLevels);
    $proficiencyBonus = getProficiencyBonus($totalLevel);

    // Creamos un array en el que vamos a meter los cálculos que hagamos en el siguiente bucle para luego enseñarlos fácilmente
    $savingThrows = [];

    /*
    En este bluque usamos las stats que ya teníamos mapeadas y traducidas de antes(statTranslations). 
    Primero sacamos el modificador de cada star, después comprobamos que el pj tenga competencia en esa stat por la clase, se
    suma el bono (si no tiene bono le suma 0) y luego guardamos los resultados en el array que creamos justo antes añadiendo 
    también el nombre traducido, el total y si tiene competencia o no para poder indicarlo después.
    */
    foreach ($statTranslations as $stat => $translatedName) {
      $baseMod = isset($statsWithModifiers[$stat]['modifier']) ? intval($statsWithModifiers[$stat]['modifier']) : 0;
      $hasProficiency = in_array($stat, $proficiencies);
      $total = $baseMod + ($hasProficiency ? $proficiencyBonus : 0);

      $savingThrows[$stat] = [
        'name' => $translatedName,
        'total' => ($total >= 0 ? '+' : '') . $total,
        'proficient' => $hasProficiency
      ];
    }


    // == Special traits == 

    // Creamos un array en el que vamos a meter los traits especiales (rages, sneak attack...) que capturemos en el siguiente 
    // bucle para luego enseñarlos fácilmente. Si el pj no tiene de estos simplemente no se guarda nada.
    $specialTraits = [];

    foreach ($classList as $index => $classInfo) {
      if ($classTraitsJson) {
        // Como siempre, decodificamos el JSON recibido desde la base de datos a un array asociativo
        $classTraits = json_decode($classTraitsJson, true);

        // Recorremos cada trait del array
        foreach ($classTraits as $traitName => $traitData) {
          // Si el trait es un array, co ncampo "type": "special", y con data...
          if (is_array($traitData) && isset($traitData['type']) && $traitData['type'] === 'special' && isset($traitData['data']) && is_array($traitData['data'])) {
            // ... recorremos cada entrada de datos dentro del trait especial ...
            foreach ($traitData['data'] as $entry) {
              // ... y si la entrada tiene un campo 'level' y ese nivel es menor o igual al del pj, la aceptamos
              if (isset($entry['level']) && intval($entry['level']) <= $level) {
                // Quitamos el nivel para no almacenarlo
                $entryWithoutLevel = $entry;
                unset($entryWithoutLevel['level']);

                // Guardamos el trait especial bajo el nombre de la clase correspondiente
                // Cada trait contiene su nombre y los datos sin el campo 'level'
                $specialTraits[$className][] = [
                  'name' => $traitName,
                  'data' => $entryWithoutLevel
                ];
              }
            }
          }
        }
      }
    }

    // == Table traits == 
    $tableTraits = [];

    foreach ($classList as $index => $classInfo) {
      if ($classTraitsJson) {
        $classTraits = json_decode($classTraitsJson, true);

        foreach ($classTraits as $traitName => $traitData) {
          if (is_array($traitData) && isset($traitData['type']) && $traitData['type'] === 'table' && isset($traitData['data']) && is_array($traitData['data'])) {
            // Filtramos solo las filas cuyo nivel sea <= nivel del personaje
            $filteredData = array_filter($traitData['data'], function ($entry) use ($level) {
              return isset($entry['level']) && intval($entry['level']) <= $level;
            });

            // Guardamos los datos filtrados sin el nivel, si quieres eliminarlo
            $cleanData = [];
            foreach ($filteredData as $entry) {
              $entryCopy = $entry;
              unset($entryCopy['level']);
              $cleanData[] = $entryCopy;
            }

            $tableTraits[$className][] = [
              'name' => $traitName,
              'data' => $cleanData
            ];
          }
        }
      }
    }

    // ===== FEATURES CLASE =====

    // Creamos un array en el que vamos a meter las features para luego enseñarlas fácilmente
    $allFeatures = [];

    /*
    Recorremos cada clase que del personaje. 
    La variable $classLevels es un array asociativo donde la clave es el ID de la clase ($classId) y el valor es el nivel que 
    tiene el personaje en esa clase.
    */
    foreach ($classLevels as $classId => $level) {
      // Obtenmos las features de la clase
      $classSelect = $dbConection->prepare("SELECT  class_features FROM classes WHERE class_id = :id");
      $classSelect->execute([':id' => $classId]);
      $classFeatures = $classSelect->fetch(PDO::FETCH_ASSOC);

      // Decodificamos el JSON que contiene las features por nivel
      $featuresJson = $classFeatures['class_features'];
      $featuresByLevel = json_decode($featuresJson, true)['level'] ?? [];

      // Inicializamos el array de las featurs de esta clase
      $allFeatures[$className] = [];

      // Recorremos cada entrada de nivel en las features
      foreach ($featuresByLevel as $levelEntry) {
        $featureLevel = intval($levelEntry[0]);

        // Solo añadimos las features si el nivel del personaje alcanza el nivel de la feature 
        if ($featureLevel <= $level) {
          // El resto de los elementos del array son las habilidades de ese nivel
          for ($i = 1; $i < count($levelEntry); $i++) {
            $feature = $levelEntry[$i];

            // Añadimos la feature al array que creamos antes añadiéndole su nombre, nivel y contenido (entries)
            $allFeatures[$className][] = [
              'level' => $featureLevel,
              'name' => $feature['name'],
              'entries' => $feature['entries']
            ];
          }
        }
      }
    }

    // ===== EDITAR =====

    if (isset($_POST['edit'])) {
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
            ':desc' => $desc,
            ':specie' => $specie,
            ':stats' => $stats,
            ':id' => $characterId
          ]);

          echo "Personaje actualizado con éxito :D.";

          header("Location: viewCharacter.php?id=" . $characterId);

        } catch (PDOException $e) {
          echo "Error al actualizar personaje: " . $e->getMessage();
        }
      }
    }

    // ===== BORRAR =====

    if (isset($_POST['delete'])) {
      try {
        $check = $dbConection->prepare("SELECT * FROM Characters WHERE character_id = :id AND character_owner = :owner");
        $check->execute([':id' => $characterId, ':owner' => $userId]);

        if ($check->rowCount() === 0) {
          die("No tienes permiso para borrar este personaje.");
        }

        $delete = $dbConection->prepare("DELETE FROM Characters WHERE character_id = :id");
        $delete->execute([':id' => $characterId]);

        echo "Personaje borrado correctamente.";
        
        header("Location: ../front/home.php");

      } catch (PDOException $e) {
        echo "Error al borrar personaje: " . $e->getMessage();
      }

    }

  } catch (PDOException $e) {
    die("Error retrieving character: " . $e->getMessage());
  }

} else {
  echo "Acceso denegado. Tira pa casa pringao.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document</title>
  <link rel="stylesheet" href="../src/styles/stylesSheet.css" />
  <script src="../src/scripts/sheet.js"></script>
  <link rel="shortcut icon" href="../src/img/D20.png" />
</head>

<body id="body">
  <div id="margin">
    <img id="menuHamburguesa" src="../src/img/menu.png" />
    <div id="menuHamburguesaBotones">
      <button id="mainPageBotton">Principal</button>
      <button id="backgroundBotton">Trasfondo</button>
      <button id="featuresBotton">Rasgos</button>
      <button id="equipmentBotton">Equipo</button>
      <button id="spellbookBotton">Libro de Hechizos</button>
    </div>
  </div>
  <div id="contenedorPrincipal">
    <div id="profile">
      <h2 id="profileTitle">Perfil</h2>
      <div id="characterName" class="characterInfo">
        <div id="characterNameTag">Nombre:</div>
        <div class="characterField" id="characterNameField">
          <?php echo htmlspecialchars($character['character_name']); ?>
        </div>
      </div>
      <div id="characterClass" class="characterInfo">
        <div id="characterClassTag">Clase:</div>
        <div class="characterField" id="characterClassField">
          <?php foreach ($classList as $class): ?>
            <li><?php echo htmlspecialchars($class['name']); ?>: Level <?php echo $class['level']; ?></li>
          <?php endforeach; ?>
        </div>
      </div>
      <div id="characterRace" class="characterInfo">
        <div id="characterRaceTag">Raza:</div>
        <div class="characterField" id="characterRaceField"><?php echo htmlspecialchars($specie['specie_name']); ?>
        </div>
      </div>
      <div id="characterBackground" class="characterInfo">
        <div id="characterBackgroundTag">Trasfondo:</div>
        <div class="characterField" id="characterBackgroundField">
          <?php echo htmlspecialchars($character['character_desc']); ?>
        </div>
      </div>
    </div>

    <!-- BOTON PARA MOSTRAR EL FORMULARIO  DE EDICIÓN -->

    <button id="boton-editar" onclick="toggleFormulario()">Editar</button>

    <!-- FORMULARIO DE EDICION -->

    <div id="formulario-edicion" style="display: none;">
      <?php
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
      <form method="POST">
        <input type="hidden" name="character_id" value="<?php echo $characterId; ?>">

        <label>Nombre:</label>
        <input type="text" name="character_name" value="<?php echo htmlspecialchars($character['character_name']); ?>"
          required><br>

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
        <input type="number" name="dexterity" value="<?php echo $stats['dexterity']; ?>" placeholder="Dexterity"
          required>
        <input type="number" name="constitution" value="<?php echo $stats['constitution']; ?>"
          placeholder="Constitution" required>
        <input type="number" name="intelligence" value="<?php echo $stats['intelligence']; ?>"
          placeholder="Intelligence" required>
        <input type="number" name="wisdom" value="<?php echo $stats['wisdom']; ?>" placeholder="Wisdom" required>
        <input type="number" name="charisma" value="<?php echo $stats['charisma']; ?>" placeholder="Charisma"
          required><br><br>

        <input type="hidden" name="edit" value="1">
        <button type="submit">Guardar cambios</button>
      </form>
    </div>



    <script>
      function toggleFormulario() {
        const formulario = document.getElementById('formulario-edicion');
        const boton = document.getElementById('boton-editar');

        if (formulario.style.display === 'none' || formulario.style.display === '') {
          formulario.style.display = 'block';
          boton.textContent = 'Ocultar formulario';
        } else {
          formulario.style.display = 'none';
          boton.textContent = 'Editar';
        }
      }
    </script>

    <!-- BOTON BORRAR -->
    <form method="POST"
      onsubmit="return confirm('¿Estás seguro de que quieres borrar este personaje? Esta acción no se puede deshacer.');">
      <input type="hidden" name="delete" value="1">
      <button type="submit" style="background-color: red; color: white;">Borrar personaje</button>
    </form>


    <h2>Estadísticas</h2>
    <div id="stats">

      <!-- Recorremos el array $statsWithModifiers para mostrar las estadísticas del personaje -->
      <!-- Cada clave es el nombre de una estadística y su valor es un array de los datos de la misma (total y modificador) -->
      <?php
      foreach ($statsWithModifiers as $name => $info) {
        ?>
        <div class="stat">
          <div id="statName">
            <?php echo $statTranslations[$name] ?>
          </div>
          <div id="statTotal">
            <?php echo $info['total']; ?>
          </div>
          <div id="statModifier" class="mod">
            <?php echo $info['modifier']; ?>
          </div>
        </div>
        <?php
      }
      ?>
      <div id="globalStats">
        <div class="globalStat" id="pg">
          <div id="pgName">Puntos de Golpe</div>
          <div id="pgNum">60</div>
        </div>
        <div class="globalStat" id="init">
          <div id="initName">Iniciativa</div>
          <div id="initNum"><?php echo htmlspecialchars($statsWithModifiers['dexterity']['modifier']) ?></div>
        </div>
        <div class="globalStat" id="speed">
          <div id="speedName">Velocidad</div>
          <div id="speedNum"><?php echo $specieTraits['speed']; ?></div>
        </div>
        <div class="globalStat" id="ca">
          <div id="caName">Clase de Armadura</div>
          <div id="caNum">9</div>
        </div>
        <div class="globalStat" id="pb">
          <div id="pbName">Bonificador de Competencia</div>
          <div id="pbNum"> <?php echo $proficiencyBonus ?> </div>
        </div>
      </div>
    </div>
    <div id="tiradas">
      <div id="saves">
        <h2 id="savesTitle">Salvaciones</h2>

        <!-- Recorremos el array $savingThrows para mostrar las tiradas de salvación del personaje -->
        <!-- Cada clave es el nombre de una estadística y su valor es un array de los datos de la misma ("level", "name" y "proficient") -->
        <?php foreach ($savingThrows as $stat => $data): ?>
          <div id="statSave" class="save">
            <?php if ($data['proficient']): ?>
              <div id="statSaveProficiency">✦</div>
            <?php else: ?>
              <div id="statSaveProficiency">✧</div>
            <?php endif; ?>
            <div id="statSaveName"><?= htmlspecialchars($data['name']) ?></div>
            <div id="statSaveNum"><?= htmlspecialchars($data['total']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="abilities">
        <h2 id="abilitiesTitle">Habilidades</h2>
        <div id="abilitiesPhysical">
          <div id="abilitiesStr">
            <h3>Fuerza</h3>
            <div id="athletics" class="ability">
              <div id="athleticsTitle">Atletismo</div>
              <div id="athleticsMod"><?php echo htmlspecialchars($statsWithModifiers['strength']['modifier']) ?></div>
            </div>
          </div>
          <div id="abilitiesDex">
            <h3>Destreza</h3>
            <div id="acrobatics" class="ability">
              <div id="acrobaticsTitle">Acrobacias</div>
              <div id="acrobaticsMod"><?php echo htmlspecialchars($statsWithModifiers['dexterity']['modifier']) ?></div>
            </div>
            <div id="sleightOfHand" class="ability">
              <div id="sleightOfHandTitle">Juego de Manos</div>
              <div id="acrobaticsMod"><?php echo htmlspecialchars($statsWithModifiers['dexterity']['modifier']) ?></div>
            </div>
            <div id="stealth" class="ability">
              <div id="stealthTitle">Sigilo</div>
              <div id="acrobaticsMod"><?php echo htmlspecialchars($statsWithModifiers['dexterity']['modifier']) ?></div>
            </div>
          </div>
        </div>
        <div id="abilitiesMental">
          <div id="abilitiesInt">
            <h3>Inteligencia</h3>
            <div id="arcana" class="ability">
              <div id="arcanaTitle">Arcanos</div>
              <div id="arcanaMod"><?php echo htmlspecialchars($statsWithModifiers['intelligence']['modifier']) ?></div>
            </div>
            <div id="history" class="ability">
              <div id="historyTitle">Historia</div>
              <div id="historyMod"><?php echo htmlspecialchars($statsWithModifiers['intelligence']['modifier']) ?></div>
            </div>
            <div id="investigation" class="ability">
              <div id="investigationTitle">Investigación</div>
              <div id="investigationMod"><?php echo htmlspecialchars($statsWithModifiers['intelligence']['modifier']) ?>
              </div>
            </div>
            <div id="religion" class="ability">
              <div id="religionTitle">Religión</div>
              <div id="religionMod"><?php echo htmlspecialchars($statsWithModifiers['intelligence']['modifier']) ?>
              </div>
            </div>
            <div id="nature" class="ability">
              <div id="natureTitle">Naturaleza</div>
              <div id="natureMod"><?php echo htmlspecialchars($statsWithModifiers['intelligence']['modifier']) ?></div>
            </div>
          </div>
          <div id="abilitiesWis">
            <h3>Sabiduría</h3>
            <div id="medicine" class="ability">
              <div id="medicineTitle">Medicina</div>
              <div id="medicineMod"><?php echo htmlspecialchars($statsWithModifiers['wisdom']['modifier']) ?></div>
            </div>
            <div id="perception" class="ability">
              <div id="perceptionTitle">Percepción</div>
              <div id="perceptionMod"><?php echo htmlspecialchars($statsWithModifiers['wisdom']['modifier']) ?></div>
            </div>
            <div id="insight" class="ability">
              <div id="insightTitle">Perspicacia</div>
              <div id="insightMod"><?php echo htmlspecialchars($statsWithModifiers['wisdom']['modifier']) ?></div>
            </div>
            <div id="survival" class="ability">
              <div id="survivalTitle">Supervivencia</div>
              <div id="survivalMod"><?php echo htmlspecialchars($statsWithModifiers['wisdom']['modifier']) ?></div>
            </div>
            <div id="animalHandling" class="ability">
              <div id="animalHandlingTitle">Trato con Animales</div>
              <div id="animalHandlingMod"><?php echo htmlspecialchars($statsWithModifiers['wisdom']['modifier']) ?>
              </div>
            </div>
          </div>
          <div id="abilitiesCha">
            <h3>Carisma</h3>
            <div id="deception" class="ability">
              <div id="deceptionTitle">Engaño</div>
              <div id="deceptionMod"><?php echo htmlspecialchars($statsWithModifiers['charisma']['modifier']) ?></div>
            </div>
            <div id="performance" class="ability">
              <div id="performanceTitle">Interpretación</div>
              <div id="performanceMod"><?php echo htmlspecialchars($statsWithModifiers['charisma']['modifier']) ?></div>
            </div>
            <div id="intimidation" class="ability">
              <div id="intimidationTitle">Intimidación</div>
              <div id="intimidationMod"><?php echo htmlspecialchars($statsWithModifiers['charisma']['modifier']) ?>
              </div>
            </div>
            <div id="persuasion" class="ability">
              <div id="persuasionTitle">Persuasión</div>
              <div id="persuasionMod"><?php echo htmlspecialchars($statsWithModifiers['charisma']['modifier']) ?></div>
            </div>
          </div>
        </div>
      </div>
      <div id="atacks">
        <h2 id="atacksTitle">Ataques</h2>
        <div id="attackBlock">
          <div>Ataque</div>
          <div>Modificador</div>
          <div>Daño</div>
          <div>Espadón a Dos Manos</div>
          <div>+4</div>
          <div>2D6+4</div>
        </div>
      </div>
    </div>
  </div>
  <div id="contenedorSecundario">
    <div id="backgroundPage">
      <h2>Trasfondo</h2>
      <h3>Profesor de Español</h3>
      <div>
        Pellentesque ante nec sapien condimentum, eu ornare eros pellentesque.
        Donec congue posuere quam, sed semper est aliquam ac. Sed vitae ligula
        ut turpis ullamcorper cursus in quis dui. Nulla in pretium velit. Sed
        semper mauris eget lectus egestas auctor. Nullam maximus eleifend
        dignissim. Donec sit amet sapien eget mi hendrerit pretium. Sed
        mattis, massa sodales pharetra gravida, leo enim venenatis nibh, non
        scelerisque sem felis id massa. Fusce tempus lorem non porttitor
        congue.
      </div>
      <h2>Rasgo</h2>
      <h3>Magistrado Universitario</h3>
      <div>
        Pellentesque ante nec sapien condimentum, eu ornare eros pellentesque.
        Donec congue posuere quam, sed semper est aliquam ac. Sed vitae ligula
        ut turpis ullamcorper cursus in quis dui. Nulla in pretium velit. Sed
        semper mauris eget lectus egestas auctor. Nullam maximus eleifend
        dignissim. Donec sit amet sapien eget mi hendrerit pretium. Sed
        mattis, massa sodales pharetra gravida, leo enim venenatis nibh, non
        scelerisque sem felis id massa. Fusce tempus lorem non porttitor
        congue.
      </div>
      <h2>Competencias</h2>
      <div>Arcanos y Español</div>
    </div>
    <div id="featuresPage">
      <h2>Rasgos de <?= htmlspecialchars($specie['specie_name']); ?></h2>
      <div>
        <h3>Rasgos base</h3>
        <h4> Tipo de Criatura </h4>
        <p><?= htmlspecialchars($specieTraits['size']); ?></p>
        <h4> Tamaño </h4>
        <p><?= htmlspecialchars($specieTraits['size']); ?></p>
        <h4> Velocidad </h4>
        <p><?= htmlspecialchars($specieTraits['speed']); ?></p>
        <h4> Edad </h4>
        <p><?= htmlspecialchars($specieTraits['Age']); ?></p>
        <h4> Idiomas </h4>
        <p><?= htmlspecialchars($specieTraits['Languages']); ?></p>

        <?php if (!empty($specieFeatures)): ?>
          <h3>Habilidades especiales</h3>

          <?php foreach ($specieFeatures as $featureName => $featureContent): ?>
            <h4><strong><?= htmlspecialchars($featureName) ?>:</strong></h4>
            <p><?= nl2br(htmlspecialchars($featureContent)) ?></p>
          <?php endforeach; ?>

        <?php endif; ?>


      </div>
      <h2>Rasgos de Clase</h2>
      <div>
        <!-- Recorremos el array $allFeatures para mostrar las features del personaje -->
        <!-- Cada clave es el nombre de la clase y su valor es un array de las features de la misma -->
        <?php foreach ($allFeatures as $className => $features): ?>
          <!-- Mostramos el nombre de la clase como título. Usamos htmlspecialchars para evitar problemas con caracteres especiales o inyecciones -->
          <h3>Clase: <?= htmlspecialchars($className) ?></h3>

          <?php foreach ($features as $feature): ?>
            <div class="feature">
              <!-- Mostramos el nombre de la habilidad y el nivel en el que se obtiene para facilitar al usuario la búsqueda de lo que necesite -->
              <h4>Nivel <?= $feature['level'] ?> - <?= htmlspecialchars($feature['name']) ?></h4>

              <?php foreach ($feature['entries'] as $entry): ?>
                <?php if (is_string($entry)): ?>
                  <!-- nl2br convierte los saltos de línea (\n) en etiquetas <br> para mantener el formato -->
                  <p><?= nl2br(htmlspecialchars($entry)) ?></p>

                  <!-- Si la entrada tiene un tipo "lista" y tiene "items", mostramos los ítems como una lista  -->
                <?php elseif (is_array($entry) && isset($entry['type']) && $entry['type'] === 'list' && isset($entry['items'])): ?>
                  <ul>
                    <?php foreach ($entry['items'] as $item): ?>
                      <li><?= htmlspecialchars($item) ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
      <div>
        <?php if (!empty($specialTraits)): ?>
          <h2>Special traits</h2>
          <h3>Habilidades especiales por clase</h3>

          <?php
          // Comprobamos si hay más de una clase
          $hasMultipleClasses = count($classList) > 1;
          ?>

          <?php foreach ($specialTraits as $className => $traits): ?>
            <div class="special-trait">
              <?php if ($hasMultipleClasses): ?>
                <!-- Mostramos el nombre de la clase solo si hay más de una -->
                <h4><?= htmlspecialchars($className) ?></h4>
              <?php endif; ?>

              <?php if (!empty($traits)): ?>
                <ul>
                  <?php foreach ($traits as $trait): ?>
                    <li>
                      <strong><?= htmlspecialchars($trait['name']) ?>:</strong><br>
                      <?php foreach ($trait['data'] as $label => $value): ?>
                        <strong><?= htmlspecialchars(ucwords(strtolower(preg_replace('/(?<!^)([A-Z])/', ' $1', $label)))) ?>
                          :</strong> <?= htmlspecialchars($value) ?><br>
                      <?php endforeach; ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p>No hay datos disponibles.</p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>




      </div>

    </div>
    <div id="equipmentPage">
      <h2>Equipamiento</h2>
      <button id="addEquipmentBotton">Añadir Equipamiento</button>
      <div id="equipmentItems">
        <ul>
          <li class="equipmentCategory">Armas
            <ul>
              <li class="equipmentItem">Espada</li>
            </ul>
          </li>
          <li class="equipmentCategory">Armadura
            <ul>
              <li class="equipmentItem">Escudo</li>
            </ul>
          </li>
          <li class="equipmentCategory">Otros
            <ul>
              <li class="equipmentItem">Flechas</li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
    <div id="spellbookPage">
      <h2>Libro de Hechizos</h2>
      <div id="spellStats">
        <div class="spellStat" id="spellStat">
          <h3>Característica de Lanzamiento de Conjuros</h3>
          CAR
        </div>
        <div class="spellStat" id="spellMod">
          <h3>Modificador de Lanzamiento de Conjuros</h3>
          +6
        </div>
        <div class="spellStat" id="spellSave">
          <h3>Salvación de Lanzamiento de Conjuros</h3>
          14
        </div>
      </div>
      <div id="spells">
        <div id="cantrips">
          <h3>Trucos</h3>
          <div class="spell cantrip">Toque Gélido</div>
          <div class="spell cantrip">Descarga de Fuego</div>
        </div>
        <div id="spells1">
          <h3>Hechizos Nivel 1</h3>
          <div class="spell lvl1">Alarma</div>
          <div class="spell lvl1">Manos Ardientes</div>
        </div>
        <div id="spells2">
          <h3>Hechizos Nivel 2</h3>
          <div class="spell lvl2">No me acuerdo</div>
          <div class="spell lvl2">Rayo Abrasador</div>
        </div>
        <div id="spells3">
          <h3>Hechizos Nivel 3</h3>
          <div class="spell lvl3">Contrahechizo</div>
          <div class="spell lvl3">Bola de Fuego</div>
        </div>
      </div>
      <div>
        <?php if (!empty($tableTraits)): ?>
          <h2>Tablita magiquita</h2>
          <?php
          foreach ($tableTraits as $className => $traits):
            $cantripsKnown = '-';
            $spellSlots = [];

            // Recorremos los traits de la clase
            foreach ($traits as $trait) {
              if ($trait['name'] === 'Cantrips') {
                if (isset($trait['data'][0]['known'])) {
                  $cantripsKnown = $trait['data'][0]['known'];
                }
              }

              if ($trait['name'] === 'Spell_slots') {
                if (isset($trait['data'][0]['slots'])) {
                  $spellSlots = $trait['data'][0]['slots'];
                }
              }
            }
            ?>
            <table border="1" cellpadding="5" cellspacing="10">
              <tr>

                <th>Spellcasting Ability</th>
                <th>Modificador de spellcasting</th>
                <th>DC de Salvación</th>
              </tr>
              <tr>

                <td> Inteligencia </td>
                <td><?= htmlspecialchars($statsWithModifiers['intelligence']['modifier']) ?></td>
                <td><?= 8 + htmlspecialchars($statsWithModifiers['intelligence']['modifier'] + $proficiencyBonus) ?></td>
              </tr>
              <tr>
                <th rowspan="2">Cantrips known</th>
                <th colspan="9">Spell Slots</th>
              </tr>
              <tr>
                <?php foreach ($spellSlots as $nivel => $cantidad): ?>
                  <th><?= htmlspecialchars($nivel) ?></th>
                <?php endforeach; ?>
              </tr>
              <tr>
                <td><?= htmlspecialchars($cantripsKnown) ?></td>
                <?php foreach ($spellSlots as $cantidad): ?>
                  <td><?= htmlspecialchars($cantidad) ?></td>
                <?php endforeach; ?>
              </tr>
            </table>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>

</html>