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
$m  = $db->prepare("
    SELECT m.*, e.designacao, e.codigo_inventario, e.id AS eq_id,
           l1.servico AS origem_servico, l1.sala AS origem_sala,
           l2.servico AS destino_servico, l2.sala AS destino_sala
    FROM movimentacoes m
    JOIN equipamentos e ON m.id_equipamento = e.id
    LEFT JOIN localizacoes l1 ON m.id_localizacao_origem = l1.id
    LEFT JOIN localizacoes l2 ON m.id_localizacao_destino = l2.id
    WHERE m.id = ?
");
$m->execute([$id]);
$m = $m->fetch(PDO::FETCH_OBJ);
$db = null;

if (!$m) {
    header('Location: lista.php');
    exit;
}
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
                        <i class="fa-solid fa-route me-2" style="color:var(--mt-blue-dark);"></i>Detalhe da Movimentação
                    </h1>
                    <p class="bo-page-subtitle"><?= date('d/m/Y H:i', strtotime($m->data_movimentacao)) ?></p>
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

                <!-- Trajecto -->
                <div class="col-lg-8">
                    <div class="bo-card mb-4">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-arrow-right-arrow-left me-2"></i>Trajecto</h5>
                        </div>
                        <div class="bo-card-body">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="text-center flex-fill p-3 rounded-3" style="background:#F3F6F9;">
                                    <small class="text-muted d-block mb-1">Origem</small>
                                    <strong><?= htmlspecialchars($m->origem_servico ?? '—') ?></strong>
                                    <?php if ($m->origem_sala): ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($m->origem_sala) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:1.5rem; color:var(--mt-blue-dark);">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </div>
                                <div class="text-center flex-fill p-3 rounded-3" style="background:#EBF5FB;">
                                    <small class="text-muted d-block mb-1">Destino</small>
                                    <strong><?= htmlspecialchars($m->destino_servico ?? '—') ?></strong>
                                    <?php if ($m->destino_sala): ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($m->destino_sala) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-circle-info me-2"></i>Informação</h5>
                        </div>
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Data</small>
                                    <strong><?= date('d/m/Y H:i', strtotime($m->data_movimentacao)) ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Motivo</small>
                                    <strong><?= htmlspecialchars($m->motivo ?? '—') ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Registado por</small>
                                    <strong><?= htmlspecialchars($m->registado_por ?? '—') ?></strong>
                                </div>
                                <?php if ($m->observacoes): ?>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Observações</small>
                                        <strong><?= htmlspecialchars($m->observacoes) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipamento -->
                <div class="col-lg-4">
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-stethoscope me-2"></i>Equipamento</h5>
                        </div>
                        <div class="bo-card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block">Designação</small>
                                <strong><?= htmlspecialchars($m->designacao) ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Código</small>
                                <code><?= htmlspecialchars($m->codigo_inventario) ?></code>
                            </div>
                            <a href="/MediTrack/private/views/equipamentos/detalhes.php?id=<?= aes_encrypt($m->eq_id) ?>"
                                class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fa-solid fa-eye me-1"></i>Ver ficha do equipamento
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
