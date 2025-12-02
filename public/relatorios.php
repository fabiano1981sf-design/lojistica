<?php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();

// ==================== DATAS PADRÃO ====================
$primeiroStmt = $pdo->query("SELECT MIN(data_envio) FROM despachos WHERE data_envio IS NOT NULL");
$primeira_data = $primeiroStmt->fetchColumn() ?: '2023-01-01';
$hoje = date('Y-m-d');

$inicio = $_GET['inicio'] ?? $primeira_data;
$fim    = $_GET['fim'] ?? $hoje;
$status = $_GET['status'] ?? '';
$transp = $_GET['transp'] ?? '';

// ==================== CONDIÇÕES E PARÂMETROS ====================
$where = ["d.data_envio BETWEEN ? AND ?"];
$params = [$inicio, $fim];

if ($status !== '') { $where[] = "d.status = ?"; $params[] = $status; }
if ($transp !== '') { $where[] = "d.transportadora = ?"; $params[] = $transp; }

$whereSql = 'WHERE ' . implode(' AND ', $where);

// ==================== ESTATÍSTICAS (CORRIGIDO COM PREPARE) ====================
$stmt = $pdo->prepare("SELECT COUNT(*) FROM despachos d $whereSql");
$stmt->execute($params);
$total_periodo = $stmt->fetchColumn();

$total_geral = $pdo->query("SELECT COUNT(*) FROM despachos")->fetchColumn();
$total_transportadoras = $pdo->query("SELECT COUNT(*) FROM transportadoras")->fetchColumn();

// ==================== GRÁFICO: Despachos por Transportadora ====================
$stmt = $pdo->prepare("
    SELECT COALESCE(t.nome, d.transportadora) AS nome, COUNT(*) AS total
    FROM despachos d
    LEFT JOIN transportadoras t ON d.transportadora_id = t.id
    $whereSql
    GROUP BY COALESCE(t.nome, d.transportadora)
    ORDER BY total DESC
");
$stmt->execute($params);
$dados_transp = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==================== TOP 10 CLIENTES ====================
$stmt = $pdo->prepare("
    SELECT d.nome, COUNT(*) AS total
    FROM despachos d
    $whereSql
    GROUP BY d.nome
    ORDER BY total DESC LIMIT 10
");
$stmt->execute($params);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==================== TOP 10 TRANSPORTADORAS ====================
$stmt = $pdo->prepare("
    SELECT COALESCE(t.nome, d.transportadora) AS nome, COUNT(*) AS total
    FROM despachos d
    LEFT JOIN transportadoras t ON d.transportadora_id = t.id
    $whereSql
    GROUP BY COALESCE(t.nome, d.transportadora)
    ORDER BY total DESC LIMIT 10
");
$stmt->execute($params);
$transp_top = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatórios Avançados - Sistema Logística</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .card { box-shadow: 0 6px 20px rgba(0,0,0,0.1); border: none; border-radius: 12px; margin-bottom: 1.5rem; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stat-card h3 { font-size: 2.5rem; font-weight: bold; }
        .chart-container { position: relative; height: 400px; }
        @media print { .no-print, .chart-container { display: none !important; } .card { box-shadow: none; } }
    </style>
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4 text-center text-primary">
            <i class="fas fa-chart-pie"></i> Relatórios Avançados de Despachos
        </h2>

        <!-- CARDS ESTATÍSTICOS -->
        <div class="row mb-5 text-center">
            <div class="col-md-4 mb-4">
                <div class="card stat-card text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-truck"></i> Total no Período</h5>
                        <h3><?= number_format($total_periodo) ?></h3>
                        <small><?= date('d/m/Y', strtotime($inicio)) ?> → <?= date('d/m/Y', strtotime($fim)) ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-database"></i> Total Geral</h5>
                        <h3><?= number_format($total_geral) ?></h3>
                        <small>Desde o primeiro despacho</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-shipping-fast"></i> Transportadoras</h5>
                        <h3><?= $total_transportadoras ?></h3>
                        <small>Cadastradas no sistema</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS -->
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-pie-chart"></i> Despachos por Transportadora</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTransportadoras"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-user-tie"></i> Top 10 Clientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartClientes"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-trophy"></i> Top 10 Transportadoras Mais Usadas</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartTopTransp"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="card mb-4 no-print">
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-3"><input type="date" name="inicio" class="form-control" value="<?= $inicio ?>"></div>
                    <div class="col-md-3"><input type="date" name="fim" class="form-control" value="<?= $fim ?>"></div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Todos Status</option>
                            <option value="Em Processamento" <?= $status==='Em Processamento'?'selected':'' ?>>Em Processamento</option>
                            <option value="Em Trânsito" <?= $status==='Em Trânsito'?'selected':'' ?>>Em Trânsito</option>
                            <option value="Entregue" <?= $status==='Entregue'?'selected':'' ?>>Entregue</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Atualizar Gráficos</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- BOTÕES -->
        <div class="text-center mb-5 no-print">
            <a href="relatorio_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success btn-lg me-3">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
			<a href="relatorio_pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-danger btn-lg me-3" target="_blank">
    <i class="fas fa-file-pdf"></i> Exportar PDF
</a>
            <button onclick="window.print()" class="btn btn-secondary btn-lg">
                <i class="fas fa-print"></i> Imprimir Relatório
            </button>
        </div>
    </div>

    <script>
        // Gráfico Pizza - Transportadoras
        new Chart(document.getElementById('chartTransportadoras'), {
            type: 'doughnut',
            data: {
                labels: [<?= "'" . implode("','", array_column($dados_transp, 'nome')) . "'" ?>],
                datasets: [{
                    data: [<?= implode(',', array_column($dados_transp, 'total')) ?>],
                    backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF','#E7E9ED','#8AC24A','#F45B5B']
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'right' } } }
        });

        // Top 10 Clientes
        new Chart(document.getElementById('chartClientes'), {
            type: 'bar',
            data: {
                labels: [<?= "'" . implode("','", array_column($clientes, 'nome')) . "'" ?>],
                datasets: [{
                    label: 'Despachos',
                    data: [<?= implode(',', array_column($clientes, 'total')) ?>],
                    backgroundColor: '#36A2EB'
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // Top 10 Transportadoras
        new Chart(document.getElementById('chartTopTransp'), {
            type: 'bar',
            data: {
                labels: [<?= "'" . implode("','", array_column($transp_top, 'nome')) . "'" ?>],
                datasets: [{
                    label: 'Total de Despachos',
                    data: [<?= implode(',', array_column($transp_top, 'total')) ?>],
                    backgroundColor: '#FF6384'
                }]
            },
            options: { indexAxis: 'y', responsive: true }
        });
    </script>
</body>
</html>