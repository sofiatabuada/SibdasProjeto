<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome             = trim($_POST['nome'] ?? '');
    $nif              = trim($_POST['nif'] ?? '');
    $tipo             = $_POST['tipo'] ?? 'outro';
    $telefone         = trim($_POST['telefone'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $morada           = trim($_POST['morada'] ?? '');
    $website          = trim($_POST['website'] ?? '');
    $pessoa_contacto  = trim($_POST['pessoa_contacto'] ?? '');
    $tel_contacto     = trim($_POST['telefone_contacto'] ?? '');
    $observacoes      = trim($_POST['observacoes'] ?? '');

    if (empty($nome)) $erros[] = 'O nome é obrigatório.';
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $erros[] = 'O email não é válido.';

    if (empty($erros)) {
        try {
            $db = get_db();
            $stmt = $db->prepare("
                INSERT INTO fornecedores
                (nome, nif, tipo, telefone, email, morada, website, pessoa_contacto, telefone_contacto, observacoes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nome,
                $nif ?: null,
                $tipo,
                $telefone ?: null,
                $email ?: null,
                $morada ?: null,
                $website ?: null,
                $pessoa_contacto ?: null,
                $tel_contacto ?: null,
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
                    <i class="fa-solid fa-plus me-2" style="color: var(--mt-blue-dark);"></i>Novo Fornecedor
                </h1>
                <p class="bo-page-subtitle">Registe um novo fornecedor no sistema</p>
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
                    <form action="novo.php" method="post" novalidate>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Dados da Empresa</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="bo-form-label">Nome <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bo-form-control" name="nome"
                                    value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="bo-form-label">NIF</label>
                                <input type="text" class="form-control bo-form-control" name="nif"
                                    value="<?= htmlspecialchars($_POST['nif'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="bo-form-label">Tipo</label>
                                <select class="form-select bo-form-control" name="tipo">
                                    <?php
                                    $tipos = ['fabricante' => 'Fabricante', 'distribuidor' => 'Distribuidor', 'assistencia_tecnica' => 'Assistência Técnica', 'consumiveis' => 'Consumíveis', 'outro' => 'Outro'];
                                    foreach ($tipos as $val => $label):
                                        $sel = (($_POST['tipo'] ?? 'outro') == $val) ? 'selected' : '';
                                    ?><option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Telefone</label>
                                <input type="text" class="form-control bo-form-control" name="telefone"
                                    value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Email</label>
                                <input type="email" class="form-control bo-form-control" name="email"
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="bo-form-label">Website</label>
                                <input type="text" class="form-control bo-form-control" name="website"
                                    value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="bo-form-label">Morada</label>
                                <input type="text" class="form-control bo-form-control" name="morada"
                                    value="<?= htmlspecialchars($_POST['morada'] ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3" style="font-family: var(--font-display);">Pessoa de Contacto</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="bo-form-label">Nome</label>
                                <input type="text" class="form-control bo-form-control" name="pessoa_contacto"
                                    value="<?= htmlspecialchars($_POST['pessoa_contacto'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="bo-form-label">Telefone</label>
                                <input type="text" class="form-control bo-form-control" name="telefone_contacto"
                                    value="<?= htmlspecialchars($_POST['telefone_contacto'] ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <label class="bo-form-label">Observações</label>
                            <textarea class="form-control bo-form-control" name="observacoes" rows="3"><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
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

<?php include '../../includes/footer.php'; ?>