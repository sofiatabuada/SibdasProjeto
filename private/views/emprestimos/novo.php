<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$equipamentos = $db->query("SELECT id, designacao, codigo_inventario, id_localizacao FROM equipamentos WHERE deleted_at IS NULL ORDER BY designacao")->fetchAll(PDO::FETCH_OBJ);
$localizacoes = $db->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll(PDO::FETCH_OBJ);

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento      = $_POST['id_equipamento'] ?? '';
    $id_loc_origem       = $_POST['id_localizacao_origem'] ?? null;
    $servico_destino     = trim($_POST['servico_destino'] ?? '');
    $responsavel         = trim($_POST['responsavel'] ?? '');
    $data_saida          = $_POST['data_saida'] ?? date('Y-m-d');
    $data_retorno_prev   = $_POST['data_retorno_prevista'] ?? '';
    $observacoes         = trim($_POST['observacoes'] ?? '');

    if (empty($id_equipamento))  $erros[] = 'O equipamento é obrigatório.';
    if (empty($servico_destino)) $erros[] = 'O serviço de destino é obrigatório.';

    if (empty($erros)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO emprestimos (id_equipamento, id_localizacao_origem, servico_destino, responsavel, data_saida, data_retorno_prevista, observacoes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id_equipamento,
                $id_loc_origem ?: null,
                $servico_destino,
                $responsavel ?: null,
                $data_saida,
                $data_retorno_prev ?: null,
                $observacoes ?: null
            ]);
            $db = null;
            header('Location: lista.php');
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
                <h1 class="bo-page-title"><i class="fa-solid fa-plus me-2" style="color:var(--mt-blue-dark);"></i>Novo Empréstimo</h1>
                <p class="bo-page-subtitle">Registe o empréstimo de um equipamento a outro serviço</p>
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
                    <form action="novo.php" method="post" novalidate>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="bo-form-label">Equipamento <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="id_equipamento" id="sel_eq">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($equipamentos as $eq):
                                        $sel = (($_POST['id_equipamento'] ?? '') == $eq->id) ? 'selected' : '';
                                    ?><option value="<?= $eq->id ?>" <?= $sel ?> data-loc="<?= $eq->id_localizacao ?>">[<?= htmlspecialchars($eq->codigo_inventario) ?>] <?= htmlspecialchars($eq->designacao) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="bo-form-label">Serviço de Origem</label>
                                <select class="form-select bo-form-control" name="id_localizacao_origem" id="sel_origem">
                                    <option value="">—</option>
                                    <?php foreach ($localizacoes as $loc):
                                        $sel = (($_POST['id_localizacao_origem'] ?? '') == $loc->id) ? 'selected' : '';
                                    ?><option value="<?= $loc->id ?>" <?= $sel ?>><?= htmlspecialchars($loc->servico . ($loc->sala ? ' — ' . $loc->sala : '')) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="bo-form-label">Emprestado ao Serviço <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="servico_destino"
                                    value="<?= htmlspecialchars($_POST['servico_destino'] ?? '') ?>"
                                    placeholder="ex: Pediatria, Urgência...">
                            </div>
                            <div class="col-md-6">
                                <label class="bo-form-label">Responsável</label>
                                <input type="text" class="form-control bo-form-control" name="responsavel"
                                    value="<?= htmlspecialchars($_POST['responsavel'] ?? '') ?>"
                                    placeholder="Nome do responsável pelo empréstimo">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data de Saída</label>
                                <input type="text" class="form-control bo-form-control" name="data_saida"
                                    id="data_saida" value="<?= htmlspecialchars($_POST['data_saida'] ?? date('Y-m-d')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Retorno Previsto</label>
                                <input type="text" class="form-control bo-form-control" name="data_retorno_prevista"
                                    id="data_retorno_prevista" value="<?= htmlspecialchars($_POST['data_retorno_prevista'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Observações</label>
                                <textarea class="form-control bo-form-control" name="observacoes" rows="2"><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-outline-secondary"><i class="fa-solid fa-xmark me-1"></i>Cancelar</a>
                            <button type="submit" class="btn btn-mt-primary"><i class="fa-regular fa-floppy-disk me-1"></i>Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    flatpickr("#data_saida", {
        dateFormat: "Y-m-d"
    });
    flatpickr("#data_retorno_prevista", {
        dateFormat: "Y-m-d"
    });
    document.getElementById('sel_eq').addEventListener('change', function() {
        const locId = this.options[this.selectedIndex].dataset.loc;
        document.getElementById('sel_origem').value = locId || '';
    });
</script>

<?php include '../../includes/footer.php'; ?>