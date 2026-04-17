<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$fornecedores = $db->query("
    SELECT nome, nif, tipo, telefone, email, morada, website, pessoa_contacto, telefone_contacto, observacoes, created_at
    FROM fornecedores WHERE deleted_at IS NULL ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);
$db = null;

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_fornecedores_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['Nome', 'NIF', 'Tipo', 'Telefone', 'Email', 'Morada', 'Website', 'Pessoa Contacto', 'Tel. Contacto', 'Observações', 'Data Registo'], ';');

$tipos = ['fabricante' => 'Fabricante', 'distribuidor' => 'Distribuidor', 'assistencia_tecnica' => 'Assistência Técnica', 'consumiveis' => 'Consumíveis', 'outro' => 'Outro'];

foreach ($fornecedores as $f) {
    fputcsv($output, [
        $f['nome'],
        $f['nif'] ?? '',
        $tipos[$f['tipo']] ?? $f['tipo'],
        $f['telefone'] ?? '',
        $f['email'] ?? '',
        $f['morada'] ?? '',
        $f['website'] ?? '',
        $f['pessoa_contacto'] ?? '',
        $f['telefone_contacto'] ?? '',
        $f['observacoes'] ?? '',
        $f['created_at'] ? date('d/m/Y', strtotime($f['created_at'])) : ''
    ], ';');
}
fclose($output);
exit;
