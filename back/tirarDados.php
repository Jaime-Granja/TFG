<?php
session_start();


if (!isset($_SESSION["user_id"])) {
    die("Acceso denegado. Inicia sesión primero.\n");
}

$caras = isset($_POST['dado']) ? (int)$_POST['dado'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;

$dados_permitidos = [4, 6, 8, 10, 12, 20, 100];


if (!in_array($caras, $dados_permitidos)) {
    die("Tipo de dado no permitido. Usa uno de estos: d" . implode(", d", $dados_permitidos) . "\n");
}

if ($cantidad < 1 || $cantidad > 100) {
    die("La cantidad de dados debe estar entre 1 y 100.\n");
}


$resultados = [];
for ($i = 0; $i < $cantidad; $i++) {
    $resultados[] = rand(1, $caras);
}
$total = array_sum($resultados);


echo "Resultado de tirar $cantidad dado(s) de $caras caras (d$caras):\n";
echo "Tiradas: " . implode(", ", $resultados) . "\n";
echo "Total: $total\n";
?>

