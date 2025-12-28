<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $veiculo_id = $_POST['veiculo_id'];
    $especialidade = $_POST['especialidade']; // Novo Campo
    $data = $_POST['data'];
    $tipo = $_POST['tipo'];
    $desc = $_POST['descricao'];
    
    // Tratamento de Moeda
    $valor_br = $_POST['valor'];
    $valor_limpo = preg_replace('/[^0-9,]/', '', $valor_br);
    $valor_db = str_replace(',', '.', $valor_limpo);

    $km = !empty($_POST['km_momento']) ? $_POST['km_momento'] : 0;
    $prox = !empty($_POST['proxima_troca_km']) ? $_POST['proxima_troca_km'] : null;

    $stmt = $pdo->prepare("INSERT INTO manutencoes (veiculo_id, especialidade, data_manutencao, tipo, descricao, valor, km_momento, proxima_troca_km) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$veiculo_id, $especialidade, $data, $tipo, $desc, $valor_db, $km, $prox]);
    
    header("Location: index.php?msg=Manutenção Salva");
}
?>