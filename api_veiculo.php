<?php
require 'auth.php';
require 'db.php';

header('Content-Type: application/json');

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ($id) {
    $stmt = $pdo->prepare("SELECT km_atual, intervalo_oleo_km, intervalo_filtro_ar_km, intervalo_filtro_comb_km, intervalo_tempo_meses FROM veiculos WHERE id = ?");
    $stmt->execute([$id]);
    $dados = $stmt->fetch();
    
    echo json_encode($dados);
} else {
    echo json_encode([]);
}
?>