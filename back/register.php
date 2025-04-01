<?php
require 'conection.php'; 

/*
Usamos el IF para asegurarnos de que el método http usado en la petición sea "POST". Por ejemplo, si 
un usuario entrase a "register.php" escribiendo la ruta del archivo en el navegador le daría el error del 
else.
*/  
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]); // Usamos trim para quitar posibles espacios 
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Los campos son required, pero añado esto para hacer una doble verificación.
    if (empty($username) || empty($email) || empty($password)) {
        die("Todos los campos son obligatorios.");
    }
    if (strlen($password) < 6) {
        die("La contraseña debe tener al menos 6 caracteres.");
    }

    // Hasheamos la contraseña para no guardarla en la base de datos sin encriptar, que si no se lía.
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        /* 
        Usamos prepare en vez de poner directamente la consulta SQL porque pdo trata los valores como datos,
        no como código SQL. Así evitamos posibles inyecciones de SQL porque MySQL interpreta primero la consulta sin 
        ejecutarla y luego se insertan los datos. 
        */
        $insert = $dbConection->prepare("INSERT INTO Users (username, email, password) VALUES (:username, :email, :password)");
        $insert->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashed_password
        ]);

        echo "Usuario registrado con éxito.";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // 23000 es el código de error para clave duplicada
            $errorInfo = $e->errorInfo[2]; /* Obtiene el mensaje de error específico de MySQL. [0] da código SQLSTATE (23000), 
            [1] da el código específico y [2] da el mensaje de error detallado.*/
            
            if (strpos($errorInfo, 'username') !== false) { // Si $errorInfo contiene 'username'
                echo "El nombre de usuario ya está registrado.";
            } elseif (strpos($errorInfo, 'email') !== false) { // Si $errorInfo contiene 'email'
                echo "El correo electrónico ya está registrado.";
            } else {
                echo "Error 2537, cabum.";
            }
        } else {
            echo "Error al registrar usuario: " . $e->getMessage();
        }
    }    
} else {
    echo "Acceso denegado. Tira pa casa pringao.";
}
?>
