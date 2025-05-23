<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEMO</title>
</head>
<body>
<!-- Esta página es una página demo que sólo sirve para probar un script :) -->
<form action="deleteCharacter.php" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres borrar esta ficha?');">
    <input type="hidden" name="character_id" value="1">
    <button type="submit">Borrar personaje</button>
</form>

</body>
</html>