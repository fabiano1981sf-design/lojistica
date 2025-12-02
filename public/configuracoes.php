<?php
// File: public/configuracoes.php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();
if (!hasPermission('gerenciar_configuracoes')) {
    die('<div class="alert alert-danger p-4">Acesso negado. Você não tem permissão para gerenciar configurações.</div>');
}

$mensagem = '';

// === ADICIONAR TRANSPORTADORA ===
if ($_POST && isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $nome = trim($_POST['nome']);
    if ($nome) {
        try {
            $stmt = $pdo->prepare("INSERT INTO transportadoras (nome) VALUES (?)");
            $stmt->execute([$nome]);
            $mensagem = '<div class="alert alert-success">Transportadora adicionada com sucesso!</div>';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensagem = '<div class="alert alert-warning">Esta transportadora já existe.</div>';
            } else {
                $mensagem = '<div class="alert alert-danger">Erro: ' . h($e->getMessage()) . '</div>';
            }
        }
    } else {
        $mensagem = '<div class="alert alert-warning">Digite um nome válido.</div>';
    }
}

// === EXCLUIR TRANSPORTADORA ===
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    try {
        $pdo->prepare("DELETE FROM transportadoras WHERE id = ?")->execute([$id]);
        $mensagem = '<div class="alert alert-success">Transportadora excluída!</div>';
    } catch (PDOException $e) {
        $mensagem = '<div class="alert alert-danger">Erro ao excluir.</div>';
    }
    header("Location: configuracoes.php");
    exit;
}

// === LISTAR TRANSPORTADORAS ===
$stmt = $pdo->query("SELECT * FROM transportadoras ORDER BY nome");
$transportadoras = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configurações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <h2>Configurações do Sistema</h2>

        <?= $mensagem ?>

        <!-- Card Transportadoras -->
        <div class="card shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Transportadoras Cadastradas</h5>
                <span class="badge bg-light text-dark"><?= count($transportadoras) ?> registradas</span>
            </div>
            <div class="card-body">
                <!-- Formulário para adicionar -->
                <form method="post" class="row g-3 mb-4">
                    <input type="hidden" name="acao" value="adicionar">
                    <div class="col-md-8">
                        <input type="text" name="nome" class="form-control" placeholder="Nome da transportadora" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success w-100">
                            Adicionar
                        </button>
                    </div>
                </form>

                <!-- Tabela de transportadoras -->
                <?php if ($transportadoras): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>Cadastrada em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transportadoras as $t): ?>
                            <tr>
                                <td><?= $t['id'] ?></td>
                                <td><strong><?= h($t['nome']) ?></strong></td>
                                <td><?= date('d/m/Y H:i', strtotime($t['data_cadastro'])) ?></td>
                                <td>
                                    <a href="configuracoes.php?excluir=<?= $t['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Tem certeza que deseja excluir esta transportadora?')">
                                        Excluir
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-truck fa-3x mb-3"></i>
                    <p>Nenhuma transportadora cadastrada ainda.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3">
            <a href="dashboard.php" class="btn btn-secondary">
                Voltar ao Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>