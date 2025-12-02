<?php
// File: public/usuarios.php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();
if (!hasPermission('gerenciar_usuarios')) {
    die('<div class="alert alert-danger">Acesso negado.</div>');
}

$mensagem = '';

// Listar usuários
global $pdo;
$stmt = $pdo->query("
    SELECT u.id, u.nome, u.email, u.status, GROUP_CONCAT(p.nome_perfil) as perfis
    FROM users u
    LEFT JOIN usuario_perfil up ON u.id = up.user_id
    LEFT JOIN perfis p ON up.perfil_id = p.id
    GROUP BY u.id
");
$usuarios = $stmt->fetchAll();

// Criar novo usuário
if ($_POST && isset($_POST['acao']) && $_POST['acao'] === 'criar') {
    $nome = trim($_POST['nome']);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha = $_POST['senha'];
    $perfil_id = (int)$_POST['perfil_id'];

    if ($nome && $email && strlen($senha) >= 6) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO users (nome, email, senha_hash) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $email, $senha_hash]);
            $user_id = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO usuario_perfil (user_id, perfil_id) VALUES (?, ?)")
                ->execute([$user_id, $perfil_id]);
            $pdo->commit();
            $mensagem = '<div class="alert alert-success">Usuário criado com sucesso!</div>';
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensagem = '<div class="alert alert-danger">Erro: ' . h($e->getMessage()) . '</div>';
        }
    } else {
        $mensagem = '<div class="alert alert-warning">Preencha todos os campos corretamente (senha ≥ 6 caracteres).</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-users"></i> Gerenciar Usuários</h2>
        <?= $mensagem ?>

        <!-- Botão para abrir modal -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
            <i class="fas fa-plus"></i> Novo Usuário
        </button>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfis</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= h($u['nome']) ?></td>
                        <td><?= h($u['email']) ?></td>
                        <td><?= h($u['perfis'] ?? 'Nenhum') ?></td>
                        <td>
                            <span class="badge bg-<?= $u['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                <?= $u['status'] ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Novo Usuário -->
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1">
        <div class="modal-dialog">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="criar">
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha (mín. 6 caracteres)</label>
                            <input type="password" name="senha" class="form-control" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Perfil</label>
                            <select name="perfil_id" class="form-select" required>
                                <?php
                                $stmt = $pdo->query("SELECT id, nome_perfil FROM perfis");
                                while ($p = $stmt->fetch()) {
                                    echo "<option value=\"{$p['id']}\">{$p['nome_perfil']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Criar Usuário</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>