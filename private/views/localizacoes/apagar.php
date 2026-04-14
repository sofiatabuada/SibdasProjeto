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
$loc = $db->prepare("SELECT servico, sala FROM localizacoes WHERE id = ?");
$loc->execute([$id]);
$loc = $loc->fetch(PDO::FETCH_OBJ);

// Verificar se tem equipamentos associados
$total = $db->prepare("SELECT COUNT(*) FROM equipamentos WHERE id_localizacao = ? AND deleted_at IS NULL");
$total->execute([$id]);
$total = $total->fetchColumn();
$db = null;

if (!$loc) {
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
            <div class="d-flex justify-content-center mt-4">
                <div class="card border-0 shadow-sm rounded-4 text-center p-5" style="max-width:500px; width:100%;">
                    <div class="text-warning display-4 mb-3">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <p class="mb-1 fs-5">Deseja remover esta localização?</p>
                    <h4 class="mb-1"><strong><?= htmlspecialchars($loc->servico) ?></strong></h4>
                    <?php if ($loc->sala): ?>
                        <p class="text-muted mb-2" style="font-size:0.9rem;"><?= htmlspecialchars($loc->sala) ?></p>
                    <?php endif; ?>
                    <?php if ($total > 0): ?>
                        <div class="alert alert-warning rounded-3 mt-3 text-start" style="font-size:0.88rem;">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Esta localização tem <strong><?= $total ?></strong> equipamento(s) associado(s).
                            Ao remover, os equipamentos ficarão sem localização definida.
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-center gap-3 mt-3">
                        <a href="lista.php" class="btn btn-outline-secondary px-4">
                            <i class="fa-solid fa-xmark me-1"></i>Cancelar
                        </a>
                        <a href="confirmar_apagar.php?id=<?= urlencode($idEnc) ?>" class="btn btn-danger px-4">
                            <i class="fa-solid fa-check me-1"></i>Confirmar
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>