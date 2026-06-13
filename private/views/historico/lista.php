<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

// Pagina de auditoria: apenas administradores.
if (($_SESSION['profile'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/private/home.php');
    exit;
}

$db = get_db();
$logs = $db->query("
    SELECT id, data_hora, tipo, utilizador, ip, descricao
    FROM logs
    ORDER BY data_hora DESC, id DESC
")->fetchAll(PDO::FETCH_OBJ);
$db = null;

// Cor do crachá conforme o tipo de evento.
$badges = [
    'LOGIN'       => 'bg-success',
    'LOGIN_FALHA' => 'bg-danger',
    'CRIAR'       => 'bg-primary',
    'EDITAR'      => 'bg-warning text-dark',
    'APAGAR'      => 'bg-danger',
    'ERRO'        => 'bg-dark',
];
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <?php include '../../includes/breadcrumb.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="bo-page-title">
                        <i class="fa-solid fa-clock-rotate-left me-2" style="color: var(--mt-blue-dark);"></i>Histórico de Atividade
                    </h1>
                    <p class="bo-page-subtitle">Registo de eventos do sistema (auditoria)</p>
                </div>
            </div>

            <div class="bo-card">
                <div class="bo-card-body">
                    <table id="tabela-historico" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data / Hora</th>
                                <th>Tipo</th>
                                <th>Utilizador</th>
                                <th>IP</th>
                                <th>Descrição</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td style="white-space:nowrap;"><?= htmlspecialchars($log->data_hora) ?></td>
                                    <td>
                                        <span class="badge <?= $badges[$log->tipo] ?? 'bg-secondary' ?>">
                                            <?= htmlspecialchars($log->tipo) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($log->utilizador ?? '—') ?></td>
                                    <td><?= htmlspecialchars($log->ip ?? '—') ?></td>
                                    <td><?= htmlspecialchars($log->descricao) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-3 text-muted" style="font-size:0.85rem;">
                Total: <strong><?= count($logs) ?></strong> registo(s)
            </p>

        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-historico').DataTable({
            pageLength: 25,
            order: [
                [0, 'desc']
            ],
            pagingType: 'full_numbers',
            language: {
                search: 'Filtrar:',
                info: 'Mostrando _START_ até _END_ de _TOTAL_ registos',
                infoEmpty: 'Sem registos',
                zeroRecords: 'Nenhum registo encontrado',
                paginate: {
                    first: 'Primeira',
                    last: 'Última',
                    next: 'Seguinte',
                    previous: 'Anterior'
                }
            }
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>