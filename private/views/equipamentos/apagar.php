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
$eq  = $db->prepare("SELECT codigo_inventario, designacao, marca FROM equipamentos WHERE id = ? AND deleted_at IS NULL");
$eq->execute([$id]);
$eq  = $eq->fetch(PDO::FETCH_OBJ);
$db  = null;

if (!$eq) {
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
                    <p class="mb-1 fs-5">Deseja desativar este equipamento?</p>
                    <h4 class="mb-1"><strong><?= htmlspecialchars($eq->designacao) ?></strong></h4>
                    <p class="text-muted mb-4" style="font-size:0.9rem;">
                        <code><?= htmlspecialchars($eq->codigo_inventario) ?></code>
                        <?php if ($eq->marca): ?> — <?= htmlspecialchars($eq->marca) ?><?php endif; ?>
                    </p>
                    <p class="text-muted small mb-4">O equipamento não será eliminado, apenas marcado como inativo no sistema.</p>
                    <div class="d-flex justify-content-center gap-3">
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