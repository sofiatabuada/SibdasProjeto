<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$localizacoes = $db->query("
    SELECT l.edificio, l.piso, l.servico, l.sala, l.observacoes,
           COUNT(e.id) as total_equipamentos
    FROM localizacoes l
    LEFT JOIN equipamentos e ON e.id_localizacao = l.id AND e.deleted_at IS NULL
    GROUP BY l.id ORDER BY l.servico
")->fetchAll(PDO::FETCH_ASSOC);
$db = null;

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_localizacoes_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['Serviço', 'Sala', 'Piso', 'Edifício', 'Total Equipamentos', 'Observações'], ';');

foreach ($localizacoes as $l) {
    fputcsv($output, [
        $l['servico'],
        $l['sala'] ?? '',
        $l['piso'] ?? '',
        $l['edificio'] ?? '',
        $l['total_equipamentos'],
        $l['observacoes'] ?? ''
    ], ';');
}
fclose($output);
exit;
