<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido.']);
    exit;
}

$nome        = trim($_POST['nome'] ?? '');
$instituicao = trim($_POST['instituicao'] ?? '');
$email       = trim($_POST['email'] ?? '');
$telefone    = trim($_POST['telefone'] ?? '');
$assunto     = trim($_POST['assunto'] ?? '');
$mensagem    = trim($_POST['mensagem'] ?? '');

// Validações
if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Por favor preencha todos os campos obrigatórios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Por favor introduza um email válido.']);
    exit;
}

try {
    $db = new PDO(
        "mysql:host=" . MYSQL_HOST . ";port=" . MYSQL_PORT . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $db->prepare("
        INSERT INTO contactos (nome, instituicao, email, telefone, assunto, mensagem)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $nome,
        $instituicao ?: null,
        $email,
        $telefone ?: null,
        $assunto,
        $mensagem
    ]);

    echo json_encode(['sucesso' => true, 'mensagem' => 'Mensagem enviada com sucesso! Entraremos em contacto brevemente.']);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao guardar a mensagem. Tente novamente.']);
}
