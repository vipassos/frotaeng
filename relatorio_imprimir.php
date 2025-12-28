<?php
require 'auth.php';
require 'db.php';

$veiculo_id = $_GET['veiculo_id'] ?? 0;
if ($veiculo_id == 0) {
    $stmtFirst = $pdo->query("SELECT id FROM veiculos LIMIT 1");
    $first = $stmtFirst->fetch();
    $veiculo_id = $first ? $first['id'] : 0;
}

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');

// Busca Veículo
$stmtV = $pdo->prepare("SELECT * FROM veiculos WHERE id = ?");
$stmtV->execute([$veiculo_id]);
$veiculo = $stmtV->fetch();

if (!$veiculo) die("Veículo não encontrado.");

// Busca Extrato com a nova coluna ESPECIALIDADE
$sql = "
    SELECT 
        data_abastecimento as data, 
        'Combustível' as categoria,
        especialidade, 
        CONCAT(litros, ' Lts (', tipo_combustivel, ') - KM ', km_momento) as descricao,
        valor_total as valor
    FROM abastecimentos 
    WHERE veiculo_id = ? AND data_abastecimento BETWEEN ? AND ?

    UNION ALL

    SELECT 
        data_manutencao as data, 
        'Manutenção' as categoria,
        especialidade,
        CONCAT(tipo, ' - ', descricao) as descricao,
        valor as valor
    FROM manutencoes 
    WHERE veiculo_id = ? AND data_manutencao BETWEEN ? AND ?

    ORDER BY data ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$veiculo_id, $data_inicio, $data_fim, $veiculo_id, $data_inicio, $data_fim]);
$lancamentos = $stmt->fetchAll();

$total_periodo = 0;
foreach($lancamentos as $l) { $total_periodo += $l['valor']; }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório - <?= $veiculo['placa'] ?></title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        @media screen {
            body { background-color: #525659; padding: 20px; }
            .folha-a4 {
                background: white; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 15mm 20mm;
            }
            .no-print { margin-bottom: 20px; }
        }
        @media print {
            @page { size: A4; margin: 10mm; }
            body { background: white; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .folha-a4 { width: 100%; margin: 0; padding: 0; border: none; }
        }
        .assinatura-box { margin-top: 60px; border-top: 1px solid #000; width: 45%; text-align: center; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="container no-print text-center">
        <div class="card p-3 d-inline-block shadow">
            <form class="d-flex gap-2 align-items-end" method="GET">
                <input type="hidden" name="veiculo_id" value="<?= $veiculo_id ?>">
                <div class="text-start">
                    <label class="form-label small mb-0">Início</label>
                    <input type="date" name="data_inicio" value="<?= $data_inicio ?>" class="form-control form-control-sm">
                </div>
                <div class="text-start">
                    <label class="form-label small mb-0">Fim</label>
                    <input type="date" name="data_fim" value="<?= $data_fim ?>" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-success">Imprimir</button>
                <a href="index.php" class="btn btn-sm btn-secondary">Voltar</a>
            </form>
        </div>
    </div>

    <div class="folha-a4">
        <div class="border-bottom border-2 border-dark pb-2 mb-4 d-flex justify-content-between">
            <div>
                <h4 class="mb-0 fw-bold text-uppercase">Relatório de Despesas</h4>
                <small class="text-muted">Controle de Frota - Reparos Engenharia</small>
            </div>
            <div class="text-end">
                <h6 class="mb-0">Emissão: <?= date('d/m/Y') ?></h6>
            </div>
        </div>

        <div class="row mb-4 p-3 bg-light border rounded mx-0">
            <div class="col-6">
                <span class="d-block fw-bold fs-5"><?= $veiculo['modelo'] ?></span>
                <span class="d-block">Placa: <strong><?= $veiculo['placa'] ?></strong></span>
            </div>
            <div class="col-6 text-end">
                <span class="d-block text-muted small">PERÍODO</span>
                <strong><?= date('d/m/Y', strtotime($data_inicio)) ?></strong> até <strong><?= date('d/m/Y', strtotime($data_fim)) ?></strong>
            </div>
        </div>

        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Data</th>
                    <th>Finalidade</th>
                    <th>Descrição</th>
                    <th class="text-end">Valor (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($lancamentos) > 0): ?>
                    <?php foreach ($lancamentos as $l): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($l['data'])) ?></td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= $l['especialidade'] ?? 'Pessoal' ?></span>
                        </td>
                        <td class="small">
                            <strong><?= $l['categoria'] ?>:</strong> <?= $l['descricao'] ?>
                        </td>
                        <td class="text-end"><?= number_format($l['valor'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted">Sem registros.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-group-divider">
                <tr class="table-active fw-bold">
                    <td colspan="3" class="text-end text-uppercase">Total</td>
                    <td class="text-end fs-5">R$ <?= number_format($total_periodo, 2, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="row mt-5 pt-5">
            <div class="col-6"><div class="assinatura-box mx-auto">Vinícius Oliveira Passos<br><small>Engenheiro / Perito</small></div></div>
            <div class="col-6"><div class="assinatura-box mx-auto">Visto / Conferência<br><small>Data: ____/____/_______</small></div></div>
        </div>
    </div>
</body>
</html>