<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$localizacoes = $db->query("
    SELECT l.*, COUNT(e.id) as total_equipamentos
    FROM localizacoes l
    LEFT JOIN equipamentos e ON e.id_localizacao = l.id AND e.deleted_at IS NULL
    GROUP BY l.id
    ORDER BY l.servico
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
                        <i class="fa-solid fa-location-dot me-2" style="color: var(--mt-blue-dark);"></i>Localizações
                    </h1>
                    <p class="bo-page-subtitle">Gestão das localizações físicas dos equipamentos</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/MediTrack/private/views/exportar/localizacoes_csv.php" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-file-excel me-1"></i>Excel
                    </a>
                    <a href="/MediTrack/private/views/exportar/localizacoes_pdf.php" class="btn btn-outline-secondary" target="_blank">
                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                    </a>
                    <a href="novo.php" class="btn btn-mt-primary">
                        <i class="fa-solid fa-plus me-2"></i>Nova localização
                    </a>
                </div>
            </div>

            <div class="bo-card">
                <div class="bo-card-body">
                    <table id="tabela-localizacoes" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Serviço / Departamento</th>
                                <th>Sala / Gabinete</th>
                                <th>Piso</th>
                                <th>Edifício</th>
                                <th class="text-center">Equipamentos</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($localizacoes as $loc): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($loc->servico) ?></strong></td>
                                    <td><?= htmlspecialchars($loc->sala ?? '—') ?></td>
                                    <td><?= htmlspecialchars($loc->piso ?? '—') ?></td>
                                    <td><?= htmlspecialchars($loc->edificio ?? '—') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?= $loc->total_equipamentos ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="editar.php?id=<?= aes_encrypt($loc->id) ?>" class="btn-action btn-action-edit" title="Editar">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <a href="apagar.php?id=<?= aes_encrypt($loc->id) ?>" class="btn-action btn-action-delete" title="Apagar">
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

            <p class="mt-3 text-muted" style="font-size:0.85rem;">
                Total: <strong><?= count($localizacoes) ?></strong> localização(ões)
            </p>

        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-localizacoes').DataTable({
            pageLength: 10,
            pagingType: 'full_numbers',
            language: {
                search: 'Filtrar:',
                info: 'Mostrando _START_ até _END_ de _TOTAL_ registos',
                infoEmpty: 'Sem registos',
                zeroRecords: 'Nenhuma localização encontrada',
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