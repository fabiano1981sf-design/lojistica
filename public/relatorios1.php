<?php
// File: public/relatorios.php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();
if (!hasPermission('visualizar_relatorios')) {
    die('<div class="alert alert-danger p-4">Acesso negado.</div>');
}

// === FILTROS ===
$data_inicio = $_GET['inicio'] ?? date('Y-m-01');
$data_fim    = $_GET['fim'] ?? date('Y-m-d');

$data_inicio = date('Y-m-d', strtotime($data_inicio));
$data_fim    = date('Y-m-d', strtotime($data_fim));

// === KPIs ===
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM despachos 
    WHERE DATE(data_criacao) BETWEEN ? AND ?
");
$stmt->execute([$data_inicio, $data_fim]);
$total_despachos = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT transportadora_id) 
    FROM despachos 
    WHERE transportadora_id IS NOT NULL 
      AND DATE(data_criacao) BETWEEN ? AND ?
");
$stmt->execute([$data_inicio, $data_fim]);
$transportadoras_ativas = $stmt->fetchColumn();

$media_por_transportadora = $transportadoras_ativas > 0 ? round($total_despachos / $transportadoras_ativas, 1) : 0;

// === Despachos por Mês (Últimos 6) ===
$meses = [];
$valores_meses = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $meses[] = date('M/Y', strtotime("-$i months"));
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM despachos 
        WHERE DATE_FORMAT(data_criacao, '%Y-%m') = ?
    ");
    $stmt->execute([$mes]);
    $valores_meses[] = (int)$stmt->fetchColumn();
}

// === Despachos por Transportadora ===
$por_transportadora = [];
$stmt = $pdo->prepare("
    SELECT t.nome, COUNT(d.id) as total
    FROM despachos d
    JOIN transportadoras t ON d.transportadora_id = t.id
    WHERE DATE(d.data_criacao) BETWEEN ? AND ?
    GROUP BY d.transportadora_id
    ORDER BY total DESC
");
$stmt->execute([$data_inicio, $data_fim]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $por_transportadora[$row['nome']] = (int)$row['total'];
}

// === Top 10 Clientes ===
$stmt = $pdo->prepare("
    SELECT destino_nome, COUNT(*) as total
    FROM despachos
    WHERE DATE(data_criacao) BETWEEN ? AND ?
    GROUP BY destino_nome
    ORDER BY total DESC
    LIMIT 10
");
$stmt->execute([$data_inicio, $data_fim]);
$top_clientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatórios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .kpi-card { transition: transform 0.2s; }
        .kpi-card:hover { transform: translateY(-5px); }
        .chart-container { position: relative; height: 300px; }
    </style>
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <h2 class="mb-4">Relatórios de Despachos</h2>

        <!-- Filtro -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Data Início</label>
                        <input type="date" name="inicio" class="form-control" value="<?= $data_inicio ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Data Fim</label>
                        <input type="date" name="fim" class="form-control" value="<?= $data_fim ?>" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card kpi-card text-white bg-primary h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Total de Despachos</h5>
                            <h2 class="mb-0"><?= number_format($total_despachos) ?></h2>
                        </div>
                        <i class="fas fa-truck fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card text-white bg-success h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Transportadoras Ativas</h5>
                            <h2 class="mb-0"><?= $transportadoras_ativas ?></h2>
                        </div>
                        <i class="fas fa-shipping-fast fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card text-white bg-info h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5>Média por Transportadora</h5>
                            <h2 class="mb-0"><?= $media_por_transportadora ?></h2>
                        </div>
                        <i class="fas fa-chart-pie fa-3x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico: Despachos por Mês -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-dark text-white">
                        <h5>Despachos por Mês (Últimos 6)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartMeses"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico: Por Transportadora -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-dark text-white">
                        <h5>Despachos por Transportadora</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartTransportadora"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top 10 Clientes -->
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5>Top 10 Clientes com Mais Despachos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Cliente</th>
                                        <th>Despachos</th>
                                        <th>% do Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_clientes as $i => $c): ?>
                                    <tr>
                                        <td><strong>#<?= $i + 1 ?></strong></td>
                                        <td><?= h($c['destino_nome']) ?></td>
                                        <td><strong><?= $c['total'] ?></strong></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" style="width: <?= ($total_despachos > 0 ? ($c['total'] / $total_despachos * 100) : 0) ?>%">
                                                    <?= round($c['total'] / $total_despachos * 100, 1) ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <script>
        new Chart(document.getElementById('chartMeses'), {
            type: 'line',
            data: {
                labels: <?= json_encode($meses) ?>,
                datasets: [{
                    label: 'Despachos',
                    data: <?= json_encode($valores_meses) ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('chartTransportadora'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($por_transportadora)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($por_transportadora)) ?>,
                    backgroundColor: [
                        '#198754', '#0d6efd', '#ffc107', '#dc3545', '#6f42c1',
                        '#fd7e14', '#20c997', '#0dcaf0', '#d63384', '#6c757d'
                    ]
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'right' } } }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>