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
$db = null;

// Pré-selecionar equipamento se vier da ficha de detalhes
$eq_preselect = $_GET['eq'] ?? null;
$eq_id = $eq_preselect ? aes_decrypt($eq_preselect) : null;

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento = $_POST['id_equipamento'] ?? '';
    $tipo           = $_POST['tipo'] ?? 'outro';
    $nome           = trim($_POST['nome'] ?? '');
    $data_doc       = $_POST['data_documento'] ?? '';
    $data_val       = $_POST['data_validade'] ?? '';
    $observacoes    = trim($_POST['observacoes'] ?? '');
    $ficheiro       = null;

    if (empty($id_equipamento)) $erros[] = 'O equipamento é obrigatório.';
    if (empty($nome))           $erros[] = 'O nome do documento é obrigatório.';

    // Processar upload de ficheiro
    if (empty($erros) && !empty($_FILES['ficheiro']['name'])) {
        $upload_dir  = __DIR__ . '/../../uploads/';
        $nome_orig   = basename($_FILES['ficheiro']['name']);
        $extensao    = strtolower(pathinfo($nome_orig, PATHINFO_EXTENSION));
        $permitidos  = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];

        if (!in_array($extensao, $permitidos)) {
            $erros[] = 'Tipo de ficheiro não permitido. Use PDF, Word, Excel ou imagem.';
        } elseif ($_FILES['ficheiro']['size'] > 10 * 1024 * 1024) {
            $erros[] = 'O ficheiro não pode ter mais de 10MB.';
        } else {
            $nome_ficheiro = uniqid('doc_') . '.' . $extensao;
            if (move_uploaded_file($_FILES['ficheiro']['tmp_name'], $upload_dir . $nome_ficheiro)) {
                $ficheiro = $nome_ficheiro;
            } else {
                $erros[] = 'Erro ao fazer upload do ficheiro.';
            }
        }
    }

    if (empty($erros)) {
        try {
            $db = get_db();
            $stmt = $db->prepare("
                INSERT INTO documentos (id_equipamento, tipo, nome, data_documento, data_validade, ficheiro, observacoes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id_equipamento,
                $tipo,
                $nome,
                $data_doc ?: null,
                $data_val ?: null,
                $ficheiro ?: null,
                $observacoes ?: null
            ]);
            $db = null;
            header('Location: lista.php');
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao guardar: ' . $e->getMessage();
        }
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
                    <i class="fa-solid fa-plus me-2" style="color: var(--mt-blue-dark);"></i>Novo Documento
                </h1>
                <p class="bo-page-subtitle">Associe documentação técnica a um equipamento</p>
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
                    <form action="novo.php" method="post" enctype="multipart/form-data" novalidate>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label class="bo-form-label">Tipo de Documento</label>
                                <select class="form-select bo-form-control" name="tipo">
                                    <?php
                                    $tipos = [
                                        'manual_utilizador'      => 'Manual de Utilizador',
                                        'manual_servico'         => 'Manual de Serviço',
                                        'certificado_calibracao' => 'Certificado de Calibração',
                                        'contrato_manutencao'    => 'Contrato de Manutenção',
                                        'fatura'                 => 'Fatura',
                                        'declaracao_conformidade' => 'Declaração de Conformidade',
                                        'relatorio_tecnico'      => 'Relatório Técnico',
                                        'outro'                  => 'Outro',
                                    ];
                                    foreach ($tipos as $val => $label):
                                        $sel = (($_POST['tipo'] ?? 'outro') == $val) ? 'selected' : '';
                                    ?><option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="bo-form-label">Nome do Documento <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="nome"
                                    value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                                    placeholder="ex: Manual de Utilizador IntelliVue MP5">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Ficheiro</label>
                                <input type="file" class="form-control bo-form-control" name="ficheiro"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <small class="text-muted">PDF, Word, Excel ou imagem. Máx. 10MB.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data do Documento</label>
                                <input type="text" class="form-control bo-form-control" name="data_documento"
                                    id="data_documento"
                                    value="<?= htmlspecialchars($_POST['data_documento'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Data de Validade</label>
                                <input type="text" class="form-control bo-form-control" name="data_validade"
                                    id="data_validade"
                                    value="<?= htmlspecialchars($_POST['data_validade'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Observações</label>
                                <textarea class="form-control bo-form-control" name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
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
    flatpickr("#data_documento", {
        dateFormat: "Y-m-d"
    });
    flatpickr("#data_validade", {
        dateFormat: "Y-m-d"
    });
</script>

<?php include '../../includes/footer.php'; ?>