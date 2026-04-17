<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
$db = get_db();
$movs = $db->query("
    SELECT m.data_movimentacao, e.designacao, e.codigo_inventario,
           l1.servico AS origem, l2.servico AS destino,
           m.motivo, m.registado_por, m.observacoes
    FROM movimentacoes m
    JOIN equipamentos e ON m.id_equipamento = e.id
    LEFT JOIN localizacoes l1 ON m.id_localizacao_origem = l1.id
    LEFT JOIN localizacoes l2 ON m.id_localizacao_destino = l2.id
    ORDER BY m.data_movimentacao DESC
")->fetchAll(PDO::FETCH_ASSOC);
$db = null;

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_movimentacoes_' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($out, ['Data', 'Equipamento', 'Código', 'Origem', 'Destino', 'Motivo', 'Registado por', 'Observações'], ';');
foreach ($movs as $m) {
    fputcsv($out, [
        date('d/m/Y H:i', strtotime($m['data_movimentacao'])),
        $m['designacao'],
        $m['codigo_inventario'],
        $m['origem'] ?? '—',
        $m['destino'] ?? '—',
        $m['motivo'] ?? '',
        $m['registado_por'] ?? '',
        $m['observacoes'] ?? ''
    ], ';');
}
fclose($out);
exit;
