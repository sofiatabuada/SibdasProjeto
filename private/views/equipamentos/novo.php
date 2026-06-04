<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$localizacoes = $db->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll(PDO::FETCH_OBJ);
$fornecedores = $db->query("SELECT id, nome FROM fornecedores WHERE deleted_at IS NULL ORDER BY nome")->fetchAll(PDO::FETCH_OBJ);

$ano = date('Y');
$ultimo = $db->query("
    SELECT codigo_inventario FROM equipamentos
    WHERE codigo_inventario LIKE 'MT-{$ano}-%'
    ORDER BY id DESC LIMIT 1
")->fetchColumn();

if ($ultimo) {
    $partes = explode('-', $ultimo);
    $num = intval(end($partes)) + 1;
} else {
    $num = 1;
}
$codigo_sugerido = 'MT-' . $ano . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
$db = null;

$erros = [];
$erro_sistema = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $codigo       = trim($_POST['codigo_inventario'] ?? '');
    $designacao   = trim($_POST['designacao'] ?? '');
    $categoria    = $_POST['categoria'] ?? '';
    $marca        = trim($_POST['marca'] ?? '');
    $modelo       = trim($_POST['modelo'] ?? '');
    $num_serie    = trim($_POST['numero_serie'] ?? '');
    $fabricante   = trim($_POST['fabricante'] ?? '');
    $data_aquis   = $_POST['data_aquisicao'] ?? '';
    $ano_fabrico  = $_POST['ano_fabrico'] ?? '';
    $custo        = $_POST['custo_aquisicao'] ?? '';
    $tipo_entrada = $_POST['tipo_entrada'] ?? 'compra';
    $estado       = $_POST['estado'] ?? 'ativo';
    $criticidade  = $_POST['criticidade'] ?? 'media';
    $id_loc       = $_POST['id_localizacao'] ?? null;
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
            $db = get_db();

            $stmt = $db->prepare("SELECT id FROM equipamentos WHERE codigo_inventario = ? AND deleted_at IS NULL");
            $stmt->execute([$codigo]);
            if ($stmt->fetch()) {
                $erros[] = 'Já existe um equipamento ativo com este código de inventário.';
            }

            if (!empty($num_serie) && !empty($fabricante) && !empty($modelo) && empty($erros)) {
                $stmt = $db->prepare("
                    SELECT id FROM equipamentos
                    WHERE numero_serie = ? AND fabricante = ? AND modelo = ? AND deleted_at IS NULL
                ");
                $stmt->execute([$num_serie, $fabricante, $modelo]);
                if ($stmt->fetch()) {
                    $erros[] = 'Já existe um equipamento ativo com este número de série para o mesmo fabricante e modelo.';
                }
            }

            if (empty($erros)) {
                $db->prepare("DELETE FROM equipamentos WHERE codigo_inventario = ? AND deleted_at IS NOT NULL")->execute([$codigo]);

                $stmt = $db->prepare("
                    INSERT INTO equipamentos
                    (codigo_inventario, designacao, categoria, marca, modelo, numero_serie, fabricante,
                     data_aquisicao, ano_fabrico, custo_aquisicao, tipo_entrada, estado, criticidade,
                     id_localizacao, observacoes, assistencia_nome, assistencia_telefone, assistencia_email)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $codigo,
                    $designacao,
                    $categoria,
                    $marca ?: null,
                    $modelo ?: null,
                    $num_serie ?: null,
                    $fabricante ?: null,
                    $data_aquis ?: null,
                    $ano_fabrico ?: null,
                    $custo ?: null,
                    $tipo_entrada,
                    $estado,
                    $criticidade,
                    $id_loc ?: null,
                    $observacoes ?: null,
                    $assistencia_nome,
                    $assistencia_tel,
                    $assistencia_email ?: null
                ]);

                $id_novo = $db->lastInsertId();

                if (!empty($fornecedor_ids)) {
                    $stmt2 = $db->prepare("INSERT INTO equipamento_fornecedor (id_equipamento, id_fornecedor) VALUES (?, ?)");
                    foreach ($fornecedor_ids as $fid) {
                        $stmt2->execute([$id_novo, $fid]);
                    }
                }

                // Processar documentos
                $doc_nomes    = $_POST['doc_nome']      ?? [];
                $doc_tipos    = $_POST['doc_tipo']      ?? [];
                $doc_datas    = $_POST['doc_data']      ?? [];
                $doc_validades = $_POST['doc_validade'] ?? [];
                $upload_dir   = __DIR__ . '/../../uploads/';
                $permitidos   = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];

                $stmt_doc = $db->prepare("
                    INSERT INTO documentos (id_equipamento, tipo, nome, data_documento, data_validade, ficheiro)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                foreach ($doc_nomes as $i => $doc_nome) {
                    $doc_nome = trim($doc_nome);
                    if (empty($doc_nome)) continue;

                    $ficheiro_guardado = null;
                    if (!empty($_FILES['doc_ficheiro']['name'][$i])) {
                        $nome_orig = basename($_FILES['doc_ficheiro']['name'][$i]);
                        $ext       = strtolower(pathinfo($nome_orig, PATHINFO_EXTENSION));
                        if (in_array($ext, $permitidos) && $_FILES['doc_ficheiro']['size'][$i] <= 10 * 1024 * 1024) {
                            $nome_ficheiro = uniqid('doc_') . '.' . $ext;
                            if (move_uploaded_file($_FILES['doc_ficheiro']['tmp_name'][$i], $upload_dir . $nome_ficheiro)) {
                                $ficheiro_guardado = $nome_ficheiro;
                            }
                        }
                    }

                    $stmt_doc->execute([
                        $id_novo,
                        $doc_tipos[$i] ?? 'outro',
                        $doc_nome,
                        !empty($doc_datas[$i]) ? $doc_datas[$i] : null,
                        !empty($doc_validades[$i]) ? $doc_validades[$i] : null,
                        $ficheiro_guardado
                    ]);
                }

                $db = null;
                header('Location: detalhes.php?id=' . aes_encrypt($id_novo));
                exit;
            }
            $db = null;
        } catch (PDOException $e) {
            $erro_sistema = 'Erro ao guardar o equipamento: ' . $e->getMessage();
        }
        $db = null;
    }
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>


