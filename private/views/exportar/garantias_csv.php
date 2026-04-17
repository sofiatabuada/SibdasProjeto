<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
$db = get_db();
$garantias = $db->query("
    SELECT g.*, e.designacao, e.codigo_inventario
    FROM garantias g JOIN equipamentos e ON g.id_equipamento = e.id
    WHERE e.deleted_at IS NULL ORDER BY g.data_fim ASC
")->fetchAll(PDO::FETCH_ASSOC);
$db = null;

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_garantias_' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($out, ['Equipamento', 'Código', 'Início Garantia', 'Fim Garantia', 'Estado', 'Tem Contrato', 'Tipo Contrato', 'Entidade', 'Periodicidade', 'Observações'], ';');
$hoje = date('Y-m-d');
foreach ($garantias as $g) {
    $estado = $g['data_fim'] ? ($g['data_fim'] < $hoje ? 'Expirada' : 'Ativa') : '—';
    fputcsv($out, [
        $g['designacao'],
        $g['codigo_inventario'],
        $g['data_inicio'] ? date('d/m/Y', strtotime($g['data_inicio'])) : '',
        $g['data_fim']    ? date('d/m/Y', strtotime($g['data_fim']))    : '',
        $estado,
        $g['tem_contrato'] ? 'Sim' : 'Não',
        $g['tipo_contrato'] ?? '',
        $g['entidade_responsavel'] ?? '',
        $g['periodicidade'] ?? '',
        $g['observacoes'] ?? ''
    ], ';');
}
fclose($out);
exit;
