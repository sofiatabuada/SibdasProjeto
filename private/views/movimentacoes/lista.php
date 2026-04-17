<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

$movimentacoes = $db->query("
    SELECT m.*, e.designacao, e.codigo_inventario,
           l1.servico AS loc_origem_servico,
           l2.servico AS loc_destino_servico
    FROM movimentacoes m
    JOIN equipamentos e ON m.id_equipamento = e.id
    LEFT JOIN localizacoes l1 ON m.id_localizacao_origem = l1.id
    LEFT JOIN localizacoes l2 ON m.id_localizacao_destino = l2.id
    ORDER BY m.data_movimentacao DESC
")->fetchAll(PDO::FETCH_OBJ);

$db = null;
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="bo-page-title">
                        <i class="fa-solid fa-route me-2" style="color: var(--mt-blue-dark);"></i>Histórico de Movimentações
                    </h1>
                    <p class="bo-page-subtitle">Registo de todas as movimentações de equipamentos entre localizações</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/MediTrack/private/views/exportar/fornecedores_csv.php" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-file-excel me-1"></i>Exportar Excel
                    </a>
                    <a href="novo.php" class="btn btn-mt-primary">
                        <i class="fa-solid fa-plus me-2"></i>Registar movimentação
                    </a>
                </div>
            </div>

            <div class="bo-card">
                <div class="bo-card-body">
                    <table id="tabela-movimentacoes" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Equipamento</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Motivo</th>
                                <th>Registado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimentacoes as $m): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($m->data_movimentacao)) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($m->designacao) ?></strong>
                                        <small class="text-muted d-block"><code><?= htmlspecialchars($m->codigo_inventario) ?></code></small>
                                    </td>
                                    <td>
                                        <?php if ($m->loc_origem_servico): ?>
                                            <span class="badge-criticidade badge-inativo"><?= htmlspecialchars($m->loc_origem_servico) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge-criticidade badge-ativo"><?= htmlspecialchars($m->loc_destino_servico ?? '—') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($m->motivo ?? '—') ?></td>
                                    <td><?= htmlspecialchars($m->registado_por ?? '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-movimentacoes').DataTable({
            pageLength: 15,
            order: [
                [0, 'desc']
            ],
            language: {
                search: 'Filtrar:',
                info: 'Mostrando _START_ até _END_ de _TOTAL_ registos',
                infoEmpty: 'Sem registos',
                zeroRecords: 'Nenhuma movimentação encontrada',
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