<?php
// File: public/dashboard.php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();

global $pdo;

// KPIs
$total_despachos = $pdo->query("SELECT COUNT(*) FROM despachos")->fetchColumn();
$entregues = $pdo->query("SELECT COUNT(*) FROM despachos WHERE status = 'Entregue'")->fetchColumn();
$em_transito = $pdo->query("SELECT COUNT(*) FROM despachos WHERE status = 'Em Trânsito'")->fetchColumn();
$atrasados = $pdo->query("
    SELECT COUNT(*) FROM despachos 
    WHERE data_prevista_entrega < CURDATE() 
      AND status NOT IN ('Entregue', 'Cancelado')
")->fetchColumn();

$total_aparelhos = $pdo->query("SELECT COUNT(*) FROM aparelhos")->fetchColumn();
$em_uso = $pdo->query("SELECT COUNT(*) FROM aparelhos WHERE status = 'Em Uso'")->fetchColumn();

// Gráfico: Despachos por status
$status_data = $pdo->query("
    SELECT status, COUNT(*) as total 
    FROM despachos 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Logística Interna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard</h2>

        <!-- KPIs -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5><i class="fas fa-truck"></i> Total Despachos</h5>
                            <h3><?= $total_despachos ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success h-100">
                    <div class="card-body">
                        <h5><i class="fas fa-check-circle"></i> Entregues</h5>
                        <h3><?= $entregues ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning h-100">
                    <div class="card-body">
                        <h5><i class="fas fa-shipping-fast"></i> Em Trânsito</h5>
                        <h3><?= $em_transito ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger h-100">
                    <div class="card-body">
                        <h5><i class="fas fa-exclamation-triangle"></i> Atrasados</h5>
                        <h3><?= $atrasados ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Despachos -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5>Despachos por Status</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chartDespachos" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Aparelhos -->
            <div class="col-lg-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-microchip"></i> Aparelhos</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4><?= $total_aparelhos ?></h4>
                                <small class="text-muted">Total</small>
                            </div>
                            <div>
                                <h4><?= $em_uso ?></h4>
                                <small class="text-muted">Em Uso</small>
                            </div>
                        </div>
                        <a href="aparelhos.php" class="btn btn-outline-info btn-sm w-100">
                            Gerenciar Aparelhos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('chartDespachos').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Em Processamento', 'Em Trânsito', 'Aguardando Retirada', 'Entregue'],
                datasets: [{
                    data: [
                        <?= $status_data['Em Processamento'] ?? 0 ?>,
                        <?= $status_data['Em Trânsito'] ?? 0 ?>,
                        <?= $status_data['Aguardando Retirada'] ?? 0 ?>,
                        <?= $status_data['Entregue'] ?? 0 ?>
                    ],
                    backgroundColor: ['#6c757d', '#ffc107', '#17a2b8', '#28a745']
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>