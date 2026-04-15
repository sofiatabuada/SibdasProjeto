<?php

// ============================================================
// MediTrack — validacoes.php
// Funções de validação reutilizáveis
// ============================================================

// ------------------------------------------------------------
// Strings
// ------------------------------------------------------------

function validar_obrigatorio(string $valor, string $campo): ?string
{
    if (empty(trim($valor))) {
        return "O campo \"$campo\" é obrigatório.";
    }
    return null;
}

function validar_tamanho(string $valor, string $campo, int $min = 1, int $max = 255): ?string
{
    $len = mb_strlen(trim($valor));
    if ($len < $min) {
        return "O campo \"$campo\" deve ter pelo menos $min caractere(s).";
    }
    if ($len > $max) {
        return "O campo \"$campo\" não pode ter mais de $max caractere(s).";
    }
    return null;
}

// ------------------------------------------------------------
// Email
// ------------------------------------------------------------

function validar_email(string $email, string $campo = 'Email'): ?string
{
    if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        return "O campo \"$campo\" não contém um email válido.";
    }
    return null;
}

// ------------------------------------------------------------
// Números
// ------------------------------------------------------------

function validar_inteiro(string $valor, string $campo, int $min = 0): ?string
{
    if (!is_numeric($valor) || intval($valor) < $min) {
        return "O campo \"$campo\" deve ser um número inteiro igual ou superior a $min.";
    }
    return null;
}

function validar_decimal(string $valor, string $campo, float $min = 0): ?string
{
    if (!is_numeric($valor) || floatval($valor) < $min) {
        return "O campo \"$campo\" deve ser um valor numérico igual ou superior a $min.";
    }
    return null;
}

// ------------------------------------------------------------
// Datas
// ------------------------------------------------------------

function validar_data(string $data, string $campo): ?string
{
    if (empty($data)) return null;
    $d = DateTime::createFromFormat('Y-m-d', $data);
    if (!$d || $d->format('Y-m-d') !== $data) {
        return "O campo \"$campo\" não contém uma data válida (formato: AAAA-MM-DD).";
    }
    return null;
}

function validar_datas_ordem(string $data_inicio, string $data_fim, string $campo_inicio = 'Data de início', string $campo_fim = 'Data de fim'): ?string
{
    if (empty($data_inicio) || empty($data_fim)) return null;
    if ($data_fim < $data_inicio) {
        return "\"$campo_fim\" não pode ser anterior a \"$campo_inicio\".";
    }
    return null;
}

// ------------------------------------------------------------
// Listas controladas (ENUM)
// ------------------------------------------------------------

function validar_enum(string $valor, array $opcoes, string $campo): ?string
{
    if (!in_array($valor, $opcoes, true)) {
        return "O valor do campo \"$campo\" não é válido.";
    }
    return null;
}

// ------------------------------------------------------------
// Upload de ficheiros
// ------------------------------------------------------------

function validar_ficheiro(array $file, array $extensoes_permitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'], int $max_bytes = 10485760): ?string
{
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return null;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "Erro ao fazer upload do ficheiro (código " . $file['error'] . ").";
    }

    $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, $extensoes_permitidas)) {
        $lista = implode(', ', $extensoes_permitidas);
        return "Tipo de ficheiro não permitido. Utilize: $lista.";
    }

    if ($file['size'] > $max_bytes) {
        $mb = round($max_bytes / 1048576);
        return "O ficheiro não pode ter mais de {$mb}MB.";
    }

    return null;
}

// ------------------------------------------------------------
// Encriptação de IDs para URLs (OpenSSL AES-256-CBC)
// Estas funções estão também em funcoes.php — aqui para referência
// ------------------------------------------------------------

function validar_id_enc(string $idEnc): bool
{
    $id = aes_decrypt($idEnc);
    return $id !== false && is_numeric($id) && intval($id) > 0;
}
