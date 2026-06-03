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

$db = get_db();
$localizacoes = $db->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll(PDO::FETCH_OBJ);
$fornecedores = $db->query("SELECT id, nome FROM fornecedores WHERE deleted_at IS NULL ORDER BY nome")->fetchAll(PDO::FETCH_OBJ);

$forn_assoc = $db->prepare("SELECT id_fornecedor FROM equipamento_fornecedor WHERE id_equipamento = ?");
$forn_assoc->execute([$id]);
$forn_assoc_ids = $forn_assoc->fetchAll(PDO::FETCH_COLUMN);

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $designacao        = trim($_POST['designacao'] ?? '');
    $categoria         = $_POST['categoria'] ?? '';
    $marca             = trim($_POST['marca'] ?? '');
    $modelo            = trim($_POST['modelo'] ?? '');
    $num_serie         = trim($_POST['numero_serie'] ?? '');
    $fabricante        = trim($_POST['fabricante'] ?? '');
    $data_aquis        = $_POST['data_aquisicao'] ?? '';
    $ano_fabrico       = $_POST['ano_fabrico'] ?? '';
    $custo             = $_POST['custo_aquisicao'] ?? '';
    $tipo_entrada      = $_POST['tipo_entrada'] ?? 'compra';
    $estado            = $_POST['estado'] ?? 'ativo';
    $criticidade       = $_POST['criticidade'] ?? 'media';
    $id_loc            = $_POST['id_localizacao'] ?? null;
    $observacoes       = trim($_POST['observacoes'] ?? '');
    $assistencia_nome  = trim($_POST['assistencia_nome'] ?? '');
    $assistencia_tel   = trim($_POST['assistencia_telefone'] ?? '');
    $assistencia_email = trim($_POST['assistencia_email'] ?? '');
    $fornecedor_ids    = $_POST['fornecedores'] ?? [];

    if (empty($designacao))       $erros[] = 'A designação é obrigatória.';
    if (empty($categoria))        $erros[] = 'A categoria é obrigatória.';
    if (empty($assistencia_nome)) $erros[] = 'O nome do contacto de assistência técnica é obrigatório.';
    if (empty($assistencia_tel))  $erros[] = 'O telefone de assistência técnica é obrigatório.';

    if (empty($erros)) {
        try {
            $stmt = $db->prepare("
                UPDATE equipamentos SET
                    designacao = ?, categoria = ?, marca = ?, modelo = ?, numero_serie = ?,
                    fabricante = ?, data_aquisicao = ?, ano_fabrico = ?, custo_aquisicao = ?,
                    tipo_entrada = ?, estado = ?, criticidade = ?, id_localizacao = ?, observacoes = ?,
                    assistencia_nome = ?, assistencia_telefone = ?, assistencia_email = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $designacao, $categoria,
                $marca ?: null, $modelo ?: null, $num_serie ?: null, $fabricante ?: null,
                $data_aquis ?: null, $ano_fabrico ?: null, $custo ?: null,
                $tipo_entrada, $estado, $criticidade,
                $id_loc ?: null, $observacoes ?: null,
                $assistencia_nome, $assistencia_tel, $assistencia_email ?: null,
                $id
            ]);

            $db->prepare("DELETE FROM equipamento_fornecedor WHERE id_equipamento = ?")->execute([$id]);
            if (!empty($fornecedor_ids)) {
                $stmt2 = $db->prepare("INSERT INTO equipamento_fornecedor (id_equipamento, id_fornecedor) VALUES (?, ?)");
                foreach ($fornecedor_ids as $fid) {
                    $stmt2->execute([$id, $fid]);
                }
            }

            $db = null;
            header('Location: detalhes.php?id=' . $idEnc);
            exit;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao atualizar: ' . $e->getMessage();
        }
    }
    $forn_assoc_ids = $fornecedor_ids;
}

$eq = $db->prepare("SELECT * FROM equipamentos WHERE id = ? AND deleted_at IS NULL");
$eq->execute([$id]);
$eq = $eq->fetch(PDO::FETCH_OBJ);
$db = null;

