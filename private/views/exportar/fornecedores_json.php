<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$fornecedores = $db->query("
    SELECT nome, nif, tipo, telefone, email, morada, website, pessoa_contacto, telefone_contacto, observacoes, created_at
    FROM fornecedores WHERE deleted_at IS NULL ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);
$db = null;

$tipos = ['fabricante' => 'Fabricante', 'distribuidor' => 'Distribuidor', 'assistencia_tecnica' => 'Assistência Técnica', 'consumiveis' => 'Consumíveis', 'outro' => 'Outro'];

$dados = [];
foreach ($fornecedores as $f) {
    $dados[] = [
        'nome'              => $f['nome'],
        'nif'               => $f['nif'],
        'tipo'              => $tipos[$f['tipo']] ?? $f['tipo'],
        'telefone'          => $f['telefone'],
        'email'             => $f['email'],
        'morada'            => $f['morada'],
        'website'           => $f['website'],
        'pessoa_contacto'   => $f['pessoa_contacto'],
        'telefone_contacto' => $f['telefone_contacto'],
        'observacoes'       => $f['observacoes'],
        'data_registo'      => $f['created_at'],
    ];
}

$resultado = [
    'exportado_em' => date('Y-m-d H:i:s'),
    'total'        => count($dados),
    'fornecedores' => $dados,
];

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_fornecedores_' . date('Y-m-d') . '.json"');

echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
