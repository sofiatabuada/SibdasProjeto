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

// Headers para download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_equipamentos_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// BOM para UTF-8 no Excel
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Cabeçalhos
fputcsv($output, [
    'Código Inventário',
    'Designação',
    'Categoria',
    'Marca',
    'Modelo',
    'Número de Série',
    'Fabricante',
    'Data Aquisição',
    'Ano Fabrico',
    'Custo Aquisição (€)',
    'Tipo Entrada',
    'Estado',
    'Criticidade',
    'Serviço',
    'Sala',
    'Piso',
    'Edifício',
    'Observações',
    'Data Registo'
], ';');

$categorias = ['monitorizacao' => 'Monitorização', 'suporte_vida' => 'Suporte de Vida', 'terapia' => 'Terapia', 'diagnostico' => 'Diagnóstico', 'laboratorio' => 'Laboratório', 'esterilizacao' => 'Esterilização', 'reabilitacao' => 'Reabilitação', 'outro' => 'Outro'];
$estados    = ['ativo' => 'Ativo', 'manutencao' => 'Em manutenção', 'inativo' => 'Inativo', 'calibracao' => 'Em calibração', 'quarentena' => 'Em quarentena', 'abatido' => 'Abatido'];
$crits      = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
$tipos      = ['compra' => 'Compra', 'doacao' => 'Doação', 'aluguer' => 'Aluguer', 'emprestimo' => 'Empréstimo'];

foreach ($equipamentos as $eq) {
    fputcsv($output, [
        $eq['codigo_inventario'],
        $eq['designacao'],
        $categorias[$eq['categoria']] ?? $eq['categoria'],
        $eq['marca'] ?? '',
        $eq['modelo'] ?? '',
        $eq['numero_serie'] ?? '',
        $eq['fabricante'] ?? '',
        $eq['data_aquisicao'] ? date('d/m/Y', strtotime($eq['data_aquisicao'])) : '',
        $eq['ano_fabrico'] ?? '',
        $eq['custo_aquisicao'] ? number_format($eq['custo_aquisicao'], 2, ',', '.') : '',
        $tipos[$eq['tipo_entrada']] ?? $eq['tipo_entrada'],
        $estados[$eq['estado']] ?? $eq['estado'],
        $crits[$eq['criticidade']] ?? $eq['criticidade'],
        $eq['servico'] ?? '',
        $eq['sala'] ?? '',
        $eq['piso'] ?? '',
        $eq['edificio'] ?? '',
        $eq['observacoes'] ?? '',
        $eq['created_at'] ? date('d/m/Y', strtotime($eq['created_at'])) : '',
    ], ';');
}

fclose($output);
exit;
