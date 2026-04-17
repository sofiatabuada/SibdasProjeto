<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

$equipamentos = $db->query("
    SELECT e.*, l.servico, l.sala,
        (SELECT COUNT(*) FROM documentos d WHERE d.id_equipamento = e.id) as total_docs
    FROM equipamentos e
    LEFT JOIN localizacoes l ON e.id_localizacao = l.id
    WHERE e.deleted_at IS NULL
    ORDER BY e.created_at DESC
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
                        <i class="fa-solid fa-stethoscope me-2" style="color: var(--mt-blue-dark);"></i>Equipamentos
                    </h1>
                    <p class="bo-page-subtitle">Gestão do inventário de equipamentos médicos</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/MediTrack/private/views/etiquetas/imprimir.php" class="btn btn-outline-secondary" target="_blank">
                        <i class="fa-solid fa-tag me-1"></i>Etiquetas
                    </a>
                    <a href="/MediTrack/private/views/exportar/equipamentos_csv.php" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-file-excel me-1"></i>Excel
                    </a>
                    <a href="/MediTrack/private/views/exportar/equipamentos_pdf.php" class="btn btn-outline-secondary" target="_blank">
                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                    </a>
                    <a href="novo.php" class="btn btn-mt-primary">
                        <i class="fa-solid fa-plus me-2"></i>Novo equipamento
                    </a>
                </div>
            </div>

            <div class="bo-card">
                <div class="bo-card-body">
                    <table id="tabela-equipamentos" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Designação</th>
                                <th>Marca / Modelo</th>
                                <th>Serviço</th>
                                <th>Estado</th>
                                <th>Criticidade</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipamentos as $eq): ?>
                                <tr class="<?= $eq->total_docs == 0 ? 'table-warning' : '' ?>">
                                    <td>
                                        <code style="font-size:0.8rem;"><?= htmlspecialchars($eq->codigo_inventario) ?></code>
                                        <?php if ($eq->total_docs == 0): ?>
                                            <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;" title="Sem documentação">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Sem docs
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($eq->designacao) ?></td>
                                    <td>
                                        <span><?= htmlspecialchars($eq->marca ?? '—') ?></span>
                                        <?php if ($eq->modelo): ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($eq->modelo) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($eq->servico ?? '—') ?></td>
                                    <td>
                                        <?php
                                        $estado_classes = [
                                            'ativo'       => 'badge-ativo',
                                            'manutencao'  => 'badge-manutencao',
                                            'inativo'     => 'badge-inativo',
                                            'calibracao'  => 'badge-manutencao',
                                            'quarentena'  => 'badge-manutencao',
                                            'abatido'     => 'badge-inativo',
                                        ];
                                        $estado_labels = [
                                            'ativo'       => 'Ativo',
                                            'manutencao'  => 'Em manutenção',
                                            'inativo'     => 'Inativo',
                                            'calibracao'  => 'Em calibração',
                                            'quarentena'  => 'Em quarentena',
                                            'abatido'     => 'Abatido',
                                        ];
                                        $classe = $estado_classes[$eq->estado] ?? 'badge-inativo';
                                        $label  = $estado_labels[$eq->estado]  ?? $eq->estado;
                                        ?>
                                        <span class="badge-criticidade <?= $classe ?>"><?= $label ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $crit_classes = [
                                            'baixa'        => 'badge-baixa',
                                            'media'        => 'badge-media',
                                            'alta'         => 'badge-alta',
                                            'suporte_vida' => 'badge-suporte',
                                        ];
                                        $crit_labels = [
                                            'baixa'        => 'Baixa',
                                            'media'        => 'Média',
                                            'alta'         => 'Alta',
                                            'suporte_vida' => 'Suporte de vida',
                                        ];
                                        $cc = $crit_classes[$eq->criticidade] ?? 'badge-baixa';
                                        $cl = $crit_labels[$eq->criticidade]  ?? $eq->criticidade;
                                        ?>
                                        <span class="badge-criticidade <?= $cc ?>"><?= $cl ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="detalhes.php?id=<?= aes_encrypt($eq->id) ?>" class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="editar.php?id=<?= aes_encrypt($eq->id) ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <a href="apagar.php?id=<?= aes_encrypt($eq->id) ?>" class="btn btn-sm btn-outline-danger" title="Apagar">
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
                Total: <strong><?= count($equipamentos) ?></strong> equipamento(s)
            </p>

        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-equipamentos').DataTable({
            pageLength: 10,
            pagingType: 'full_numbers',
            language: {
                search: 'Filtrar:',
                info: 'Mostrando _START_ até _END_ de _TOTAL_ registos',
                infoEmpty: 'Sem registos',
                zeroRecords: 'Nenhum equipamento encontrado',
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