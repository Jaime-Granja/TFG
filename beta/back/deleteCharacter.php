<?php
session_start();
require 'conection.php';

// Como siempre, verificamos que el usuario haya iniciado sesión
if (!isset($_SESSION['user_id'])) {
    die("Debes iniciar sesión.");
}

/* Y también, como siempre, nos aseguramos de que el script sólo se ejecute si el método http es post
y, en este caso, si existe un id de personaje. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['character_id'])) {
    $characterId = (int)$_POST['character_id'];
    $userId = $_SESSION['user_id'];

    try {
        /* Antes de borrar la ficha, verificamos que le pertenece al usuario. No debería haber posibilidad
        de que otra persona acceda a la ficha e intente borrarlo, pero como no sé de ciberseguridad y quizá
        hayan podido acceder por algún bug mejor prevenir que curar. */
        $check = $dbConection->prepare("SELECT * FROM Characters WHERE character_id = :id AND character_owner = :owner");
        $check->execute([':id' => $characterId, ':owner' => $userId]);

        /* En el caso hipotético de que alguien accediese a una ficha que no fuese suya e intentase borrarla
        le enseñaríamos un mensaje de error y no podría borrar la fucha*/
        if ($check->rowCount() === 0) {
            die("No tienes permiso para borrar este personaje.");
        } else { // En el caso normal, borraremos el personaje
            $delete = $dbConection->prepare("DELETE FROM Characters WHERE character_id = :id");
            $delete->execute([':id' => $characterId]);

            echo "Personaje borrado correctamente.";
        }

        // DESCOMENTAR EL HEADER DESPUÉS DE PROBAR EL SCRIPT
        // header("Location: ../front/public/home.html");

    } catch (PDOException $e) {
        echo "Error al borrar personaje: " . $e->getMessage();
    }
} else {
    echo "Acceso denegado. Tira pa casa pringao.";
}
?>
