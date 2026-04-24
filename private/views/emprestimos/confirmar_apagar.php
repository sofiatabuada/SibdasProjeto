<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$idEnc = $_GET['id'] ?? null;
$id    = aes_decrypt($idEnc);
if (!$id || !is_numeric($id)) {
    header('Location: lista.php');
    exit;
}

$db = get_db();
$db->prepare("DELETE FROM emprestimos WHERE id = ?")->execute([$id]);
$db = null;

header('Location: lista.php');
exit;
