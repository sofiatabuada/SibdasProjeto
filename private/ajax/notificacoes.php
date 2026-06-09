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
        'url'   => '/MediTrack/private/views/garantias/lista.php',
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
        'url'   => '/MediTrack/private/views/garantias/lista.php',
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
            'url'   => '/MediTrack/private/views/backoffice/mensagens.php',
        ];
    }
}

$db = null;

echo json_encode([
    'total' => array_sum(array_column($items, 'count')),
    'items' => $items,
]);
