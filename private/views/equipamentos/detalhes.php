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

$fornecedores = $db->prepare("
    SELECT f.nome, f.tipo, f.telefone, f.email
    FROM fornecedores f
    JOIN equipamento_fornecedor ef ON f.id = ef.id_fornecedor
    WHERE ef.id_equipamento = ?
");
$fornecedores->execute([$id]);
$fornecedores = $fornecedores->fetchAll(PDO::FETCH_OBJ);

$documentos = $db->prepare("SELECT * FROM documentos WHERE id_equipamento = ? ORDER BY created_at DESC");
$documentos->execute([$id]);
$documentos = $documentos->fetchAll(PDO::FETCH_OBJ);

$garantia = $db->prepare("SELECT * FROM garantias WHERE id_equipamento = ?");
$garantia->execute([$id]);
$garantia = $garantia->fetch(PDO::FETCH_OBJ);

$componentes = $db->prepare("SELECT * FROM componentes WHERE id_equipamento = ? ORDER BY codigo ASC, designacao ASC");
$componentes->execute([$id]);
$componentes = $componentes->fetchAll(PDO::FETCH_OBJ);

$db = null;

$estado_labels = [
    'ativo'       => 'Ativo',
    'manutencao'  => 'Em manutenção',
    'inativo'     => 'Inativo',
    'calibracao'  => 'Em calibração',
    'quarentena'  => 'Em quarentena',
    'abatido'     => 'Abatido'
];
$crit_labels = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
$cat_labels  = [
    'monitorizacao' => 'Monitorização',
    'suporte_vida'  => 'Suporte de Vida',
    'terapia'       => 'Terapia',
    'diagnostico'   => 'Diagnóstico',
    'laboratorio'   => 'Laboratório',
    'esterilizacao' => 'Esterilização',
    'reabilitacao'  => 'Reabilitação',
    'outro'         => 'Outro'
];
$ec = ['ativo' => 'badge-ativo', 'manutencao' => 'badge-manutencao', 'inativo' => 'badge-inativo', 'calibracao' => 'badge-manutencao', 'quarentena' => 'badge-manutencao', 'abatido' => 'badge-inativo'];
$cc = ['baixa' => 'badge-baixa', 'media' => 'badge-media', 'alta' => 'badge-alta', 'suporte_vida' => 'badge-suporte'];
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<style>
.equip-hero {
    background: linear-gradient(135deg, var(--mt-blue-light) 0%, #fff 70%);
    border: 1px solid var(--mt-border);
    border-radius: var(--mt-radius);
    padding: 1.75rem 2rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}
.equip-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 160px; height: 160px;
    background: var(--mt-blue-light);
    border-radius: 50%;
    opacity: 0.5;
}
.equip-hero-icon {
    width: 56px; height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, var(--mt-blue), var(--mt-blue-dark));
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(74,144,184,0.3);
}
.equip-tabs {
    border-bottom: 2px solid var(--mt-border);
    gap: 0.15rem;
    flex-wrap: nowrap;
    overflow-x: auto;
}
.equip-tabs .nav-link {
    border: none !important;
    border-radius: 10px 10px 0 0 !important;
    padding: 0.6rem 1rem !important;
    font-weight: 500;
    font-size: 0.85rem;
    color: var(--mt-text-muted) !important;
    background: transparent;
    transition: all 0.2s ease;
    margin-bottom: -2px;
    white-space: nowrap;
}
.equip-tabs .nav-link:hover {
    color: var(--mt-blue-dark) !important;
    background: var(--mt-blue-light);
}
.equip-tabs .nav-link.active {
    color: var(--mt-blue-dark) !important;
    background: var(--mt-white) !important;
    border-bottom: 2px solid var(--mt-blue-dark) !important;
    font-weight: 600;
}
.equip-tabs .tab-badge {
    background: var(--mt-blue-light);
    color: var(--mt-blue-dark);
    font-size: 0.7rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 100px;
    margin-left: 4px;
}
.equip-tabs .nav-link.active .tab-badge {
    background: var(--mt-blue-dark);
    color: #fff;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0;
}
.info-cell {
    padding: 1rem 1.25rem;
    border-right: 1px solid var(--mt-border);
    border-bottom: 1px solid var(--mt-border);
}
.info-cell:nth-child(3n) { border-right: none; }
.info-cell .ic-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--mt-text-muted);
    margin-bottom: 0.3rem;
    display: flex;
    align-items: center;
    gap: 5px;
}
.info-cell .ic-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--mt-text);
}
.obs-block {
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--mt-border);
}
.obs-block .ic-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--mt-text-muted);
    margin-bottom: 0.4rem;
}
.doc-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.85rem 1rem;
    border-radius: 10px;
    border: 1px solid var(--mt-border);
    background: var(--mt-bg);
    transition: all 0.2s ease;
    margin-bottom: 0.6rem;
}
.doc-item:last-child { margin-bottom: 0; }
.doc-item:hover { border-color: var(--mt-blue); background: var(--mt-blue-light); }
.doc-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: var(--mt-blue-light);
    color: var(--mt-blue-dark);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.forn-card {
    border: 1px solid var(--mt-border);
    border-radius: 14px;
    padding: 1.25rem 1.4rem;
    background: var(--mt-bg);
    transition: all 0.2s ease;
    height: 100%;
}
.forn-card:hover { border-color: var(--mt-blue); box-shadow: var(--mt-shadow); }
.forn-avatar {
    width: 46px; height: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--mt-blue), var(--mt-blue-dark));
    color: white;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.loc-block {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid var(--mt-border);
    border-radius: 12px;
    background: var(--mt-bg);
    margin-bottom: 0.6rem;
}
.loc-block:last-child { margin-bottom: 0; }
.loc-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: var(--mt-green-light);
    color: #2D8653;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}