if (!$eq) {
    header('Location: lista.php');
    exit;
}

$categorias = ['monitorizacao' => 'Monitorização', 'suporte_vida' => 'Suporte de Vida', 'terapia' => 'Terapia', 'diagnostico' => 'Diagnóstico', 'laboratorio' => 'Laboratório', 'esterilizacao' => 'Esterilização', 'reabilitacao' => 'Reabilitação', 'outro' => 'Outro'];
$estados    = ['ativo' => 'Ativo', 'manutencao' => 'Em manutenção', 'inativo' => 'Inativo', 'calibracao' => 'Em calibração', 'quarentena' => 'Em quarentena', 'abatido' => 'Abatido'];
$crits      = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
$tipos      = ['compra' => 'Compra', 'doacao' => 'Doação', 'aluguer' => 'Aluguer', 'emprestimo' => 'Empréstimo'];
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<style>
.equip-hero {
    background: linear-gradient(135deg, var(--mt-yellow-light) 0%, #fff 70%);
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
    background: var(--mt-yellow-light);
    border-radius: 50%;
    opacity: 0.5;
}
.equip-hero-icon {
    width: 56px; height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, #F9D89C, #D97706);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(217,119,6,0.25);
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
    padding: 0.6rem 1.1rem !important;
    font-weight: 500;
    font-size: 0.85rem;
    color: var(--mt-text-muted) !important;
    background: transparent;
    transition: all 0.2s ease;
    margin-bottom: -2px;
    white-space: nowrap;
    cursor: pointer;
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
.tab-nav-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid var(--mt-border);
    background: var(--mt-bg-alt);
    border-radius: 0 0 var(--mt-radius) var(--mt-radius);
}
.forn-check-card {
    border: 1.5px solid var(--mt-border);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: all 0.18s ease;
    background: var(--mt-bg);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.forn-check-card:hover { border-color: var(--mt-blue); background: var(--mt-blue-light); }
.forn-check-card.selected { border-color: var(--mt-blue-dark); background: var(--mt-blue-light); }
</style>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <!-- Hero header -->
            <div class="equip-hero mb-4">
                <div class="d-flex align-items-center gap-3 position-relative">
                    <div class="equip-hero-icon">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h1 style="font-family:var(--font-display);font-size:1.5rem;margin:0;color:var(--mt-text);">
                            <?= htmlspecialchars($eq->designacao) ?>
                        </h1>
                        <code style="font-size:0.8rem;background:rgba(217,119,6,0.12);color:#B45309;padding:2px 8px;border-radius:6px;">
                            <?= htmlspecialchars($eq->codigo_inventario) ?>
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
                    <ul class="mb-0 mt-1">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if (!empty($erro_sistema)): ?>
                <div class="alert alert-danger rounded-3 mb-3"><?= htmlspecialchars($erro_sistema) ?></div>
            <?php endif; ?>

            <form id="form-editar" action="editar.php?id=<?= $idEnc ?>" method="post" novalidate>

                <!-- Tabs nav -->
                <ul class="nav equip-tabs mb-0" id="editarTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-identificacao" type="button" role="tab">
                            <i class="fa-solid fa-tag me-1"></i>Identificação
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-aquisicao" type="button" role="tab">
                            <i class="fa-solid fa-euro-sign me-1"></i>Aquisição
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-estado" type="button" role="tab">
                            <i class="fa-solid fa-location-dot me-1"></i>Estado & Localização
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fornecedores" type="button" role="tab">
                            <i class="fa-solid fa-truck-medical me-1"></i>Fornecedores
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-assistencia" type="button" role="tab">
                            <i class="fa-solid fa-headset me-1"></i>Assistência Técnica
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-obs" type="button" role="tab">
                            <i class="fa-solid fa-note-sticky me-1"></i>Observações
                        </button>
                    </li>
                </ul>

                <div class="tab-content bo-card" style="border-top:none;border-radius:0 0 var(--mt-radius) var(--mt-radius);" id="editarTabsContent">

                    <!-- Tab: Identificação -->
                    <div class="tab-pane fade show active" id="tab-identificacao" role="tabpanel">
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="bo-form-label">Código de Inventário</label>
                                    <input type="text" class="form-control bo-form-control"
                                        value="<?= htmlspecialchars($eq->codigo_inventario) ?>"
                                        readonly style="background:var(--mt-bg-alt);color:var(--mt-text-muted);cursor:default;">
                                </div>
                                <div class="col-md-8">
                                    <label class="bo-form-label">Designação <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bo-form-control" name="designacao"
                                        value="<?= htmlspecialchars($_POST['designacao'] ?? $eq->designacao) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Categoria <span class="text-danger">*</span></label>
                                    <select class="form-select bo-form-control" name="categoria">
                                        <?php $cat_atual = $_POST['categoria'] ?? $eq->categoria;
                                        foreach ($categorias as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= $cat_atual == $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Marca</label>
                                    <input type="text" class="form-control bo-form-control" name="marca"
                                        value="<?= htmlspecialchars($_POST['marca'] ?? $eq->marca ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Modelo</label>
                                    <input type="text" class="form-control bo-form-control" name="modelo"
                                        value="<?= htmlspecialchars($_POST['modelo'] ?? $eq->modelo ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Número de Série</label>
                                    <input type="text" class="form-control bo-form-control" name="numero_serie"
                                        value="<?= htmlspecialchars($_POST['numero_serie'] ?? $eq->numero_serie ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Fabricante</label>
                                    <input type="text" class="form-control bo-form-control" name="fabricante"
                                        value="<?= htmlspecialchars($_POST['fabricante'] ?? $eq->fabricante ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <span class="text-muted" style="font-size:0.82rem;"><span class="text-danger">*</span> campos obrigatórios</span>
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-aquisicao')" style="padding:0.35rem 1rem;font-size:0.875rem;">
                                Próximo <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Aquisição -->
                    <div class="tab-pane fade" id="tab-aquisicao" role="tabpanel">
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="bo-form-label">Data de Aquisição</label>
                                    <input type="text" class="form-control bo-form-control" name="data_aquisicao"
                                        id="data_aquisicao"
                                        value="<?= htmlspecialchars($_POST['data_aquisicao'] ?? ($eq->data_aquisicao ? date('Y-m-d', strtotime($eq->data_aquisicao)) : '')) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">Ano de Fabrico</label>
                                    <input type="number" class="form-control bo-form-control" name="ano_fabrico"
                                        value="<?= htmlspecialchars($_POST['ano_fabrico'] ?? $eq->ano_fabrico ?? '') ?>"
                                        min="1900" max="<?= date('Y') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">Custo de Aquisição (€)</label>
                                    <input type="number" class="form-control bo-form-control" name="custo_aquisicao"
                                        value="<?= htmlspecialchars($_POST['custo_aquisicao'] ?? $eq->custo_aquisicao ?? '') ?>"
                                        step="0.01" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">Tipo de Entrada</label>
                                    <select class="form-select bo-form-control" name="tipo_entrada">
                                        <?php $te_atual = $_POST['tipo_entrada'] ?? $eq->tipo_entrada;
                                        foreach ($tipos as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= $te_atual == $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-identificacao')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-estado')" style="padding:0.35rem 1rem;font-size:0.875rem;">
                                Próximo <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Estado & Localização -->
                    <div class="tab-pane fade" id="tab-estado" role="tabpanel">
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="bo-form-label">Estado</label>
                                    <select class="form-select bo-form-control" name="estado">
                                        <?php $est_atual = $_POST['estado'] ?? $eq->estado;
                                        foreach ($estados as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= $est_atual == $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Criticidade</label>
                                    <select class="form-select bo-form-control" name="criticidade">
                                        <?php $crit_atual = $_POST['criticidade'] ?? $eq->criticidade;
                                        foreach ($crits as $val => $label): ?>
                                            <option value="<?= $val ?>" <?= $crit_atual == $val ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Localização</label>
                                    <div class="d-flex gap-2">
                                        <select class="form-select bo-form-control" name="id_localizacao" id="select-localizacao">
                                            <option value="">Sem localização</option>
                                            <?php $loc_atual = $_POST['id_localizacao'] ?? $eq->id_localizacao;
                                            foreach ($localizacoes as $loc): ?>
                                                <option value="<?= $loc->id ?>" <?= $loc_atual == $loc->id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($loc->servico . ($loc->sala ? ' — ' . $loc->sala : '')) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-outline-warning flex-shrink-0"
                                            data-bs-toggle="modal" data-bs-target="#modalNovaLocalizacao"
                                            style="padding:0.5rem 0.75rem;" title="Nova Localização">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-aquisicao')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-fornecedores')" style="padding:0.35rem 1rem;font-size:0.875rem;">
                                Próximo <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Fornecedores -->
                    <div class="tab-pane fade" id="tab-fornecedores" role="tabpanel">
                        <div class="bo-card-body">
                            <p class="text-muted mb-3" style="font-size:0.85rem;">Selecione os fornecedores associados a este equipamento.</p>
                            <?php if (empty($fornecedores)): ?>
                                <div class="text-center py-4">
                                    <i class="fa-solid fa-truck-medical fa-2x mb-2" style="color:var(--mt-border);"></i>
                                    <p class="text-muted mb-0" style="font-size:0.9rem;">Sem fornecedores registados.</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-2">
                                    <?php foreach ($fornecedores as $forn):
                                        $checked = in_array($forn->id, $forn_assoc_ids);
                                    ?>
                                        <div class="col-md-4">
                                            <label class="forn-check-card <?= $checked ? 'selected' : '' ?>" for="forn_<?= $forn->id ?>">
                                                <input class="form-check-input m-0 flex-shrink-0" type="checkbox"
                                                    name="fornecedores[]" value="<?= $forn->id ?>"
                                                    id="forn_<?= $forn->id ?>" <?= $checked ? 'checked' : '' ?>
                                                    onchange="this.closest('.forn-check-card').classList.toggle('selected', this.checked)">
                                                <span style="font-size:0.9rem;font-weight:500;"><?= htmlspecialchars($forn->nome) ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-estado')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-assistencia')" style="padding:0.35rem 1rem;font-size:0.875rem;">
                                Próximo <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Assistência Técnica -->
                    <div class="tab-pane fade" id="tab-assistencia" role="tabpanel">
                        <div class="bo-card-body">
                            <p class="text-muted mb-3" style="font-size:0.85rem;">Contacto responsável pela manutenção e assistência técnica deste equipamento.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="bo-form-label">Nome <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bo-form-control" name="assistencia_nome"
                                        value="<?= htmlspecialchars($_POST['assistencia_nome'] ?? $eq->assistencia_nome ?? '') ?>"
                                        placeholder="ex: João Silva / TechMed Serviços">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">Telefone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bo-form-control" name="assistencia_telefone"
                                        value="<?= htmlspecialchars($_POST['assistencia_telefone'] ?? $eq->assistencia_telefone ?? '') ?>"
                                        placeholder="ex: 912 345 678">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">Email</label>
                                    <input type="email" class="form-control bo-form-control" name="assistencia_email"
                                        value="<?= htmlspecialchars($_POST['assistencia_email'] ?? $eq->assistencia_email ?? '') ?>"
                                        placeholder="ex: suporte@empresa.pt">
                                </div>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-fornecedores')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-obs')" style="padding:0.35rem 1rem;font-size:0.875rem;">
                                Próximo <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Observações -->
                    <div class="tab-pane fade" id="tab-obs" role="tabpanel">
                        <div class="bo-card-body">
                            <label class="bo-form-label">Observações</label>
                            <textarea class="form-control bo-form-control" name="observacoes" rows="5"
                                placeholder="Notas adicionais sobre o equipamento..."><?= htmlspecialchars($_POST['observacoes'] ?? $eq->observacoes ?? '') ?></textarea>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-assistencia')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="submit" class="btn btn-outline-warning btn-sm">
                                <i class="fa-regular fa-floppy-disk me-1"></i> Guardar Alterações
                            </button>
                        </div>
                    </div>

                </div><!-- /tab-content -->

            </form>

        </main>
    </div>
</div>

<!-- Modal: Nova Localização -->
<div class="modal fade" id="modalNovaLocalizacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--mt-radius);border:1px solid var(--mt-border);">
            <div class="modal-header" style="background:var(--mt-bg-alt);border-bottom:1px solid var(--mt-border);">
                <h5 class="modal-title" style="font-family:var(--font-display);">
                    <i class="fa-solid fa-location-dot me-2" style="color:var(--mt-blue-dark);"></i>Nova Localização
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="loc-modal-erro" class="alert alert-danger d-none mb-3" style="font-size:0.88rem;"></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="bo-form-label">Serviço / Departamento <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bo-form-control" id="loc-servico"
                            placeholder="ex: Unidade de Cuidados Intensivos">
                    </div>
                    <div class="col-md-6">
                        <label class="bo-form-label">Sala / Gabinete</label>
                        <input type="text" class="form-control bo-form-control" id="loc-sala"
                            placeholder="ex: Sala de Reanimação">
                    </div>
                    <div class="col-md-6">
                        <label class="bo-form-label">Piso</label>
                        <input type="text" class="form-control bo-form-control" id="loc-piso"
                            placeholder="ex: Piso 2">
                    </div>
                    <div class="col-md-6">
                        <label class="bo-form-label">Edifício</label>
                        <input type="text" class="form-control bo-form-control" id="loc-edificio"
                            placeholder="ex: Edifício Principal">
                    </div>
                    <div class="col-12">
                        <label class="bo-form-label">Observações</label>
                        <textarea class="form-control bo-form-control" id="loc-observacoes" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--mt-border);background:var(--mt-bg-alt);">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-outline-warning btn-sm" id="btn-guardar-localizacao">
                    <i class="fa-regular fa-floppy-disk me-1"></i>Guardar Localização
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function goToTab(tabId) {
    const tab = document.querySelector('[data-bs-target="#' + tabId + '"]');
    if (tab) new bootstrap.Tab(tab).show();
}

