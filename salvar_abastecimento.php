<?php
require 'auth.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $veiculo_id = $_POST['veiculo_id'];
    $especialidade = $_POST['especialidade']; // Novo Campo
    $data = $_POST['data'];
    $km = $_POST['km_momento'];
    $litros = $_POST['litros'];
    $tipo = $_POST['tipo_combustivel'];
    
    // Tratamento de Moeda (Remove R$, pontos e troca vírgula por ponto)
    $valor_br = $_POST['valor_total'];
    $valor_limpo = preg_replace('/[^0-9,]/', '', $valor_br); // Deixa só números e vírgula
    $valor_db = str_replace(',', '.', $valor_limpo); // Troca vírgula por ponto

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO abastecimentos (veiculo_id, especialidade, data_abastecimento, litros, valor_total, km_momento, tipo_combustivel) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$veiculo_id, $especialidade, $data, $litros, $valor_db, $km, $tipo]);

        // Atualiza KM
        $stmtUp = $pdo->prepare("UPDATE veiculos SET km_atual = ? WHERE id = ? AND km_atual < ?");
        $stmtUp->execute([$km, $veiculo_id, $km]);

        $pdo->commit();
        header("Location: index.php?msg=Abastecimento Salvo");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro: " . $e->getMessage());
    }
}
?>