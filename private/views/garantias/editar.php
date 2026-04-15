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

$db = get_db();
$equipamentos = $db->query("SELECT id, designacao, codigo_inventario FROM equipamentos WHERE deleted_at IS NULL ORDER BY designacao")->fetchAll(PDO::FETCH_OBJ);

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento      = $_POST['id_equipamento'] ?? '';
    $data_inicio         = $_POST['data_inicio'] ?? '';
    $data_fim            = $_POST['data_fim'] ?? '';
    $tem_contrato        = isset($_POST['tem_contrato']) ? 1 : 0;
    $tipo_contrato       = trim($_POST['tipo_contrato'] ?? '');
    $entidade_responsavel = trim($_POST['entidade_responsavel'] ?? '');
    $periodicidade       = trim($_POST['periodicidade'] ?? '');
    $observacoes         = trim($_POST['observacoes'] ?? '');

    if (empty($id_equipamento)) $erros[] = 'O equipamento é obrigatório.';
    if (!empty($data_inicio) && !empty($data_fim) && $data_fim < $data_inicio)
        $erros[] = 'A data de fim não pode ser anterior à data de início.';

    if (empty($erros)) {
        try {
            $stmt = $db->prepare("
                UPDATE garantias SET
                    id_equipamento = ?, data_inicio = ?, data_fim = ?, tem_contrato = ?,
                    tipo_contrato = ?, entidade_responsavel = ?, periodicidade = ?, observacoes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $id_equipamento,
                $data_inicio ?: null,
                $data_fim ?: null,
                $tem_contrato,
                $tipo_contrato ?: null,
                $entidade_responsavel ?: null,
                $periodicidade ?: null,
                $observacoes ?: null,
                $id
            ]);
            $db = null;
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro: ' . $e->getMessage();
        }
    }
}

$g = $db->prepare("SELECT * FROM garantias WHERE id = ?");
$g->execute([$id]);
$g = $g->fetch(PDO::FETCH_OBJ);
$db = null;
if (!$g) {
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
                    <i class="fa-regular fa-pen-to-square me-2" style="color: var(--mt-blue-dark);"></i>Editar Garantia / Contrato
                </h1>
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

                        <h5 class="mb-3" style="font-family: var(--font-display);">Equipamento</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="bo-form-label">Equipamento <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="id_equipamento">
                                    <?php
                                    $eq_atual = $_POST['id_equipamento'] ?? $g->id_equipamento;
                                    foreach ($equipamentos as $eq):
                                    ?><option value="<?= $eq->id ?>" <?= $eq_atual == $eq->id ? 'selected' : '' ?>>[<?= htmlspecialchars($eq->codigo_inventario) ?>] <?= htmlspecialchars($eq->designacao) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Período de Garantia</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="bo-form-label">Data de Início</label>
                                <input type="text" class="form-control bo-form-control" name="data_inicio" id="data_inicio"
                                    value="<?= htmlspecialchars($_POST['data_inicio'] ?? ($g->data_inicio ? date('Y-m-d', strtotime($g->data_inicio)) : '')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data de Fim</label>
                                <input type="text" class="form-control bo-form-control" name="data_fim" id="data_fim"
                                    value="<?= htmlspecialchars($_POST['data_fim'] ?? ($g->data_fim ? date('Y-m-d', strtotime($g->data_fim)) : '')) ?>">
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Contrato de Manutenção</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tem_contrato" id="tem_contrato"
                                        <?= (isset($_POST['tem_contrato']) || (!isset($_POST['tem_contrato']) && $g->tem_contrato)) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="tem_contrato">Existe contrato de manutenção</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Tipo de Contrato</label>
                                <input type="text" class="form-control bo-form-control" name="tipo_contrato"
                                    value="<?= htmlspecialchars($_POST['tipo_contrato'] ?? $g->tipo_contrato ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Entidade Responsável</label>
                                <input type="text" class="form-control bo-form-control" name="entidade_responsavel"
                                    value="<?= htmlspecialchars($_POST['entidade_responsavel'] ?? $g->entidade_responsavel ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Periodicidade</label>
                                <input type="text" class="form-control bo-form-control" name="periodicidade"
                                    value="<?= htmlspecialchars($_POST['periodicidade'] ?? $g->periodicidade ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Observações</label>
                                <textarea class="form-control bo-form-control" name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? $g->observacoes ?? '') ?></textarea>
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

<script>
    flatpickr("#data_inicio", {
        dateFormat: "Y-m-d"
    });
    flatpickr("#data_fim", {
        dateFormat: "Y-m-d"
    });
</script>

<?php include '../../includes/footer.php'; ?>