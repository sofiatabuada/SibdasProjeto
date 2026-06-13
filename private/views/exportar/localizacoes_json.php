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

$dados = [];
foreach ($localizacoes as $l) {
    $dados[] = [
        'servico'            => $l['servico'],
        'sala'               => $l['sala'],
        'piso'               => $l['piso'],
        'edificio'           => $l['edificio'],
        'total_equipamentos' => (int) $l['total_equipamentos'],
        'observacoes'        => $l['observacoes'],
    ];
}

$resultado = [
    'exportado_em' => date('Y-m-d H:i:s'),
    'total'        => count($dados),
    'localizacoes' => $dados,
];

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_localizacoes_' . date('Y-m-d') . '.json"');

echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;