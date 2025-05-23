<?php

    $host = "localhost"; // El host de XAMPP. Si tuvieramos un servidor que no fuese local habría que poner su IP o su dominio
    $dbName = "dnd_tfg"; // Exactamente igual a como está en el XAMPP
    $userName = "root"; // Usuario por defecto en XAMPP
    $password = ""; // XAMPP por defecto va sin contraseña, así que mientras estemos en local lo dejamos vacío

    try {
        // Creamos la conexión usando PDO porque es más seguro y flexible. charset=utf8 nos evita problemas con acentos o caracteres especiales
        $dbConection = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $userName, $password);
        // Configuramos PDO para que lance excepciones en caso de que haya algún error
        $dbConection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Si conecta no dirá nada. Si no conecta sale la excepción
    } catch (PDOException $e) {
        // Usamos die para parar la ejecución del script, y mostramos mensaje de error. En este caso no es necesario el die() porque ya está pasando por la excepción, pero ayuda a recordar usarlo
        die("Error de conexión: " . $e->getMessage());
    }
?>
