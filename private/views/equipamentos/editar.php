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
$localizacoes = $db->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll(PDO::FETCH_OBJ);
$fornecedores = $db->query("SELECT id, nome FROM fornecedores WHERE deleted_at IS NULL ORDER BY nome")->fetchAll(PDO::FETCH_OBJ);

// Fornecedores já associados
$forn_assoc = $db->prepare("SELECT id_fornecedor FROM equipamento_fornecedor WHERE id_equipamento = ?");
$forn_assoc->execute([$id]);
$forn_assoc_ids = $forn_assoc->fetchAll(PDO::FETCH_COLUMN);

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $designacao   = trim($_POST['designacao'] ?? '');
    $categoria    = $_POST['categoria'] ?? '';
    $marca        = trim($_POST['marca'] ?? '');
    $modelo       = trim($_POST['modelo'] ?? '');
    $num_serie    = trim($_POST['numero_serie'] ?? '');
    $fabricante   = trim($_POST['fabricante'] ?? '');
    $data_aquis   = $_POST['data_aquisicao'] ?? '';
    $ano_fabrico  = $_POST['ano_fabrico'] ?? '';
    $custo        = $_POST['custo_aquisicao'] ?? '';
    $tipo_entrada = $_POST['tipo_entrada'] ?? 'compra';
    $estado       = $_POST['estado'] ?? 'ativo';
    $criticidade  = $_POST['criticidade'] ?? 'media';
    $id_loc       = $_POST['id_localizacao'] ?? null;
    $observacoes  = trim($_POST['observacoes'] ?? '');
    $fornecedor_ids = $_POST['fornecedores'] ?? [];

    if (empty($designacao)) $erros[] = 'A designação é obrigatória.';
    if (empty($categoria))  $erros[] = 'A categoria é obrigatória.';

    if (empty($erros)) {
        try {
            $stmt = $db->prepare("
                UPDATE equipamentos SET
                    designacao = ?, categoria = ?, marca = ?, modelo = ?, numero_serie = ?,
                    fabricante = ?, data_aquisicao = ?, ano_fabrico = ?, custo_aquisicao = ?,
                    tipo_entrada = ?, estado = ?, criticidade = ?, id_localizacao = ?, observacoes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $designacao,
                $categoria,
                $marca ?: null,
                $modelo ?: null,
                $num_serie ?: null,
                $fabricante ?: null,
                $data_aquis ?: null,
                $ano_fabrico ?: null,
                $custo ?: null,
                $tipo_entrada,
                $estado,
                $criticidade,
                $id_loc ?: null,
                $observacoes ?: null,
                $id
            ]);

            // Atualizar fornecedores
            $db->prepare("DELETE FROM equipamento_fornecedor WHERE id_equipamento = ?")->execute([$id]);
            if (!empty($fornecedor_ids)) {
                $stmt2 = $db->prepare("INSERT INTO equipamento_fornecedor (id_equipamento, id_fornecedor) VALUES (?, ?)");
                foreach ($fornecedor_ids as $fid) {
                    $stmt2->execute([$id, $fid]);
                }
            }

            $db = null;
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao atualizar: ' . $e->getMessage();
        }
    }
    $forn_assoc_ids = $fornecedor_ids;
}

// Carregar dados atuais
$eq = $db->prepare("SELECT * FROM equipamentos WHERE id = ? AND deleted_at IS NULL");
$eq->execute([$id]);
$eq = $eq->fetch(PDO::FETCH_OBJ);
$db = null;

