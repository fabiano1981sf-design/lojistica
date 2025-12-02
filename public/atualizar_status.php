<?php
// File: public/atualizar_status.php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();
if (!hasPermission('atualizar_rastreio')) {
    die('<div class="alert alert-danger">Acesso negado.</div>');
}

$mensagem = '';
$despacho = null;

if ($_POST && isset($_POST['codigo'])) {
    $codigo = trim($_POST['codigo']);
    $evento = trim($_POST['evento']);
    $localizacao = trim($_POST['localizacao']);

    if ($codigo && $evento) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id, status FROM despachos WHERE codigo_rastreio = ?");
        $stmt->execute([$codigo]);
        $d = $stmt->fetch();

        if ($d) {
            $pdo->prepare("
                INSERT INTO rastreio_historico (despacho_id, evento, localizacao)
                VALUES (?, ?, ?)
            ")->execute([$d['id'], $evento, $localizacao]);

            // Atualizar status do despacho
            if ($_POST['atualizar_status'] ?? false) {
                $novo_status = $_POST['novo_status'];
                $pdo->prepare("UPDATE despachos SET status = ? WHERE id = ?")
                    ->execute([$novo_status, $d['id']]);
            }

            $mensagem = '<div class="alert alert-success">Evento registrado com sucesso!</div>';
            $despacho = $d;
        } else {
            $mensagem = '<div class="alert alert-danger">Código não encontrado.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Scanner de QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        #preview { width: 100%; max-width: 500px; border: 2px solid #0d6efd; border-radius: 8px; }
    </style>
</head>
<body class="bg-light">
    <?php include 'partials/navbar.php'; ?>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4>Scanner de QR Code</h4>
                    </div>
                    <div class="card-body text-center">
                        <?= $mensagem ?>
                        <video id="preview" class="mb-3"></video>
                        <form method="post" id="scannerForm">
                            <input type="hidden" name="codigo" id="codigo">
                            <div class="mb-3">
                                <label class="form-label">Evento</label>
                                <input type="text" name="evento" class="form-control" placeholder="ex: Saiu para entrega" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Localização</label>
                                <input type="text" name="localizacao" class="form-control" placeholder="ex: São Paulo/SP">
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" name="atualizar_status" id="atualizar_status" class="form-check-input">
                                <label class="form-check-label">Atualizar status do despacho</label>
                            </div>
                            <div id="statusSelect" style="display:none;">
                                <select name="novo_status" class="form-select mb-3">
                                    <option value="Em Trânsito">Em Trânsito</option>
                                    <option value="Aguardando Retirada">Aguardando Retirada</option>
                                    <option value="Entregue">Entregue</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">Registrar Evento</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/instascan/1.0.0/instascan.min.js"></script>
    <script>
        const scanner = new Instascan.Scanner({ video: document.getElementById('preview') });
        scanner.addListener('scan', function (content) {
            document.getElementById('codigo').value = content;
            alert('QR Code lido: ' + content);
            scanner.stop();
        });
        Instascan.Camera.getCameras().then(cameras => {
            if (cameras.length > 0) scanner.start(cameras[0]);
            else alert('Nenhuma câmera encontrada.');
        });

        document.getElementById('atualizar_status').addEventListener('change', function() {
            document.getElementById('statusSelect').style.display = this.checked ? 'block' : 'none';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>