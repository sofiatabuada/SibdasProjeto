<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$localizacoes = $db->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll(PDO::FETCH_OBJ);
$fornecedores = $db->query("SELECT id, nome FROM fornecedores WHERE deleted_at IS NULL ORDER BY nome")->fetchAll(PDO::FETCH_OBJ);

// Gerar código automático: MT-AAAA-NNN
$ano = date('Y');
$ultimo = $db->query("
    SELECT codigo_inventario FROM equipamentos
    WHERE codigo_inventario LIKE 'MT-{$ano}-%'
    ORDER BY id DESC LIMIT 1
")->fetchColumn();

if ($ultimo) {
    $partes = explode('-', $ultimo);
    $num = intval(end($partes)) + 1;
} else {
    $num = 1;
}
$codigo_sugerido = 'MT-' . $ano . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
$db = null;

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $codigo       = trim($_POST['codigo_inventario'] ?? '');
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

    // Validações
    if (empty($codigo))     $erros[] = 'O código de inventário é obrigatório.';
    if (empty($designacao)) $erros[] = 'A designação é obrigatória.';
    if (empty($categoria))  $erros[] = 'A categoria é obrigatória.';

    if (empty($erros)) {
        try {
            $db = get_db();

            // Verificar código duplicado apenas em equipamentos ativos
            $stmt = $db->prepare("SELECT id FROM equipamentos WHERE codigo_inventario = ? AND deleted_at IS NULL");
            $stmt->execute([$codigo]);
            if ($stmt->fetch()) {
                $erros[] = 'Já existe um equipamento ativo com este código de inventário.';
            }

            // Verificar número de série duplicado para o mesmo fabricante e modelo
            if (!empty($num_serie) && !empty($fabricante) && !empty($modelo) && empty($erros)) {
                $stmt = $db->prepare("
                    SELECT id FROM equipamentos
                    WHERE numero_serie = ? AND fabricante = ? AND modelo = ? AND deleted_at IS NULL
                ");
                $stmt->execute([$num_serie, $fabricante, $modelo]);
                if ($stmt->fetch()) {
                    $erros[] = 'Já existe um equipamento ativo com este número de série para o mesmo fabricante e modelo.';
                }
            }

            if (empty($erros)) {
                // Apagar definitivamente registos antigos com este código
                $db->prepare("DELETE FROM equipamentos WHERE codigo_inventario = ? AND deleted_at IS NOT NULL")->execute([$codigo]);

                $stmt = $db->prepare("
                    INSERT INTO equipamentos
                    (codigo_inventario, designacao, categoria, marca, modelo, numero_serie, fabricante,
                     data_aquisicao, ano_fabrico, custo_aquisicao, tipo_entrada, estado, criticidade,
                     id_localizacao, observacoes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $codigo,
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
                    $observacoes ?: null
                ]);

                $id_novo = $db->lastInsertId();

                // Associar fornecedores
                if (!empty($fornecedor_ids)) {
                    $stmt2 = $db->prepare("INSERT INTO equipamento_fornecedor (id_equipamento, id_fornecedor) VALUES (?, ?)");
                    foreach ($fornecedor_ids as $fid) {
                        $stmt2->execute([$id_novo, $fid]);
                    }
                }

                $db = null;
                header('Location: lista.php');
                exit;
            }
            $db = null;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao guardar o equipamento: ' . $e->getMessage();
        }
        $db = null;
    }
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
                    <i class="fa-solid fa-plus me-2" style="color: var(--mt-blue-dark);"></i>Novo Equipamento
                </h1>
                <p class="bo-page-subtitle">Registe um novo equipamento no inventário</p>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger rounded-3">
                    <strong>Erros encontrados:</strong>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-danger rounded-3"><?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <div class="bo-card">
                <div class="bo-card-body">
                    <form action="novo.php" method="post" novalidate>

                        <!-- Identificação -->
                        <h5 class="mb-3" style="font-family: var(--font-display);">Identificação</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="bo-form-label">Código de Inventário <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="codigo_inventario"
                                    value="<?= htmlspecialchars($_POST['codigo_inventario'] ?? $codigo_sugerido) ?>" placeholder="ex: MT-2025-001">
                                <small class="text-muted">Gerado automaticamente — pode ser alterado.</small>
                            </div>
                            <div class="col-md-8">
                                <label class="bo-form-label">Designação <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="designacao"
                                    value="<?= htmlspecialchars($_POST['designacao'] ?? '') ?>" placeholder="ex: Monitor Multiparamétrico">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Categoria <span class="text-danger">*</span></label>
                                <select class="form-select bo-form-control" name="categoria">
                                    <option value="">Selecione...</option>
                                    <?php
                                    $categorias = [
                                        'monitorizacao' => 'Monitorização',
                                        'suporte_vida' => 'Suporte de Vida',
                                        'terapia' => 'Terapia',
                                        'diagnostico' => 'Diagnóstico',
                                        'laboratorio' => 'Laboratório',
                                        'esterilizacao' => 'Esterilização',
                                        'reabilitacao' => 'Reabilitação',
                                        'outro' => 'Outro'
                                    ];
                                    foreach ($categorias as $val => $label):
                                        $sel = (($_POST['categoria'] ?? '') == $val) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Marca</label>
                                <input type="text" class="form-control bo-form-control" name="marca"
                                    value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Modelo</label>
                                <input type="text" class="form-control bo-form-control" name="modelo"
                                    value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Número de Série</label>
                                <input type="text" class="form-control bo-form-control" name="numero_serie"
                                    value="<?= htmlspecialchars($_POST['numero_serie'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Fabricante</label>
                                <input type="text" class="form-control bo-form-control" name="fabricante"
                                    value="<?= htmlspecialchars($_POST['fabricante'] ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <!-- Aquisição -->
                        <h5 class="mb-3" style="font-family: var(--font-display);">Aquisição</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="bo-form-label">Data de Aquisição</label>
                                <input type="text" class="form-control bo-form-control" name="data_aquisicao"
                                    id="data_aquisicao" value="<?= htmlspecialchars($_POST['data_aquisicao'] ?? '') ?>"
                                    placeholder="AAAA-MM-DD">
                            </div>
                            <div class="col-md-3">
                                <label class="bo-form-label">Ano de Fabrico</label>
                                <input type="number" class="form-control bo-form-control" name="ano_fabrico"
                                    value="<?= htmlspecialchars($_POST['ano_fabrico'] ?? '') ?>"
                                    min="1900" max="<?= date('Y') ?>" placeholder="ex: 2022">
                            </div>
                            <div class="col-md-3">
                                <label class="bo-form-label">Custo de Aquisição (€)</label>
                                <input type="number" class="form-control bo-form-control" name="custo_aquisicao"
                                    value="<?= htmlspecialchars($_POST['custo_aquisicao'] ?? '') ?>"
                                    step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="bo-form-label">Tipo de Entrada</label>
                                <select class="form-select bo-form-control" name="tipo_entrada">
                                    <?php
                                    $tipos = ['compra' => 'Compra', 'doacao' => 'Doação', 'aluguer' => 'Aluguer', 'emprestimo' => 'Empréstimo'];
                                    foreach ($tipos as $val => $label):
                                        $sel = (($_POST['tipo_entrada'] ?? 'compra') == $val) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <!-- Estado e Localização -->
                        <h5 class="mb-3" style="font-family: var(--font-display);">Estado e Localização</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="bo-form-label">Estado</label>
                                <select class="form-select bo-form-control" name="estado">
                                    <?php
                                    $estados = [
                                        'ativo' => 'Ativo',
                                        'manutencao' => 'Em manutenção',
                                        'inativo' => 'Inativo',
                                        'calibracao' => 'Em calibração',
                                        'quarentena' => 'Em quarentena',
                                        'abatido' => 'Abatido'
                                    ];
                                    foreach ($estados as $val => $label):
                                        $sel = (($_POST['estado'] ?? 'ativo') == $val) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Criticidade</label>
                                <select class="form-select bo-form-control" name="criticidade">
                                    <?php
                                    $crits = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
                                    foreach ($crits as $val => $label):
                                        $sel = (($_POST['criticidade'] ?? 'media') == $val) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Localização</label>
                                <select class="form-select bo-form-control" name="id_localizacao">
                                    <option value="">Sem localização</option>
                                    <?php foreach ($localizacoes as $loc):
                                        $sel = (($_POST['id_localizacao'] ?? '') == $loc->id) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $loc->id ?>" <?= $sel ?>>
                                            <?= htmlspecialchars($loc->servico . ($loc->sala ? ' — ' . $loc->sala : '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <!-- Fornecedores -->
                        <h5 class="mb-3" style="font-family: var(--font-display);">Fornecedores Associados</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="row g-2">
                                    <?php foreach ($fornecedores as $forn):
                                        $checked = in_array($forn->id, $_POST['fornecedores'] ?? []) ? 'checked' : '';
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
                            </div>
                        </div>

                        <hr>

                        <!-- Observações -->
                        <div class="mb-4">
                            <label class="bo-form-label">Observações</label>
                            <textarea class="form-control bo-form-control" name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                        </div>

                        <!-- Botões -->
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
    flatpickr("#data_aquisicao", {
        dateFormat: "Y-m-d"
    });
</script>

<?php include '../../includes/footer.php'; ?>