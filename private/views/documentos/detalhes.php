<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$idEnc = $_GET['id'] ?? null;
$id    = aes_decrypt($idEnc);
if (!$id || !is_numeric($id)) {
    header('Location: lista.php');
    exit;
}

$db  = get_db();
$doc = $db->prepare("
    SELECT d.*, e.designacao, e.codigo_inventario
    FROM documentos d
    JOIN equipamentos e ON d.id_equipamento = e.id
    WHERE d.id = ?
");
$doc->execute([$id]);
$doc = $doc->fetch(PDO::FETCH_OBJ);
$db  = null;

if (!$doc) {
    header('Location: lista.php');
    exit;
}

$tipos = [
    'manual_utilizador'       => 'Manual de Utilizador',
    'manual_servico'          => 'Manual de Serviço',
    'certificado_calibracao'  => 'Certificado de Calibração',
    'contrato_manutencao'     => 'Contrato de Manutenção',
    'fatura'                  => 'Fatura',
    'declaracao_conformidade' => 'Declaração de Conformidade',
    'relatorio_tecnico'       => 'Relatório Técnico',
    'outro'                   => 'Outro',
];

$upload_dir  = __DIR__ . '/../../uploads/';
$tem_ficheiro = $doc->ficheiro && file_exists($upload_dir . $doc->ficheiro);
$expirado     = $doc->data_validade && strtotime($doc->data_validade) < time();
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
                        <i class="fa-solid fa-file-lines me-2" style="color:var(--mt-blue-dark);"></i>Detalhe do Documento
                    </h1>
                    <p class="bo-page-subtitle"><?= htmlspecialchars($doc->nome) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="editar.php?id=<?= $idEnc ?>" class="btn-action btn-action-edit" title="Editar" style="width:auto; padding:6px 14px;">
                        <i class="fa-regular fa-pen-to-square me-1"></i>Editar
                    </a>
                    <a href="lista.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i>Voltar
                    </a>
                </div>
            </div>

            <div class="row g-4">

                <!-- Informação principal -->
                <div class="col-lg-8">
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-info-circle me-2"></i>Informação do Documento</h5>
                            <span class="badge-criticidade badge-baixa"><?= $tipos[$doc->tipo] ?? ucfirst($doc->tipo) ?></span>
                        </div>
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <small class="text-muted d-block">Nome</small>
                                    <strong><?= htmlspecialchars($doc->nome) ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Equipamento</small>
                                    <strong><?= htmlspecialchars($doc->designacao) ?></strong>
                                    <small class="text-muted d-block"><code><?= htmlspecialchars($doc->codigo_inventario) ?></code></small>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Data do Documento</small>
                                    <strong><?= $doc->data_documento ? date('d/m/Y', strtotime($doc->data_documento)) : '—' ?></strong>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted d-block">Data de Validade</small>
                                    <strong class="<?= $expirado ? 'text-danger' : '' ?>">
                                        <?= $doc->data_validade ? date('d/m/Y', strtotime($doc->data_validade)) : '—' ?>
                                        <?php if ($expirado): ?>
                                            <span class="badge bg-danger ms-1" style="font-size:0.7rem;">Expirado</span>
                                        <?php endif; ?>
                                    </strong>
                                </div>
                                <?php if ($doc->observacoes): ?>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Observações</small>
                                        <strong><?= htmlspecialchars($doc->observacoes) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ficheiro -->
                <div class="col-lg-4">
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-paperclip me-2"></i>Ficheiro</h5>
                        </div>
                        <div class="bo-card-body">
                            <?php if ($tem_ficheiro): ?>
                                <?php
                                $ext  = strtolower(pathinfo($doc->ficheiro, PATHINFO_EXTENSION));
                                $icon = match($ext) {
                                    'pdf'              => 'fa-file-pdf text-danger',
                                    'doc', 'docx'      => 'fa-file-word text-primary',
                                    'xls', 'xlsx'      => 'fa-file-excel text-success',
                                    'jpg','jpeg','png' => 'fa-file-image text-info',
                                    default            => 'fa-file text-muted',
                                };
                                $tamanho = filesize($upload_dir . $doc->ficheiro);
                                $tamanho_fmt = $tamanho > 1048576
                                    ? round($tamanho / 1048576, 1) . ' MB'
                                    : round($tamanho / 1024, 0) . ' KB';
                                ?>
                                <div class="text-center py-3">
                                    <i class="fa-solid <?= $icon ?>" style="font-size:3rem;"></i>
                                    <p class="mt-2 mb-1 fw-semibold" style="font-size:0.85rem; word-break:break-all;">
                                        <?= htmlspecialchars($doc->ficheiro) ?>
                                    </p>
                                    <small class="text-muted"><?= $tamanho_fmt ?></small>
                                    <div class="mt-3 d-grid gap-2">
                                        <a href="download.php?id=<?= $idEnc ?>" target="_blank"
                                            class="btn btn-mt-primary btn-sm">
                                            <i class="fa-solid fa-eye me-1"></i>Abrir ficheiro
                                        </a>
                                        <a href="download.php?id=<?= $idEnc ?>&dl=1"
                                            class="btn btn-outline-secondary btn-sm">
                                            <i class="fa-solid fa-download me-1"></i>Descarregar
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-file-circle-xmark" style="font-size:2.5rem; opacity:0.3;"></i>
                                    <p class="mt-2 mb-0" style="font-size:0.9rem;">Sem ficheiro associado</p>
                                    <a href="editar.php?id=<?= $idEnc ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                        <i class="fa-solid fa-upload me-1"></i>Adicionar ficheiro
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
