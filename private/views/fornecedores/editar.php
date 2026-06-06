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

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = trim($_POST['nome'] ?? '');
    $nif             = trim($_POST['nif'] ?? '');
    $tipo            = $_POST['tipo'] ?? 'outro';
    $telefone        = trim($_POST['telefone'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $morada          = trim($_POST['morada'] ?? '');
    $website         = trim($_POST['website'] ?? '');
    $pessoa_contacto = trim($_POST['pessoa_contacto'] ?? '');
    $tel_contacto    = trim($_POST['telefone_contacto'] ?? '');
    $observacoes     = trim($_POST['observacoes'] ?? '');

    if (empty($nome)) $erros[] = 'O nome é obrigatório.';
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $erros[] = 'O email não é válido.';

    if (empty($erros)) {
        try {
            $db = get_db();
            $stmt = $db->prepare("
                UPDATE fornecedores SET
                    nome = ?, nif = ?, tipo = ?, telefone = ?, email = ?,
                    morada = ?, website = ?, pessoa_contacto = ?, telefone_contacto = ?, observacoes = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $nome, $nif ?: null, $tipo,
                $telefone ?: null, $email ?: null, $morada ?: null, $website ?: null,
                $pessoa_contacto ?: null, $tel_contacto ?: null, $observacoes ?: null,
                $id
            ]);
            $db = null;
            header('Location: detalhes.php?id=' . $idEnc);
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao atualizar: ' . $e->getMessage();
        }
    }
}

$db = get_db();
$f  = $db->prepare("SELECT * FROM fornecedores WHERE id = ? AND deleted_at IS NULL");
$f->execute([$id]);
$f  = $f->fetch(PDO::FETCH_OBJ);
$db = null;
if (!$f) {
    header('Location: lista.php');
    exit;
}

$tipos = ['fabricante' => 'Fabricante', 'distribuidor' => 'Distribuidor', 'consumiveis' => 'Consumíveis', 'outro' => 'Outro'];
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <?php include '../../includes/breadcrumb.php'; ?>

            <!-- Hero -->
            <div class="equip-hero equip-hero-edit mb-4">
                <div class="d-flex align-items-center gap-3 position-relative">
                    <div class="equip-hero-icon equip-hero-icon-edit">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h1 style="font-family:var(--font-display);font-size:1.5rem;margin:0;color:var(--mt-text);">
                            <?= htmlspecialchars($f->nome) ?>
                        </h1>
                        <code style="font-size:0.8rem;background:rgba(217,119,6,0.12);color:#B45309;padding:2px 8px;border-radius:6px;">
                            <?= $tipos[$f->tipo] ?? ucfirst($f->tipo) ?>
                        </code>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="detalhes.php?id=<?= $idEnc ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-xmark me-1"></i>Cancelar
                        </a>
                        <button form="form-editar" type="submit" class="btn btn-outline-warning btn-sm">
                            <i class="fa-regular fa-floppy-disk me-1"></i>Guardar
                        </button>
                    </div>
                </div>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger rounded-3 mb-3">
                    <strong><i class="fa-solid fa-triangle-exclamation me-1"></i>Erros encontrados:</strong>
                    <ul class="mb-0 mt-1"><?php foreach ($erros as $erro): ?><li><?= htmlspecialchars($erro) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-danger rounded-3 mb-3"><?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <form id="form-editar" action="editar.php?id=<?= $idEnc ?>" method="post" novalidate>

                <ul class="nav equip-tabs mb-0" id="editarTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-empresa" type="button" role="tab">
                            <i class="fa-solid fa-building me-1"></i>Dados da Empresa
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-contacto" type="button" role="tab">
                            <i class="fa-solid fa-address-book me-1"></i>Pessoa de Contacto
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-obs" type="button" role="tab">
                            <i class="fa-solid fa-note-sticky me-1"></i>Observações
                        </button>
                    </li>
                </ul>

                <div class="tab-content bo-card" style="border-top:none;border-radius:0 0 var(--mt-radius) var(--mt-radius);">

                    <!-- Tab: Dados da Empresa -->
                    <div class="tab-pane fade show active" id="tab-empresa" role="tabpanel">
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="bo-form-label">Nome <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bo-form-control" name="nome"
                                        value="<?= htmlspecialchars($_POST['nome'] ?? $f->nome) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">NIF</label>
                                    <input type="text" class="form-control bo-form-control" name="nif"
                                        value="<?= htmlspecialchars($_POST['nif'] ?? $f->nif ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">Tipo</label>
                                    <select class="form-select bo-form-control" name="tipo">
                                        <?php $t_atual = $_POST['tipo'] ?? $f->tipo;
                                        foreach ($tipos as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= $t_atual == $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Telefone</label>
                                    <input type="text" class="form-control bo-form-control" name="telefone"
                                        value="<?= htmlspecialchars($_POST['telefone'] ?? $f->telefone ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Email</label>
                                    <input type="email" class="form-control bo-form-control" name="email"
                                        value="<?= htmlspecialchars($_POST['email'] ?? $f->email ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Website</label>
                                    <input type="text" class="form-control bo-form-control" name="website"
                                        value="<?= htmlspecialchars($_POST['website'] ?? $f->website ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="bo-form-label">Morada</label>
                                    <input type="text" class="form-control bo-form-control" name="morada"
                                        value="<?= htmlspecialchars($_POST['morada'] ?? $f->morada ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <span class="text-muted" style="font-size:0.82rem;"><span class="text-danger">*</span> campos obrigatórios</span>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="goToTab('tab-contacto')">
                                Próximo <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Pessoa de Contacto -->
                    <div class="tab-pane fade" id="tab-contacto" role="tabpanel">
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="bo-form-label">Nome</label>
                                    <input type="text" class="form-control bo-form-control" name="pessoa_contacto"
                                        value="<?= htmlspecialchars($_POST['pessoa_contacto'] ?? $f->pessoa_contacto ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="bo-form-label">Telefone</label>
                                    <input type="text" class="form-control bo-form-control" name="telefone_contacto"
                                        value="<?= htmlspecialchars($_POST['telefone_contacto'] ?? $f->telefone_contacto ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-empresa')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="goToTab('tab-obs')">
                                Próximo <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Observações -->
                    <div class="tab-pane fade" id="tab-obs" role="tabpanel">
                        <div class="bo-card-body">
                            <label class="bo-form-label">Observações</label>
                            <textarea class="form-control bo-form-control" name="observacoes" rows="5"><?= htmlspecialchars($_POST['observacoes'] ?? $f->observacoes ?? '') ?></textarea>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-contacto')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="submit" class="btn btn-outline-warning btn-sm">
                                <i class="fa-regular fa-floppy-disk me-1"></i> Guardar Alterações
                            </button>
                        </div>
                    </div>

                </div>
            </form>

        </main>
    </div>
</div>

<script>
function goToTab(tabId) {
    const tab = document.querySelector('[data-bs-target="#' + tabId + '"]');
    if (tab) new bootstrap.Tab(tab).show();
}
<?php if (!empty($erros)): ?>goToTab('tab-empresa');<?php endif; ?>
</script>

<?php include '../../includes/footer.php'; ?>
