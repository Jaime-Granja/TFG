<?php
require 'conection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['jsonFile'])) {
    $fileName = $_FILES['jsonFile']['name'];
    $fileTmp = $_FILES['jsonFile']['tmp_name'];

    // Detectar si el archivo es para clases o especies y adaptamos las variables en cada caso
    if ($fileName === 'classes.json') {
        $table = 'Classes';
        $nameField = 'class_name';
        $featuresField = 'class_features';
        $traitsField = 'class_traits';
    } elseif ($fileName === 'species.json') {
        $table = 'Species';
        $nameField = 'specie_name';
        $featuresField = 'specie_features';
        $traitsField = 'specie_traits';
    } else {
        die("Nombre de archivo no reconocido. Debe llamarse 'classes.json' o 'species.json'. Espabila un poco macho.");
    }

    // Leer y decodificar JSON
    $jsonData = file_get_contents($fileTmp);
    $entities = json_decode($jsonData, true);

    if (!$entities || !is_array($entities)) {
        die("Error: El archivo JSON no tiene un formato válido.");
    }

    $updated = 0;

    foreach ($entities as $entity) {
        if (!isset($entity[$nameField])) continue;

        $name = $entity[$nameField];
        $features = isset($entity['features']) ? json_encode($entity['features'], JSON_UNESCAPED_UNICODE) : null;
        $traits = isset($entity['traits']) ? json_encode($entity['traits'], JSON_UNESCAPED_UNICODE) : null;

        $update = $dbConection->prepare("
            UPDATE $table 
            SET $featuresField = :features, $traitsField = :traits
            WHERE $nameField = :name
        ");

        $update->bindParam(':features', $features);
        $update->bindParam(':traits', $traits);
        $update->bindParam(':name', $name);

        if ($update->execute() && $update->rowCount()) {
            $updated++;
        }
    }

    echo "Se actualizaron $updated registros en la tabla $table.";
}
?>

<!--
    Como el formulario necesita que le subas un archivo tenemos que poner un enctype. 
    "multipart/form-data" divide el contenido del formulario en diferentes partes, 
    cada una con su propio tipo de contenido. Así el JSON se puede procesar y subir bien.
-->
<h2>Importar archivo JSON</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Selecciona el archivo (debe llamarse "classes.json" o "species.json". No la líes que te conozco.):</label><br>
    <input type="file" name="jsonFile" accept=".json" required><br><br>
    <button type="submit">Importar</button>
</form>
