<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

$garantias = $db->query("
    SELECT g.*, e.designacao, e.codigo_inventario
    FROM garantias g
    JOIN equipamentos e ON g.id_equipamento = e.id
    WHERE e.deleted_at IS NULL
    ORDER BY g.data_fim ASC
")->fetchAll(PDO::FETCH_OBJ);

$db = null;

$hoje = new DateTime();
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
                        <i class="fa-solid fa-file-signature me-2" style="color: var(--mt-blue-dark);"></i>Garantias e Contratos
                    </h1>
                    <p class="bo-page-subtitle">Gestão de garantias e contratos de manutenção</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/MediTrack/private/views/exportar/garantias_csv.php" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-file-excel me-1"></i>Exportar Excel
                    </a>
                    <a href="novo.php" class="btn btn-mt-primary">
                        <i class="fa-solid fa-plus me-2"></i>Nova garantia
                    </a>
                </div>
            </div>

            <div class="bo-card">
                <div class="bo-card-body">
                    <table id="tabela-garantias" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Equipamento</th>
                                <th>Início</th>
                                <th>Fim</th>
                                <th>Estado</th>
                                <th>Contrato</th>
                                <th>Entidade Responsável</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($garantias as $g): ?>
                                <?php
                                $expirada   = $g->data_fim && new DateTime($g->data_fim) < $hoje;
                                $expira_em  = $g->data_fim ? (new DateTime($g->data_fim))->diff($hoje)->days : null;
                                $expira_30  = $g->data_fim && !$expirada && $expira_em <= 30;
                                $row_class  = $expirada ? 'table-danger' : ($expira_30 ? 'table-warning' : '');
                                ?>
                                <tr class="<?= $row_class ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($g->designacao) ?></strong>
                                        <small class="text-muted d-block"><code><?= htmlspecialchars($g->codigo_inventario) ?></code></small>
                                    </td>
                                    <td><?= $g->data_inicio ? date('d/m/Y', strtotime($g->data_inicio)) : '—' ?></td>
                                    <td><?= $g->data_fim ? date('d/m/Y', strtotime($g->data_fim)) : '—' ?></td>
                                    <td>
                                        <?php if ($expirada): ?>
                                            <span class="badge-criticidade badge-suporte">Expirada</span>
                                        <?php elseif ($expira_30): ?>
                                            <span class="badge-criticidade badge-alta">Expira em <?= $expira_em ?> dias</span>
                                        <?php elseif ($g->data_fim): ?>
                                            <span class="badge-criticidade badge-ativo">Ativa</span>
                                        <?php else: ?>
                                            <span class="badge-criticidade badge-inativo">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($g->tem_contrato): ?>
                                            <span class="badge-criticidade badge-ativo">
                                                <i class="fa-solid fa-check me-1"></i>Sim
                                            </span>
                                            <?php if ($g->tipo_contrato): ?>
                                                <small class="text-muted d-block"><?= htmlspecialchars($g->tipo_contrato) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge-criticidade badge-inativo">Não</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($g->entidade_responsavel ?? '—') ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="editar.php?id=<?= aes_encrypt($g->id) ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <a href="apagar.php?id=<?= aes_encrypt($g->id) ?>" class="btn btn-sm btn-outline-danger" title="Apagar">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex gap-3 mt-3" style="font-size:0.82rem;">
                <span><span class="badge bg-danger me-1">&nbsp;</span> Garantia expirada</span>
                <span><span class="badge bg-warning text-dark me-1">&nbsp;</span> Expira em 30 dias</span>
                <span><span class="badge bg-light text-dark border me-1">&nbsp;</span> Ativa</span>
            </div>

        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-garantias').DataTable({
            pageLength: 10,
            pagingType: 'full_numbers',
            language: {
                search: 'Filtrar:',
                info: 'Mostrando _START_ até _END_ de _TOTAL_ registos',
                infoEmpty: 'Sem registos',
                zeroRecords: 'Nenhuma garantia encontrada',
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