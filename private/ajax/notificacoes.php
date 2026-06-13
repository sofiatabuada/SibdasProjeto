<?php
require_once __DIR__ . '/../includes/funcoes.php';
redirect_if_not_logged();

header('Content-Type: application/json; charset=utf-8');

$db      = get_db();
$is_admin = ($_SESSION['profile'] ?? '') === 'admin';
$items   = [];

// Garantias expiradas
$exp = (int) $db->query("
    SELECT COUNT(*) FROM garantias g
    JOIN equipamentos e ON e.id = g.id_equipamento
    WHERE g.data_fim < CURDATE()
    AND e.deleted_at IS NULL
")->fetchColumn();

if ($exp > 0) {
    $items[] = [
        'tipo'  => 'garantia_expirada',
        'count' => $exp,
        'label' => $exp === 1 ? '1 garantia expirada' : "$exp garantias expiradas",
        'icon'  => 'fa-triangle-exclamation',
        'cor'   => 'danger',
        'url'   => BASE_URL . '/private/views/garantias/lista.php',
    ];
}

// Garantias a expirar em 30 dias
$prox = (int) $db->query("
    SELECT COUNT(*) FROM garantias g
    JOIN equipamentos e ON e.id = g.id_equipamento
    WHERE g.data_fim >= CURDATE()
    AND g.data_fim <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    AND e.deleted_at IS NULL
")->fetchColumn();

if ($prox > 0) {
    $items[] = [
        'tipo'  => 'garantia_30dias',
        'count' => $prox,
        'label' => $prox === 1 ? '1 garantia expira em 30 dias' : "$prox garantias expiram em 30 dias",
        'icon'  => 'fa-clock',
        'cor'   => 'warning',
        'url'   => BASE_URL . '/private/views/garantias/lista.php',
    ];
}

// Manutenções agendadas com início nos próximos 7 dias
$man_prox = (int) $db->query("
    SELECT COUNT(*) FROM manutencoes m
    JOIN equipamentos e ON e.id = m.id_equipamento
    WHERE m.estado = 'agendada'
    AND m.data_inicio IS NOT NULL
    AND m.data_inicio >= CURDATE()
    AND m.data_inicio <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND e.deleted_at IS NULL
")->fetchColumn();

if ($man_prox > 0) {
    $items[] = [
        'tipo'  => 'manutencao_proxima',
        'count' => $man_prox,
        'label' => $man_prox === 1 ? '1 manutenção agendada nos próximos 7 dias' : "$man_prox manutenções agendadas nos próximos 7 dias",
        'icon'  => 'fa-wrench',
        'cor'   => 'warning',
        'url'   => BASE_URL . '/private/views/manutencoes/lista.php?estado=agendada',
    ];
}

// Manutenções em curso com data de fim ultrapassada
$man_atras = (int) $db->query("
    SELECT COUNT(*) FROM manutencoes m
    JOIN equipamentos e ON e.id = m.id_equipamento
    WHERE m.estado = 'em_curso'
    AND m.data_fim IS NOT NULL
    AND m.data_fim < CURDATE()
    AND e.deleted_at IS NULL
")->fetchColumn();

if ($man_atras > 0) {
    $items[] = [
        'tipo'  => 'manutencao_atrasada',
        'count' => $man_atras,
        'label' => $man_atras === 1 ? '1 manutenção em curso atrasada' : "$man_atras manutenções em curso atrasadas",
        'icon'  => 'fa-circle-exclamation',
        'cor'   => 'danger',
        'url'   => BASE_URL . '/private/views/manutencoes/lista.php?estado=em_curso',
    ];
}

// Mensagens não lidas (apenas admin)
if ($is_admin) {
    $msgs = (int) $db->query("SELECT COUNT(*) FROM contactos WHERE lido = 0")->fetchColumn();
    if ($msgs > 0) {
        $items[] = [
            'tipo'  => 'mensagem',
            'count' => $msgs,
            'label' => $msgs === 1 ? '1 mensagem não lida' : "$msgs mensagens não lidas",
            'icon'  => 'fa-envelope',
            'cor'   => 'info',
            'url'   => BASE_URL . '/private/views/backoffice/mensagens.php',
        ];
    }
}

$db = null;

echo json_encode([
    'total' => array_sum(array_column($items, 'count')),
    'items' => $items,
]);
