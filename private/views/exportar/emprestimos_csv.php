<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();
$db = get_db();
$emps = $db->query("
    SELECT em.data_saida, em.data_retorno_prevista, em.data_retorno_real,
           e.designacao, e.codigo_inventario,
           l.servico AS origem, em.servico_destino,
           em.responsavel, em.observacoes
    FROM emprestimos em
    JOIN equipamentos e ON em.id_equipamento = e.id
    LEFT JOIN localizacoes l ON em.id_localizacao_origem = l.id
    ORDER BY em.data_saida DESC
")->fetchAll(PDO::FETCH_ASSOC);
$db = null;

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="meditrack_emprestimos_' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($out, ['Equipamento', 'Código', 'Serviço Origem', 'Emprestado a', 'Responsável', 'Data Saída', 'Retorno Previsto', 'Retorno Real', 'Estado', 'Observações'], ';');
foreach ($emps as $em) {
    $estado = $em['data_retorno_real'] ? 'Devolvido' : ($em['data_retorno_prevista'] && $em['data_retorno_prevista'] < date('Y-m-d') ? 'Atrasado' : 'Em curso');
    fputcsv($out, [
        $em['designacao'],
        $em['codigo_inventario'],
        $em['origem'] ?? '—',
        $em['servico_destino'],
        $em['responsavel'] ?? '',
        date('d/m/Y', strtotime($em['data_saida'])),
        $em['data_retorno_prevista'] ? date('d/m/Y', strtotime($em['data_retorno_prevista'])) : '',
        $em['data_retorno_real'] ? date('d/m/Y', strtotime($em['data_retorno_real'])) : '',
        $estado,
        $em['observacoes'] ?? ''
    ], ';');
}
fclose($out);
exit;
