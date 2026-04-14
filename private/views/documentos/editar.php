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
    $id_equipamento = $_POST['id_equipamento'] ?? '';
    $tipo           = $_POST['tipo'] ?? 'outro';
    $nome           = trim($_POST['nome'] ?? '');
    $data_doc       = $_POST['data_documento'] ?? '';
    $data_val       = $_POST['data_validade'] ?? '';
    $ficheiro       = trim($_POST['ficheiro'] ?? '');
    $observacoes    = trim($_POST['observacoes'] ?? '');

    if (empty($id_equipamento)) $erros[] = 'O equipamento é obrigatório.';
    if (empty($nome))           $erros[] = 'O nome é obrigatório.';

    if (empty($erros)) {
        try {
            $stmt = $db->prepare("
                UPDATE documentos SET id_equipamento=?, tipo=?, nome=?, data_documento=?, data_validade=?, ficheiro=?, observacoes=?
                WHERE id=?
            ");
            $stmt->execute([$id_equipamento, $tipo, $nome, $data_doc ?: null, $data_val ?: null, $ficheiro ?: null, $observacoes ?: null, $id]);
            $db = null;
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro: ' . $e->getMessage();
        }
    }
}

$doc = $db->prepare("SELECT * FROM documentos WHERE id = ?");
$doc->execute([$id]);
$doc = $doc->fetch(PDO::FETCH_OBJ);
$db  = null;
if (!$doc) {
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
                <h1 class="bo-page-title"><i class="fa-regular fa-pen-to-square me-2" style="color:var(--mt-blue-dark);"></i>Editar Documento</h1>
                <p class="bo-page-subtitle"><?= htmlspecialchars($doc->nome) ?></p>
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
                                <label class="bo-form-label">Equipamento <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="id_equipamento">
                                    <?php foreach ($equipamentos as $eq):
                                        $sel = (($_POST['id_equipamento'] ?? $doc->id_equipamento) == $eq->id) ? 'selected' : '';
                                    ?><option value="<?= $eq->id ?>" <?= $sel ?>>[<?= htmlspecialchars($eq->codigo_inventario) ?>] <?= htmlspecialchars($eq->designacao) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="bo-form-label">Tipo</label>
                                <select class="form-select bo-form-control" name="tipo">
                                    <?php
                                    $tipos = ['manual_utilizador' => 'Manual de Utilizador', 'manual_servico' => 'Manual de Serviço', 'certificado_calibracao' => 'Certificado de Calibração', 'contrato_manutencao' => 'Contrato de Manutenção', 'fatura' => 'Fatura', 'declaracao_conformidade' => 'Declaração de Conformidade', 'relatorio_tecnico' => 'Relatório Técnico', 'outro' => 'Outro'];
                                    $t_atual = $_POST['tipo'] ?? $doc->tipo;
                                    foreach ($tipos as $val => $label):
                                    ?><option value="<?= $val ?>" <?= $t_atual == $val ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="bo-form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="nome"
                                    value="<?= htmlspecialchars($_POST['nome'] ?? $doc->nome) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Ficheiro / Link</label>
                                <input type="text" class="form-control bo-form-control" name="ficheiro"
                                    value="<?= htmlspecialchars($_POST['ficheiro'] ?? $doc->ficheiro ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data do Documento</label>
                                <input type="text" class="form-control bo-form-control" name="data_documento" id="data_documento"
                                    value="<?= htmlspecialchars($_POST['data_documento'] ?? ($doc->data_documento ? date('Y-m-d', strtotime($doc->data_documento)) : '')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data de Validade</label>
                                <input type="text" class="form-control bo-form-control" name="data_validade" id="data_validade"
                                    value="<?= htmlspecialchars($_POST['data_validade'] ?? ($doc->data_validade ? date('Y-m-d', strtotime($doc->data_validade)) : '')) ?>">
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Observações</label>
                                <textarea class="form-control bo-form-control" name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? $doc->observacoes ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i>Cancelar</a>
                            <button type="submit" class="btn btn-mt-primary"><i class="fa-regular fa-floppy-disk me-1"></i>Guardar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    flatpickr("#data_documento", {
        dateFormat: "Y-m-d"
    });
    flatpickr("#data_validade", {
        dateFormat: "Y-m-d"
    });
</script>

<?php include '../../includes/footer.php'; ?>