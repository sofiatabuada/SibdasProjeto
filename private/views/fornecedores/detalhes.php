<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$idEnc = $_GET['id'] ?? null;
$id    = aes_decrypt($idEnc);
if (!$id || !is_numeric($id)) {
    header('Location: lista.php');
    exit;
}

$db = get_db();
$f  = $db->prepare("SELECT * FROM fornecedores WHERE id = ? AND deleted_at IS NULL");
$f->execute([$id]);
$f  = $f->fetch(PDO::FETCH_OBJ);

if (!$f) {
    header('Location: lista.php');
    exit;
}

$equipamentos = $db->prepare("
    SELECT e.id, e.codigo_inventario, e.designacao, e.estado
    FROM equipamentos e
    JOIN equipamento_fornecedor ef ON e.id = ef.id_equipamento
    WHERE ef.id_fornecedor = ? AND e.deleted_at IS NULL
    ORDER BY e.codigo_inventario
");
$equipamentos->execute([$id]);
$equipamentos = $equipamentos->fetchAll(PDO::FETCH_OBJ);
$db = null;

$tipos = [
    'fabricante'          => 'Fabricante',
    'distribuidor'        => 'Distribuidor',
    'assistencia_tecnica' => 'Assistência Técnica',
    'consumiveis'         => 'Consumíveis',
    'outro'               => 'Outro',
];
$estado_badge = [
    'ativo'      => 'badge-ativo',
    'manutencao' => 'badge-manutencao',
    'inativo'    => 'badge-inativo',
    'calibracao' => 'badge-manutencao',
    'quarentena' => 'badge-manutencao',
    'abatido'    => 'badge-inativo',
];
$estado_label = [
    'ativo'      => 'Ativo',
    'manutencao' => 'Em manutenção',
    'inativo'    => 'Inativo',
    'calibracao' => 'Em calibração',
    'quarentena' => 'Em quarentena',
    'abatido'    => 'Abatido',
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
                        <i class="fa-solid fa-truck-medical me-2" style="color:var(--mt-blue-dark);"></i>Detalhe do Fornecedor
                    </h1>
                    <p class="bo-page-subtitle"><?= htmlspecialchars($f->nome) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="editar.php?id=<?= $idEnc ?>" class="btn-action btn-action-edit" title="Editar" style="width:auto; padding:6px 14px;">
                        <i class="fa-regular fa-pen-to-square me-1"></i>Editar
                    </a>
                    <a href="lista.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i>Voltar
                    </a>
                </div>
            </div>

            <div class="row g-4">

                <!-- Informação do fornecedor -->
                <div class="col-lg-4">
                    <div class="bo-card mb-4">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-info-circle me-2"></i>Informação</h5>
                            <span class="badge-criticidade badge-baixa"><?= $tipos[$f->tipo] ?? ucfirst($f->tipo) ?></span>
                        </div>
                        <div class="bo-card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">Nome</small>
                                <strong><?= htmlspecialchars($f->nome) ?></strong>
                            </div>
                            <?php if ($f->nif): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">NIF</small>
                                    <strong><?= htmlspecialchars($f->nif) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($f->morada): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Morada</small>
                                    <strong><?= htmlspecialchars($f->morada) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($f->observacoes): ?>
                                <div>
                                    <small class="text-muted d-block">Observações</small>
                                    <strong><?= htmlspecialchars($f->observacoes) ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-address-book me-2"></i>Contacto</h5>
                        </div>
                        <div class="bo-card-body">
                            <?php if ($f->telefone): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Telefone</small>
                                    <strong><i class="fa-solid fa-phone me-1 text-muted"></i><?= htmlspecialchars($f->telefone) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($f->email): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Email</small>
                                    <strong><a href="mailto:<?= htmlspecialchars($f->email) ?>"><?= htmlspecialchars($f->email) ?></a></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($f->website): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Website</small>
                                    <strong><a href="<?= htmlspecialchars($f->website) ?>" target="_blank"><?= htmlspecialchars($f->website) ?></a></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($f->pessoa_contacto): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Pessoa de Contacto</small>
                                    <strong><?= htmlspecialchars($f->pessoa_contacto) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($f->telefone_contacto): ?>
                                <div>
                                    <small class="text-muted d-block">Tel. Contacto Directo</small>
                                    <strong><i class="fa-solid fa-phone me-1 text-muted"></i><?= htmlspecialchars($f->telefone_contacto) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if (!$f->telefone && !$f->email && !$f->website && !$f->pessoa_contacto): ?>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem informação de contacto.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Equipamentos associados -->
                <div class="col-lg-8">
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-stethoscope me-2"></i>Equipamentos Associados (<?= count($equipamentos) ?>)</h5>
                        </div>
                        <div class="bo-card-body">
                            <?php if (empty($equipamentos)): ?>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Nenhum equipamento associado a este fornecedor.</p>
                            <?php else: ?>
                                <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Código</th>
                                            <th>Designação</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Ver</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($equipamentos as $eq): ?>
                                            <tr>
                                                <td><code style="font-size:0.78rem;"><?= htmlspecialchars($eq->codigo_inventario) ?></code></td>
                                                <td><?= htmlspecialchars($eq->designacao) ?></td>
                                                <td class="text-center">
                                                    <span class="badge-criticidade <?= $estado_badge[$eq->estado] ?? 'badge-inativo' ?>" style="font-size:0.72rem;">
                                                        <?= $estado_label[$eq->estado] ?? $eq->estado ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="/MediTrack/private/views/equipamentos/detalhes.php?id=<?= aes_encrypt($eq->id) ?>" class="btn-action btn-action-view" title="Ver equipamento">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
