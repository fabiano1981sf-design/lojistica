<?php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

requireLogin();

// ==================== ORDENAÇÃO POR CLIQUE ====================
$colunas_permitidas = [
    'data_envio'     => 'd.data_envio',
    'nome'           => 'd.nome',
    'transportadora' => 'COALESCE(t.nome, d.transportadora)'
];

$sort_default = 'd.data_envio DESC';
$sort = $sort_default;

if (isset($_GET['sort']) && array_key_exists($_GET['sort'], $colunas_permitidas)) {
    $col = $_GET['sort'];
    $dir = (isset($_GET['dir']) && $_GET['dir'] === 'ASC') ? 'ASC' : 'DESC';
    $sort = $colunas_permitidas[$col] . ' ' . $dir;
}

// ==================== FILTROS ====================
$busca  = trim($_GET['busca'] ?? '');
$inicio = $_GET['inicio'] ?? '';
$fim    = $_GET['fim'] ?? '';
$transp = $_GET['transp'] ?? '';

$where = []; $params = [];

if ($busca !== '') {
    $where[] = "(d.nome LIKE ? OR d.num_sedex LIKE ? OR d.num_nota LIKE ? OR d.anotacao1 LIKE ? OR d.anotacao2 LIKE ?)";
    $like = "%$busca%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}
if ($inicio !== '') { $where[] = "d.data_envio >= ?"; $params[] = $inicio; }
if ($fim !== '')    { $where[] = "d.data_envio <= ?"; $params[] = $fim; }
if ($transp !== '') { $where[] = "d.transportadora = ?"; $params[] = $transp; }

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Contagem total
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM despachos d $whereSql");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();

// Paginação
$por_pagina = 50;
$pagina = max(1, (int)($_GET['pag'] ?? 1));
$offset = ($pagina - 1) * $por_pagina;

// Consulta principal
$sql = "SELECT d.*,
               COALESCE(t.nome, d.transportadora) AS transportadora_nome
        FROM despachos d
        LEFT JOIN transportadoras t ON d.transportadora_id = t.id
        $whereSql
        ORDER BY $sort
        LIMIT $offset, $por_pagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$despachos = $stmt->fetchAll();

// Transportadoras para filtro
$transpStmt = $pdo->query("SELECT DISTINCT transportadora FROM despachos WHERE transportadora IS NOT NULL AND transportadora != '' ORDER BY transportadora");
$transportadoras_filtro = $transpStmt->fetchAll(PDO::FETCH_COLUMN);

// Função de seta
function getSeta($coluna) {
    if (!isset($_GET['sort']) || $_GET['sort'] !== $coluna) return '';
    $dir = ($_GET['dir'] ?? 'DESC') === 'DESC' ? '↓' : '↑';
    return " <small class='text-white opacity-75'>$dir</small>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Despachos - Sistema Logística</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background-color: #f8f9fa; }
        th.sortable { cursor: pointer; user-select: none; }
        th.sortable:hover { background-color: #495057 !important; }
        .table-anotacao { max-width: 180px; cursor: pointer; }
        .table-anotacao:hover { background-color: #e9ecef; }
        .btn-outline-danger:hover { background-color: #dc3545; color: white; border-color: #dc3545; }
    </style>
</head>
<body>
    <?php include 'partials/navbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">
                <i class="fas fa-truck text-primary"></i> 
                Despachos 
                <span class="badge bg-dark fs-5"><?= number_format($total) ?></span>
            </h2>
            <?php if (hasPermission('criar_despacho')): ?>
                <a href="despacho_criar.php" class="btn btn-success btn-lg shadow">
                    <i class="fas fa-plus"></i> Novo Despacho
                </a>
            <?php endif; ?>
        </div>

        <!-- FILTROS -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <input type="text" name="busca" class="form-control" placeholder="Buscar..." value="<?= h($busca) ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="inicio" class="form-control" value="<?= h($inicio) ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="fim" class="form-control" value="<?= h($fim) ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="transp" class="form-select">
                            <option value="">Todas Transportadoras</option>
                            <?php foreach ($transportadoras_filtro as $t): ?>
                                <option value="<?= h($t) ?>" <?= $transp === $t ? 'selected' : '' ?>><?= h($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABELA -->
        <div class="card shadow">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="sortable" onclick="ordenar('data_envio')">
                                Data Envio <?= getSeta('data_envio') ?>
                            </th>
                            <th class="sortable" onclick="ordenar('nome')">
                                Cliente <?= getSeta('nome') ?>
                            </th>
                            <th>Cód. Rastreio</th>
                            <th class="sortable" onclick="ordenar('transportadora')">
                                Transportadora <?= getSeta('transportadora') ?>
                            </th>
                            <th>Nº Nota</th>
                            <th>Anotação 1</th>
                            <th>Anotação 2</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($despachos)): ?>
                            <tr><td colspan="9" class="text-center py-5 text-muted fs-4">Nenhum despacho encontrado</td></tr>
                        <?php endif; ?>
                        <?php foreach ($despachos as $d): ?>
                            <tr>
                                <td><strong><?= date('d/m/Y', strtotime($d['data_envio'])) ?></strong></td>
                                <td><strong><?= h($d['nome']) ?></strong></td>
                                <td><?= $d['num_sedex'] ? '<span class="badge bg-primary">'.h($d['num_sedex']).'</span>' : '—' ?></td>
                                <td><?= h($d['transportadora_nome'] ?: $d['transportadora']) ?></td>
                                <td><?= h($d['num_nota']) ?: '—' ?></td>
                                <td class="table-anotacao text-truncate" data-bs-toggle="modal" data-bs-target="#modalAnotacao"
                                    data-titulo="Anotação 1 - <?= h($d['nome']) ?>" data-conteudo="<?= nl2br(h($d['anotacao1'])) ?>">
                                    <?= $d['anotacao1'] ? (strlen($d['anotacao1'])>40 ? h(substr($d['anotacao1'],0,40)).'...' : h($d['anotacao1'])) : '—' ?>
                                </td>
                                <td class="table-anotacao text-truncate" data-bs-toggle="modal" data-bs-target="#modalAnotacao"
                                    data-titulo="Anotação 2 - <?= h($d['nome']) ?>" data-conteudo="<?= nl2br(h($d['anotacao2'])) ?>">
                                    <?= $d['anotacao2'] ? (strlen($d['anotacao2'])>40 ? h(substr($d['anotacao2'],0,40)).'...' : h($d['anotacao2'])) : '—' ?>
                                </td>
                                <td><span class="badge bg-<?= $d['status']=='Entregue'?'success':($d['status']=='Em Trânsito'?'warning':'secondary') ?>"><?= h($d['status']) ?></span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="despacho_detalhe.php?id=<?= $d['id'] ?>" class="btn btn-outline-primary" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (hasPermission('gerenciar_despachos')): ?>
                                            <a href="despacho_editar.php?id=<?= $d['id'] ?>" class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger excluir-despacho" title="Excluir" data-id="<?= $d['id'] ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total > $por_pagina): ?>
                <div class="card-footer bg-white">
                    <?= paginacao($total, $por_pagina, $pagina, $_GET) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL ANOTAÇÃO -->
    <div class="modal fade" id="modalAnotacao" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-sticky-note"></i> <span id="tituloModal">Anotação</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="conteudoModal"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function ordenar(coluna) {
            const url = new URL(window.location);
            const atual = url.searchParams.get('sort');
            let dir = 'DESC';
            if (atual === coluna && (url.searchParams.get('dir') || 'DESC') === 'DESC') dir = 'ASC';
            url.searchParams.set('sort', coluna);
            url.searchParams.set('dir', dir);
            window.location = url;
        }

        // Exclusão com SweetAlert2
        document.querySelectorAll('.excluir-despacho').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Tem certeza?',
                    text: `Você vai excluir o despacho #${id}. Esta ação não pode ser desfeita!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('despacho_excluir.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'id=' + id
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Excluído!', 'O despacho foi removido.', 'success')
                                .then(() => {
                                    this.closest('tr').remove();
                                    const badge = document.querySelector('.badge.bg-dark');
                                    if (badge) {
                                        let num = parseInt(badge.textContent.replace(/\D/g,'')) - 1;
                                        badge.textContent = num.toLocaleString('pt-BR');
                                    }
                                });
                            } else {
                                Swal.fire('Erro!', data.message || 'Não foi possível excluir.', 'error');
                            }
                        });
                    }
                });
            });
        });

        // Modal de anotações
        document.getElementById('modalAnotacao').addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('tituloModal').textContent = btn.getAttribute('data-titulo');
            const conteudo = btn.getAttribute('data-conteudo') || '—';
            document.getElementById('conteudoModal').innerHTML = conteudo === '—' ? '<em class="text-muted">Sem anotação</em>' : conteudo;
        });
    </script>
</body>
</html>

<?php
function paginacao($total, $por_pagina, $pagina, $params = []) {
    $total_paginas = ceil($total / $por_pagina);
    if ($total_paginas <= 1) return '';
    unset($params['pag']);
    $base = '?' . http_build_query($params) . '&pag=';
    $html = '<nav><ul class="pagination justify-content-center mb-0">';
    $html .= $pagina > 1 ? '<li class="page-item"><a class="page-link" href="'.$base.'1">Primeira</a></li>' : '<li class="page-item disabled"><span class="page-link">Primeira</span></li>';
    for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++) {
        $active = $i == $pagina ? 'active' : '';
        $html .= "<li class='page-item $active'><a class='page-link' href='{$base}{$i}'>$i</a></li>";
    }
    $html .= $pagina < $total_paginas ? '<li class="page-item"><a class="page-link" href="'.$base.$total_paginas.'">Última</a></li>' : '<li class="page-item disabled"><span class="page-link">Última</span></li>';
    $html .= '</ul></nav>';
    return $html;
}
?>