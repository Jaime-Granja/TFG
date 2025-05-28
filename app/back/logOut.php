<?php
session_start();
// Genero una cookie para poder generar el pop-up de cierre de sesión.
setcookie("logOutMessage", "Has cerrado sesión correctamente", time() + 5, "/");
session_destroy(); // Elimina la sesión
header("Location: ../front/index.php"); // Redirige al inicio
exit();
?>
