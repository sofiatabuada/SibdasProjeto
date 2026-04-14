<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

if (($_SESSION['profile'] ?? '') !== 'admin') {
    header('Location: /MediTrack/private/home.php');
    exit;
}

$db = get_db();

// Marcar como lido se pedido
if (isset($_GET['ler']) && is_numeric($_GET['ler'])) {
    $db->prepare("UPDATE contactos SET lido = 1 WHERE id = ?")->execute([$_GET['ler']]);
    header('Location: mensagens.php');
    exit;
}

// Apagar se pedido
if (isset($_GET['apagar']) && is_numeric($_GET['apagar'])) {
    $db->prepare("DELETE FROM contactos WHERE id = ?")->execute([$_GET['apagar']]);
    header('Location: mensagens.php');
    exit;
}

$mensagens   = $db->query("SELECT * FROM contactos ORDER BY created_at DESC")->fetchAll(PDO::FETCH_OBJ);
$nao_lidas   = $db->query("SELECT COUNT(*) FROM contactos WHERE lido = 0")->fetchColumn();
$db = null;
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="bo-page-title">
                        <i class="fa-solid fa-envelope me-2" style="color: var(--mt-blue-dark);"></i>Mensagens
                    </h1>
                    <p class="bo-page-subtitle">Contactos recebidos através do site público</p>
                </div>
                <?php if ($nao_lidas > 0): ?>
                    <span class="badge bg-danger" style="font-size:0.9rem;">
                        <?= $nao_lidas ?> não lida(s)
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($mensagens)): ?>
                <div class="bo-card">
                    <div class="bo-card-body text-center py-5 text-muted">
                        <i class="fa-solid fa-envelope-open fa-3x mb-3 d-block"></i>
                        Ainda não foram recebidas mensagens.
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($mensagens as $msg): ?>
                        <div class="bo-card <?= !$msg->lido ? 'border-start border-4 border-primary' : '' ?>">
                            <div class="bo-card-header">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!$msg->lido): ?>
                                        <span class="badge bg-primary" style="font-size:0.75rem;">Nova</span>
                                    <?php endif; ?>
                                    <strong><?= htmlspecialchars($msg->nome) ?></strong>
                                    <?php if ($msg->instituicao): ?>
                                        <span class="text-muted" style="font-size:0.85rem;">— <?= htmlspecialchars($msg->instituicao) ?></span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($msg->created_at)) ?>
                                </small>
                            </div>
                            <div class="bo-card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Email</small>
                                        <a href="mailto:<?= htmlspecialchars($msg->email) ?>">
                                            <?= htmlspecialchars($msg->email) ?>
                                        </a>
                                    </div>
                                    <?php if ($msg->telefone): ?>
                                        <div class="col-md-4">
                                            <small class="text-muted d-block">Telefone</small>
                                            <?= htmlspecialchars($msg->telefone) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Assunto</small>
                                        <strong><?= htmlspecialchars($msg->assunto) ?></strong>
                                    </div>
                                </div>
                                <div class="p-3 rounded-3" style="background: var(--mt-bg-alt); font-size:0.92rem;">
                                    <?= nl2br(htmlspecialchars($msg->mensagem)) ?>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <?php if (!$msg->lido): ?>
                                        <a href="mensagens.php?ler=<?= $msg->id ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-check me-1"></i>Marcar como lida
                                        </a>
                                    <?php endif; ?>
                                    <a href="mailto:<?= htmlspecialchars($msg->email) ?>" class="btn btn-sm btn-mt-primary">
                                        <i class="fa-solid fa-reply me-1"></i>Responder
                                    </a>
                                    <a href="mensagens.php?apagar=<?= $msg->id ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Apagar esta mensagem?')">
                                        <i class="fa-solid fa-trash-can me-1"></i>Apagar
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>