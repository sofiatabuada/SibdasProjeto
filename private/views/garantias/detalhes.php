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
$g  = $db->prepare("
    SELECT g.*, e.designacao, e.codigo_inventario, e.id AS eq_id
    FROM garantias g
    JOIN equipamentos e ON g.id_equipamento = e.id
    WHERE g.id = ?
");
$g->execute([$id]);
$g = $g->fetch(PDO::FETCH_OBJ);
$db = null;

if (!$g) {
    header('Location: lista.php');
    exit;
}

$hoje     = date('Y-m-d');
$expirada = $g->data_fim && $g->data_fim < $hoje;
$ativa    = $g->data_fim && $g->data_fim >= $hoje;
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
                        <i class="fa-solid fa-file-signature me-2" style="color:var(--mt-blue-dark);"></i>Detalhe da Garantia
                    </h1>
                    <p class="bo-page-subtitle"><?= htmlspecialchars($g->designacao) ?></p>
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

                <!-- Equipamento -->
                <div class="col-lg-4">
                    <div class="bo-card mb-4">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-stethoscope me-2"></i>Equipamento</h5>
                        </div>
                        <div class="bo-card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block">Designação</small>
                                <strong><?= htmlspecialchars($g->designacao) ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Código</small>
                                <code><?= htmlspecialchars($g->codigo_inventario) ?></code>
                            </div>
                            <a href="/MediTrack/private/views/equipamentos/detalhes.php?id=<?= aes_encrypt($g->eq_id) ?>"
                                class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fa-solid fa-eye me-1"></i>Ver ficha do equipamento
                            </a>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-circle-info me-2"></i>Estado</h5>
                        </div>
                        <div class="bo-card-body text-center py-3">
                            <?php if ($expirada): ?>
                                <span class="badge-criticidade badge-suporte" style="font-size:1rem; padding:8px 20px;">Expirada</span>
                            <?php elseif ($ativa): ?>
                                <span class="badge-criticidade badge-ativo" style="font-size:1rem; padding:8px 20px;">Ativa</span>
                            <?php else: ?>
                                <span class="badge-criticidade badge-inativo" style="font-size:1rem; padding:8px 20px;">Sem data</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Detalhes da garantia -->
                <div class="col-lg-8">
                    <div class="bo-card mb-4">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-calendar me-2"></i>Período de Garantia</h5>
                        </div>
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Início</small>
                                    <strong><?= $g->data_inicio ? date('d/m/Y', strtotime($g->data_inicio)) : '—' ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Fim</small>
                                    <strong class="<?= $expirada ? 'text-danger' : '' ?>">
                                        <?= $g->data_fim ? date('d/m/Y', strtotime($g->data_fim)) : '—' ?>
                                        <?php if ($expirada): ?>
                                            <span class="badge bg-danger ms-1" style="font-size:0.7rem;">Expirada</span>
                                        <?php endif; ?>
                                    </strong>
                                </div>
                                <?php if ($g->observacoes): ?>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Observações</small>
                                        <strong><?= htmlspecialchars($g->observacoes) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-file-contract me-2"></i>Contrato de Manutenção</h5>
                        </div>
                        <div class="bo-card-body">
                            <?php if ($g->tem_contrato): ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Tipo de Contrato</small>
                                        <strong><?= htmlspecialchars($g->tipo_contrato ?? '—') ?></strong>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Periodicidade</small>
                                        <strong><?= htmlspecialchars($g->periodicidade ?? '—') ?></strong>
                                    </div>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Entidade Responsável</small>
                                        <strong><?= htmlspecialchars($g->entidade_responsavel ?? '—') ?></strong>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem contrato de manutenção associado.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