<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <?php include '../../includes/breadcrumb.php'; ?>

            <!-- Hero header -->
            <div class="equip-hero mb-4">
                <div class="d-flex align-items-center gap-3 position-relative">
                    <div class="equip-hero-icon">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h1 style="font-family:var(--font-display);font-size:1.5rem;margin:0;color:var(--mt-text);">
                            Novo Equipamento
                        </h1>
                        <p class="mb-0" style="font-size:0.88rem;color:var(--mt-text-muted);">
                            Registe um novo equipamento no inventário
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <a href="lista.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-xmark me-1"></i>Cancelar
                        </a>
                        <button form="form-novo" type="submit" class="btn btn-mt-primary btn-sm" style="padding:0.35rem 1rem;font-size:0.875rem;">
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

            <form id="form-novo" action="novo.php" method="post" enctype="multipart/form-data" novalidate>

                <!-- Tabs nav -->
                <ul class="nav equip-tabs mb-0" id="novoTabs" role="tablist">
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
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documentos" type="button" role="tab">
                            <i class="fa-solid fa-folder-open me-1"></i>Documentos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-obs" type="button" role="tab">
                            <i class="fa-solid fa-note-sticky me-1"></i>Observações
                        </button>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content bo-card" style="border-top:none;border-radius:0 0 var(--mt-radius) var(--mt-radius);" id="novoTabsContent">

                    <!-- Tab: Identificação -->
                    <div class="tab-pane fade show active" id="tab-identificacao" role="tabpanel">
                        <div class="bo-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="bo-form-label">Código de Inventário</label>
                                    <input type="text" class="form-control bo-form-control" name="codigo_inventario"
                                        value="<?= htmlspecialchars($codigo_sugerido) ?>"
                                        readonly style="background:var(--mt-bg-alt);color:var(--mt-text-muted);cursor:default;">
                                    <small class="text-muted">Gerado automaticamente.</small>
                                </div>
                                <div class="col-md-8">
                                    <label class="bo-form-label">Designação <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bo-form-control" name="designacao"
                                        value="<?= htmlspecialchars($_POST['designacao'] ?? '') ?>" placeholder="ex: Monitor Multiparamétrico">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Categoria <span class="text-danger">*</span></label>
                                    <select class="form-select bo-form-control" name="categoria" required>
                                        <option value="">Selecione...</option>
                                        <?php
                                        $categorias = [
                                            'monitorizacao' => 'Monitorização',
                                            'suporte_vida'  => 'Suporte de Vida',
                                            'terapia'       => 'Terapia',
                                            'diagnostico'   => 'Diagnóstico',
                                            'laboratorio'   => 'Laboratório',
                                            'esterilizacao' => 'Esterilização',
                                            'reabilitacao'  => 'Reabilitação',
                                            'outro'         => 'Outro'
                                        ];
                                        foreach ($categorias as $val => $label):
                                            $sel = (($_POST['categoria'] ?? '') == $val) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Marca</label>
                                    <input type="text" class="form-control bo-form-control" name="marca"
                                        value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Modelo</label>
                                    <input type="text" class="form-control bo-form-control" name="modelo"
                                        value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Número de Série</label>
                                    <input type="text" class="form-control bo-form-control" name="numero_serie"
                                        value="<?= htmlspecialchars($_POST['numero_serie'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Fabricante</label>
                                    <input type="text" class="form-control bo-form-control" name="fabricante"
                                        value="<?= htmlspecialchars($_POST['fabricante'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <span class="text-muted" style="font-size:0.82rem;"><span class="text-danger">*</span> campos obrigatórios</span>
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-aquisicao')">
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
                                    <input type="date" class="form-control bo-form-control" name="data_aquisicao"
                                        id="data_aquisicao" value="<?= htmlspecialchars($_POST['data_aquisicao'] ?? '') ?>"
                                        placeholder="AAAA-MM-DD">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">Ano de Fabrico</label>
                                    <input type="number" class="form-control bo-form-control" name="ano_fabrico"
                                        value="<?= htmlspecialchars($_POST['ano_fabrico'] ?? '') ?>"
                                        min="1900" max="<?= date('Y') ?>" placeholder="ex: 2022">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">Custo de Aquisição (€)</label>
                                    <input type="number" class="form-control bo-form-control" name="custo_aquisicao"
                                        value="<?= htmlspecialchars($_POST['custo_aquisicao'] ?? '') ?>"
                                        step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="col-md-3">
                                    <label class="bo-form-label">Tipo de Entrada</label>
                                    <select class="form-select bo-form-control" name="tipo_entrada">
                                        <?php
                                        $tipos = ['compra' => 'Compra', 'doacao' => 'Doação', 'aluguer' => 'Aluguer', 'emprestimo' => 'Empréstimo'];
                                        foreach ($tipos as $val => $label):
                                            $sel = (($_POST['tipo_entrada'] ?? 'compra') == $val) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-identificacao')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-estado')">
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
                                        <?php
                                        $estados = [
                                            'ativo'      => 'Ativo',
                                            'manutencao' => 'Em manutenção',
                                            'inativo'    => 'Inativo',
                                            'calibracao' => 'Em calibração',
                                            'quarentena' => 'Em quarentena',
                                            'abatido'    => 'Abatido'
                                        ];
                                        foreach ($estados as $val => $label):
                                            $sel = (($_POST['estado'] ?? 'ativo') == $val) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Criticidade</label>
                                    <select class="form-select bo-form-control" name="criticidade">
                                        <?php
                                        $crits = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
                                        foreach ($crits as $val => $label):
                                            $sel = (($_POST['criticidade'] ?? 'media') == $val) ? 'selected' : '';
                                        ?>
                                            <option value="<?= $val ?>" <?= $sel ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="bo-form-label">Localização</label>
                                    <div class="d-flex gap-2">
                                        <select class="form-select bo-form-control" name="id_localizacao" id="select-localizacao">
                                            <option value="">Sem localização</option>
                                            <?php foreach ($localizacoes as $loc):
                                                $sel = (($_POST['id_localizacao'] ?? '') == $loc->id) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $loc->id ?>" <?= $sel ?>>
                                                    <?= htmlspecialchars($loc->servico . ($loc->sala ? ' — ' . $loc->sala : '')) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-mt-primary flex-shrink-0"
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
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-fornecedores')">
                                Próximo <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Fornecedores -->
                    <div class="tab-pane fade" id="tab-fornecedores" role="tabpanel">
                        <div class="bo-card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="text-muted mb-0" style="font-size:0.85rem;">Selecione os fornecedores associados a este equipamento.</p>
                                <button type="button" class="btn btn-sm btn-mt-primary" data-bs-toggle="modal" data-bs-target="#modalNovoFornecedor">
                                    <i class="fa-solid fa-plus me-1"></i>Novo Fornecedor
                                </button>
                            </div>
                            <div class="row g-2" id="lista-fornecedores">
                                <?php if (empty($fornecedores)): ?>
                                    <div class="col-12 text-center py-3" id="msg-sem-fornecedores">
                                        <i class="fa-solid fa-truck-medical fa-2x mb-2" style="color:var(--mt-border);"></i>
                                        <p class="text-muted mb-0" style="font-size:0.9rem;">Sem fornecedores registados.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($fornecedores as $forn):
                                        $checked = in_array($forn->id, $_POST['fornecedores'] ?? []);
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
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-estado')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-obs')">
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
                                    <input type="text" class="form-control bo-form-control" name="assistencia_nome" required minlength="3" maxlength="150"
                                        value="<?= htmlspecialchars($_POST['assistencia_nome'] ?? '') ?>"
                                        placeholder="ex: João Silva / TechMed Serviços">
                                </div>
                                <div class="col-md-6">
                                    <label class="bo-form-label">Telefone <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bo-form-control" name="assistencia_telefone" required pattern="[0-9\s\-\+]+" minlength="9" maxlength="20"
                                        value="<?= htmlspecialchars($_POST['assistencia_telefone'] ?? '') ?>"
                                        placeholder="ex: 912 345 678">
                                </div>
                                <div class="col-md-6">
                                    <label class="bo-form-label">Email</label>
                                    <input type="email" class="form-control bo-form-control" name="assistencia_email" maxlength="100"
                                        value="<?= htmlspecialchars($_POST['assistencia_email'] ?? '') ?>"
                                        placeholder="ex: suporte@empresa.pt">
                                </div>
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-fornecedores')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="button" class="btn btn-mt-primary btn-sm" onclick="goToTab('tab-obs')">
                                Próximo <i class="fa-solid fa-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tab: Documentos -->
                    <div class="tab-pane fade" id="tab-documentos" role="tabpanel">
                        <div class="bo-card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="text-muted mb-0" style="font-size:0.85rem;">Adicione documentos associados a este equipamento. Pode adicionar mais depois nos detalhes.</p>
                                <button type="button" class="btn btn-sm btn-mt-primary" id="btn-add-doc">
                                    <i class="fa-solid fa-plus me-1"></i>Adicionar Documento
                                </button>
                            </div>
                            <div id="lista-docs">
                                <!-- linhas geradas dinamicamente -->
                            </div>
                            <div id="msg-sem-docs" class="text-center py-3" style="color:var(--mt-text-muted);font-size:0.88rem;">
                                <i class="fa-solid fa-folder-open fa-2x mb-2 d-block" style="color:var(--mt-border);"></i>
                                Nenhum documento adicionado. Clique em "Adicionar Documento" para começar.
                            </div>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-assistencia')">
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
                                placeholder="Notas adicionais sobre o equipamento..."><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                        </div>
                        <div class="tab-nav-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="goToTab('tab-documentos')">
                                <i class="fa-solid fa-arrow-left me-1"></i> Anterior
                            </button>
                            <button type="submit" class="btn btn-mt-primary btn-sm">
                                <i class="fa-regular fa-floppy-disk me-1"></i> Guardar Equipamento
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
                <button type="button" class="btn btn-mt-primary btn-sm" id="btn-guardar-localizacao">
                    <i class="fa-regular fa-floppy-disk me-1"></i>Guardar Localização
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Novo Fornecedor -->
<div class="modal fade" id="modalNovoFornecedor" tabindex="-1" aria-labelledby="modalNovoFornecedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:var(--mt-radius);border:1px solid var(--mt-border);">
            <div class="modal-header" style="background:var(--mt-bg-alt);border-bottom:1px solid var(--mt-border);">
                <h5 class="modal-title" id="modalNovoFornecedorLabel" style="font-family:var(--font-display);">
                    <i class="fa-solid fa-truck-medical me-2" style="color:var(--mt-blue-dark);"></i>Novo Fornecedor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modal-erro" class="alert alert-danger d-none mb-3" style="font-size:0.88rem;"></div>

                <p class="mb-2" style="font-family:var(--font-display);font-size:1rem;color:var(--mt-text);">Dados da Empresa</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="bo-form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control bo-form-control" id="forn-nome">
                    </div>
                    <div class="col-md-3">
                        <label class="bo-form-label">NIF</label>
                        <input type="text" class="form-control bo-form-control" id="forn-nif">
                    </div>
                    <div class="col-md-3">
                        <label class="bo-form-label">Tipo</label>
                        <select class="form-select bo-form-control" id="forn-tipo">
                            <option value="fabricante">Fabricante</option>
                            <option value="distribuidor">Distribuidor</option>
                            <option value="assistencia_tecnica">Assistência Técnica</option>
                            <option value="consumiveis">Consumíveis</option>
                            <option value="outro" selected>Outro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="bo-form-label">Telefone</label>
                        <input type="text" class="form-control bo-form-control" id="forn-telefone">
                    </div>
                    <div class="col-md-4">
                        <label class="bo-form-label">Email</label>
                        <input type="email" class="form-control bo-form-control" id="forn-email">
                    </div>
                    <div class="col-md-4">
                        <label class="bo-form-label">Website</label>
                        <input type="text" class="form-control bo-form-control" id="forn-website" placeholder="ex: www.empresa.pt">
                    </div>
                    <div class="col-12">
                        <label class="bo-form-label">Morada</label>
                        <input type="text" class="form-control bo-form-control" id="forn-morada">
                    </div>
                </div>

                <hr style="border-color:var(--mt-border);">

                <p class="mb-2 mt-3" style="font-family:var(--font-display);font-size:1rem;color:var(--mt-text);">Pessoa de Contacto</p>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="bo-form-label">Nome</label>
                        <input type="text" class="form-control bo-form-control" id="forn-pessoa-contacto">
                    </div>
                    <div class="col-md-6">
                        <label class="bo-form-label">Telefone</label>
                        <input type="text" class="form-control bo-form-control" id="forn-tel-contacto">
                    </div>
                </div>

                <hr style="border-color:var(--mt-border);">

                <div class="mt-3">
                    <label class="bo-form-label">Observações</label>
                    <textarea class="form-control bo-form-control" id="forn-observacoes" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--mt-border);background:var(--mt-bg-alt);">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-mt-primary btn-sm" id="btn-guardar-fornecedor">
                    <i class="fa-regular fa-floppy-disk me-1"></i>Guardar Fornecedor
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

    flatpickr("#data_aquisicao", {
        dateFormat: "Y-m-d"
    });

    <?php if (!empty($erros)): ?>
        goToTab('tab-identificacao');
    <?php endif; ?>

    // Documentos dinâmicos
    const tiposDoc = {
        'manual_utilizador':       'Manual de Utilizador',
        'manual_servico':          'Manual de Serviço',
        'certificado_calibracao':  'Certificado de Calibração',
        'contrato_manutencao':     'Contrato de Manutenção',
        'fatura':                  'Fatura',
        'declaracao_conformidade': 'Declaração de Conformidade',
        'relatorio_tecnico':       'Relatório Técnico',
        'outro':                   'Outro',
    };
    let docCount = 0;

    function tiposOptions() {
        return Object.entries(tiposDoc).map(([v, l]) =>
            `<option value="${v}">${l}</option>`
        ).join('');
    }

    document.getElementById('btn-add-doc').addEventListener('click', function () {
        document.getElementById('msg-sem-docs').style.display = 'none';
        const idx = docCount++;
        const row = document.createElement('div');
        row.className = 'doc-row border rounded-3 p-3 mb-3';
        row.style.background = 'var(--mt-bg)';
        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span style="font-size:0.85rem;font-weight:600;color:var(--mt-text);">
                    <i class="fa-solid fa-file-lines me-1" style="color:var(--mt-blue-dark);"></i>Documento ${idx + 1}
                </span>
                <button type="button" class="btn btn-sm btn-outline-danger" style="padding:2px 8px;font-size:0.75rem;" onclick="removeDoc(this)">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="bo-form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" class="form-control bo-form-control" name="doc_nome[]" placeholder="ex: Manual de Utilizador">
                </div>
                <div class="col-md-4">
                    <label class="bo-form-label">Tipo</label>
                    <select class="form-select bo-form-control" name="doc_tipo[]">${tiposOptions()}</select>
                </div>
                <div class="col-md-3">
                    <label class="bo-form-label">Ficheiro</label>
                    <input type="file" class="form-control bo-form-control" name="doc_ficheiro[]"
                        accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                    <small class="text-muted" style="font-size:0.72rem;">PDF, Word, Excel, imagem. Máx. 10MB.</small>
                </div>
                <div class="col-md-3">
                    <label class="bo-form-label">Data do Documento</label>
                    <input type="text" class="form-control bo-form-control fp-doc-data" name="doc_data[]" placeholder="AAAA-MM-DD">
                </div>
                <div class="col-md-3">
                    <label class="bo-form-label">Data de Validade</label>
                    <input type="text" class="form-control bo-form-control fp-doc-val" name="doc_validade[]" placeholder="AAAA-MM-DD">
                </div>
            </div>`;
        document.getElementById('lista-docs').appendChild(row);
        flatpickr(row.querySelector('.fp-doc-data'), { dateFormat: 'Y-m-d' });
        flatpickr(row.querySelector('.fp-doc-val'),  { dateFormat: 'Y-m-d' });
    });

    function removeDoc(btn) {
        btn.closest('.doc-row').remove();
        if (document.querySelectorAll('.doc-row').length === 0) {
            document.getElementById('msg-sem-docs').style.display = '';
        }
    }

    document.getElementById('btn-guardar-localizacao').addEventListener('click', function() {
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
        data.append('servico', servico);
        data.append('sala', document.getElementById('loc-sala').value.trim());
        data.append('piso', document.getElementById('loc-piso').value.trim());
        data.append('edificio', document.getElementById('loc-edificio').value.trim());
        data.append('observacoes', document.getElementById('loc-observacoes').value.trim());

        fetch('/MediTrack/private/views/localizacoes/ajax_novo.php', {
                method: 'POST',
                body: data
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    erroDiv.textContent = res.erro;
                    erroDiv.classList.remove('d-none');
                    return;
                }

                const select = document.getElementById('select-localizacao');
                const option = document.createElement('option');
                option.value = res.id;
                option.text = res.label;
                option.selected = true;
                select.appendChild(option);

                bootstrap.Modal.getInstance(document.getElementById('modalNovaLocalizacao')).hide();
                ['loc-servico', 'loc-sala', 'loc-piso', 'loc-edificio', 'loc-observacoes']
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

    document.getElementById('btn-guardar-fornecedor').addEventListener('click', function() {
        const erroDiv = document.getElementById('modal-erro');
        erroDiv.classList.add('d-none');
        erroDiv.textContent = '';

        const nome = document.getElementById('forn-nome').value.trim();
        if (!nome) {
            erroDiv.textContent = 'O nome é obrigatório.';
            erroDiv.classList.remove('d-none');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>A guardar...';

        const data = new FormData();
        data.append('nome', nome);
        data.append('nif', document.getElementById('forn-nif').value.trim());
        data.append('tipo', document.getElementById('forn-tipo').value);
        data.append('telefone', document.getElementById('forn-telefone').value.trim());
        data.append('email', document.getElementById('forn-email').value.trim());
        data.append('website', document.getElementById('forn-website').value.trim());
        data.append('morada', document.getElementById('forn-morada').value.trim());
        data.append('pessoa_contacto', document.getElementById('forn-pessoa-contacto').value.trim());
        data.append('telefone_contacto', document.getElementById('forn-tel-contacto').value.trim());
        data.append('observacoes', document.getElementById('forn-observacoes').value.trim());

        fetch('/MediTrack/private/views/fornecedores/ajax_novo.php', {
                method: 'POST',
                body: data
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    erroDiv.textContent = res.erro;
                    erroDiv.classList.remove('d-none');
                    return;
                }

                const msgVazio = document.getElementById('msg-sem-fornecedores');
                if (msgVazio) msgVazio.remove();

                const lista = document.getElementById('lista-fornecedores');
                const uid = 'forn_new_' + res.id;
                const col = document.createElement('div');
                col.className = 'col-md-4';
                col.innerHTML = `
                <label class="forn-check-card selected" for="${uid}">
                    <input class="form-check-input m-0 flex-shrink-0" type="checkbox"
                        name="fornecedores[]" value="${res.id}"
                        id="${uid}" checked
                        onchange="this.closest('.forn-check-card').classList.toggle('selected', this.checked)">
                    <span style="font-size:0.9rem;font-weight:500;">${res.nome}</span>
                </label>`;
                lista.appendChild(col);

                bootstrap.Modal.getInstance(document.getElementById('modalNovoFornecedor')).hide();

                // Limpa todos os campos do modal
                ['forn-nome', 'forn-nif', 'forn-telefone', 'forn-email', 'forn-website',
                    'forn-morada', 'forn-pessoa-contacto', 'forn-tel-contacto', 'forn-observacoes'
                ]
                .forEach(id => document.getElementById(id).value = '');
                document.getElementById('forn-tipo').value = 'outro';
            })
            .catch(() => {
                erroDiv.textContent = 'Erro de ligação. Tente novamente.';
                erroDiv.classList.remove('d-none');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-regular fa-floppy-disk me-1"></i>Guardar Fornecedor';
            });
    });
</script>

<?php include '../../includes/footer.php'; ?>