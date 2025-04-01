<?php
session_start();
session_destroy(); // Elimina la sesión
header("Location: index.html"); // Redirige al inicio
exit();
?>
