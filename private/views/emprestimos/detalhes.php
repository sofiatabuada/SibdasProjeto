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
$em = $db->prepare("
    SELECT em.*, e.designacao, e.codigo_inventario, e.id AS eq_id,
           l.servico AS servico_origem, l.sala AS sala_origem
    FROM emprestimos em
    JOIN equipamentos e ON em.id_equipamento = e.id
    LEFT JOIN localizacoes l ON em.id_localizacao_origem = l.id
    WHERE em.id = ?
");
$em->execute([$id]);
$em = $em->fetch(PDO::FETCH_OBJ);
$db = null;

if (!$em) {
    header('Location: lista.php');
    exit;
}

$hoje     = date('Y-m-d');
$devolvido = !empty($em->data_retorno_real);
$atrasado  = !$devolvido && $em->data_retorno_prevista && $em->data_retorno_prevista < $hoje;
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
                        <i class="fa-solid fa-right-left me-2" style="color:var(--mt-blue-dark);"></i>Detalhe do Empréstimo
                    </h1>
                    <p class="bo-page-subtitle"><?= htmlspecialchars($em->designacao) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <?php if (!$devolvido): ?>
                        <a href="devolver.php?id=<?= $idEnc ?>" class="btn-action btn-action-return" title="Devolver" style="width:auto; padding:6px 14px;">
                            <i class="fa-solid fa-rotate-left me-1"></i>Devolver
                        </a>
                    <?php endif; ?>
                    <a href="editar.php?id=<?= $idEnc ?>" class="btn-action btn-action-edit" title="Editar" style="width:auto; padding:6px 14px;">
                        <i class="fa-regular fa-pen-to-square me-1"></i>Editar
                    </a>
                    <a href="lista.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i>Voltar
                    </a>
                </div>
            </div>

            <div class="row g-4">

                <!-- Informação principal -->
                <div class="col-lg-8">
                    <div class="bo-card mb-4">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-arrow-right-arrow-left me-2"></i>Trajecto do Empréstimo</h5>
                        </div>
                        <div class="bo-card-body">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="text-center flex-fill p-3 rounded-3" style="background:#F3F6F9;">
                                    <small class="text-muted d-block mb-1">Origem</small>
                                    <strong><?= htmlspecialchars($em->servico_origem ?? '—') ?></strong>
                                    <?php if ($em->sala_origem): ?>
                                        <small class="text-muted d-block"><?= htmlspecialchars($em->sala_origem) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:1.5rem; color:var(--mt-blue-dark);">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </div>
                                <div class="text-center flex-fill p-3 rounded-3" style="background:#EBF5FB;">
                                    <small class="text-muted d-block mb-1">Emprestado a</small>
                                    <strong><?= htmlspecialchars($em->servico_destino) ?></strong>
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
                                    <small class="text-muted d-block">Responsável</small>
                                    <strong><?= htmlspecialchars($em->responsavel ?? '—') ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Data de Saída</small>
                                    <strong><?= date('d/m/Y', strtotime($em->data_saida)) ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Retorno Previsto</small>
                                    <strong class="<?= $atrasado ? 'text-danger' : '' ?>">
                                        <?= $em->data_retorno_prevista ? date('d/m/Y', strtotime($em->data_retorno_prevista)) : '—' ?>
                                    </strong>
                                </div>
                                <?php if ($devolvido): ?>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Data de Devolução</small>
                                        <strong class="text-success"><?= date('d/m/Y', strtotime($em->data_retorno_real)) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($em->observacoes): ?>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Observações</small>
                                        <strong><?= htmlspecialchars($em->observacoes) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Equipamento + Estado -->
                <div class="col-lg-4">
                    <div class="bo-card mb-4">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-stethoscope me-2"></i>Equipamento</h5>
                        </div>
                        <div class="bo-card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block">Designação</small>
                                <strong><?= htmlspecialchars($em->designacao) ?></strong>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Código</small>
                                <code><?= htmlspecialchars($em->codigo_inventario) ?></code>
                            </div>
                            <a href="/MediTrack/private/views/equipamentos/detalhes.php?id=<?= aes_encrypt($em->eq_id) ?>"
                                class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fa-solid fa-eye me-1"></i>Ver ficha do equipamento
                            </a>
                        </div>
                    </div>

                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-circle-info me-2"></i>Estado</h5>
                        </div>
                        <div class="bo-card-body text-center py-3">
                            <?php if ($devolvido): ?>
                                <span class="badge-criticidade badge-ativo" style="font-size:1rem; padding:8px 20px;">Devolvido</span>
                                <small class="text-muted d-block mt-2"><?= date('d/m/Y', strtotime($em->data_retorno_real)) ?></small>
                            <?php elseif ($atrasado): ?>
                                <span class="badge-criticidade badge-suporte" style="font-size:1rem; padding:8px 20px;">Atrasado</span>
                            <?php else: ?>
                                <span class="badge-criticidade badge-manutencao" style="font-size:1rem; padding:8px 20px;">Em curso</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
