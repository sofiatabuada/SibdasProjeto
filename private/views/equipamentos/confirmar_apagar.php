<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$idEnc = $_GET['id'] ?? null;
$id    = aes_decrypt($idEnc);

if (!$id || !is_numeric($id)) {
    header('Location: lista.php');
    exit;
}

try {
    $db = get_db();
    $stmt = $db->prepare("UPDATE equipamentos SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    registar_log('APAGAR', 'Equipamento apagado: id=' . $id);
    $db = null;
    header('Location: lista.php');
    exit;
} catch (PDOException $e) {
    registar_log('ERRO', 'Erro ao apagar equipamento id=' . $id . ': ' . $e->getMessage());
    echo "<p class='text-danger'>Erro: " . $e->getMessage() . "</p>";
    exit;
}
