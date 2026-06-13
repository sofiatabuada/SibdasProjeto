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
$f  = $db->prepare("SELECT * FROM fornecedores WHERE id = ? AND deleted_at IS NULL");
$f->execute([$id]);
$f  = $f->fetch(PDO::FETCH_OBJ);

if (!$f) {
    header('Location: lista.php');
    exit;
}

$equipamentos = $db->prepare("
    SELECT e.id, e.codigo_inventario, e.designacao, e.estado
    FROM equipamentos e
    JOIN equipamento_fornecedor ef ON e.id = ef.id_equipamento
    WHERE ef.id_fornecedor = ? AND e.deleted_at IS NULL
    ORDER BY e.codigo_inventario
");
$equipamentos->execute([$id]);
$equipamentos = $equipamentos->fetchAll(PDO::FETCH_OBJ);
$db = null;

$tipos = [
    'fabricante'          => 'Fabricante',
    'distribuidor'        => 'Distribuidor',
    'assistencia_tecnica' => 'Assistência Técnica',
    'consumiveis'         => 'Consumíveis',
    'outro'               => 'Outro',
];
$estado_badge = [
    'ativo'      => 'badge-ativo',
    'manutencao' => 'badge-manutencao',
    'inativo'    => 'badge-inativo',
    'calibracao' => 'badge-manutencao',
    'quarentena' => 'badge-manutencao',
    'abatido'    => 'badge-inativo',
];
$estado_label = [
    'ativo'      => 'Ativo',
    'manutencao' => 'Em manutenção',
    'inativo'    => 'Inativo',
    'calibracao' => 'Em calibração',
    'quarentena' => 'Em quarentena',
    'abatido'    => 'Abatido',
];
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <?php include '../../includes/breadcrumb.php'; ?>

            <!-- Hero -->
            <div class="equip-hero mb-4">
                <div class="d-flex align-items-center gap-3 position-relative">
                    <div class="equip-hero-icon">
                        <i class="fa-solid fa-truck-medical"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h1 style="font-family:var(--font-display);font-size:1.5rem;margin:0;color:var(--mt-text);">
                            <?= htmlspecialchars($f->nome) ?>
                        </h1>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <code style="font-size:0.8rem;background:rgba(74,144,184,0.12);color:var(--mt-blue-dark);padding:2px 8px;border-radius:6px;">
                                <?= $tipos[$f->tipo] ?? ucfirst($f->tipo) ?>
                            </code>
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
            <ul class="nav equip-tabs mb-0" id="fornTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info" type="button" role="tab">
                        <i class="fa-solid fa-info-circle me-1"></i>Informação Geral
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-contactos" type="button" role="tab">
                        <i class="fa-solid fa-address-book me-1"></i>Contactos
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-equipamentos" type="button" role="tab">
                        <i class="fa-solid fa-stethoscope me-1"></i>Equipamentos
                        <?php if (count($equipamentos) > 0): ?>
                            <span class="tab-badge"><?= count($equipamentos) ?></span>
                        <?php endif; ?>
                    </button>
                </li>
            </ul>

            <div class="tab-content bo-card" style="border-top:none;border-radius:0 0 var(--mt-radius) var(--mt-radius);">

                <!-- Tab: Informação Geral -->
                <div class="tab-pane fade show active" id="tab-info" role="tabpanel">
                    <table class="w-100 mb-0" style="border-collapse:collapse;">
                        <?php
                        $fields = [
                            ['Nome',  htmlspecialchars($f->nome)],
                            ['Tipo',  $tipos[$f->tipo] ?? ucfirst($f->tipo)],
                            ['NIF',   htmlspecialchars($f->nif ?? '—')],
                            ['Morada', htmlspecialchars($f->morada ?? '—')],
                        ];
                        foreach ($fields as $i => [$label, $value]):
                        ?>
                            <tr style="border-bottom:1px solid var(--mt-border);">
                                <td style="width:35%;padding:0.85rem 1.5rem;font-size:0.85rem;font-weight:500;color:var(--mt-text-muted);background:var(--mt-bg-alt);">
                                    <?= $label ?>
                                </td>
                                <td style="padding:0.85rem 1.5rem;font-size:0.9rem;font-weight:600;color:var(--mt-text);">
                                    <?= $value ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($f->observacoes): ?>
                            <tr>
                                <td style="padding:0.85rem 1.5rem;font-size:0.85rem;font-weight:500;color:var(--mt-text-muted);background:var(--mt-bg-alt);vertical-align:top;">
                                    Observações
                                </td>
                                <td style="padding:0.85rem 1.5rem;font-size:0.9rem;color:var(--mt-text);">
                                    <?= nl2br(htmlspecialchars($f->observacoes)) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Tab: Contactos -->
                <div class="tab-pane fade" id="tab-contactos" role="tabpanel">
                    <table class="w-100 mb-0" style="border-collapse:collapse;">
                        <?php
                        $contactos = [
                            ['Telefone',          $f->telefone ? '<i class="fa-solid fa-phone me-1 text-muted"></i>' . htmlspecialchars($f->telefone) : '—'],
                            ['Email',             $f->email ? '<a href="mailto:' . htmlspecialchars($f->email) . '">' . htmlspecialchars($f->email) . '</a>' : '—'],
                            ['Website',           $f->website ? '<a href="' . htmlspecialchars($f->website) . '" target="_blank">' . htmlspecialchars($f->website) . '</a>' : '—'],
                            ['Pessoa de Contacto', htmlspecialchars($f->pessoa_contacto ?? '—')],
                            ['Tel. Contacto Directo', $f->telefone_contacto ? '<i class="fa-solid fa-phone me-1 text-muted"></i>' . htmlspecialchars($f->telefone_contacto) : '—'],
                        ];
                        foreach ($contactos as [$label, $value]):
                        ?>
                            <tr style="border-bottom:1px solid var(--mt-border);">
                                <td style="width:35%;padding:0.85rem 1.5rem;font-size:0.85rem;font-weight:500;color:var(--mt-text-muted);background:var(--mt-bg-alt);">
                                    <?= $label ?>
                                </td>
                                <td style="padding:0.85rem 1.5rem;font-size:0.9rem;font-weight:600;color:var(--mt-text);">
                                    <?= $value ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <!-- Tab: Equipamentos -->
                <div class="tab-pane fade" id="tab-equipamentos" role="tabpanel">
                    <div class="bo-card-body">
                        <?php if (empty($equipamentos)): ?>
                            <div class="text-center py-4">
                                <i class="fa-solid fa-stethoscope fa-2x mb-2" style="color:var(--mt-border);"></i>
                                <p class="text-muted mb-0" style="font-size:0.9rem;">Nenhum equipamento associado a este fornecedor.</p>
                            </div>
                        <?php else: ?>
                            <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                                <thead>
                                    <tr style="border-bottom:2px solid var(--mt-border);">
                                        <th style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Código</th>
                                        <th style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Designação</th>
                                        <th class="text-center" style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Estado</th>
                                        <th class="text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipamentos as $eq): ?>
                                        <tr style="border-bottom:1px solid var(--mt-border);">
                                            <td><code style="font-size:0.78rem;background:var(--mt-bg-alt);padding:2px 6px;border-radius:5px;"><?= htmlspecialchars($eq->codigo_inventario) ?></code></td>
                                            <td style="font-weight:500;"><?= htmlspecialchars($eq->designacao) ?></td>
                                            <td class="text-center">
                                                <span class="badge-criticidade <?= $estado_badge[$eq->estado] ?? 'badge-inativo' ?>" style="font-size:0.72rem;">
                                                    <?= $estado_label[$eq->estado] ?? $eq->estado ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="<?= BASE_URL ?>/private/views/equipamentos/detalhes.php?id=<?= aes_encrypt($eq->id) ?>"
                                                    class="btn-action btn-action-view" title="Ver equipamento">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>