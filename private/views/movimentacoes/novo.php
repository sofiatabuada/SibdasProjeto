<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$equipamentos = $db->query("SELECT id, designacao, codigo_inventario, id_localizacao FROM equipamentos WHERE deleted_at IS NULL ORDER BY designacao")->fetchAll(PDO::FETCH_OBJ);
$localizacoes = $db->query("SELECT id, servico, sala, piso FROM localizacoes ORDER BY servico")->fetchAll(PDO::FETCH_OBJ);

$eq_preselect = $_GET['eq'] ?? null;
$eq_id = $eq_preselect ? aes_decrypt($eq_preselect) : null;

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento      = $_POST['id_equipamento'] ?? '';
    $id_loc_origem       = $_POST['id_localizacao_origem'] ?? null;
    $id_loc_destino      = $_POST['id_localizacao_destino'] ?? null;
    $motivo              = trim($_POST['motivo'] ?? '');
    $observacoes         = trim($_POST['observacoes'] ?? '');
    $data_movimentacao   = $_POST['data_movimentacao'] ?? date('Y-m-d');

    if (empty($id_equipamento))  $erros[] = 'O equipamento é obrigatório.';
    if (empty($id_loc_destino))  $erros[] = 'A localização de destino é obrigatória.';
    if ($id_loc_origem == $id_loc_destino && !empty($id_loc_origem))
        $erros[] = 'A origem e o destino não podem ser iguais.';

    if (empty($erros)) {
        try {
            // Registar movimentação
            $stmt = $db->prepare("
                INSERT INTO movimentacoes (id_equipamento, id_localizacao_origem, id_localizacao_destino, motivo, observacoes, data_movimentacao, registado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id_equipamento,
                $id_loc_origem ?: null,
                $id_loc_destino ?: null,
                $motivo ?: null,
                $observacoes ?: null,
                $data_movimentacao,
                $_SESSION['utilizador']
            ]);

            // Atualizar localização do equipamento
            $db->prepare("UPDATE equipamentos SET id_localizacao = ? WHERE id = ?")->execute([$id_loc_destino, $id_equipamento]);

            $db = null;
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao registar: ' . $e->getMessage();
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
                    <i class="fa-solid fa-plus me-2" style="color: var(--mt-blue-dark);"></i>Registar Movimentação
                </h1>
                <p class="bo-page-subtitle">Registe a transferência de um equipamento entre localizações</p>
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
                                <select class="form-select bo-form-control" name="id_equipamento" id="sel_equipamento">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($equipamentos as $eq):
                                        $sel = (($_POST['id_equipamento'] ?? $eq_id) == $eq->id) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $eq->id ?>" <?= $sel ?>
                                            data-loc="<?= $eq->id_localizacao ?>">
                                            [<?= htmlspecialchars($eq->codigo_inventario) ?>] <?= htmlspecialchars($eq->designacao) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="bo-form-label">Data da Movimentação</label>
                                <input type="text" class="form-control bo-form-control" name="data_movimentacao"
                                    id="data_movimentacao"
                                    value="<?= htmlspecialchars($_POST['data_movimentacao'] ?? date('Y-m-d')) ?>">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="bo-form-label">Localização de Origem</label>
                                <select class="form-select bo-form-control" name="id_localizacao_origem" id="sel_origem">
                                    <option value="">Sem localização definida</option>
                                    <?php foreach ($localizacoes as $loc):
                                        $sel = (($_POST['id_localizacao_origem'] ?? '') == $loc->id) ? 'selected' : '';
                                    ?><option value="<?= $loc->id ?>" <?= $sel ?>><?= htmlspecialchars($loc->servico . ($loc->sala ? ' — ' . $loc->sala : '')) ?></option><?php endforeach; ?>
                                </select>
                                <small class="text-muted">Preenchido automaticamente ao selecionar o equipamento.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="bo-form-label">Localização de Destino <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="id_localizacao_destino">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($localizacoes as $loc):
                                        $sel = (($_POST['id_localizacao_destino'] ?? '') == $loc->id) ? 'selected' : '';
                                    ?><option value="<?= $loc->id ?>" <?= $sel ?>><?= htmlspecialchars($loc->servico . ($loc->sala ? ' — ' . $loc->sala : '')) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="bo-form-label">Motivo</label>
                                <input type="text" class="form-control bo-form-control" name="motivo"
                                    value="<?= htmlspecialchars($_POST['motivo'] ?? '') ?>"
                                    placeholder="ex: Transferência para manutenção, Empréstimo temporário...">
                            </div>
                            <div class="col-md-6">
                                <label class="bo-form-label">Observações</label>
                                <input type="text" class="form-control bo-form-control" name="observacoes"
                                    value="<?= htmlspecialchars($_POST['observacoes'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-mt-primary">
                                <i class="fa-regular fa-floppy-disk me-1"></i>Registar
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    flatpickr("#data_movimentacao", {
        dateFormat: "Y-m-d"
    });

    // Preencher origem automaticamente ao selecionar equipamento
    document.getElementById('sel_equipamento').addEventListener('change', function() {
        const locId = this.options[this.selectedIndex].dataset.loc;
        const origem = document.getElementById('sel_origem');
        if (locId) {
            origem.value = locId;
        } else {
            origem.value = '';
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>