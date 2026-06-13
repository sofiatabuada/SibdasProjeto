<?php

require_once __DIR__ . '/../../config/config.php';

function start_session()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function check_session()
{
    return isset($_SESSION['utilizador']);
}

function redirect_if_not_logged($redirect_to = '/public/login.php')
{
    start_session();
    if (!check_session()) {
        header("Location: " . BASE_URL . $redirect_to);
        exit;
    }
}

function logout_and_redirect($redirect_to = '/public/login.php')
{
    start_session();
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL . $redirect_to);
    exit;
}

function aes_encrypt($value)
{
    return bin2hex(openssl_encrypt(
        $value,
        OPENSSL_METHOD,
        OPENSSL_KEY,
        OPENSSL_RAW_DATA,
        OPENSSL_IV
    ));
}

function aes_decrypt($value)
{
    if (!is_string($value) || strlen($value) % 2 !== 0) return false;
    return openssl_decrypt(
        hex2bin($value),
        OPENSSL_METHOD,
        OPENSSL_KEY,
        OPENSSL_RAW_DATA,
        OPENSSL_IV
    );
}

function get_db()
{
    try {
        $pdo = new PDO(
            MYSQL_DSN,
            MYSQL_USERNAME,
            MYSQL_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Erro na ligação à base de dados: " . $e->getMessage());
    }
}

/**
 * Regista um evento do sistema na tabela `logs` da base de dados.
 * Tipos usados: LOGIN, LOGIN_FALHA, CRIAR, EDITAR, APAGAR, ERRO.
 */
function registar_log($tipo, $mensagem)
{
    $utilizador = $_SESSION['utilizador'] ?? 'anonimo';
    $ip         = $_SERVER['REMOTE_ADDR'] ?? '-';

    // O registo de log nunca deve interromper a aplicacao: usa uma ligacao
    // propria e ignora silenciosamente qualquer falha.
    try {
        $db = new PDO(
            MYSQL_DSN,
            MYSQL_USERNAME,
            MYSQL_PASSWORD,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $stmt = $db->prepare(
            "INSERT INTO logs (tipo, utilizador, ip, descricao) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$tipo, $utilizador, $ip, $mensagem]);
        $db = null;
    } catch (PDOException $e) {
        // Falha no registo de log e ignorada de propósito.
    }
}
