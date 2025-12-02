<?php
// File: public/despacho_detalhe.php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();

$despacho_id = $_GET['id'] ?? 0;
$despacho_id = (int)$despacho_id;

if (!$despacho_id) {
    die('<div class="alert alert-danger">ID do despacho inválido.</div>');
}

// Buscar despacho
global $pdo;
$stmt = $pdo->prepare("
    SELECT d.*, u.nome as despachante_nome
    FROM despachos d
    LEFT JOIN users u ON d.despachante_id = u.id
    WHERE d.id = ?
");
$stmt->execute([$despacho_id]);
$despacho = $stmt->fetch();

if (!$despacho) {
    die('<div class="alert alert-warning">Despacho não encontrado.</div>');
}

// Histórico de rastreio
$stmt = $pdo->prepare("
    SELECT * FROM rastreio_historico 
    WHERE despacho_id = ? 
    ORDER BY data_hora ASC
");
$stmt->execute([$despacho_id]);
$historico = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Despacho #<?= $despacho['codigo_rastreio'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 0; bottom: 0; left: 50%;
            width: 2px;
            background: #dee2e6;
            transform: translateX(-50%);
        }
        .timeline-item {
            position: relative;
            margin: 30px 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            width: 16px; height: 16px;
            border-radius: 50%;
            background: #0d6efd;
            top: 0; left: 50%;
            transform: translateX(-50%);
            z-index: 1;
            border: 3px solid white;
            box-shadow: 0 0 0 4px #0d6efd33;
        }
        .timeline-content {
            width: 45%;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .timeline-item:nth-child(odd) .timeline-content { margin-left: 55%; }
        .timeline-item:nth-child(even) .timeline-content { margin-left: 5%; text-align: right; }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.4em 0.8em;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Cabeçalho -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-truck"></i> Despacho #<?= h($despacho['codigo_rastreio']) ?>
                        </h4>
                        <a href="qrcode_gerar.php?id=<?= $despacho_id ?>" target="_blank" class="btn btn-light btn-sm">
                            <i class="fas fa-qrcode"></i> QR Code
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <strong>Cliente:</strong> <?= h($despacho['destino_nome']) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Despachante:</strong> <?= h($despacho['despachante_nome'] ?? 'Sistema') ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Transportadora:</strong> <?= h($despacho['transportadora'] ?? 'Não informada') ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Nota Fiscal:</strong> <?= h($despacho['nota_fiscal'] ?? '—') ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Data de Criação:</strong> 
                                <?= date('d/m/Y H:i', strtotime($despacho['data_criacao'])) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong>
                                <span class="badge status-badge bg-<?= 
                                    $despacho['status'] === 'Entregue' ? 'success' :
                                    ($despacho['status'] === 'Em Trânsito' ? 'warning' :
                                    ($despacho['status'] === 'Aguardando Retirada' ? 'info' : 'secondary'))
                                ?>">
                                    <?= h($despacho['status']) ?>
                                </span>
                            </div>
                            <?php if ($despacho['anotacao']): ?>
                            <div class="col-12">
                                <strong>Anotação:</strong><br>
                                <div class="bg-light p-3 rounded">
                                    <?= nl2br(h($despacho['anotacao'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Timeline de Rastreio -->
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-history"></i> Histórico de Rastreio</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($historico): ?>
                            <div class="timeline">
                                <?php foreach ($historico as $i => $h): ?>
                                <div class="timeline-item">
                                    <div class="timeline-content">
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($h['data_hora'])) ?>
                                        </small>
                                        <p class="mb-1 fw-bold"><?= h($h['evento']) ?></p>
                                        <?php if ($h['localizacao']): ?>
                                            <small class="text-primary">
                                                <i class="fas fa-map-marker-alt"></i> <?= h($h['localizacao']) ?>
                                            pequena>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Nenhum evento registrado ainda.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Botão Voltar -->
                <div class="mt-4 text-center">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>