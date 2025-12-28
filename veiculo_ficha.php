<?php
require 'auth.php';
require 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$msg = "";

// 1. Processa a atualização se o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Dados Cadastrais e Técnicos Originais
    $cor = $_POST['cor'];
    $renavam = $_POST['renavam'];
    $chassi = $_POST['chassi'];
    $combustivel = $_POST['combustivel_padrao'];
    $oleo = $_POST['oleo_motor'];
    $pneus = $_POST['calibragem_pneus'];
    $seguradora = $_POST['seguradora'];
    $apolice = $_POST['apolice'];
    $tel_seguro = $_POST['telefone_seguro'];

    // NOVOS DADOS: Plano de Manutenção
    $int_oleo = $_POST['intervalo_oleo_km'];
    $int_ar = $_POST['intervalo_filtro_ar_km'];
    $int_comb = $_POST['intervalo_filtro_comb_km'];
    $int_meses = $_POST['intervalo_tempo_meses'];

    // Atualiza todos os dados
    $sql = "UPDATE veiculos SET
            cor=?, renavam=?, chassi=?, combustivel_padrao=?, oleo_motor=?, calibragem_pneus=?,
            seguradora=?, apolice=?, telefone_seguro=?,
            intervalo_oleo_km=?, intervalo_filtro_ar_km=?, intervalo_filtro_comb_km=?, intervalo_tempo_meses=?
            WHERE id=?";

    $stmt = $pdo->prepare($sql);

    $params = [
        $cor, $renavam, $chassi, $combustivel, $oleo, $pneus,
        $seguradora, $apolice, $tel_seguro,
        $int_oleo, $int_ar, $int_comb, $int_meses,
        $id
    ];

    if($stmt->execute($params)) {
        $msg = "Ficha e Plano de Manutenção atualizados com sucesso!";
    }
}

// 2. Busca os dados atuais do veículo
$stmt = $pdo->prepare("SELECT * FROM veiculos WHERE id = ?");
$stmt->execute([$id]);
$v = $stmt->fetch();

if(!$v) die("Veículo não encontrado.");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ficha Técnica - <?= $v['modelo'] ?></title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<div class="container py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-0"><?= $v['modelo'] ?> <span class="text-muted fs-4">| <?= $v['placa'] ?></span></h2>
            <div class="mt-1">
                <span class="badge bg-primary"><?= $v['marca'] ?></span>
                <span class="badge bg-secondary"><?= $v['ano'] ?></span>
            </div>
        </div>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>

    <?php if($msg): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $v['id'] ?>">

        <div class="row">

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white"><i class="bi bi-file-earmark-text"></i> Documentação</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cor do Veículo</label>
                            <input type="text" name="cor" class="form-control" value="<?= $v['cor'] ?>" placeholder="Ex: Branco">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">RENAVAM</label>
                            <input type="text" name="renavam" class="form-control" value="<?= $v['renavam'] ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Chassi</label>
                            <input type="text" name="chassi" class="form-control" value="<?= $v['chassi'] ?>">
                        </div>
                        <hr>
                        <h6 class="text-primary fw-bold"><i class="bi bi-shield-check"></i> Seguro</h6>
                        <div class="mb-2">
                            <label class="form-label">Seguradora</label>
                            <input type="text" name="seguradora" class="form-control" value="<?= $v['seguradora'] ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Apólice</label>
                            <input type="text" name="apolice" class="form-control" value="<?= $v['apolice'] ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Telefone 24h</label>
                            <input type="text" name="telefone_seguro" class="form-control" value="<?= $v['telefone_seguro'] ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-warning text-dark"><i class="bi bi-wrench"></i> Especificações</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Combustível Preferencial</label>
                            <select name="combustivel_padrao" class="form-select">
                                <option value="Gasolina" <?= $v['combustivel_padrao'] == 'Gasolina' ? 'selected' : '' ?>>Gasolina</option>
                                <option value="Etanol" <?= $v['combustivel_padrao'] == 'Etanol' ? 'selected' : '' ?>>Etanol</option>
                                <option value="Diesel" <?= $v['combustivel_padrao'] == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="GNV" <?= $v['combustivel_padrao'] == 'GNV' ? 'selected' : '' ?>>GNV (Gás Natural)</option>
                            </select>
                            <div class="form-text">Usado para base de cálculo.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Óleo do Motor</label>
                            <input type="text" name="oleo_motor" class="form-control" value="<?= $v['oleo_motor'] ?>" placeholder="Ex: 5W30 Sintético">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Calibragem Pneus (PSI)</label>
                            <input type="text" name="calibragem_pneus" class="form-control" value="<?= $v['calibragem_pneus'] ?>" placeholder="Ex: 32 / 30">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100 border-success">
                    <div class="card-header bg-success text-white"><i class="bi bi-calendar-check"></i> Plano de Revisão</div>
                    <div class="card-body">
                        <p class="small text-muted mb-3">Defina os intervalos (em KM) recomendados pelo fabricante.</p>

                        <div class="mb-3">
                            <label class="fw-bold text-success">Óleo e Filtro (KM)</label>
                            <input type="number" name="intervalo_oleo_km" class="form-control" value="<?= $v['intervalo_oleo_km'] ?>" placeholder="Padrão: 10000">
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold text-success">Filtro de Ar (KM)</label>
                            <input type="number" name="intervalo_filtro_ar_km" class="form-control" value="<?= $v['intervalo_filtro_ar_km'] ?>" placeholder="Padrão: 20000">
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold text-success">Filtro Combustível (KM)</label>
                            <input type="number" name="intervalo_filtro_comb_km" class="form-control" value="<?= $v['intervalo_filtro_comb_km'] ?>" placeholder="Padrão: 20000">
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Limite de Tempo (Meses)</label>
                            <input type="number" name="intervalo_tempo_meses" class="form-control" value="<?= $v['intervalo_tempo_meses'] ?>" placeholder="Padrão: 12">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-success btn-lg px-5"><i class="bi bi-save"></i> Salvar Ficha</button>
        </div>
    </form>
</div>

</body>
</html>