<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$fornecedores = $db->query("
    SELECT f.*, COUNT(ef.id_equipamento) as total_equipamentos
    FROM fornecedores f
    LEFT JOIN equipamento_fornecedor ef ON f.id = ef.id_fornecedor
    WHERE f.deleted_at IS NULL
    GROUP BY f.id
    ORDER BY f.nome
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
                        <i class="fa-solid fa-truck-medical me-2" style="color: var(--mt-blue-dark);"></i>Fornecedores
                    </h1>
                    <p class="bo-page-subtitle">Gestão de fabricantes, distribuidores e assistência técnica</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/MediTrack/private/views/exportar/fornecedores_csv.php" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-file-excel me-1"></i>Excel
                    </a>
                    <a href="/MediTrack/private/views/exportar/fornecedores_pdf.php" class="btn btn-outline-secondary" target="_blank">
                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                    </a>
                    <a href="novo.php" class="btn btn-mt-primary">
                        <i class="fa-solid fa-plus me-2"></i>Novo fornecedor
                    </a>
                </div>
            </div>

            <div class="bo-card">
                <div class="bo-card-body">
                    <table id="tabela-fornecedores" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                <th>Pessoa de Contacto</th>
                                <th class="text-center">Equipamentos</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fornecedores as $f): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($f->nome) ?></strong>
                                        <?php if ($f->nif): ?>
                                            <small class="text-muted d-block">NIF: <?= htmlspecialchars($f->nif) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $tipo_labels = [
                                            'fabricante'         => ['label' => 'Fabricante',    'class' => 'badge-ativo'],
                                            'distribuidor'       => ['label' => 'Distribuidor',  'class' => 'badge-media'],
                                            'assistencia_tecnica' => ['label' => 'Assistência',   'class' => 'badge-alta'],
                                            'consumiveis'        => ['label' => 'Consumíveis',   'class' => 'badge-baixa'],
                                            'outro'              => ['label' => 'Outro',         'class' => 'badge-inativo'],
                                        ];
                                        $t = $tipo_labels[$f->tipo] ?? ['label' => $f->tipo, 'class' => 'badge-inativo'];
                                        ?>
                                        <span class="badge-criticidade <?= $t['class'] ?>"><?= $t['label'] ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($f->telefone ?? '—') ?></td>
                                    <td><?= htmlspecialchars($f->email ?? '—') ?></td>
                                    <td><?= htmlspecialchars($f->pessoa_contacto ?? '—') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?= $f->total_equipamentos ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="editar.php?id=<?= aes_encrypt($f->id) ?>" class="btn-action btn-action-edit" title="Editar">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <a href="apagar.php?id=<?= aes_encrypt($f->id) ?>" class="btn-action btn-action-delete" title="Apagar">
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
                Total: <strong><?= count($fornecedores) ?></strong> fornecedor(es)
            </p>

        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-fornecedores').DataTable({
            pageLength: 10,
            pagingType: 'full_numbers',
            language: {
                search: 'Filtrar:',
                info: 'Mostrando _START_ até _END_ de _TOTAL_ registos',
                infoEmpty: 'Sem registos',
                zeroRecords: 'Nenhum fornecedor encontrado',
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