<?php
require 'auth.php';
require 'db.php';

$veiculo_id = filter_input(INPUT_GET, 'veiculo_id', FILTER_SANITIZE_NUMBER_INT);

if (!$veiculo_id) {
    header("Location: index.php");
    exit;
}

// 1. Busca dados do Veículo
$stmtV = $pdo->prepare("SELECT * FROM veiculos WHERE id = ?");
$stmtV->execute([$veiculo_id]);
$veiculo = $stmtV->fetch();

if(!$veiculo) die("Veículo não encontrado.");

// 2. Busca histórico de Checklists
$stmt = $pdo->prepare("SELECT * FROM checklists WHERE veiculo_id = ? ORDER BY data_verificacao DESC");
$stmt->execute([$veiculo_id]);
$checklists = $stmt->fetchAll();

// Função auxiliar para colorir as badges (Status Visual)
function getStatusBadge($status) {
    $status = trim($status);
    if (in_array($status, ['OK', 'Normal', 'Calibrados', 'Intacta', 'Todas Funcionando'])) {
        return "<span class='badge bg-success'><i class='bi bi-check-circle'></i> $status</span>";
    } elseif (in_array($status, ['Baixo', 'Risco', 'Risco Novo', 'Queimada', 'Precisam Calibrar'])) {
        return "<span class='badge bg-warning text-dark'><i class='bi bi-exclamation-triangle'></i> $status</span>";
    } else {
        return "<span class='badge bg-danger'><i class='bi bi-x-octagon'></i> $status</span>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Verificações - <?= $veiculo['placa'] ?></title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Diário de Bordo <small class="text-muted">| Checklists</small></h4>
            <span class="fs-5"><?= $veiculo['modelo'] ?></span> <span class="badge bg-primary"><?= $veiculo['placa'] ?></span>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-dark d-print-none"><i class="bi bi-printer"></i> Imprimir</button>
            <a href="index.php" class="btn btn-secondary d-print-none">Voltar</a>
        </div>
    </div>

    <?php if(count($checklists) > 0): ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Óleo Motor</th>
                            <th>Água / Radiador</th>
                            <th>Pneus</th>
                            <th>Luzes</th>
                            <th>Lataria</th>
                            <th>Obs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checklists as $c): ?>
                        <tr>
                            <td class="fw-bold"><?= date('d/m/Y', strtotime($c['data_verificacao'])) ?></td>
                            <td><?= getStatusBadge($c['nivel_oleo']) ?></td>
                            <td><?= getStatusBadge($c['nivel_agua']) ?></td>
                            <td><?= getStatusBadge($c['calibragem_pneus']) ?></td>
                            <td><?= getStatusBadge($c['luzes_sinalizacao']) ?></td>
                            <td><?= getStatusBadge($c['lataria_pintura']) ?></td>
                            <td class="small text-muted fst-italic">
                                <?= $c['observacoes'] ? $c['observacoes'] : '-' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-clipboard-x display-4 d-block mb-3"></i>
            Nenhuma verificação de rotina registrada para este veículo.
        </div>
    <?php endif; ?>

    <div class="mt-4 text-center d-none d-print-block">
        <small>Relatório gerado pelo Sistema Frota Passos em <?= date('d/m/Y H:i') ?></small>
    </div>

</div>

</body>
</html>