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
    // Desassociar equipamentos desta localização
    $db->prepare("UPDATE equipamentos SET id_localizacao = NULL WHERE id_localizacao = ?")->execute([$id]);
    // Apagar localização
    $db->prepare("DELETE FROM localizacoes WHERE id = ?")->execute([$id]);
    $db = null;
    header('Location: lista.php');
    exit;
} catch (PDOException $e) {
    echo "<p class='text-danger'>Erro: " . $e->getMessage() . "</p>";
    exit;
}