.garantia-banner {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.25rem;
    font-size: 0.9rem;
}
.garantia-banner.valida   { background: var(--mt-green-light); border: 1px solid #A8D5BA; color: #2D8653; }
.garantia-banner.expirada { background: var(--mt-pink-light);  border: 1px solid var(--mt-pink); color: #C0526B; }
.garantia-banner.sem      { background: var(--mt-bg-alt); border: 1px solid var(--mt-border); color: var(--mt-text-muted); }
.garantia-fields {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
}
.g-field .gf-label {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--mt-text-muted);
    margin-bottom: 0.25rem;
}
.g-field .gf-value {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--mt-text);
}
</style>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <?php include '../../includes/breadcrumb.php'; ?>

            <!-- Hero header -->
            <div class="equip-hero mb-4">
                <div class="d-flex align-items-center gap-3 position-relative">
                    <div class="equip-hero-icon">
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h1 style="font-family:var(--font-display);font-size:1.5rem;margin:0;color:var(--mt-text);">
                            <?= htmlspecialchars($eq->designacao) ?>
                        </h1>
                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                            <code style="font-size:0.8rem;background:rgba(74,144,184,0.12);color:var(--mt-blue-dark);padding:2px 8px;border-radius:6px;">
                                <?= htmlspecialchars($eq->codigo_inventario) ?>
                            </code>
                            <span class="badge-criticidade <?= $ec[$eq->estado] ?? 'badge-inativo' ?>">
                                <?= $estado_labels[$eq->estado] ?? $eq->estado ?>
                            </span>
                            <span class="badge-criticidade <?= $cc[$eq->criticidade] ?? 'badge-baixa' ?>">
                                <?= $crit_labels[$eq->criticidade] ?? $eq->criticidade ?>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="editar.php?id=<?= $idEnc ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fa-regular fa-pen-to-square me-1"></i>Editar
                        </a>
                        <a href="lista.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i>Voltar
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav equip-tabs mb-0" id="equipTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-geral" type="button" role="tab">
                        <i class="fa-solid fa-info-circle me-1"></i>Informação Geral
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-localizacao" type="button" role="tab">
                        <i class="fa-solid fa-location-dot me-1"></i>Localização
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-garantia" type="button" role="tab">
                        <i class="fa-solid fa-file-signature me-1"></i>Garantia
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documentos" type="button" role="tab">
                        <i class="fa-solid fa-folder-open me-1"></i>Documentos
                        <?php if (count($documentos) > 0): ?>
                            <span class="tab-badge"><?= count($documentos) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-componentes" type="button" role="tab">
                        <i class="fa-solid fa-puzzle-piece me-1"></i>Componentes
                        <?php if (count($componentes) > 0): ?>
                            <span class="tab-badge"><?= count($componentes) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fornecedores" type="button" role="tab">
                        <i class="fa-solid fa-truck-medical me-1"></i>Fornecedores
                        <?php if (count($fornecedores) > 0): ?>
                            <span class="tab-badge"><?= count($fornecedores) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-assistencia" type="button" role="tab">
                        <i class="fa-solid fa-headset me-1"></i>Assistência Técnica
                    </button>
                </li>
            </ul>

            <!-- Conteúdo das tabs -->
            <div class="tab-content bo-card" style="border-top:none;border-radius:0 0 var(--mt-radius) var(--mt-radius);" id="equipTabsContent">

                <!-- Tab: Informação Geral -->
                <div class="tab-pane fade show active" id="tab-geral" role="tabpanel">
                    <table class="w-100 mb-0" style="border-collapse:collapse;">
                        <?php
                        $fields = [
                            ['Designação',       htmlspecialchars($eq->designacao)],
                            ['Categoria',        $cat_labels[$eq->categoria] ?? $eq->categoria],
                            ['Marca',            htmlspecialchars($eq->marca ?? '—')],
                            ['Modelo',           htmlspecialchars($eq->modelo ?? '—')],
                            ['Número de Série',  htmlspecialchars($eq->numero_serie ?? '—')],
                            ['Fabricante',       htmlspecialchars($eq->fabricante ?? '—')],
                            ['Ano de Fabrico',   htmlspecialchars($eq->ano_fabrico ?? '—')],
                            ['Data de Aquisição',$eq->data_aquisicao ? date('d/m/Y', strtotime($eq->data_aquisicao)) : '—'],
                            ['Custo de Aquisição',$eq->custo_aquisicao ? number_format($eq->custo_aquisicao, 2, ',', '.') . ' €' : '—'],
                            ['Tipo de Entrada',  ucfirst($eq->tipo_entrada ?? '—')],
                        ];
                        foreach ($fields as $i => [$label, $value]):
                        ?>
                            <tr style="border-bottom:1px solid var(--mt-border);">
                                <td style="width:38%;padding:0.85rem 1.5rem;font-size:0.85rem;font-weight:500;color:var(--mt-text-muted);background:var(--mt-bg-alt);">
                                    <?= $label ?>
                                </td>
                                <td style="padding:0.85rem 1.5rem;font-size:0.9rem;font-weight:600;color:var(--mt-text);">
                                    <?= $value ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($eq->observacoes): ?>
                            <tr>
                                <td style="padding:0.85rem 1.5rem;font-size:0.85rem;font-weight:500;color:var(--mt-text-muted);background:var(--mt-bg-alt);vertical-align:top;">
                                    Observações
                                </td>
                                <td style="padding:0.85rem 1.5rem;font-size:0.9rem;color:var(--mt-text);">
                                    <?= nl2br(htmlspecialchars($eq->observacoes)) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Tab: Localização -->
                <div class="tab-pane fade" id="tab-localizacao" role="tabpanel">
                    <div class="bo-card-body">
                        <?php if ($eq->servico): ?>
                            <div class="row g-3">
                                <?php
                                $loc_items = [
                                    ['fa-solid fa-briefcase-medical', 'Serviço', $eq->servico],
                                    ['fa-solid fa-door-closed', 'Sala', $eq->sala],
                                    ['fa-solid fa-layer-group', 'Piso', $eq->piso],
                                    ['fa-solid fa-building', 'Edifício', $eq->edificio],
                                ];
                                foreach ($loc_items as [$icon, $label, $val]):
                                    if (!$val) continue;
                                ?>
                                    <div class="col-sm-6 col-md-3">
                                        <div class="loc-block">
                                            <div class="loc-icon"><i class="<?= $icon ?>"></i></div>
                                            <div>
                                                <div style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.6px;color:var(--mt-text-muted);margin-bottom:0.2rem;"><?= $label ?></div>
                                                <div style="font-size:0.95rem;font-weight:600;color:var(--mt-text);"><?= htmlspecialchars($val) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fa-solid fa-location-dot fa-2x mb-2" style="color:var(--mt-border);"></i>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem localização definida.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab: Garantia -->
                <div class="tab-pane fade" id="tab-garantia" role="tabpanel">
                    <div class="bo-card-body">
                        <?php if ($garantia): ?>
                            <?php
                            $expirada = $garantia->data_fim && strtotime($garantia->data_fim) < time();
                            $dias_restantes = $garantia->data_fim ? ceil((strtotime($garantia->data_fim) - time()) / 86400) : null;
                            ?>
                            <div class="garantia-banner <?= $expirada ? 'expirada' : 'valida' ?>">
                                <i class="fa-solid <?= $expirada ? 'fa-triangle-exclamation' : 'fa-circle-check' ?> fa-lg"></i>
                                <div>
                                    <?php if ($expirada): ?>
                                        <strong>Garantia expirada</strong> — expirou em <?= date('d/m/Y', strtotime($garantia->data_fim)) ?>
                                    <?php else: ?>
                                        <strong>Garantia válida</strong><?= $dias_restantes !== null ? " — termina em <strong>{$dias_restantes} dias</strong>" : '' ?>
                                    <?php endif; ?>
                                </div>
                                <div class="ms-auto">
                                    <a href="/MediTrack/private/views/garantias/editar.php?id=<?= aes_encrypt($garantia->id) ?>"
                                        class="btn btn-sm btn-outline-warning">
                                        <i class="fa-regular fa-pen-to-square me-1"></i>Editar
                                    </a>
                                </div>
                            </div>
                            <div class="garantia-fields">
                                <?php if ($garantia->data_inicio): ?>
                                    <div class="g-field">
                                        <div class="gf-label"><i class="fa-regular fa-calendar me-1"></i>Início</div>
                                        <div class="gf-value"><?= date('d/m/Y', strtotime($garantia->data_inicio)) ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($garantia->data_fim): ?>
                                    <div class="g-field">
                                        <div class="gf-label"><i class="fa-regular fa-calendar-xmark me-1"></i>Fim</div>
                                        <div class="gf-value <?= $expirada ? 'text-danger' : '' ?>"><?= date('d/m/Y', strtotime($garantia->data_fim)) ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($garantia->tem_contrato): ?>
                                    <div class="g-field">
                                        <div class="gf-label"><i class="fa-solid fa-file-contract me-1"></i>Contrato</div>
                                        <div class="gf-value"><?= htmlspecialchars($garantia->tipo_contrato ?? 'Sim') ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($garantia->entidade_responsavel): ?>
                                    <div class="g-field">
                                        <div class="gf-label"><i class="fa-solid fa-building me-1"></i>Entidade</div>
                                        <div class="gf-value"><?= htmlspecialchars($garantia->entidade_responsavel) ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($garantia->periodicidade): ?>
                                    <div class="g-field">
                                        <div class="gf-label"><i class="fa-solid fa-rotate me-1"></i>Periodicidade</div>
                                        <div class="gf-value"><?= htmlspecialchars($garantia->periodicidade) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="garantia-banner sem">
                                <i class="fa-solid fa-circle-info fa-lg"></i>
                                <span>Sem informação de garantia registada.</span>
                                <div class="ms-auto">
                                    <a href="/MediTrack/private/views/garantias/novo.php?eq=<?= $idEnc ?>"
                                        class="btn btn-sm btn-mt-primary">
                                        <i class="fa-solid fa-plus me-1"></i>Adicionar
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab: Documentos -->
                <div class="tab-pane fade" id="tab-documentos" role="tabpanel">
                    <div class="bo-card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="mb-0 text-muted" style="font-size:0.85rem;"><?= count($documentos) ?> documento(s) associado(s)</p>
                            <a href="/MediTrack/private/views/documentos/novo.php?eq=<?= $idEnc ?>" class="btn btn-sm btn-mt-primary">
                                <i class="fa-solid fa-plus me-1"></i>Adicionar
                            </a>
                        </div>
                        <?php if (empty($documentos)): ?>
                            <div class="text-center py-4">
                                <i class="fa-solid fa-folder-open fa-2x mb-2" style="color:var(--mt-border);"></i>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem documentos associados.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($documentos as $doc): ?>
                                <div class="doc-item">
                                    <div class="doc-icon"><i class="fa-solid fa-file-lines"></i></div>
                                    <div class="flex-grow-1">
                                        <div style="font-weight:600;font-size:0.9rem;"><?= htmlspecialchars($doc->nome) ?></div>
                                        <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $doc->tipo)) ?></small>
                                    </div>
                                    <?php if ($doc->data_validade): ?>
                                        <small class="text-muted flex-shrink-0">
                                            <i class="fa-regular fa-calendar me-1"></i>Val: <?= date('d/m/Y', strtotime($doc->data_validade)) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab: Componentes -->
                <div class="tab-pane fade" id="tab-componentes" role="tabpanel">
                    <div class="bo-card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <p class="mb-0 text-muted" style="font-size:0.85rem;"><?= count($componentes) ?> componente(s) registado(s)</p>
                            <a href="/MediTrack/private/views/componentes/novo.php?eq=<?= $idEnc ?>" class="btn btn-sm btn-mt-primary">
                                <i class="fa-solid fa-plus me-1"></i>Adicionar
                            </a>
                        </div>
                        <?php if (empty($componentes)): ?>
                            <div class="text-center py-4">
                                <i class="fa-solid fa-puzzle-piece fa-2x mb-2" style="color:var(--mt-border);"></i>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem componentes registados.</p>
                            </div>
                        <?php else: ?>
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr style="border-bottom:2px solid var(--mt-border);">
                                        <th style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Código</th>
                                        <th style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Designação</th>
                                        <th class="text-center" style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Qtd</th>
                                        <th class="text-center" style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($componentes as $comp):
                                        $comp_estado = ['ativo' => 'badge-ativo', 'inativo' => 'badge-inativo', 'substituido' => 'badge-manutencao'];
                                        $comp_label  = ['ativo' => 'Ativo', 'inativo' => 'Inativo', 'substituido' => 'Substituído'];
                                    ?>
                                        <tr style="border-bottom:1px solid var(--mt-border);">
                                            <td><code style="font-size:0.75rem;background:var(--mt-bg-alt);padding:2px 6px;border-radius:5px;"><?= htmlspecialchars($comp->codigo ?? '—') ?></code></td>
                                            <td style="font-size:0.88rem;font-weight:500;"><?= htmlspecialchars($comp->designacao) ?></td>
                                            <td class="text-center" style="font-size:0.88rem;"><?= $comp->quantidade ?></td>
                                            <td class="text-center">
                                                <span class="badge-criticidade <?= $comp_estado[$comp->estado] ?? 'badge-inativo' ?>" style="font-size:0.7rem;">
                                                    <?= $comp_label[$comp->estado] ?? $comp->estado ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="/MediTrack/private/views/componentes/editar.php?id=<?= aes_encrypt($comp->id) ?>"
                                                        class="btn-action btn-action-edit" title="Editar">
                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                    </a>
                                                    <a href="/MediTrack/private/views/componentes/apagar.php?id=<?= aes_encrypt($comp->id) ?>"
                                                        class="btn-action btn-action-delete" title="Apagar"
                                                        onclick="return confirm('Apagar este componente?')">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab: Fornecedores -->
                <div class="tab-pane fade" id="tab-fornecedores" role="tabpanel">
                    <div class="bo-card-body">
                        <?php if (empty($fornecedores)): ?>
                            <div class="text-center py-4">
                                <i class="fa-solid fa-truck-medical fa-2x mb-2" style="color:var(--mt-border);"></i>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Sem fornecedores associados.</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($fornecedores as $forn): ?>
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="forn-card">
                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                <div class="forn-avatar"><i class="fa-solid fa-truck-medical"></i></div>
                                                <div>
                                                    <div style="font-weight:700;font-size:0.92rem;"><?= htmlspecialchars($forn->nome) ?></div>
                                                    <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $forn->tipo)) ?></small>
                                                </div>
                                            </div>
                                            <?php if ($forn->telefone): ?>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <i class="fa-solid fa-phone text-muted" style="font-size:0.8rem;width:14px;"></i>
                                                    <small><?= htmlspecialchars($forn->telefone) ?></small>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($forn->email): ?>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fa-solid fa-envelope text-muted" style="font-size:0.8rem;width:14px;"></i>
                                                    <small><?= htmlspecialchars($forn->email) ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab: Assistência Técnica -->
                <div class="tab-pane fade" id="tab-assistencia" role="tabpanel">
                    <div class="bo-card-body">
                        <?php if ($eq->assistencia_nome): ?>
                            <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background:var(--mt-blue-light);border:1px solid var(--mt-border);">
                                <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--mt-blue),var(--mt-blue-dark));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">
                                    <i class="fa-solid fa-headset"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:1rem;"><?= htmlspecialchars($eq->assistencia_nome) ?></div>
                                    <div class="d-flex gap-3 mt-1 flex-wrap">
                                        <?php if ($eq->assistencia_telefone): ?>
                                            <span style="font-size:0.88rem;"><i class="fa-solid fa-phone me-1 text-muted"></i><?= htmlspecialchars($eq->assistencia_telefone) ?></span>
                                        <?php endif; ?>
                                        <?php if ($eq->assistencia_email): ?>
                                            <span style="font-size:0.88rem;"><i class="fa-solid fa-envelope me-1 text-muted"></i><?= htmlspecialchars($eq->assistencia_email) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ms-auto">
                                    <a href="editar.php?id=<?= $idEnc ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="fa-regular fa-pen-to-square me-1"></i>Editar
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fa-solid fa-headset fa-2x mb-2" style="color:var(--mt-border);"></i>
                                <p class="text-muted mb-2" style="font-size:0.9rem;">Sem contacto de assistência técnica definido.</p>
                                <a href="editar.php?id=<?= $idEnc ?>" class="btn btn-sm btn-mt-primary">
                                    <i class="fa-solid fa-plus me-1"></i>Adicionar
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /tab-content -->

        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
