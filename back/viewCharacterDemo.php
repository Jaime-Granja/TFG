<?php
session_start();
require 'conection.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in.");
}

$characterId = 2;
// HAY QUE CAMBIAR EL !== A === 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

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

        // Obtenemos el nombre de la especie, porque ahora sólo tenemos su id
        $speciesSelect = $dbConection->prepare("SELECT specie_name FROM species WHERE specie_id = :id");
        $speciesSelect->execute([':id' => $character['specie']]);
        $speciesName = $speciesSelect->fetchColumn() ?: "Unknown species";

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
    <link rel="stylesheet" href="../front/src/styles/stylesSheet.css" />
    <script src="../src/scripts/sheet.js"></script>
    <link rel="shortcut icon" href="../src/img/D20.png" />
  </head>

  <body>
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
          <div class="characterField" id="characterNameField"><?php echo htmlspecialchars($character['character_name']); ?></div>
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
          <div class="characterField" id="characterRaceField"><?php echo htmlspecialchars($speciesName); ?></div>
        </div>
        <div id="characterBackground" class="characterInfo">
          <div id="characterBackgroundTag">Trasfondo:</div>
          <div class="characterField" id="characterBackgroundField">
            <?php echo htmlspecialchars($character['character_desc']); ?>
          </div>
        </div>
      </div>
      <h2>Estadísticas</h2>
      <div id="stats">
        <div class="stat" id="str">
          <div id="strName">Fuerza</div>
          <!--Todos estos números hay que sacarlos de la base de datos-->
          <div id="strNum"><?php echo $stats['strength']; ?></div>
          <div class="mod" id="strMod"><!--AÑADIR MODIFICADOR--></div>
        </div>
        <div class="stat" id="dex">
          <div id="dexName">Destreza</div>
          <div id="dexNum"><?php echo $stats['dexterity']; ?></div>
          <div class="mod" id="dexMod"><!--AÑADIR MODIFICADOR--></div>
        </div>
        <div class="stat" id="con">
          <div id="conName">Constitución</div>
          <div id="conNum"><?php echo $stats['constitution']; ?></div>
          <div class="mod" id="conMod"><!--AÑADIR MODIFICADOR--></div>
        </div>
        <div class="stat" id="int">
          <div id="intName">Inteligencia</div>
          <div id="intNum"><?php echo $stats['intelligence']; ?></div>
          <div class="mod" id="intMod"><!--AÑADIR MODIFICADOR--></div>
        </div>
        <div class="stat" id="wis">
          <div id="wisName">Sabiduría</div>
          <div id="wisNum"><?php echo $stats['wisdom']; ?></div>
          <div class="mod" id="wisMod"><!--AÑADIR MODIFICADOR--></div>
        </div>
        <div class="stat" id="cha">
          <div id="chaName">Carisma</div>
          <div id="chaNum"><?php echo $stats['charisma']; ?></div>
          <div class="mod" id="chaMod"><!--AÑADIR MODIFICADOR--></div>
        </div>
        <div id="globalStats">
          <div class="globalStat" id="pg">
            <div id="pgName">Puntos de Golpe</div>
            <div id="pgNum">60</div>
          </div>
          <div class="globalStat" id="init">
            <div id="initName">Iniciativa</div>
            <div id="initNum"><?php echo $stats['dexterity']; ?></div>
          </div>
          <div class="globalStat" id="speed">
            <div id="speedName">Velocidad</div>
            <div id="speedNum">30</div>
          </div>
          <div class="globalStat" id="ca">
            <div id="caName">Clase de Armadura</div>
            <div id="caNum">9</div>
          </div>
          <div class="globalStat" id="pb">
            <div id="pbName">Bonificador de Competencia</div>
            <div id="pbNum"><!--AÑADIR MODIFICADOR--></div>
          </div>
        </div>
      </div>
      <div id="tiradas">
        <div id="saves">
          <h2 id="savesTitle">Salvaciones</h2>
          <div id="strSave" class="save">
            <div id="strSaveName">Fue</div>
            <div id="strSaveNum"><!--AÑADIR MODIFICADOR--></div>
          </div>
          <div id="dexSave" class="save">
            <div id="dexSaveName">Des</div>
            <div id="dexSaveNum"><!--AÑADIR MODIFICADOR--></div>
          </div>
          <div id="conSave" class="save">
            <div id="conSaveName">Con</div>
            <div id="conSaveNum"><!--AÑADIR MODIFICADOR--></div>
          </div>
          <div id="intSave" class="save">
            <div id="intSaveName">Int</div>
            <div id="intSaveNum"><!--AÑADIR MODIFICADOR--></div>
          </div>
          <div id="wisSave" class="save">
            <div id="wisSaveName">Sab</div>
            <div id="wisSaveNum"><!--AÑADIR MODIFICADOR--></div>
          </div>
          <div id="chaSave" class="save">
            <div id="chaSaveName">Car</div>
            <div id="chaSaveNum"><!--AÑADIR MODIFICADOR--></div>
          </div>
        </div>
        <div id="abilities">
          <h2 id="abilitiesTitle">Habilidades</h2>
          <div id="abilitiesPhysical">
            <div id="abilitiesStr">
              <h3>Fuerza</h3>
              <div id="athletics" class="ability">
                <div id="athleticsTitle">Atletismo</div>
                <div id="athleticsMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
            </div>
            <div id="abilitiesDex">
              <h3>Destreza</h3>
              <div id="acrobatics" class="ability">
                <div id="acrobaticsTitle">Acrobacias</div>
                <div id="acrobaticsMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="sleightOfHand" class="ability">
                <div id="sleightOfHandTitle">Juego de Manos</div>
                <div id="acrobaticsMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="stealth" class="ability">
                <div id="stealthTitle">Sigilo</div>
                <div id="acrobaticsMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
            </div>
          </div>
          <div id="abilitiesMental">
            <div id="abilitiesInt">
              <h3>Inteligencia</h3>
              <div id="arcana" class="ability">
                <div id="arcanaTitle">Arcanos</div>
                <div id="arcanaMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="history" class="ability">
                <div id="historyTitle">Historia</div>
                <div id="historyMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="investigation" class="ability">
                <div id="investigationTitle">Investigación</div>
                <div id="investigationMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="religion" class="ability">
                <div id="religionTitle">Religión</div>
                <div id="religionMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="nature" class="ability">
                <div id="natureTitle">Naturaleza</div>
                <div id="natureMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
            </div>
            <div id="abilitiesWis">
              <h3>Sabiduría</h3>
              <div id="medicine" class="ability">
                <div id="medicineTitle">Medicina</div>
                <div id="medicineMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="perception" class="ability">
                <div id="perceptionTitle">Percepción</div>
                <div id="perceptionMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="insight" class="ability">
                <div id="insightTitle">Perspicacia</div>
                <div id="insightMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="survival" class="ability">
                <div id="survivalTitle">Supervivencia</div>
                <div id="survivalMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="animalHandling" class="ability">
                <div id="animalHandlingTitle">Trato con Animales</div>
                <div id="animalHandlingMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
            </div>
            <div id="abilitiesCha">
              <h3>Carisma</h3>
              <div id="deception" class="ability">
                <div id="deceptionTitle">Engaño</div>
                <div id="deceptionMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="performance" class="ability">
                <div id="performanceTitle">Interpretación</div>
                <div id="performanceMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="intimidation" class="ability">
                <div id="intimidationTitle">Intimidación</div>
                <div id="intimidationMod"><!--AÑADIR MODIFICADOR--></div>
              </div>
              <div id="persuasion" class="ability">
                <div id="persuasionTitle">Persuasión</div>
                <div id="persuasionMod"><!--AÑADIR MODIFICADOR--></div>
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
        <h2>Rasgos de Raza</h2>
        <h3>Drow</h3>
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
        <h2>Rasgos de Clase</h2>
        <h3>Mago</h3>
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
      </div>
    </div>
  </body>
</html>
