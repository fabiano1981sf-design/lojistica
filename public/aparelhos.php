<?php
// File: public/aparelhos.php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();
if (!hasPermission('gerenciar_aparelhos')) {
    die('<div class="alert alert-danger">Acesso negado.</div>');
}

$mensagem = '';

// Listar aparelhos
$stmt = $pdo->query("SELECT * FROM aparelhos ORDER BY nome");
$aparelhos = $stmt->fetchAll();

// Adicionar aparelho
if ($_POST && $_POST['acao'] === 'adicionar') {
    $nome = trim($_POST['nome']);
    $modelo = trim($_POST['modelo']);
    $serial = trim($_POST['serial']);
    $status = $_POST['status'];

    if ($nome && $serial) {
        $stmt = $pdo->prepare("INSERT INTO aparelhos (nome, modelo, serial, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $modelo, $serial, $status]);
        $mensagem = '<div class="alert alert-success">Aparelho adicionado!</div>';
    } else {
        $mensagem = '<div class="alert alert-warning">Preencha nome e serial.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Aparelhos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-microchip"></i> Aparelhos</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovo">
                <i class="fas fa-plus"></i> Novo Aparelho
            </button>
        </div>

        <?= $mensagem ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Modelo</th>
                        <th>Serial</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aparelhos as $a): ?>
                    <tr>
                        <td><?= h($a['nome']) ?></td>
                        <td><?= h($a['modelo']) ?></td>
                        <td><code><?= h($a['serial']) ?></code></td>
                        <td>
                            <span class="badge bg-<?= $a['status'] === 'Em Uso' ? 'success' : 'secondary' ?>">
                                <?= h($a['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Novo Aparelho -->
    <div class="modal fade" id="modalNovo">
        <div class="modal-dialog">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Aparelho</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="adicionar">
                        <div class="mb-3">
                            <label>Nome *</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Modelo</label>
                            <input type="text" name="modelo" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Serial *</label>
                            <input type="text" name="serial" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="Disponível">Disponível</option>
                                <option value="Em Uso">Em Uso</option>
                                <option value="Manutenção">Manutenção</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Salvar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>