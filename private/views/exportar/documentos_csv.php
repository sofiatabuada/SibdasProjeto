<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
$db = get_db();
$docs = $db->query("
    SELECT d.nome, d.tipo, d.data_documento, d.data_validade, d.ficheiro,
           e.codigo_inventario, e.designacao, d.observacoes
    FROM documentos d JOIN equipamentos e ON d.id_equipamento = e.id
    WHERE e.deleted_at IS NULL ORDER BY e.codigo_inventario
")->fetchAll(PDO::FETCH_ASSOC);
$db = null;

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_documentos_' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($out, ['Equipamento', 'Código', 'Nome Documento', 'Tipo', 'Data Documento', 'Data Validade', 'Ficheiro', 'Observações'], ';');
$tipos = ['manual_utilizador' => 'Manual Utilizador', 'manual_servico' => 'Manual Serviço', 'certificado_calibracao' => 'Cert. Calibração', 'contrato_manutencao' => 'Contrato Manutenção', 'fatura' => 'Fatura', 'declaracao_conformidade' => 'Decl. Conformidade', 'relatorio_tecnico' => 'Relatório Técnico', 'outro' => 'Outro'];
foreach ($docs as $d) {
    fputcsv($out, [
        $d['designacao'],
        $d['codigo_inventario'],
        $d['nome'],
        $tipos[$d['tipo']] ?? $d['tipo'],
        $d['data_documento'] ? date('d/m/Y', strtotime($d['data_documento'])) : '',
        $d['data_validade']  ? date('d/m/Y', strtotime($d['data_validade']))  : '',
        $d['ficheiro'] ?? '',
        $d['observacoes'] ?? ''
    ], ';');
}
fclose($out);
exit;
