<?php
require 'auth.php';
require 'db.php';

try {
    // 1. DADOS GERAIS (HISTÓRICO MISTO)
    $stmt = $pdo->query("SELECT * FROM v_relatorio_consumo LIMIT 20");
    $historico = $stmt->fetchAll();

    // 2. TOTAIS GERAIS (CAIXA DA EMPRESA)
    $stmtTotal = $pdo->query("SELECT SUM(valor_total) as total FROM abastecimentos");
    $totalGasto = $stmtTotal->fetch()['total'] ?? 0;

    $stmtMedia = $pdo->query("SELECT AVG(media_kml) as media FROM v_relatorio_consumo WHERE media_kml > 0");
    $mediaGeral = $stmtMedia->fetch()['media'] ?? 0;

    // 3. QUERY NOVA: RESUMO POR VEÍCULO E POR COMBUSTÍVEL
    // Agora agrupamos também pelo tipo_combustivel
    $sqlPorCarro = "
        SELECT
            v.modelo,
            v.placa,
            a.tipo_combustivel,
            SUM(a.valor_total) as gasto_total,
            AVG(rel.media_kml) as media_especifica,
            COUNT(a.id) as qtd_abastecimentos
        FROM veiculos v
        LEFT JOIN abastecimentos a ON v.id = a.veiculo_id
        LEFT JOIN v_relatorio_consumo rel ON a.id = rel.id
        WHERE a.id IS NOT NULL
        GROUP BY v.id, a.tipo_combustivel
        ORDER BY v.modelo, a.tipo_combustivel
    ";
    $stmtPorCarro = $pdo->query($sqlPorCarro);
    $resumoVeiculos = $stmtPorCarro->fetchAll();

    // 4. MANUTENÇÕES PENDENTES
    $sqlManutencao = "
        SELECT v.id, v.modelo, v.placa, v.km_atual,
        (
            SELECT proxima_troca_km
            FROM manutencoes m
            WHERE m.veiculo_id = v.id AND m.proxima_troca_km IS NOT NULL
            ORDER BY m.data_manutencao DESC LIMIT 1
        ) as proxima_troca
        FROM veiculos v
    ";
    $stmtManut = $pdo->query($sqlManutencao);
    $statusFrota = $stmtManut->fetchAll();

} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Frota Passos</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .card-kpi { border-left: 5px solid #0d6efd; transition: transform 0.2s; }
        .card-kpi:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h2><i class="bi bi-speedometer2"></i> Dashboard de Frota</h2>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar ao Menu</a>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card card-kpi shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-primary">
                        <i class="bi bi-cash-coin display-6"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Gasto Total da Frota</h6>
                        <h3 class="mb-0">R$ <?= number_format($totalGasto, 2, ',', '.') ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-kpi shadow-sm h-100" style="border-left-color: #198754;">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-success">
                        <i class="bi bi-droplet-half display-6"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Média Geral (Global)</h6>
                        <h3 class="mb-0"><?= number_format($mediaGeral, 2, ',', '.') ?> <small class="fs-6 text-muted">Km/L</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-kpi shadow-sm h-100" style="border-left-color: #dc3545;">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-danger">
                        <i class="bi bi-car-front-fill display-6"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Veículos Ativos</h6>
                        <h3 class="mb-0"><?= count($statusFrota) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-bar-chart-fill"></i> Desempenho por Combustível (Revezamento)
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 text-center align-middle">
                            <thead>
                                <tr>
                                    <th class="text-start ps-3">Veículo</th>
                                    <th>Combustível</th>
                                    <th>Abastecimentos</th>
                                    <th>Gasto Total</th>
                                    <th>Média Obtida</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($resumoVeiculos) > 0): ?>
                                    <?php foreach ($resumoVeiculos as $r): ?>
                                    <tr>
                                        <td class="text-start ps-3 fw-bold">
                                            <?= $r['modelo'] ?> <br>
                                            <small class="text-muted fw-normal"><?= $r['placa'] ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeColor = 'secondary';
                                            if($r['tipo_combustivel'] == 'Gasolina') $badgeColor = 'danger';
                                            if($r['tipo_combustivel'] == 'Etanol') $badgeColor = 'success';
                                            if($r['tipo_combustivel'] == 'Diesel') $badgeColor = 'dark';
                                            if($r['tipo_combustivel'] == 'GNV') $badgeColor = 'primary';
                                            ?>
                                            <span class="badge bg-<?= $badgeColor ?>"><?= $r['tipo_combustivel'] ?></span>
                                        </td>
                                        <td><?= $r['qtd_abastecimentos'] ?></td>
                                        <td class="text-success fw-bold">R$ <?= number_format($r['gasto_total'], 2, ',', '.') ?></td>
                                        <td>
                                            <?php if($r['media_especifica'] > 0): ?>
                                                <span class="badge bg-white text-dark border border-dark">
                                                    <?= number_format($r['media_especifica'], 2, ',', '.') ?>
                                                    <?= ($r['tipo_combustivel'] == 'GNV') ? 'm³/km' : 'km/l' ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">--</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="p-3 text-muted">Nenhum dado de abastecimento para calcular médias.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="bi bi-wrench-adjustable"></i> Próximas Trocas
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($statusFrota as $v): ?>
                        <?php
                            if (empty($v['proxima_troca'])) continue;
                            $km_restante = $v['proxima_troca'] - $v['km_atual'];

                            $classe = "list-group-item-light";
                            $icone = "bi-check-circle-fill text-success";
                            $texto_status = "OK";

                            if ($km_restante < 0) {
                                $classe = "list-group-item-danger";
                                $icone = "bi-exclamation-octagon-fill text-danger";
                                $texto_status = "VENCIDO";
                            } elseif ($km_restante < 1000) {
                                $classe = "list-group-item-warning";
                                $icone = "bi-exclamation-triangle-fill text-warning";
                                $texto_status = "Atenção";
                            }
                        ?>
                        <li class="list-group-item <?= $classe ?> d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= $v['modelo'] ?></strong><br>
                                <small>Faltam: <strong><?= $km_restante ?> km</strong></small>
                            </div>
                            <div class="text-end">
                                <i class="bi <?= $icone ?> fs-4"></i><br>
                                <span style="font-size: 0.7rem"><?= $texto_status ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-clock-history"></i> Últimas Movimentações
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 small">
                        <thead>
                            <tr>
                                <th>Veículo</th>
                                <th>Data</th>
                                <th>Combustível</th>
                                <th>Percorrido</th>
                                <th>Média</th>
                                <th>Custo/Km</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historico as $h): ?>
                            <tr>
                                <td><?= $h['modelo'] ?></td>
                                <td><?= date('d/m/y', strtotime($h['data_abastecimento'])) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $h['tipo_combustivel'] ?? 'Gasolina' ?></span></td>
                                <td><?= $h['km_percorrido'] ? $h['km_percorrido'].'km' : '-' ?></td>
                                <td><?= $h['media_kml'] ? str_replace('.', ',', $h['media_kml']) : '-' ?></td>
                                <td><?= $h['custo_por_km'] ? 'R$ '.str_replace('.', ',', $h['custo_por_km']) : '-' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>