flatpickr("#data_aquisicao", { dateFormat: "Y-m-d" });

<?php if (!empty($erros)): ?>
goToTab('tab-identificacao');
<?php endif; ?>

document.getElementById('btn-guardar-localizacao').addEventListener('click', function () {
    const erroDiv = document.getElementById('loc-modal-erro');
    erroDiv.classList.add('d-none');
    erroDiv.textContent = '';

    const servico = document.getElementById('loc-servico').value.trim();
    if (!servico) {
        erroDiv.textContent = 'O serviço/departamento é obrigatório.';
        erroDiv.classList.remove('d-none');
        return;
    }

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>A guardar...';

    const data = new FormData();
    data.append('servico',     servico);
    data.append('sala',        document.getElementById('loc-sala').value.trim());
    data.append('piso',        document.getElementById('loc-piso').value.trim());
    data.append('edificio',    document.getElementById('loc-edificio').value.trim());
    data.append('observacoes', document.getElementById('loc-observacoes').value.trim());

    fetch('/MediTrack/private/views/localizacoes/ajax_novo.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                erroDiv.textContent = res.erro;
                erroDiv.classList.remove('d-none');
                return;
            }

            const select = document.getElementById('select-localizacao');
            const option = document.createElement('option');
            option.value    = res.id;
            option.text     = res.label;
            option.selected = true;
            select.appendChild(option);

            bootstrap.Modal.getInstance(document.getElementById('modalNovaLocalizacao')).hide();
            ['loc-servico','loc-sala','loc-piso','loc-edificio','loc-observacoes']
                .forEach(id => document.getElementById(id).value = '');
        })
        .catch(() => {
            erroDiv.textContent = 'Erro de ligação. Tente novamente.';
            erroDiv.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-regular fa-floppy-disk me-1"></i>Guardar Localização';
        });
});
</script>

<?php include '../../includes/footer.php'; ?>
