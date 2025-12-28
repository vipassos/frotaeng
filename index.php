<?php
require 'auth.php';
require 'db.php';

// Busca veículos
try {
    $stmt = $pdo->query("SELECT * FROM veiculos ORDER BY modelo");
    $veiculos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro no banco.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Frota</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4 sticky-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php"><i class="bi bi-car-front-fill"></i> Frota Passos</a>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-sm btn-light" title="Dashboard"><i class="bi bi-graph-up"></i></a>
            <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#modalSenha"><i class="bi bi-key"></i></button>
            <a href="logout.php" class="btn btn-sm btn-danger">Sair</a>
        </div>
    </div>
</nav>

<div class="container">

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs flex-nowrap overflow-auto" id="myTab" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#resumo"><i class="bi bi-car-front"></i> Meus Veículos</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#abastecer"><i class="bi bi-fuel-pump"></i> Novo Abastecimento</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#manutencao"><i class="bi bi-wrench"></i> Nova Manutenção</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#checklist"><i class="bi bi-clipboard-check"></i> Checklist Rápido</button></li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                <div class="tab-pane fade show active" id="resumo">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Veículo</th>
                                    <th>Placa</th>
                                    <th>KM Atual</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($veiculos) > 0): ?>
                                    <?php foreach ($veiculos as $v): ?>
                                    <tr>
                                        <td>
                                            <strong><?= $v['modelo'] ?></strong> <small class="text-muted"><?= $v['marca'] ?></small>
                                            <?php if(!empty($v['arquivo_crv'])): ?>
                                                <a href="assets/docs/<?= $v['arquivo_crv'] ?>" target="_blank" title="Documento Digital Disponível">
                                                    <i class="bi bi-file-pdf text-danger ms-1 fs-3"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= $v['placa'] ?></span></td>
                                        <td><?= number_format($v['km_atual'], 0, ',', '.') ?> km</td>
                                        <td class="text-end">
                                            <div class="dropdown position-static">
                                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-boundary="viewport">
                                                    Ações
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                                    <li>
                                                        <a href="veiculo_ficha.php?id=<?= $v['id'] ?>" class="dropdown-item">
                                                            <i class="bi bi-card-checklist text-info me-2"></i> Ficha Técnica
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="historico_checklist.php?veiculo_id=<?= $v['id'] ?>" class="dropdown-item">
                                                            <i class="bi bi-clipboard-data text-warning me-2"></i> Histórico Checks
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="relatorio_imprimir.php?veiculo_id=<?= $v['id'] ?>" target="_blank" class="dropdown-item">
                                                            <i class="bi bi-printer text-dark me-2"></i> Imprimir Relatório
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a href="excluir_veiculo.php?id=<?= $v['id'] ?>" class="dropdown-item text-danger" onclick="return confirm('Confirma a exclusão?')">
                                                            <i class="bi bi-trash me-2"></i> Excluir
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center p-4">Nenhum veículo cadastrado.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#modalVeiculo">
                        <i class="bi bi-plus-lg"></i> Adicionar Veículo
                    </button>
                </div>

                <div class="tab-pane fade" id="abastecer">
                    <form action="salvar_abastecimento.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Veículo</label>
                                <select name="veiculo_id" class="form-select" required>
                                    <?php foreach ($veiculos as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= $v['modelo'] ?> - <?= $v['placa'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Finalidade / Despesa</label>
                                <select name="especialidade" class="form-select" required>
                                    <option value="Pessoal">Pessoal</option>
                                    <option value="Perícia Judicial">Perícia Judicial</option>
                                    <option value="Serviços de Engenharia">Serviços de Engenharia</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Data</label>
                                <input type="date" name="data" value="<?= date('Y-m-d') ?>" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">KM no Painel</label>
                                <input type="number" name="km_momento" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Combustível</label>
                                <select name="tipo_combustivel" class="form-select">
                                    <option value="Gasolina">Gasolina</option>
                                    <option value="Etanol">Etanol</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="GNV">GNV</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Litros</label>
                                <input type="number" step="0.01" name="litros" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Valor Total</label>
                                <input type="text" name="valor_total" class="form-control dinheiro" placeholder="R$ 0,00" onkeyup="formatarMoeda(this)" required>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-fuel-pump"></i> Salvar Abastecimento</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="manutencao">
                    <form action="salvar_manutencao.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Veículo</label>
                                <select name="veiculo_id" id="manut_veiculo_id" class="form-select" required onchange="calcularProximaTroca()">
                                    <option value="" selected disabled>Selecione...</option>
                                    <?php foreach ($veiculos as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= $v['modelo'] ?> - <?= $v['placa'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Finalidade / Despesa</label>
                                <select name="especialidade" class="form-select" required>
                                    <option value="Pessoal">Pessoal</option>
                                    <option value="Perícia Judicial">Perícia Judicial</option>
                                    <option value="Serviços de Engenharia">Serviços de Engenharia</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tipo de Serviço</label>
                                <select name="tipo" id="manut_tipo" class="form-select" required onchange="calcularProximaTroca()">
                                    <option value="Outros">Outros / Reparo Geral</option>
                                    <option value="Troca de Óleo">Troca de Óleo e Filtro</option>
                                    <option value="Filtro de Ar">Troca Filtro de Ar</option>
                                    <option value="Filtro de Combustível">Troca Filtro de Combustível</option>
                                    <option value="Pneus">Pneus</option>
                                    <option value="Freios">Freios</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Data</label>
                                <input type="date" name="data" value="<?= date('Y-m-d') ?>" class="form-control" required>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">KM no Painel (Realizado em)</label>
                                <input type="number" name="km_momento" id="manut_km_momento" class="form-control" required onkeyup="calcularProximaTroca()">
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="uso_severo" onchange="calcularProximaTroca()">
                                    <label class="form-check-label text-danger fw-bold" for="uso_severo">
                                        Uso Severo (Estrada de terra / Poeira excessiva)
                                    </label>
                                    <div class="form-text">Reduz o intervalo de troca pela metade.</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Valor</label>
                                <input type="text" name="valor" class="form-control dinheiro" placeholder="R$ 0,00" onkeyup="formatarMoeda(this)" required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Descrição Detalhada</label>
                                <input type="text" name="descricao" class="form-control" placeholder="Ex: Óleo 5w30 Sintético e Filtros" required>
                            </div>

                            <div class="col-md-12">
                                <div class="alert alert-warning d-flex align-items-center" role="alert">
                                    <i class="bi bi-calculator me-2 fs-4"></i>
                                    <div class="w-100">
                                        <label class="fw-bold">Próxima Troca (Sugestão Automática)</label>
                                        <input type="number" name="proxima_troca_km" id="proxima_troca_km" class="form-control fw-bold" placeholder="Calculando..." readonly>
                                        <small class="text-muted" id="info_calculo"></small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-2"><button type="submit" class="btn btn-warning px-4"><i class="bi bi-wrench"></i> Salvar Manutenção</button></div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="checklist">
                    <form action="salvar_checklist.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Veículo</label>
                                <select name="veiculo_id" class="form-select" required>
                                    <option value="" disabled selected>Selecione...</option>
                                    <?php foreach ($veiculos as $v): ?>
                                        <option value="<?= $v['id'] ?>"><?= $v['modelo'] ?> - <?= $v['placa'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Data da Verificação</label>
                                <input type="date" name="data" value="<?= date('Y-m-d') ?>" class="form-control" required>
                            </div>

                            <hr class="my-4">
                            <h6 class="text-primary fw-bold"><i class="bi bi-list-check"></i> Itens de Segurança</h6>

                            <div class="col-md-4">
                                <label class="form-label">Nível de Óleo</label>
                                <select name="nivel_oleo" class="form-select">
                                    <option value="OK">🟢 Normal / OK</option>
                                    <option value="Baixo">🟡 Baixo (Completar)</option>
                                    <option value="Crítico">🔴 Crítico / Vencido</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Água do Radiador</label>
                                <select name="nivel_agua" class="form-select">
                                    <option value="OK">🟢 Normal / OK</option>
                                    <option value="Baixo">🟡 Baixo (Completar)</option>
                                    <option value="Vazamento">🔴 Vazamento Visível</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Pneus / Estepe</label>
                                <select name="calibragem_pneus" class="form-select">
                                    <option value="OK">🟢 Calibrados</option>
                                    <option value="Baixo">🟡 Precisam Calibrar</option>
                                    <option value="Desgaste">🔴 Carecas / Irregulares</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Luzes / Sinalização</label>
                                <select name="luzes_sinalizacao" class="form-select">
                                    <option value="OK">🟢 Todas Funcionando</option>
                                    <option value="Queimada">🔴 Lâmpada Queimada</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Lataria / Vidros</label>
                                <select name="lataria_pintura" class="form-select">
                                    <option value="OK">🟢 Intacta</option>
                                    <option value="Risco">🟡 Risco Novo / Pequeno</option>
                                    <option value="Avaria">🔴 Batida / Avaria</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Observações Gerais</label>
                                <textarea name="observacoes" class="form-control" rows="2" placeholder="Ex: Palheta do limpador ressecada, barulho na suspensão..."></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-success px-4 w-100">
                                    <i class="bi bi-check-circle"></i> Registrar Verificação
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVeiculo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="salvar_veiculo.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Novo Veículo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Marca</label><input type="text" name="marca" class="form-control" required></div>
                    <div class="mb-3"><label>Modelo</label><input type="text" name="modelo" class="form-control" required></div>
                    <div class="mb-3"><label>Placa</label><input type="text" name="placa" class="form-control" required></div>
                    <div class="mb-3"><label>Ano</label><input type="number" name="ano" class="form-control" required></div>
                    <div class="mb-3"><label>KM Atual</label><input type="number" name="km_atual" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSenha" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Alterar Senha</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="alterar_senha.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3"><label>Senha Atual</label><input type="password" name="senha_atual" class="form-control" required></div>
                    <div class="mb-3"><label>Nova Senha</label><input type="password" name="nova_senha" class="form-control" required></div>
                    <div class="mb-3"><label>Confirmar</label><input type="password" name="confirma_senha" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function formatarMoeda(i) {
        var v = i.value.replace(/\D/g,'');
        v = (v/100).toFixed(2) + '';
        v = v.replace(".", ",");
        v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
        v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
        i.value = 'R$ ' + v;
    }

    // --- LÓGICA DE CÁLCULO DE TROCA ---
    async function calcularProximaTroca() {
        const veiculoId = document.getElementById('manut_veiculo_id').value;
        const tipoServico = document.getElementById('manut_tipo').value;
        const kmInput = document.getElementById('manut_km_momento');
        const kmAtual = kmInput.value ? parseInt(kmInput.value) : 0;
        const usoSevero = document.getElementById('uso_severo').checked;
        const campoResultado = document.getElementById('proxima_troca_km');
        const infoCalculo = document.getElementById('info_calculo');

        if (!veiculoId) return;

        // Se ainda não digitou KM do serviço, usa 0 ou espera
        if (kmAtual <= 0) {
             return;
        }

        try {
            // Busca as regras do carro na API
            const response = await fetch(`api_veiculo.php?id=${veiculoId}`);
            const dados = await response.json();

            let intervalo = 0;
            let nomeRegra = "";

            if (tipoServico === 'Troca de Óleo') {
                intervalo = parseInt(dados.intervalo_oleo_km || 10000);
                nomeRegra = "Padrão Óleo";
            } else if (tipoServico === 'Filtro de Ar') {
                intervalo = parseInt(dados.intervalo_filtro_ar_km || 20000);
                nomeRegra = "Padrão Filtro Ar";
            } else if (tipoServico === 'Filtro de Combustível') {
                intervalo = parseInt(dados.intervalo_filtro_comb_km || 20000);
                nomeRegra = "Padrão Filtro Comb.";
            }

            // Se for uso severo, divide por 2
            if (usoSevero && intervalo > 0) {
                intervalo = intervalo / 2;
                nomeRegra += " (Uso Severo -50%)";
            }

            if (intervalo > 0) {
                const proxima = kmAtual + intervalo;
                campoResultado.value = proxima;
                infoCalculo.innerText = `Baseado em: ${nomeRegra} (${intervalo} km)`;
            } else {
                campoResultado.value = "";
                infoCalculo.innerText = "Este serviço não possui cálculo automático.";
            }

        } catch (error) {
            console.error("Erro ao calcular:", error);
        }
    }
</script>

</body>
</html>
