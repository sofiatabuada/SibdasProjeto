<?php
require_once __DIR__ . '/../../includes/funcoes.php';
header('Content-Type: application/json');

if (!check_session()) {
    echo json_encode(['success' => false, 'erro' => 'Não autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'erro' => 'Método inválido.']);
    exit;
}

$nome            = trim($_POST['nome'] ?? '');
$nif             = trim($_POST['nif'] ?? '');
$tipo            = $_POST['tipo'] ?? 'outro';
$telefone        = trim($_POST['telefone'] ?? '');
$email           = trim($_POST['email'] ?? '');
$website         = trim($_POST['website'] ?? '');
$morada          = trim($_POST['morada'] ?? '');
$pessoa_contacto = trim($_POST['pessoa_contacto'] ?? '');
$tel_contacto    = trim($_POST['telefone_contacto'] ?? '');
$observacoes     = trim($_POST['observacoes'] ?? '');

if (empty($nome)) {
    echo json_encode(['success' => false, 'erro' => 'O nome é obrigatório.']);
    exit;
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'erro' => 'O email não é válido.']);
    exit;
}

try {
    $db = get_db();
    $stmt = $db->prepare("
        INSERT INTO fornecedores (nome, nif, tipo, telefone, email, morada, website, pessoa_contacto, telefone_contacto, observacoes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $nome, $nif ?: null, $tipo,
        $telefone ?: null, $email ?: null, $morada ?: null,
        $website ?: null, $pessoa_contacto ?: null, $tel_contacto ?: null, $observacoes ?: null
    ]);
    $id = $db->lastInsertId();
    $db = null;

    echo json_encode(['success' => true, 'id' => $id, 'nome' => $nome]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'erro' => 'Erro ao guardar: ' . $e->getMessage()]);
}
