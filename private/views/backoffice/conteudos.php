<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

// Apenas admin pode aceder
if (($_SESSION['profile'] ?? '') !== 'admin') {
    header('Location: /MediTrack/private/home.php');
    exit;
}

$db = get_db();
$sucesso = '';
$erro    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $db->prepare("UPDATE conteudos_publicos SET valor = ? WHERE chave = ?");

        foreach ($_POST['conteudo'] as $chave => $valor) {
            $stmt->execute([trim($valor), $chave]);
        }

        $sucesso = 'Conteúdos atualizados com sucesso!';
    } catch (PDOException $e) {
        $erro = 'Erro ao guardar: ' . $e->getMessage();
    }
}

// Carregar todos os conteúdos
$rows = $db->query("SELECT chave, valor FROM conteudos_publicos ORDER BY id")->fetchAll(PDO::FETCH_KEY_PAIR);
$db   = null;
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <div class="mb-4">
                <h1 class="bo-page-title">
                    <i class="fa-solid fa-pen-to-square me-2" style="color: var(--mt-blue-dark);"></i>Área Pública
                </h1>
                <p class="bo-page-subtitle">Edite os textos e informações visíveis no site público</p>
            </div>

            <?php if ($sucesso): ?>
                <div class="alert alert-success rounded-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <?= htmlspecialchars($sucesso) ?>
                </div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="alert alert-danger rounded-3"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="post" action="conteudos.php" novalidate>
                <div class="row g-4">

                    <!-- Secção Hero -->
                    <div class="col-12">
                        <div class="bo-card">
                            <div class="bo-card-header">
                                <h5><i class="fa-solid fa-house me-2"></i>Secção Principal</h5>
                            </div>
                            <div class="bo-card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="bo-form-label">Título principal</label>
                                        <input type="text" class="form-control bo-form-control"
                                            name="conteudo[hero_titulo]"
                                            value="<?= htmlspecialchars($rows['hero_titulo'] ?? '') ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="bo-form-label">Descrição</label>
                                        <textarea class="form-control bo-form-control" rows="3"
                                            name="conteudo[hero_descricao]"><?= htmlspecialchars($rows['hero_descricao'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Secção Sobre -->
                    <div class="col-12">
                        <div class="bo-card">
                            <div class="bo-card-header">
                                <h5><i class="fa-solid fa-circle-info me-2"></i>Secção Sobre</h5>
                            </div>
                            <div class="bo-card-body">
                                <div class="col-12">
                                    <label class="bo-form-label">Texto sobre a empresa</label>
                                    <textarea class="form-control bo-form-control" rows="4"
                                        name="conteudo[sobre_texto]"><?= htmlspecialchars($rows['sobre_texto'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Secção Contactos -->
                    <div class="col-12">
                        <div class="bo-card">
                            <div class="bo-card-header">
                                <h5><i class="fa-solid fa-address-book me-2"></i>Informações de Contacto</h5>
                            </div>
                            <div class="bo-card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="bo-form-label">Morada</label>
                                        <input type="text" class="form-control bo-form-control"
                                            name="conteudo[contacto_morada]"
                                            value="<?= htmlspecialchars($rows['contacto_morada'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="bo-form-label">Telefone</label>
                                        <input type="text" class="form-control bo-form-control"
                                            name="conteudo[contacto_telefone]"
                                            value="<?= htmlspecialchars($rows['contacto_telefone'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="bo-form-label">Email</label>
                                        <input type="email" class="form-control bo-form-control"
                                            name="conteudo[contacto_email]"
                                            value="<?= htmlspecialchars($rows['contacto_email'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="bo-form-label">Horário de apoio</label>
                                        <input type="text" class="form-control bo-form-control"
                                            name="conteudo[contacto_horario]"
                                            value="<?= htmlspecialchars($rows['contacto_horario'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/MediTrack/public/index.php" target="_blank" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ver site público
                            </a>
                            <button type="submit" class="btn btn-mt-primary">
                                <i class="fa-regular fa-floppy-disk me-1"></i>Guardar alterações
                            </button>
                        </div>
                    </div>

                </div>
            </form>

        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>