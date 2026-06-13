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
$man = $db->prepare("
    SELECT m.*, e.designacao, e.codigo_inventario
    FROM manutencoes m
    JOIN equipamentos e ON m.id_equipamento = e.id
    WHERE m.id = ?
");
$man->execute([$id]);
$man = $man->fetch(PDO::FETCH_OBJ);

if (!$man) {
    header('Location: lista.php');
    exit;
}

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo        = $_POST['tipo']        ?? '';
    $estado      = $_POST['estado']      ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim    = $_POST['data_fim']    ?? '';
    $descricao   = trim($_POST['descricao']        ?? '');
    $trabalho    = trim($_POST['trabalho_realizado'] ?? '');

    if (empty($tipo))   $erros[] = 'O tipo de manutenção é obrigatório.';
    if (empty($estado)) $erros[] = 'O estado é obrigatório.';
    if (!empty($data_inicio) && !empty($data_fim) && $data_fim < $data_inicio)
        $erros[] = 'A data de fim não pode ser anterior à data de início.';

    if (empty($erros)) {
        try {
            $db->prepare("
                UPDATE manutencoes
                SET tipo = ?, estado = ?, data_inicio = ?, data_fim = ?, descricao = ?, trabalho_realizado = ?
                WHERE id = ?
            ")->execute([
                $tipo,
                $estado,
                $data_inicio ?: null,
                $data_fim    ?: null,
                $descricao   ?: null,
                $trabalho    ?: null,
                $id,
            ]);

            // Sincronizar estado do equipamento
            if (in_array($estado, ['agendada', 'em_curso'])) {
                $db->prepare("UPDATE equipamentos SET estado = 'manutencao' WHERE id = ?")->execute([$man->id_equipamento]);
            } else {
                // Se não ficam manutenções ativas para este equipamento, reverter para ativo
                $ativas = $db->prepare("
                    SELECT COUNT(*) FROM manutencoes
                    WHERE id_equipamento = ? AND id != ? AND estado IN ('agendada','em_curso')
                ");
                $ativas->execute([$man->id_equipamento, $id]);
                if ($ativas->fetchColumn() == 0) {
                    $db->prepare("UPDATE equipamentos SET estado = 'ativo' WHERE id = ? AND estado = 'manutencao'")->execute([$man->id_equipamento]);
                }
            }

            registar_log('EDITAR', 'Manutenção editada: id=' . $id);
            $db = null;
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao guardar: ' . $e->getMessage();
        }
    }
}
$db = null;

$tipo_val   = $_POST['tipo']        ?? $man->tipo;
$estado_val = $_POST['estado']      ?? $man->estado;
$di_val     = $_POST['data_inicio'] ?? ($man->data_inicio ? date('Y-m-d', strtotime($man->data_inicio)) : '');
$df_val     = $_POST['data_fim']    ?? ($man->data_fim    ? date('Y-m-d', strtotime($man->data_fim))    : '');
$desc_val   = $_POST['descricao']           ?? $man->descricao ?? '';
$trab_val   = $_POST['trabalho_realizado']  ?? $man->trabalho_realizado ?? '';

$tipos  = ['preventiva' => 'Preventiva', 'corretiva' => 'Corretiva', 'calibracao' => 'Calibração', 'inspecao' => 'Inspeção'];
$estados = ['agendada' => 'Agendada', 'em_curso' => 'Em Curso', 'concluida' => 'Concluída', 'cancelada' => 'Cancelada'];
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
                    <i class="fa-regular fa-pen-to-square me-2" style="color: var(--mt-blue-dark);"></i>Editar Manutenção
                </h1>
                <p class="bo-page-subtitle">
                    <?= htmlspecialchars($man->designacao) ?>
                    <code class="ms-2"><?= htmlspecialchars($man->codigo_inventario) ?></code>
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
                            <div class="col-md-4">
                                <label class="bo-form-label">Tipo <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="tipo">
                                    <?php foreach ($tipos as $v => $l): ?>
                                        <option value="<?= $v ?>" <?= $tipo_val == $v ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="estado">
                                    <?php foreach ($estados as $v => $l): ?>
                                        <option value="<?= $v ?>" <?= $estado_val == $v ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Marcar como "Concluída" ou "Cancelada" liberta o equipamento automaticamente.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data de Início</label>
                                <input type="text" class="form-control bo-form-control" name="data_inicio"
                                    id="data_inicio" value="<?= htmlspecialchars($di_val) ?>"
                                    placeholder="AAAA-MM-DD">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data de Fim</label>
                                <input type="text" class="form-control bo-form-control" name="data_fim"
                                    id="data_fim" value="<?= htmlspecialchars($df_val) ?>"
                                    placeholder="AAAA-MM-DD">
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Descrição / Motivo</label>
                                <textarea class="form-control bo-form-control" name="descricao" rows="2"
                                    placeholder="ex: Avaria na sonda de temperatura..."><?= htmlspecialchars($desc_val) ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Trabalho Realizado</label>
                                <textarea class="form-control bo-form-control" name="trabalho_realizado" rows="2"
                                    placeholder="ex: Substituição da sonda, limpeza do filtro..."><?= htmlspecialchars($trab_val) ?></textarea>
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
