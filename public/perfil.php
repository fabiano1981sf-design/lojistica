<?php
// File: public/perfil.php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();

$user = getCurrentUser();
if (!$user) {
    logout();
    exit;
}

$mensagem = '';

// === Atualizar dados pessoais ===
if ($_POST && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_dados') {
    $nome = trim($_POST['nome']);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if ($nome && $email) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET nome = ?, email = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $user['id']]);
            $mensagem = '<div class="alert alert-success">Dados atualizados com sucesso!</div>';
            $user['nome'] = $nome;
            $user['email'] = $email;
        } catch (PDOException $e) {
            $mensagem = '<div class="alert alert-danger">Erro ao salvar: ' . h($e->getMessage()) . '</div>';
        }
    } else {
        $mensagem = '<div class="alert alert-warning">Preencha nome e e-mail válido.</div>';
    }
}

// === Alterar senha ===
if ($_POST && isset($_POST['acao']) && $_POST['acao'] === 'alterar_senha') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if ($senha_atual && $nova_senha && $confirmar) {
        if ($nova_senha !== $confirmar) {
            $mensagem = '<div class="alert alert-warning">A nova senha e a confirmação não coincidem.</div>';
        } elseif (strlen($nova_senha) < 6) {
            $mensagem = '<div class="alert alert-warning">A nova senha deve ter pelo menos 6 caracteres.</div>';
        } else {
            // Verificar senha atual
            $stmt = $pdo->prepare("SELECT senha_hash FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $hash = $stmt->fetchColumn();

            if (password_verify($senha_atual, $hash)) {
                $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET senha_hash = ? WHERE id = ?");
                $stmt->execute([$novo_hash, $user['id']]);
                $mensagem = '<div class="alert alert-success">Senha alterada com sucesso!</div>';
            } else {
                $mensagem = '<div class="alert alert-danger">Senha atual incorreta.</div>';
            }
        }
    } else {
        $mensagem = '<div class="alert alert-warning">Preencha todos os campos de senha.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user"></i> Meu Perfil</h4>
                    </div>
                    <div class="card-body">
                        <?= $mensagem ?>

                        <!-- Dados Pessoais -->
                        <h5 class="mt-3">Informações Pessoais</h5>
                        <form method="post" class="row g-3">
                            <input type="hidden" name="acao" value="atualizar_dados">
                            <div class="col-md-6">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" name="nome" class="form-control" value="<?= h($user['nome']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" value="<?= h($user['email']) ?>" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Atualizar Dados
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <!-- Alterar Senha -->
                        <h5>Alterar Senha</h5>
                        <form method="post" class="row g-3">
                            <input type="hidden" name="acao" value="alterar_senha">
                            <div class="col-md-4">
                                <label class="form-label">Senha Atual</label>
                                <input type="password" name="senha_atual" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nova Senha</label>
                                <input type="password" name="nova_senha" class="form-control" minlength="6" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirmar Nova Senha</label>
                                <input type="password" name="confirmar" class="form-control" minlength="6" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key"></i> Alterar Senha
                                </button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <!-- Informações do Sistema -->
                        <h5>Informações do Sistema</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted">ID do Usuário</small>
                                <p class="fw-bold">#<?= $user['id'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Status</small>
                                <p class="fw-bold">
                                    <span class="badge bg-success">Ativo</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Último Login</small>
                                <p class="fw-bold">
                                    <?= date('d/m/Y H:i', strtotime($_SESSION['last_login'] ?? 'now')) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Sessão Atual</small>
                                <p class="fw-bold">
                                    <span class="text-success">Ativa</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão Voltar -->
                <div alumni="text-center mt-3">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>