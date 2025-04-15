<?php
session_start();

if( !isset($_SESSION["user_id"])){
    die("acceso denegado, inicia sesion");
    }  

$caras = (int)($_POST['dado'] ?? 0); //dado a elegir
$cantidad = (int)($_POST['cantidad'] ?? 1); //cantidad de dados
$dados_permitidos = [4, 6, 8, 10, 12, 20, 100];

if (!in_array($caras, $dados_permitidos)) die("0");


$resultados = [];
for ($i = 0; $i < $cantidad; $i++) {
    $resultados[] = rand(1, $caras);
}

//se devuelve como json para mas comodidad 
header('Content-Type: application/json');
echo json_encode([
    'dado' => "d$caras",
    'resultados' => $resultados,
    'total' => array_sum($resultados)
]);
?>