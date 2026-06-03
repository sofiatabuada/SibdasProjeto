<?php
require_once __DIR__ . '/../../includes/funcoes.php';
header('Content-Type: application/json');

start_session();
if (!check_session()) {
    echo json_encode(['success' => false, 'erro' => 'Não autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'erro' => 'Método inválido.']);
    exit;
}

$servico     = trim($_POST['servico'] ?? '');
$sala        = trim($_POST['sala'] ?? '');
$piso        = trim($_POST['piso'] ?? '');
$edificio    = trim($_POST['edificio'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

if (empty($servico)) {
    echo json_encode(['success' => false, 'erro' => 'O serviço/departamento é obrigatório.']);
    exit;
}

try {
    $db = get_db();
    $stmt = $db->prepare("INSERT INTO localizacoes (servico, sala, piso, edificio, observacoes) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$servico, $sala ?: null, $piso ?: null, $edificio ?: null, $observacoes ?: null]);
    $id = $db->lastInsertId();
    $db = null;

    $label = $servico . ($sala ? ' — ' . $sala : '');
    echo json_encode(['success' => true, 'id' => $id, 'label' => $label]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'erro' => 'Erro ao guardar: ' . $e->getMessage()]);
}
