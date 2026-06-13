<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

$equipamentos = $db->query("
    SELECT e.codigo_inventario, e.designacao, e.categoria, e.marca, e.modelo,
           e.numero_serie, e.fabricante, e.data_aquisicao, e.ano_fabrico,
           e.custo_aquisicao, e.tipo_entrada, e.estado, e.criticidade,
           l.servico, l.sala, l.piso, l.edificio, e.observacoes, e.created_at
    FROM equipamentos e
    LEFT JOIN localizacoes l ON e.id_localizacao = l.id
    WHERE e.deleted_at IS NULL
    ORDER BY e.codigo_inventario
")->fetchAll(PDO::FETCH_ASSOC);

$db = null;

// Traduções dos valores codificados para texto legível.
$categorias = ['monitorizacao' => 'Monitorização', 'suporte_vida' => 'Suporte de Vida', 'terapia' => 'Terapia', 'diagnostico' => 'Diagnóstico', 'laboratorio' => 'Laboratório', 'esterilizacao' => 'Esterilização', 'reabilitacao' => 'Reabilitação', 'outro' => 'Outro'];
$estados    = ['ativo' => 'Ativo', 'manutencao' => 'Em manutenção', 'inativo' => 'Inativo', 'calibracao' => 'Em calibração', 'quarentena' => 'Em quarentena', 'abatido' => 'Abatido'];
$crits      = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
$tipos      = ['compra' => 'Compra', 'doacao' => 'Doação', 'aluguer' => 'Aluguer', 'emprestimo' => 'Empréstimo'];

$dados = [];
foreach ($equipamentos as $eq) {
    $dados[] = [
        'codigo_inventario' => $eq['codigo_inventario'],
        'designacao'        => $eq['designacao'],
        'categoria'         => $categorias[$eq['categoria']] ?? $eq['categoria'],
        'marca'             => $eq['marca'],
        'modelo'            => $eq['modelo'],
        'numero_serie'      => $eq['numero_serie'],
        'fabricante'        => $eq['fabricante'],
        'data_aquisicao'    => $eq['data_aquisicao'],
        'ano_fabrico'       => $eq['ano_fabrico'] !== null ? (int) $eq['ano_fabrico'] : null,
        'custo_aquisicao'   => $eq['custo_aquisicao'] !== null ? (float) $eq['custo_aquisicao'] : null,
        'tipo_entrada'      => $tipos[$eq['tipo_entrada']] ?? $eq['tipo_entrada'],
        'estado'            => $estados[$eq['estado']] ?? $eq['estado'],
        'criticidade'       => $crits[$eq['criticidade']] ?? $eq['criticidade'],
        'localizacao'       => [
            'servico'  => $eq['servico'],
            'sala'     => $eq['sala'],
            'piso'     => $eq['piso'],
            'edificio' => $eq['edificio'],
        ],
        'observacoes'       => $eq['observacoes'],
        'data_registo'      => $eq['created_at'],
    ];
}

$resultado = [
    'exportado_em' => date('Y-m-d H:i:s'),
    'total'        => count($dados),
    'equipamentos' => $dados,
];

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_equipamentos_' . date('Y-m-d') . '.json"');

echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;