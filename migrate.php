<?php
// migrate.php - Migração segura de tb_controle → despachos
require 'config/database.php';
require 'includes/auth.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    die("Acesso negado. Apenas admin.");
}

// === BUSCAR DADOS ANTIGOS ===
$stmt_old = $pdo->query("SELECT * FROM tb_controle ORDER BY id");
$antigos = $stmt_old->fetchAll(PDO::FETCH_ASSOC);

if (empty($antigos)) {
    die("Nenhum dado antigo encontrado em tb_controle.");
}

echo "<pre>Migrando " . count($antigos) . " registros...\n";

$success = 0;
$errors = [];

$pdo->beginTransaction();

try {
    foreach ($antigos as $old) {
        // === MAPEAMENTO DE CAMPOS ===
        $codigo_rastreio = trim($old['num_sedex'] ?? '');
        $destino_nome    = trim($old['nome'] ?? '');
        $nota_fiscal     = trim($old['num_nota'] ?? '');
        $anotacao        = trim(($old['anotacao1'] ?? '') . "\n" . ($old['anotacao2'] ?? ''));
        $data_criacao    = $old['data_envio'] ?? date('Y-m-d H:i:s');

        // === BUSCAR transportadora_id ===
        $transportadora_nome = trim($old['transportadora'] ?? '');
        $transportadora_id = null;

        if ($transportadora_nome) {
            // Tenta encontrar por nome
            $stmt = $pdo->prepare("SELECT id FROM transportadoras WHERE nome = ?");
            $stmt->execute([$transportadora_nome]);
            $tid = $stmt->fetchColumn();

            if (!$tid) {
                // Cria nova transportadora se não existir
                $stmt = $pdo->prepare("INSERT INTO transportadoras (nome) VALUES (?)");
                $stmt->execute([$transportadora_nome]);
                $tid = $pdo->lastInsertId();
                echo "Nova transportadora criada: $transportadora_nome (ID: $tid)\n";
            }
            $transportadora_id = $tid;
        }

        // === INSERIR NO NOVO BANCO ===
        $sql = "
            INSERT INTO despachos 
            (codigo_rastreio, data_criacao, destino_nome, nota_fiscal, 
             transportadora_id, transportadora, anotacao, despachante_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Em Processamento')
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $codigo_rastreio,
            $data_criacao,
            $destino_nome,
            $nota_fiscal,
            $transportadora_id,
            $transportadora_nome,
            $anotacao,
            $_SESSION['user_id'] ?? 1, // despachante = admin
        ]);

        $success++;
    }

    $pdo->commit();
    echo "\n\nSUCESSO! $success registros migrados com sucesso!\n";
    echo "Acesse: http://localhost/logistica/public/index.php\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERRO: " . $e->getMessage() . "\n";
    print_r($errors);
}
?>