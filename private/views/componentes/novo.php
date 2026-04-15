<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$idEncEq = $_GET['eq'] ?? null;
$id_eq   = aes_decrypt($idEncEq);
if (!$id_eq || !is_numeric($id_eq)) {
    header('Location: /MediTrack/private/views/equipamentos/lista.php');
    exit;
}

$db = get_db();
$eq = $db->prepare("SELECT id, designacao, codigo_inventario FROM equipamentos WHERE id = ? AND deleted_at IS NULL");
$eq->execute([$id_eq]);
$eq = $eq->fetch(PDO::FETCH_OBJ);
if (!$eq) {
    header('Location: /MediTrack/private/views/equipamentos/lista.php');
    exit;
}

// Gerar código automático do componente: CODIGO_EQ.NN
$ultimo_comp = $db->prepare("
    SELECT codigo FROM componentes
    WHERE id_equipamento = ? AND codigo LIKE ?
    ORDER BY id DESC LIMIT 1
");
$ultimo_comp->execute([$id_eq, $eq->codigo_inventario . '.%']);
$ultimo_cod = $ultimo_comp->fetchColumn();

if ($ultimo_cod) {
    $partes = explode('.', $ultimo_cod);
    $num_comp = intval(end($partes)) + 1;
} else {
    $num_comp = 1;
}
$codigo_comp_sugerido = $eq->codigo_inventario . '.' . str_pad($num_comp, 2, '0', STR_PAD_LEFT);

$db = null;

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
    if ($quantidade < 1)    $erros[] = 'A quantidade deve ser pelo menos 1.';

    if (empty($erros)) {
        try {
            $db = get_db();
            $stmt = $db->prepare("
                INSERT INTO componentes (id_equipamento, codigo, designacao, quantidade, numero_serie, estado, observacoes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$id_eq, $codigo ?: null, $designacao, $quantidade, $num_serie ?: null, $estado, $observacoes ?: null]);
            $db = null;
            header('Location: /MediTrack/private/views/equipamentos/detalhes.php?id=' . $idEncEq);
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
                    <i class="fa-solid fa-plus me-2" style="color: var(--mt-blue-dark);"></i>Novo Componente
                </h1>
                <p class="bo-page-subtitle">
                    Equipamento: <strong><?= htmlspecialchars($eq->designacao) ?></strong>
                    <code class="ms-2"><?= htmlspecialchars($eq->codigo_inventario) ?></code>
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
                    <form action="novo.php?eq=<?= $idEncEq ?>" method="post" novalidate>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="bo-form-label">Código</label>
                                <input type="text" class="form-control bo-form-control" name="codigo"
                                    value="<?= htmlspecialchars($_POST['codigo'] ?? $codigo_comp_sugerido) ?>"
                                    placeholder="ex: MT-2025-001.01">
                                <small class="text-muted">Gerado automaticamente.</small>
                            </div>
                            <div class="col-md-7">
                                <label class="bo-form-label">Designação <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="designacao"
                                    value="<?= htmlspecialchars($_POST['designacao'] ?? '') ?>"
                                    placeholder="ex: Sensor de oximetria (SpO₂)">
                            </div>
                            <div class="col-md-2">
                                <label class="bo-form-label">Quantidade</label>
                                <input type="number" class="form-control bo-form-control" name="quantidade"
                                    value="<?= htmlspecialchars($_POST['quantidade'] ?? '1') ?>" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Número de Série</label>
                                <input type="text" class="form-control bo-form-control" name="numero_serie"
                                    value="<?= htmlspecialchars($_POST['numero_serie'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Estado</label>
                                <select class="form-select bo-form-control" name="estado">
                                    <?php
                                    $estados = ['ativo' => 'Ativo', 'inativo' => 'Inativo', 'substituido' => 'Substituído'];
                                    foreach ($estados as $val => $label):
                                        $sel = (($_POST['estado'] ?? 'ativo') == $val) ? 'selected' : '';
                                    ?><option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Observações</label>
                                <textarea class="form-control bo-form-control" name="observacoes" rows="2"><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/MediTrack/private/views/equipamentos/detalhes.php?id=<?= $idEncEq ?>"
                                class="btn btn-outline-secondary">
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

<?php include '../../includes/footer.php'; ?>