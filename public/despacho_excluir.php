<?php
require '../config/database.php';
require '../config/session.php';
require '../includes/auth.php';

header('Content-Type: application/json');

if (!hasPermission('gerenciar_despachos')) {
    echo json_encode(['success' => false, 'message' => 'Sem permissão']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$id = (int)$_POST['id'];

try {
    $pdo->beginTransaction();

    // Exclui histórico primeiro
    $pdo->prepare("DELETE FROM rastreio_historico WHERE despacho_id = ?")->execute([$id]);
    
    // Depois exclui o despacho
    $stmt = $pdo->prepare("DELETE FROM despachos WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
}
?>