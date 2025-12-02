<?php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();
if (!hasPermission('gerenciar_despachos')) {
    die('<div class="alert alert-danger p-4">Você não tem permissão para editar.</div>');
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM despachos WHERE id = ?");
$stmt->execute([$id]);
$despacho = $stmt->fetch();

if (!$despacho) {
    die("Despacho não encontrado.");
}

$mensagem = '';
$transportadoras = $pdo->query("SELECT nome FROM transportadoras ORDER BY nome")->fetchAll(PDO::FETCH_COLUMN);

if ($_POST) {
    $nome         = trim($_POST['nome']);
    $data_envio   = $_POST['data_envio'];
    $num_sedex    = trim($_POST['num_sedex']);
    $transportadora = trim($_POST['transportadora']);
    $num_nota     = trim($_POST['num_nota']);
    $anotacao1    = trim($_POST['anotacao1']);
    $anotacao2    = trim($_POST['anotacao2']);

    // Atualizar transportadora
    $stmt = $pdo->prepare("SELECT id FROM transportadoras WHERE nome = ?");
    $stmt->execute([$transportadora]);
    $tid = $stmt->fetchColumn();
    if (!$tid && $transportadora) {
        $stmt = $pdo->prepare("INSERT INTO transportadoras (nome) VALUES (?)");
        $stmt->execute([$transportadora]);
        $tid = $pdo->lastInsertId();
    }

    $sql = "UPDATE despachos SET
            nome = ?, data_envio = ?, num_sedex = ?, transportadora = ?, transportadora_id = ?,
            num_nota = ?, anotacao1 = ?, anotacao2 = ?, destino_nome = ?, codigo_rastreio = ?, nota_fiscal = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nome, $data_envio, $num_sedex, $transportadora, $tid,
        $num_nota, $anotacao1, $anotacao2, $nome, $num_sedex, $num_nota, $id
    ]);

    $mensagem = '<div class="alert alert-success">Despacho atualizado com sucesso!</div>';
    $despacho = array_merge($despacho, $_POST);
    $despacho['transportadora_id'] = $tid;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Despacho #<?= $id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-edit"></i> Editando Despacho #<?= $id ?></h4>
                <a href="despacho_detalhe.php?id=<?= $id ?>" class="btn btn-outline-dark btn-sm">Ver Detalhes</a>
            </div>
            <div class="card-body">
                <?= $mensagem ?>
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nome do Cliente</label>
                            <input type="text" name="nome" class="form-control form-control-lg" value="<?= h($despacho['nome']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Data de Envio</label>
                            <input type="date" name="data_envio" class="form-control" value="<?= $despacho['data_envio'] ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-5">
                            <label class="form-label">Código de Rastreio</label>
                            <input type="text" name="num_sedex" class="form-control text-uppercase" value="<?= h($despacho['num_sedex']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Transportadora</label>
                            <input type="text" name="transportadora" class="form-control" list="transportadoras_list" value="<?= h($despacho['transportadora']) ?>">
                            <datalist id="transportadoras_list">
                                <?php foreach ($transportadoras as $t): ?>
                                    <option value="<?= h($t) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Número da Nota</label>
                            <input type="text" name="num_nota" class="form-control" value="<?= h($despacho['num_nota']) ?>">
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">Anotação 1</label>
                            <textarea name="anotacao1" class="form-control" rows="4"><?= h($despacho['anotacao1']) ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Anotação 2</label>
                            <textarea name="anotacao2" class="form-control" rows="4"><?= h($despacho['anotacao2']) ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary btn-lg px-5">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>