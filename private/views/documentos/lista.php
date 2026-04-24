<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$documentos = $db->query("
    SELECT d.*, e.designacao, e.codigo_inventario
    FROM documentos d
    JOIN equipamentos e ON d.id_equipamento = e.id
    WHERE e.deleted_at IS NULL
    ORDER BY d.created_at DESC
")->fetchAll(PDO::FETCH_OBJ);
$db = null;

$tipo_labels = [
    'manual_utilizador'      => 'Manual de Utilizador',
    'manual_servico'         => 'Manual de Serviço',
    'certificado_calibracao' => 'Certificado de Calibração',
    'contrato_manutencao'    => 'Contrato de Manutenção',
    'fatura'                 => 'Fatura',
    'declaracao_conformidade' => 'Declaração de Conformidade',
    'relatorio_tecnico'      => 'Relatório Técnico',
    'outro'                  => 'Outro',
];
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
                        <i class="fa-solid fa-folder-open me-2" style="color: var(--mt-blue-dark);"></i>Documentos
                    </h1>
                    <p class="bo-page-subtitle">Gestão da documentação técnica associada aos equipamentos</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/MediTrack/private/views/exportar/documentos_csv.php" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-file-excel me-1"></i>Excel
                    </a>
                    <a href="/MediTrack/private/views/exportar/documentos_pdf.php" class="btn btn-outline-secondary" target="_blank">
                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                    </a>
                    <a href="novo.php" class="btn btn-mt-primary">
                        <i class="fa-solid fa-plus me-2"></i>Novo documento
                    </a>
                </div>
            </div>

            <div class="bo-card">
                <div class="bo-card-body">
                    <table id="tabela-documentos" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nome</th>
                                <th>Tipo</th>
                                <th>Equipamento</th>
                                <th>Data</th>
                                <th>Validade</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentos as $doc): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($doc->nome) ?></strong>
                                        <?php if ($doc->ficheiro): ?>
                                            <small class="text-muted d-block">
                                                <i class="fa-solid fa-paperclip me-1"></i><?= htmlspecialchars($doc->ficheiro) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge-criticidade badge-baixa" style="font-size:0.75rem;">
                                            <?= $tipo_labels[$doc->tipo] ?? ucfirst($doc->tipo) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($doc->designacao) ?>
                                        <small class="text-muted d-block"><code><?= htmlspecialchars($doc->codigo_inventario) ?></code></small>
                                    </td>
                                    <td><?= $doc->data_documento ? date('d/m/Y', strtotime($doc->data_documento)) : '—' ?></td>
                                    <td>
                                        <?php if ($doc->data_validade): ?>
                                            <?php $expirado = strtotime($doc->data_validade) < time(); ?>
                                            <span class="<?= $expirado ? 'text-danger fw-semibold' : '' ?>">
                                                <?= date('d/m/Y', strtotime($doc->data_validade)) ?>
                                                <?= $expirado ? '<i class="fa-solid fa-circle-exclamation ms-1"></i>' : '' ?>
                                            </span>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="detalhes.php?id=<?= aes_encrypt($doc->id) ?>" class="btn-action btn-action-view" title="Ver detalhes">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="editar.php?id=<?= aes_encrypt($doc->id) ?>" class="btn-action btn-action-edit" title="Editar">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <a href="apagar.php?id=<?= aes_encrypt($doc->id) ?>" class="btn-action btn-action-delete" title="Apagar">
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
                Total: <strong><?= count($documentos) ?></strong> documento(s)
            </p>

        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-documentos').DataTable({
            pageLength: 10,
            pagingType: 'full_numbers',
            language: {
                search: 'Filtrar:',
                info: 'Mostrando _START_ até _END_ de _TOTAL_ registos',
                infoEmpty: 'Sem registos',
                zeroRecords: 'Nenhum documento encontrado',
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