<?php

session_start();

if( !isset($_SESSION["user_id"])){
die("acceso denegado, inicia sesion");
}  


$archivo = 'diario.json';

// leer entradas (guardados) existentes
$entradas = [];
if (file_exists($archivo)) {
    $entradas = json_decode(file_get_contents($archivo), true) ?: [];
}

// Nuevo guardado 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoGuardado = [
        'fecha' => date('d/m/Y H:i'),
        'titulo' => $_POST['titulo'],
        'contenido' => $_POST['contenido']
    ];
    
    $entradas[] = $nuevoGuardado;
    file_put_contents($archivo, json_encode($entradas));
    echo json_encode($nuevoGuardado); // Devuelve el guardado
    exit;
}

header('Content-Type: application/json');
echo json_encode($entradas);
?>