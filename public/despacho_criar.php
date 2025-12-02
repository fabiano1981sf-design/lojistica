<?php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();
if (!hasPermission('criar_despacho')) {
    die('<div class="alert alert-danger p-4">Você não tem permissão para criar despachos.</div>');
}

$mensagem = '';

// Buscar transportadoras
$stmt = $pdo->query("SELECT nome FROM transportadoras ORDER BY nome");
$transportadoras = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_POST) {
    $nome         = trim($_POST['nome'] ?? '');
    $data_envio   = $_POST['data_envio'] ?? '';
    $num_sedex    = trim($_POST['num_sedex'] ?? '');
    $transportadora = trim($_POST['transportadora'] ?? '');
    $num_nota     = trim($_POST['num_nota'] ?? '');
    $anotacao1    = trim($_POST['anotacao1'] ?? '');
    $anotacao2    = trim($_POST['anotacao2'] ?? '');

    if ($nome && $data_envio) {
        // Buscar ou criar transportadora
        $stmt = $pdo->prepare("SELECT id FROM transportadoras WHERE nome = ?");
        $stmt->execute([$transportadora]);
        $tid = $stmt->fetchColumn();

        if (!$tid && $transportadora !== '') {
            $stmt = $pdo->prepare("INSERT INTO transportadoras (nome) VALUES (?)");
            $stmt->execute([$transportadora]);
            $tid = $pdo->lastInsertId();
        }

        // Inserir despacho
        $sql = "INSERT INTO despachos 
                (nome, data_envio, num_sedex, transportadora, transportadora_id, num_nota, anotacao1, anotacao2,
                 destino_nome, codigo_rastreio, nota_fiscal, data_criacao, despachante_id, status)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'Em Processamento')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $nome, $data_envio, $num_sedex, $transportadora, $tid, $num_nota, $anotacao1, $anotacao2,
            $nome, $num_sedex, $num_nota, $_SESSION['user_id']
        ]);

        $despacho_id = $pdo->lastInsertId();

        // Histórico
        $pdo->prepare("INSERT INTO rastreio_historico (despacho_id, evento, localizacao) VALUES (?, 'Despacho criado via sistema', 'Sistema')")
            ->execute([$despacho_id]);

        $mensagem = '<div class="alert alert-success">Despacho criado com sucesso!</div>';
    } else {
        $mensagem = '<div class="alert alert-warning">Preencha Nome e Data de Envio.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Criar Despacho</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-truck"></i> Novo Despacho</h4>
                        <a href="index.php" class="btn btn-light btn-sm">Voltar</a>
                    </div>
                    <div class="card-body">
                        <?= $mensagem ?>

                        <form method="post">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Nome do Cliente</label>
                                    <input type="text" name="nome" class="form-control form-control-lg" required autofocus>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Data de Envio</label>
                                    <input type="date" name="data_envio" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-md-5">
                                    <label class="form-label">Código de Rastreio (num_sedex)</label>
                                    <input type="text" name="num_sedex" class="form-control text-uppercase" placeholder="Ex: OV450389536BR">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Transportadora</label>
                                    <select name="transportadora" class="form-select">
                                        <option value="">Selecione ou digite nova</option>
                                        <?php foreach ($transportadoras as $t): ?>
                                            <option value="<?= h($t) ?>"><?= h($t) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Número da Nota</label>
                                    <input type="text" name="num_nota" class="form-control" placeholder="Ex: 143186">
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label class="form-label">Anotação 1</label>
                                    <textarea name="anotacao1" class="form-control" rows="4" placeholder="Ex: SEDEX REVERSO, COLETA POR WHATSAPP..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Anotação 2</label>
                                    <textarea name="anotacao2" class="form-control" rows="4" placeholder="Observações extras..."></textarea>
                                </div>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    <i class="fas fa-save"></i> Criar Despacho
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>