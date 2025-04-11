<?php
require 'conection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['jsonFile'])) {
    $jsonData = file_get_contents($_FILES['jsonFile']['tmp_name']);
    $classes = json_decode($jsonData, true);

    if (!$classes || !is_array($classes)) {
        die("Error: Formato JSON no válido.");
    }

    $updated = 0;

    foreach ($classes as $class) {
        if (!isset($class['class_name'])) continue;

        $name = $class['class_name'];
        $features = isset($class['features']) ? json_encode($class['features'], JSON_UNESCAPED_UNICODE) : null;
        $traits = isset($class['traits']) ? json_encode($class['traits'], JSON_UNESCAPED_UNICODE) : null;

        $update = $dbConection->prepare("
            UPDATE Classes 
            SET class_features = :features, class_traits = :traits
            WHERE class_name = :name
        ");

        $update->bindParam(':features', $features);
        $update->bindParam(':traits', $traits);
        $update->bindParam(':name', $name);

        if ($update->execute() && $update->rowCount()) {
            $updated++;
        }
    }

    echo "Se actualizaron $updated clases con éxito.";
}
?>

<!--
    Como el formulario necesita que le subas un archivo tenemos que poner un enctype. 
    "multipart/form-data" divide el contenido del formulario en diferentes partes, 
    cada una con su propio tipo de contenido. Así el JSON se puede procesar y subir bien.
-->
<form method="POST" enctype="multipart/form-data">
    <label>Sube el archivo JSON con todas las clases:</label><br>
    <input type="file" name="jsonFile" accept=".json" required><br><br>
    <button type="submit">Importar clases</button>
</form>
