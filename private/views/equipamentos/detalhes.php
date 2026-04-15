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

$eq = $db->prepare("
    SELECT e.*, l.edificio, l.piso, l.servico, l.sala
    FROM equipamentos e
    LEFT JOIN localizacoes l ON e.id_localizacao = l.id
    WHERE e.id = ? AND e.deleted_at IS NULL
");
$eq->execute([$id]);
$eq = $eq->fetch(PDO::FETCH_OBJ);

if (!$eq) {
    header('Location: lista.php');
    exit;
}

// Fornecedores associados
$fornecedores = $db->prepare("
    SELECT f.nome, f.tipo, f.telefone, f.email
    FROM fornecedores f
    JOIN equipamento_fornecedor ef ON f.id = ef.id_fornecedor
    WHERE ef.id_equipamento = ?
");
$fornecedores->execute([$id]);
$fornecedores = $fornecedores->fetchAll(PDO::FETCH_OBJ);

// Documentos
$documentos = $db->prepare("SELECT * FROM documentos WHERE id_equipamento = ? ORDER BY created_at DESC");
$documentos->execute([$id]);
$documentos = $documentos->fetchAll(PDO::FETCH_OBJ);

// Garantia
$garantia = $db->prepare("SELECT * FROM garantias WHERE id_equipamento = ?");
$garantia->execute([$id]);
$garantia = $garantia->fetch(PDO::FETCH_OBJ);

$db = null;

$estado_labels = [
    'ativo' => 'Ativo',
    'manutencao' => 'Em manutenção',
    'inativo' => 'Inativo',
    'calibracao' => 'Em calibração',
    'quarentena' => 'Em quarentena',
    'abatido' => 'Abatido'
];
$crit_labels   = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
$cat_labels    = [
    'monitorizacao' => 'Monitorização',
    'suporte_vida' => 'Suporte de Vida',
    'terapia' => 'Terapia',
    'diagnostico' => 'Diagnóstico',
    'laboratorio' => 'Laboratório',
    'esterilizacao' => 'Esterilização',
    'reabilitacao' => 'Reabilitação',
    'outro' => 'Outro'
];
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
                        <i class="fa-solid fa-file-medical me-2" style="color: var(--mt-blue-dark);"></i>Ficha do Equipamento
                    </h1>
                    <p class="bo-page-subtitle"><?= htmlspecialchars($eq->codigo_inventario) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="editar.php?id=<?= $idEnc ?>" class="btn btn-outline-warning btn-sm">
                        <i class="fa-regular fa-pen-to-square me-1"></i>Editar
                    </a>
                    <a href="lista.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left me-1"></i>Voltar
                    </a>
                </div>
            </div>

            <div class="row g-4">

                <!-- Dados principais -->
                <div class="col-lg-8">
                    <div class="bo-card mb-4">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-info-circle me-2"></i>Informação Geral</h5>
                            <div class="d-flex gap-2">
                                <?php
                                $ec = ['ativo' => 'badge-ativo', 'manutencao' => 'badge-manutencao', 'inativo' => 'badge-inativo', 'calibracao' => 'badge-manutencao', 'quarentena' => 'badge-manutencao', 'abatido' => 'badge-inativo'];
                                $cc = ['baixa' => 'badge-baixa', 'media' => 'badge-media', 'alta' => 'badge-alta', 'suporte_vida' => 'badge-suporte'];
                                ?>
                                <span class="badge-criticidade <?= $ec[$eq->estado] ?? 'badge-inativo' ?>">
                                    <?= $estado_labels[$eq->estado] ?? $eq->estado ?>
                                </span>
                                <span class="badge-criticidade <?= $cc[$eq->criticidade] ?? 'badge-baixa' ?>">
                                    <?= $crit_labels[$eq->criticidade] ?? $eq->criticidade ?>
                                </span>
                            </div>
                        </div>
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Designação</small>
                                    <strong><?= htmlspecialchars($eq->designacao) ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Categoria</small>
                                    <strong><?= $cat_labels[$eq->categoria] ?? $eq->categoria ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Marca</small>
                                    <strong><?= htmlspecialchars($eq->marca ?? '—') ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Modelo</small>
                                    <strong><?= htmlspecialchars($eq->modelo ?? '—') ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Número de Série</small>
                                    <strong><?= htmlspecialchars($eq->numero_serie ?? '—') ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Fabricante</small>
                                    <strong><?= htmlspecialchars($eq->fabricante ?? '—') ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Ano de Fabrico</small>
                                    <strong><?= htmlspecialchars($eq->ano_fabrico ?? '—') ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Data de Aquisição</small>
                                    <strong><?= $eq->data_aquisicao ? date('d/m/Y', strtotime($eq->data_aquisicao)) : '—' ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Custo de Aquisição</small>
                                    <strong><?= $eq->custo_aquisicao ? number_format($eq->custo_aquisicao, 2, ',', '.') . ' €' : '—' ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Tipo de Entrada</small>
                                    <strong><?= ucfirst($eq->tipo_entrada ?? '—') ?></strong>
                                </div>
                                <?php if ($eq->observacoes): ?>
                                    <div class="col-12">
                                        <small class="text-muted d-block">Observações</small>
                                        <strong><?= htmlspecialchars($eq->observacoes) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Documentos -->
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-folder-open me-2"></i>Documentos (<?= count($documentos) ?>)</h5>
                            <a href="/MediTrack/private/views/documentos/novo.php?eq=<?= $idEnc ?>" class="btn btn-sm btn-mt-primary">
                                <i class="fa-solid fa-plus me-1"></i>Adicionar
                            </a>
                        </div>
                        <div class="bo-card-body">
                            <?php if (empty($documentos)): ?>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem documentos associados.</p>
                            <?php else: ?>
                                <?php foreach ($documentos as $doc): ?>
                                    <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                        <i class="fa-solid fa-file-lines text-muted"></i>
                                        <div class="flex-grow-1">
                                            <strong style="font-size:0.9rem;"><?= htmlspecialchars($doc->nome) ?></strong>
                                            <small class="text-muted d-block"><?= ucfirst(str_replace('_', ' ', $doc->tipo)) ?></small>
                                        </div>
                                        <?php if ($doc->data_validade): ?>
                                            <small class="text-muted">Val: <?= date('d/m/Y', strtotime($doc->data_validade)) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar de detalhes -->
                <div class="col-lg-4">

                    <!-- Localização -->
                    <div class="bo-card mb-4">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-location-dot me-2"></i>Localização</h5>
                        </div>
                        <div class="bo-card-body">
                            <?php if ($eq->servico): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Serviço</small>
                                    <strong><?= htmlspecialchars($eq->servico) ?></strong>
                                </div>
                                <?php if ($eq->sala): ?>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Sala</small>
                                        <strong><?= htmlspecialchars($eq->sala) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($eq->piso): ?>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Piso</small>
                                        <strong><?= htmlspecialchars($eq->piso) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($eq->edificio): ?>
                                    <div>
                                        <small class="text-muted d-block">Edifício</small>
                                        <strong><?= htmlspecialchars($eq->edificio) ?></strong>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem localização definida.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Fornecedores -->
                    <div class="bo-card mb-4">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-truck-medical me-2"></i>Fornecedores</h5>
                        </div>
                        <div class="bo-card-body">
                            <?php if (empty($fornecedores)): ?>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem fornecedores associados.</p>
                            <?php else: ?>
                                <?php foreach ($fornecedores as $forn): ?>
                                    <div class="mb-3">
                                        <strong style="font-size:0.9rem;"><?= htmlspecialchars($forn->nome) ?></strong>
                                        <small class="text-muted d-block"><?= ucfirst(str_replace('_', ' ', $forn->tipo)) ?></small>
                                        <?php if ($forn->telefone): ?>
                                            <small class="d-block"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($forn->telefone) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Garantia -->
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-file-signature me-2"></i>Garantia</h5>
                            <?php if ($garantia): ?>
                                <a href="/MediTrack/private/views/garantias/editar.php?id=<?= aes_encrypt($garantia->id) ?>"
                                    class="btn btn-sm btn-outline-warning">
                                    <i class="fa-regular fa-pen-to-square me-1"></i>Editar
                                </a>
                            <?php else: ?>
                                <a href="/MediTrack/private/views/garantias/novo.php?eq=<?= $idEnc ?>"
                                    class="btn btn-sm btn-mt-primary">
                                    <i class="fa-solid fa-plus me-1"></i>Adicionar
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="bo-card-body">
                            <?php if ($garantia): ?>
                                <?php if ($garantia->data_inicio): ?>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Início</small>
                                        <strong><?= date('d/m/Y', strtotime($garantia->data_inicio)) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($garantia->data_fim): ?>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Fim</small>
                                        <?php $expirada = strtotime($garantia->data_fim) < time(); ?>
                                        <strong class="<?= $expirada ? 'text-danger' : '' ?>">
                                            <?= date('d/m/Y', strtotime($garantia->data_fim)) ?>
                                            <?= $expirada ? '<span class="badge bg-danger ms-1">Expirada</span>' : '' ?>
                                        </strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($garantia->tem_contrato): ?>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Contrato</small>
                                        <strong><?= htmlspecialchars($garantia->tipo_contrato ?? 'Sim') ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($garantia->entidade_responsavel): ?>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Entidade</small>
                                        <strong><?= htmlspecialchars($garantia->entidade_responsavel) ?></strong>
                                    </div>
                                <?php endif; ?>
                                <?php if ($garantia->periodicidade): ?>
                                    <div>
                                        <small class="text-muted d-block">Periodicidade</small>
                                        <strong><?= htmlspecialchars($garantia->periodicidade) ?></strong>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem informação de garantia.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>