<?php
require_once 'includes/funcoes.php';
start_session();

// Apenas POST é permitido
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../public/login.php');
    return;
}

$username = $_POST['text_username'] ?? '';
$password = $_POST['text_password'] ?? '';

// --------------------------------------------------------------------
// VALIDAÇÃO DOS DADOS
// --------------------------------------------------------------------
$validation_errors = [];

if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
    $validation_errors[] = 'O username tem que ser um email válido.';
}
if (strlen($username) < 5 || strlen($username) > 50) {
    $validation_errors[] = 'O username deve ter entre 5 e 50 caracteres.';
}
if (strlen($password) < 6 || strlen($password) > 20) {
    $validation_errors[] = 'A password deve ter entre 6 e 20 caracteres.';
}

if (!empty($validation_errors)) {
    $_SESSION['validation_errors'] = $validation_errors;
    header('Location: ../public/login.php');
    return;
}

// --------------------------------------------------------------------
// VERIFICAÇÃO NA BASE DE DADOS
// --------------------------------------------------------------------
try {
    $ligacao = new PDO(
        "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Query com AES_DECRYPT para comparar o email encriptado
    $comando = $ligacao->prepare("
        SELECT *, AES_DECRYPT(name, :chave) AS email
        FROM agentes
        WHERE AES_DECRYPT(name, :chave) = :u
        AND deleted_at IS NULL
    ");
    $comando->execute([
        ':chave' => MYSQL_AES_KEY,
        ':u'     => $username
    ]);

    $agente = $comando->fetch(PDO::FETCH_OBJ);

    // Verificar se existe e se a password está correta
    if (!$agente || $password !== $agente->passwrd) {
        $_SESSION['server_error'] = 'Email ou password incorretos.';
        header('Location: ../public/login.php');
        return;
    }

    // Atualizar last_login
    $stmt = $ligacao->prepare("UPDATE agentes SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$agente->id]);

    // Guardar na sessão
    $_SESSION['utilizador'] = $agente->email;
    $_SESSION['profile']    = $agente->profile;
} catch (PDOException $e) {
    $_SESSION['server_error'] = 'Erro ao ligar à base de dados.';
    header('Location: ../public/login.php');
    return;
}

// Redirecionar para o dashboard
header('Location: home.php');
exit;
