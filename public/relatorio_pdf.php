<?php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';
requireLogin();

use Dompdf\Dompdf;
use Dompdf\Options;

require_once '../vendor/autoload.php'; // <- IMPORTANTE: tem o Dompdf instalado?

// ==================== MESMO FILTRO DO RELATÓRIO ====================
$primeiroStmt = $pdo->query("SELECT MIN(data_envio) FROM despachos WHERE data_envio IS NOT NULL");
$primeira_data = $primeiroStmt->fetchColumn() ?: '2023-01-01';
$hoje = date('Y-m-d');

$inicio = $_GET['inicio'] ?? $primeira_data;
$fim    = $_GET['fim'] ?? $hoje;
$status = $_GET['status'] ?? '';
$transp = $_GET['transp'] ?? '';

$where = ["d.data_envio BETWEEN ? AND ?"];
$params = [$inicio, $fim];
if ($status !== '') { $where[] = "d.status = ?"; $params[] = $status; }
if ($transp !== '') { $where[] = "d.transportadora = ?"; $params[] = $transp; }
$whereSql = 'WHERE ' . implode(' AND ', $where);

// Estatísticas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM despachos d $whereSql");
$stmt->execute($params);
$total_periodo = $stmt->fetchColumn();

$total_geral = $pdo->query("SELECT COUNT(*) FROM despachos")->fetchColumn();
$transportadoras_cadastradas = $pdo->query("SELECT COUNT(*) FROM transportadoras")->fetchColumn();

// Dados dos gráficos (mesmo código do relatorios.php)
$stmt = $pdo->prepare("SELECT COALESCE(t.nome, d.transportadora) AS nome, COUNT(*) AS total FROM despachos d LEFT JOIN transportadoras t ON d.transportadora_id = t.id $whereSql GROUP BY COALESCE(t.nome, d.transportadora) ORDER BY total DESC");
$stmt->execute($params);
$dados_transp = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT d.nome, COUNT(*) AS total FROM despachos d $whereSql GROUP BY d.nome ORDER BY total DESC LIMIT 10");
$stmt->execute($params);
$top_clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COALESCE(t.nome, d.transportadora) AS nome, COUNT(*) AS total FROM despachos d LEFT JOIN transportadoras t ON d.transportadora_id = t.id $whereSql GROUP BY COALESCE(t.nome, d.transportadora) ORDER BY total DESC LIMIT 10");
$stmt->execute($params);
$top_transp = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Todos os despachos para a tabela
$stmt = $pdo->prepare("SELECT d.*, COALESCE(t.nome, d.transportadora) AS transportadora_nome FROM despachos d LEFT JOIN transportadoras t ON d.transportadora_id = t.id $whereSql ORDER BY d.data_envio DESC");
$stmt->execute($params);
$despachos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Despachos - PDF</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1, h2, h3 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .stats { display: flex; justify-content: space-around; margin: 30px 0; }
        .stat-box { text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px; width: 30%; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RELATÓRIO DE DESPACHOS</h1>
        <p><strong>Período:</strong> <?= date('d/m/Y', strtotime($inicio)) ?> até <?= date('d/m/Y', strtotime($fim)) ?></p>
        <p><strong>Gerado em:</strong> <?= date('d/m/Y \à\s H:i') ?> | Total no período: <strong><?= number_format($total_periodo) ?></strong></p>
    </div>

    <div class="stats">
        <div class="stat-box">
            <h3><?= number_format($total_periodo) ?></h3>
            <p>Despachos no Período</p>
        </div>
        <div class="stat-box">
            <h3><?= number_format($total_geral) ?></h3>
            <p>Total Geral</p>
        </div>
        <div class="stat-box">
            <h3><?= $transportadoras_cadastradas ?></h3>
            <p>Transportadoras</p>
        </div>
    </div>

    <h2>Despachos por Transportadora</h2>
    <table>
        <tr><th>Transportadora</th><th>Total</th></tr>
        <?php foreach ($dados_transp as $t): ?>
            <tr><td><?= htmlspecialchars($t['nome'] ?: 'Sem transportadora') ?></td><td><?= $t['total'] ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h2>Top 10 Clientes</h2>
    <table>
        <tr><th>Cliente</th><th>Despachos</th></tr>
        <?php foreach ($top_clientes as $c): ?>
            <tr><td><?= htmlspecialchars($c['nome']) ?></td><td><?= $c['total'] ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h2>Top 10 Transportadoras Mais Usadas</h2>
    <table>
        <tr><th>Transportadora</th><th>Total</th></tr>
        <?php foreach ($top_transp as $t): ?>
            <tr><td><?= htmlspecialchars($t['nome']) ?></td><td><?= $t['total'] ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h2>Todos os Despachos do Período</h2>
    <table>
        <thead>
            <tr>
                <th>Data</th><th>Cliente</th><th>Cód. Rastreio</th><th>Transportadora</th><th>Nota</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($despachos as $d): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($d['data_envio'])) ?></td>
                    <td><?= htmlspecialchars($d['nome']) ?></td>
                    <td><?= htmlspecialchars($d['num_sedex']) ?></td>
                    <td><?= htmlspecialchars($d['transportadora_nome'] ?: $d['transportadora']) ?></td>
                    <td><?= htmlspecialchars($d['num_nota']) ?></td>
                    <td><?= htmlspecialchars($d['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Relatório gerado pelo Sistema de Logística • Página <span class="pageNumber"></span>
    </div>
</body>
</html>

<?php
// =============== GERA O PDF ===============
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml(ob_get_clean());
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Nome do arquivo
$filename = "Relatorio_Despachos_" . date('d-m-Y') . ".pdf";
$dompdf->stream($filename, ['Attachment' => true]);
?>