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
    $db->prepare("UPDATE emprestimos SET data_retorno_real = NOW() WHERE id = ?")->execute([$id]);
    $db = null;
} catch (PDOException $e) {
}

header('Location: lista.php');
exit;
