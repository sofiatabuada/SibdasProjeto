<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    header('Location: lista.php');
    exit;
}

$idEnc = $_GET['id'] ?? null;
$id    = aes_decrypt($idEnc);
if (!$id || !is_numeric($id)) {
    header('Location: lista.php');
    exit;
}

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servico     = trim($_POST['servico'] ?? '');
    $sala        = trim($_POST['sala'] ?? '');
    $piso        = trim($_POST['piso'] ?? '');
    $edificio    = trim($_POST['edificio'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');

    if (empty($servico)) $erros[] = 'O serviço/departamento é obrigatório.';

    if (empty($erros)) {
        try {
            $db = get_db();
            $stmt = $db->prepare("UPDATE localizacoes SET servico = ?, sala = ?, piso = ?, edificio = ?, observacoes = ? WHERE id = ?");
            $stmt->execute([$servico, $sala ?: null, $piso ?: null, $edificio ?: null, $observacoes ?: null, $id]);
            $db = null;
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao atualizar: ' . $e->getMessage();
        }
    }
}

$db  = get_db();
$loc = $db->prepare("SELECT * FROM localizacoes WHERE id = ?");
$loc->execute([$id]);
$loc = $loc->fetch(PDO::FETCH_OBJ);
$db  = null;
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

            <div class="mb-4">
                <h1 class="bo-page-title">
                    <i class="fa-regular fa-pen-to-square me-2" style="color: var(--mt-blue-dark);"></i>Editar Localização
                </h1>
                <p class="bo-page-subtitle"><?= htmlspecialchars($loc->servico) ?></p>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger rounded-3">
                    <ul class="mb-0"><?php foreach ($erros as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-danger rounded-3"><?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <div class="bo-card">
                <div class="bo-card-body">
                    <form action="editar.php?id=<?= $idEnc ?>" method="post" novalidate>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="bo-form-label">Serviço / Departamento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="servico"
                                    value="<?= htmlspecialchars($_POST['servico'] ?? $loc->servico) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="bo-form-label">Sala / Gabinete</label>
                                <input type="text" class="form-control bo-form-control" name="sala"
                                    value="<?= htmlspecialchars($_POST['sala'] ?? $loc->sala ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Piso</label>
                                <input type="text" class="form-control bo-form-control" name="piso"
                                    value="<?= htmlspecialchars($_POST['piso'] ?? $loc->piso ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Edifício</label>
                                <input type="text" class="form-control bo-form-control" name="edificio"
                                    value="<?= htmlspecialchars($_POST['edificio'] ?? $loc->edificio ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Observações</label>
                                <textarea class="form-control bo-form-control" name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? $loc->observacoes ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-mt-primary">
                                <i class="fa-regular fa-floppy-disk me-1"></i>Guardar alterações
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>