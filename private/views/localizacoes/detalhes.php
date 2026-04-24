<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$idEnc = $_GET['id'] ?? null;
$id    = aes_decrypt($idEnc);
if (!$id || !is_numeric($id)) {
    header('Location: lista.php');
    exit;
}

$db  = get_db();
$loc = $db->prepare("SELECT * FROM localizacoes WHERE id = ?");
$loc->execute([$id]);
$loc = $loc->fetch(PDO::FETCH_OBJ);

if (!$loc) {
    header('Location: lista.php');
    exit;
}

$equipamentos = $db->prepare("
    SELECT e.id, e.codigo_inventario, e.designacao, e.estado, e.criticidade
    FROM equipamentos e
    WHERE e.id_localizacao = ? AND e.deleted_at IS NULL
    ORDER BY e.codigo_inventario
");
$equipamentos->execute([$id]);
$equipamentos = $equipamentos->fetchAll(PDO::FETCH_OBJ);
$db = null;

$estado_badge = [
    'ativo'       => 'badge-ativo',
    'manutencao'  => 'badge-manutencao',
    'inativo'     => 'badge-inativo',
    'calibracao'  => 'badge-manutencao',
    'quarentena'  => 'badge-manutencao',
    'abatido'     => 'badge-inativo',
];
$estado_label = [
    'ativo'       => 'Ativo',
    'manutencao'  => 'Em manutenção',
    'inativo'     => 'Inativo',
    'calibracao'  => 'Em calibração',
    'quarentena'  => 'Em quarentena',
    'abatido'     => 'Abatido',
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
                        <i class="fa-solid fa-location-dot me-2" style="color:var(--mt-blue-dark);"></i>Detalhe da Localização
                    </h1>
                    <p class="bo-page-subtitle"><?= htmlspecialchars($loc->servico) ?></p>
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

                <!-- Informação da localização -->
                <div class="col-lg-4">
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-info-circle me-2"></i>Informação</h5>
                        </div>
                        <div class="bo-card-body">
                            <div class="mb-3">
                                <small class="text-muted d-block">Serviço / Departamento</small>
                                <strong><?= htmlspecialchars($loc->servico) ?></strong>
                            </div>
                            <?php if ($loc->sala): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Sala / Gabinete</small>
                                    <strong><?= htmlspecialchars($loc->sala) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($loc->piso): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Piso</small>
                                    <strong><?= htmlspecialchars($loc->piso) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($loc->edificio): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Edifício</small>
                                    <strong><?= htmlspecialchars($loc->edificio) ?></strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($loc->observacoes): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Observações</small>
                                    <strong><?= htmlspecialchars($loc->observacoes) ?></strong>
                                </div>
                            <?php endif; ?>
                            <div>
                                <small class="text-muted d-block">Total de equipamentos</small>
                                <strong><?= count($equipamentos) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipamentos nesta localização -->
                <div class="col-lg-8">
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-stethoscope me-2"></i>Equipamentos (<?= count($equipamentos) ?>)</h5>
                        </div>
                        <div class="bo-card-body">
                            <?php if (empty($equipamentos)): ?>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Nenhum equipamento nesta localização.</p>
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
