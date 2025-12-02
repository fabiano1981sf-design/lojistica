<?php
// File: public/rastreio_publico.php
require '../config/database.php';

$codigo = $_GET['codigo'] ?? '';
$despacho = null;
$historico = [];

if ($codigo) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM despachos WHERE codigo_rastreio = ?");
    $stmt->execute([$codigo]);
    $despacho = $stmt->fetch();

    if ($despacho) {
        $stmt = $pdo->prepare("SELECT * FROM rastreio_historico WHERE despacho_id = ? ORDER BY data_hora ASC");
        $stmt->execute([$despacho['id']]);
        $historico = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Rastreio Público</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .timeline { position: relative; padding: 20px 0; }
        .timeline::before { content: ''; position: absolute; top: 0; bottom: 0; left: 50%; width: 2px; background: #ccc; }
        .timeline-item { margin-bottom: 20px; position: relative; }
        .timeline-item::before { content: ''; position: absolute; width: 16px; height: 16px; border-radius: 50%; background: #007bff; top: 5px; left: 50%; transform: translateX(-50%); z-index: 1; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Rastreie sua encomenda</h2>
        <form method="get" class="mb-4">
            <div class="input-group">
                <input type="text" name="codigo" class="form-control" placeholder="Código de rastreio (ex: AABB123456BR)" value="<?= htmlspecialchars($codigo) ?>">
                <button class="btn btn-primary">Rastrear</button>
            </div>
        </form>

        <?php if ($despacho): ?>
            <div class="card">
                <div class="card-header"><strong>Código:</strong> <?= $despacho['codigo_rastreio'] ?></div>
                <div class="card-body">
                    <p><strong>Status:</strong> <?= $despacho['status'] ?></p>
                    <div class="timeline">
                        <?php foreach ($historico as $h): ?>
                            <div class="timeline-item">
                                <div class="card" style="margin-left: 52%; width: 45%;">
                                    <div class="card-body">
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($h['data_hora'])) ?></small>
                                        <p><?= htmlspecialchars($h['evento']) ?></p>
                                        <?php if ($h['localizacao']): ?>
                                            <small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($h['localizacao']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php elseif ($codigo): ?>
            <div class="alert alert-warning">Código não encontrado.</div>
        <?php endif; ?>
    </div>
</body>
</html>