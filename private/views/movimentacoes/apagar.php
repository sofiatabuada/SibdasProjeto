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
    SELECT m.*, e.designacao, e.codigo_inventario
    FROM movimentacoes m JOIN equipamentos e ON m.id_equipamento = e.id
    WHERE m.id = ?
");
$m->execute([$id]);
$m  = $m->fetch(PDO::FETCH_OBJ);
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
            <div class="d-flex justify-content-center mt-4">
                <div class="card border-0 shadow-sm rounded-4 text-center p-5" style="max-width:500px; width:100%;">
                    <div class="text-warning display-4 mb-3"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <p class="mb-1 fs-5">Deseja remover esta movimentação?</p>
                    <h5 class="mb-1"><strong><?= htmlspecialchars($m->designacao) ?></strong></h5>
                    <p class="text-muted mb-4"><?= date('d/m/Y H:i', strtotime($m->data_movimentacao)) ?></p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="lista.php" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-xmark me-1"></i>Cancelar</a>
                        <a href="confirmar_apagar.php?id=<?= urlencode($idEnc) ?>" class="btn btn-danger px-4"><i class="fa-solid fa-check me-1"></i>Confirmar</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
