<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$idEnc = $_GET['id'] ?? null;
$id    = aes_decrypt($idEnc);
if (!$id || !is_numeric($id)) {
    header('Location: lista.php');
    exit;
}

$db  = get_db();
$doc = $db->prepare("SELECT ficheiro, nome FROM documentos WHERE id = ?");
$doc->execute([$id]);
$doc = $doc->fetch(PDO::FETCH_OBJ);
$db  = null;

if (!$doc || !$doc->ficheiro) {
    header('Location: lista.php');
    exit;
}

$caminho = __DIR__ . '/../../uploads/' . basename($doc->ficheiro);

if (!file_exists($caminho)) {
    header('Location: lista.php');
    exit;
}

$mime        = mime_content_type($caminho) ?: 'application/octet-stream';
$disposicao  = isset($_GET['dl']) ? 'attachment' : 'inline';
header('Content-Type: ' . $mime);
header('Content-Disposition: ' . $disposicao . '; filename="' . addslashes($doc->ficheiro) . '"');
header('Content-Length: ' . filesize($caminho));
readfile($caminho);
exit;
