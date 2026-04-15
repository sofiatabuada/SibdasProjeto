<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$idEnc = $_GET['id'] ?? null;
$id    = aes_decrypt($idEnc);
if (!$id || !is_numeric($id)) {
    header('Location: /MediTrack/private/views/equipamentos/lista.php');
    exit;
}

$db   = get_db();
$comp = $db->prepare("SELECT c.*, e.designacao as eq_designacao, e.codigo_inventario FROM componentes c JOIN equipamentos e ON c.id_equipamento = e.id WHERE c.id = ?");
$comp->execute([$id]);
$comp = $comp->fetch(PDO::FETCH_OBJ);
if (!$comp) {
    header('Location: /MediTrack/private/views/equipamentos/lista.php');
    exit;
}
$idEncEq = aes_encrypt($comp->id_equipamento);

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $designacao  = trim($_POST['designacao'] ?? '');
    $codigo      = trim($_POST['codigo'] ?? '');
    $quantidade  = intval($_POST['quantidade'] ?? 1);
    $num_serie   = trim($_POST['numero_serie'] ?? '');
    $estado      = $_POST['estado'] ?? 'ativo';
    $observacoes = trim($_POST['observacoes'] ?? '');

    if (empty($designacao)) $erros[] = 'A designação é obrigatória.';

    if (empty($erros)) {
        try {
            $stmt = $db->prepare("
                UPDATE componentes SET codigo=?, designacao=?, quantidade=?, numero_serie=?, estado=?, observacoes=?
                WHERE id=?
            ");
            $stmt->execute([$codigo ?: null, $designacao, $quantidade, $num_serie ?: null, $estado, $observacoes ?: null, $id]);
            $db = null;
            header('Location: /MediTrack/private/views/equipamentos/detalhes.php?id=' . $idEncEq);
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro: ' . $e->getMessage();
        }
    }
}
$db = null;
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>
        <main class="col-md-9 col-lg-10 bo-content">

            <div class="mb-4">
                <h1 class="bo-page-title">
                    <i class="fa-regular fa-pen-to-square me-2" style="color: var(--mt-blue-dark);"></i>Editar Componente
                </h1>
                <p class="bo-page-subtitle">
                    Equipamento: <strong><?= htmlspecialchars($comp->eq_designacao) ?></strong>
                    <code class="ms-2"><?= htmlspecialchars($comp->codigo_inventario) ?></code>
                </p>
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
                            <div class="col-md-3">
                                <label class="bo-form-label">Código</label>
                                <input type="text" class="form-control bo-form-control" name="codigo"
                                    value="<?= htmlspecialchars($_POST['codigo'] ?? $comp->codigo ?? '') ?>">
                            </div>
                            <div class="col-md-7">
                                <label class="bo-form-label">Designação <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="designacao"
                                    value="<?= htmlspecialchars($_POST['designacao'] ?? $comp->designacao) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="bo-form-label">Quantidade</label>
                                <input type="number" class="form-control bo-form-control" name="quantidade"
                                    value="<?= htmlspecialchars($_POST['quantidade'] ?? $comp->quantidade) ?>" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Número de Série</label>
                                <input type="text" class="form-control bo-form-control" name="numero_serie"
                                    value="<?= htmlspecialchars($_POST['numero_serie'] ?? $comp->numero_serie ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Estado</label>
                                <select class="form-select bo-form-control" name="estado">
                                    <?php
                                    $estados = ['ativo' => 'Ativo', 'inativo' => 'Inativo', 'substituido' => 'Substituído'];
                                    $est_atual = $_POST['estado'] ?? $comp->estado;
                                    foreach ($estados as $val => $label):
                                    ?><option value="<?= $val ?>" <?= $est_atual == $val ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Observações</label>
                                <textarea class="form-control bo-form-control" name="observacoes" rows="2"><?= htmlspecialchars($_POST['observacoes'] ?? $comp->observacoes ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="/MediTrack/private/views/equipamentos/detalhes.php?id=<?= $idEncEq ?>" class="btn btn-outline-secondary">
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