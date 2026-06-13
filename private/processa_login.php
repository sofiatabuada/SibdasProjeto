<?php
require_once 'includes/funcoes.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../public/login.php');
    return;
}

$username = $_POST['text_username'] ?? '';
$password = $_POST['text_password'] ?? '';

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

try {
    $ligacao = new PDO(
        MYSQL_DSN,
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

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

    if (!$agente) {
        registar_log('LOGIN_FALHA', 'Email inexistente: ' . $username);
        $_SESSION['server_error'] = 'Email ou password incorretos.';
        header('Location: ../public/login.php');
        return;
    }

    // Validacao da password. As passwords sao guardadas com password_hash().
    // Para registos antigos ainda em texto simples, a password e validada e
    // migrada automaticamente para hash no primeiro login bem-sucedido.
    if (password_get_info($agente->passwrd)['algoName'] !== 'unknown') {
        $password_ok = password_verify($password, $agente->passwrd);
    } else {
        $password_ok = hash_equals((string) $agente->passwrd, $password);
        if ($password_ok) {
            $novo_hash = password_hash($password, PASSWORD_DEFAULT);
            $migra = $ligacao->prepare("UPDATE agentes SET passwrd = ? WHERE id = ?");
            $migra->execute([$novo_hash, $agente->id]);
        }
    }

    if (!$password_ok) {
        registar_log('LOGIN_FALHA', 'Password incorreta: ' . $username);
        $_SESSION['server_error'] = 'Email ou password incorretos.';
        header('Location: ../public/login.php');
        return;
    }

    $stmt = $ligacao->prepare("UPDATE agentes SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$agente->id]);

    $_SESSION['utilizador'] = $agente->email;
    $_SESSION['profile']    = $agente->profile;
    registar_log('LOGIN', 'Login com sucesso: ' . $username);
} catch (PDOException $e) {
    registar_log('ERRO', 'Erro no login: ' . $e->getMessage());
    $_SESSION['server_error'] = 'Erro ao ligar à base de dados.';
    header('Location: ../public/login.php');
    return;
}

header('Location: home.php');
exit;