if (!$eq) {
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
                    <i class="fa-regular fa-pen-to-square me-2" style="color: var(--mt-blue-dark);"></i>Editar Equipamento
                </h1>
                <p class="bo-page-subtitle"><?= htmlspecialchars($eq->codigo_inventario) ?></p>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger rounded-3">
                    <strong>Erros encontrados:</strong>
                    <ul class="mb-0 mt-1"><?php foreach ($erros as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-danger rounded-3"><?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <div class="bo-card">
                <div class="bo-card-body">
                    <form action="editar.php?id=<?= $idEnc ?>" method="post" novalidate>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Identificação</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="bo-form-label">Código de Inventário</label>
                                <input type="text" class="form-control bo-form-control"
                                    value="<?= htmlspecialchars($eq->codigo_inventario) ?>" disabled>
                            </div>
                            <div class="col-md-8">
                                <label class="bo-form-label">Designação <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="designacao"
                                    value="<?= htmlspecialchars($_POST['designacao'] ?? $eq->designacao) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Categoria <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="categoria">
                                    <?php
                                    $categorias = ['monitorizacao' => 'Monitorização', 'suporte_vida' => 'Suporte de Vida', 'terapia' => 'Terapia', 'diagnostico' => 'Diagnóstico', 'laboratorio' => 'Laboratório', 'esterilizacao' => 'Esterilização', 'reabilitacao' => 'Reabilitação', 'outro' => 'Outro'];
                                    $cat_atual = $_POST['categoria'] ?? $eq->categoria;
                                    foreach ($categorias as $val => $label):
                                    ?><option value="<?= $val ?>" <?= $cat_atual == $val ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Marca</label>
                                <input type="text" class="form-control bo-form-control" name="marca"
                                    value="<?= htmlspecialchars($_POST['marca'] ?? $eq->marca ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Modelo</label>
                                <input type="text" class="form-control bo-form-control" name="modelo"
                                    value="<?= htmlspecialchars($_POST['modelo'] ?? $eq->modelo ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Número de Série</label>
                                <input type="text" class="form-control bo-form-control" name="numero_serie"
                                    value="<?= htmlspecialchars($_POST['numero_serie'] ?? $eq->numero_serie ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Fabricante</label>
                                <input type="text" class="form-control bo-form-control" name="fabricante"
                                    value="<?= htmlspecialchars($_POST['fabricante'] ?? $eq->fabricante ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Aquisição</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="bo-form-label">Data de Aquisição</label>
                                <input type="text" class="form-control bo-form-control" name="data_aquisicao"
                                    id="data_aquisicao"
                                    value="<?= htmlspecialchars($_POST['data_aquisicao'] ?? ($eq->data_aquisicao ? date('Y-m-d', strtotime($eq->data_aquisicao)) : '')) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="bo-form-label">Ano de Fabrico</label>
                                <input type="number" class="form-control bo-form-control" name="ano_fabrico"
                                    value="<?= htmlspecialchars($_POST['ano_fabrico'] ?? $eq->ano_fabrico ?? '') ?>"
                                    min="1900" max="<?= date('Y') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="bo-form-label">Custo de Aquisição (€)</label>
                                <input type="number" class="form-control bo-form-control" name="custo_aquisicao"
                                    value="<?= htmlspecialchars($_POST['custo_aquisicao'] ?? $eq->custo_aquisicao ?? '') ?>"
                                    step="0.01" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="bo-form-label">Tipo de Entrada</label>
                                <select class="form-select bo-form-control" name="tipo_entrada">
                                    <?php
                                    $tipos = ['compra' => 'Compra', 'doacao' => 'Doação', 'aluguer' => 'Aluguer', 'emprestimo' => 'Empréstimo'];
                                    $te_atual = $_POST['tipo_entrada'] ?? $eq->tipo_entrada;
                                    foreach ($tipos as $val => $label):
                                    ?><option value="<?= $val ?>" <?= $te_atual == $val ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Estado e Localização</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="bo-form-label">Estado</label>
                                <select class="form-select bo-form-control" name="estado">
                                    <?php
                                    $estados = ['ativo' => 'Ativo', 'manutencao' => 'Em manutenção', 'inativo' => 'Inativo', 'calibracao' => 'Em calibração', 'quarentena' => 'Em quarentena', 'abatido' => 'Abatido'];
                                    $est_atual = $_POST['estado'] ?? $eq->estado;
                                    foreach ($estados as $val => $label):
                                    ?><option value="<?= $val ?>" <?= $est_atual == $val ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Criticidade</label>
                                <select class="form-select bo-form-control" name="criticidade">
                                    <?php
                                    $crits = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
                                    $crit_atual = $_POST['criticidade'] ?? $eq->criticidade;
                                    foreach ($crits as $val => $label):
                                    ?><option value="<?= $val ?>" <?= $crit_atual == $val ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Localização</label>
                                <select class="form-select bo-form-control" name="id_localizacao">
                                    <option value="">Sem localização</option>
                                    <?php
                                    $loc_atual = $_POST['id_localizacao'] ?? $eq->id_localizacao;
                                    foreach ($localizacoes as $loc):
                                    ?><option value="<?= $loc->id ?>" <?= $loc_atual == $loc->id ? 'selected' : '' ?>><?= htmlspecialchars($loc->servico . ($loc->sala ? ' — ' . $loc->sala : '')) ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Fornecedores Associados</h5>
                        <div class="row g-2 mb-4">
                            <?php foreach ($fornecedores as $forn):
                                $checked = in_array($forn->id, $forn_assoc_ids) ? 'checked' : '';
                            ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            name="fornecedores[]" value="<?= $forn->id ?>"
                                            id="forn_<?= $forn->id ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="forn_<?= $forn->id ?>" style="font-size:0.9rem;">
                                            <?= htmlspecialchars($forn->nome) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <label class="bo-form-label">Observações</label>
                            <textarea class="form-control bo-form-control" name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? $eq->observacoes ?? '') ?></textarea>
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
    flatpickr("#data_aquisicao", {
        dateFormat: "Y-m-d"
    });
</script>

<?php include '../../includes/footer.php'; ?>