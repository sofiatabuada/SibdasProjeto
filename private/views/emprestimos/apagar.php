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
    SELECT em.*, e.designacao, e.codigo_inventario
    FROM emprestimos em JOIN equipamentos e ON em.id_equipamento = e.id
    WHERE em.id = ?
");
$em->execute([$id]);
$em  = $em->fetch(PDO::FETCH_OBJ);
$db  = null;

if (!$em) {
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
                    <p class="mb-1 fs-5">Deseja remover este empréstimo?</p>
                    <h5 class="mb-1"><strong><?= htmlspecialchars($em->designacao) ?></strong></h5>
                    <p class="text-muted mb-4"><?= htmlspecialchars($em->servico_destino) ?> — <?= date('d/m/Y', strtotime($em->data_saida)) ?></p>
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
