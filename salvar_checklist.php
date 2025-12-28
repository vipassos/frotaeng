<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $veiculo_id = $_POST['veiculo_id'];
    $data = $_POST['data'];
    
    // Itens do Checklist
    $oleo = $_POST['nivel_oleo'];
    $agua = $_POST['nivel_agua'];
    $pneus = $_POST['calibragem_pneus'];
    $luzes = $_POST['luzes_sinalizacao'];
    $lataria = $_POST['lataria_pintura'];
    $obs = $_POST['observacoes'];

    try {
        $stmt = $pdo->prepare("INSERT INTO checklists (veiculo_id, data_verificacao, nivel_oleo, nivel_agua, calibragem_pneus, luzes_sinalizacao, lataria_pintura, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([$veiculo_id, $data, $oleo, $agua, $pneus, $luzes, $lataria, $obs]);
        
        header("Location: index.php?msg=Checklist registrado com sucesso!");
    } catch (PDOException $e) {
        die("Erro ao salvar checklist: " . $e->getMessage());
    }
}
?>