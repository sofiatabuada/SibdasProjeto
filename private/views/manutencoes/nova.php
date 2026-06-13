<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$equipamentos = $db->query("
    SELECT id, designacao, codigo_inventario
    FROM equipamentos
    WHERE deleted_at IS NULL
    ORDER BY designacao
")->fetchAll(PDO::FETCH_OBJ);

$eq_preselect = $_GET['eq'] ?? null;
$eq_id = $eq_preselect ? aes_decrypt($eq_preselect) : null;

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento   = $_POST['id_equipamento'] ?? '';
    $tipo             = $_POST['tipo'] ?? '';
    $estado           = $_POST['estado'] ?? '';
    $data_inicio      = $_POST['data_inicio'] ?? '';
    $data_fim         = $_POST['data_fim'] ?? '';
    $descricao        = trim($_POST['descricao'] ?? '');
    $trabalho         = trim($_POST['trabalho_realizado'] ?? '');

    if (empty($id_equipamento)) $erros[] = 'O equipamento é obrigatório.';
    if (empty($tipo))           $erros[] = 'O tipo de manutenção é obrigatório.';
    if (empty($estado))         $erros[] = 'O estado é obrigatório.';
    if (!empty($data_inicio) && !empty($data_fim) && $data_fim < $data_inicio)
        $erros[] = 'A data de fim não pode ser anterior à data de início.';

    if (empty($erros)) {
        try {
            $db->prepare("
                INSERT INTO manutencoes (id_equipamento, tipo, estado, descricao, trabalho_realizado, data_inicio, data_fim)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $id_equipamento,
                $tipo,
                $estado,
                $descricao ?: null,
                $trabalho ?: null,
                $data_inicio ?: null,
                $data_fim ?: null,
            ]);

            if (in_array($estado, ['agendada', 'em_curso'])) {
                $db->prepare("UPDATE equipamentos SET estado = 'manutencao' WHERE id = ?")->execute([$id_equipamento]);
            }

            registar_log('CRIAR', 'Manutenção registada para equipamento id=' . $id_equipamento);
            $db = null;
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao guardar: ' . $e->getMessage();
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

            <?php include '../../includes/breadcrumb.php'; ?>

            <div class="mb-4">
                <h1 class="bo-page-title">
                    <i class="fa-solid fa-plus me-2" style="color: var(--mt-blue-dark);"></i>Nova Manutenção
                </h1>
                <p class="bo-page-subtitle">Registe uma nova manutenção de equipamento</p>
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
                    <form action="nova.php" method="post" novalidate>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Equipamento</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="bo-form-label">Equipamento <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="id_equipamento">
                                    <option value="">Selecione um equipamento...</option>
                                    <?php foreach ($equipamentos as $eq):
                                        $sel = (($_POST['id_equipamento'] ?? $eq_id) == $eq->id) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $eq->id ?>" <?= $sel ?>>
                                            [<?= htmlspecialchars($eq->codigo_inventario) ?>] <?= htmlspecialchars($eq->designacao) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Dados da Manutenção</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="bo-form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="tipo">
                                    <option value="">— Selecione —</option>
                                    <?php
                                    $tipos = ['preventiva' => 'Preventiva', 'corretiva' => 'Corretiva', 'calibracao' => 'Calibração', 'inspecao' => 'Inspeção'];
                                    foreach ($tipos as $v => $l):
                                        $sel = (($_POST['tipo'] ?? '') == $v) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $v ?>" <?= $sel ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="estado">
                                    <option value="">— Selecione —</option>
                                    <?php
                                    $estados = ['agendada' => 'Agendada', 'em_curso' => 'Em Curso', 'concluida' => 'Concluída', 'cancelada' => 'Cancelada'];
                                    foreach ($estados as $v => $l):
                                        $sel = (($_POST['estado'] ?? '') == $v) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $v ?>" <?= $sel ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Estados "Agendada" ou "Em Curso" alteram automaticamente o equipamento para "Em manutenção".</small>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data de Início</label>
                                <input type="text" class="form-control bo-form-control" name="data_inicio"
                                    id="data_inicio" value="<?= htmlspecialchars($_POST['data_inicio'] ?? '') ?>"
                                    placeholder="AAAA-MM-DD">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data de Fim</label>
                                <input type="text" class="form-control bo-form-control" name="data_fim"
                                    id="data_fim" value="<?= htmlspecialchars($_POST['data_fim'] ?? '') ?>"
                                    placeholder="AAAA-MM-DD">
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Descrição / Motivo</label>
                                <textarea class="form-control bo-form-control" name="descricao" rows="2"
                                    placeholder="ex: Avaria na sonda de temperatura, revisão periódica..."><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Trabalho Realizado</label>
                                <textarea class="form-control bo-form-control" name="trabalho_realizado" rows="2"
                                    placeholder="ex: Substituição da sonda, limpeza do filtro..."><?= htmlspecialchars($_POST['trabalho_realizado'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="lista.php" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-mt-primary">
                                <i class="fa-regular fa-floppy-disk me-1"></i>Guardar
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    flatpickr("#data_inicio", { dateFormat: "Y-m-d" });
    flatpickr("#data_fim",    { dateFormat: "Y-m-d" });
</script>

<?php include '../../includes/footer.php'; ?>
