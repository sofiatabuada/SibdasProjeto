<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$idEnc = $_GET['id'] ?? null;
$id    = aes_decrypt($idEnc);
if (!$id || !is_numeric($id)) {
    header('Location: /MediTrack/private/views/equipamentos/lista.php');
    exit;
}

$db   = get_db();
$comp = $db->prepare("SELECT c.*, e.designacao as eq_nome FROM componentes c JOIN equipamentos e ON c.id_equipamento = e.id WHERE c.id = ?");
$comp->execute([$id]);
$comp = $comp->fetch(PDO::FETCH_OBJ);
if (!$comp) {
    header('Location: /MediTrack/private/views/equipamentos/lista.php');
    exit;
}

// Confirmar apagar directamente
$db->prepare("DELETE FROM componentes WHERE id = ?")->execute([$id]);
$db = null;

$idEncEq = aes_encrypt($comp->id_equipamento);
header('Location: /MediTrack/private/views/equipamentos/detalhes.php?id=' . $idEncEq);
exit;
