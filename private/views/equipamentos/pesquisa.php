<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

// Carregar filtros para os selects
$localizacoes = $db->query("SELECT id, servico, sala FROM localizacoes ORDER BY servico")->fetchAll(PDO::FETCH_OBJ);
$fornecedores = $db->query("SELECT id, nome FROM fornecedores WHERE deleted_at IS NULL ORDER BY nome")->fetchAll(PDO::FETCH_OBJ);

// Recolher filtros do GET
$f_designacao  = trim($_GET['designacao'] ?? '');
$f_categoria   = $_GET['categoria'] ?? '';
$f_estado      = $_GET['estado'] ?? '';
$f_criticidade = $_GET['criticidade'] ?? '';
$f_localizacao = $_GET['id_localizacao'] ?? '';
$f_fornecedor  = $_GET['id_fornecedor'] ?? '';
$f_marca       = trim($_GET['marca'] ?? '');

$resultados = [];
$pesquisou  = !empty(array_filter([$f_designacao, $f_categoria, $f_estado, $f_criticidade, $f_localizacao, $f_fornecedor, $f_marca]));

if ($pesquisou) {
    $where  = ["e.deleted_at IS NULL"];
    $params = [];

    if ($f_designacao) {
        $where[]  = "(e.designacao LIKE ? OR e.codigo_inventario LIKE ? OR e.marca LIKE ? OR e.modelo LIKE ?)";
        $params[] = "%$f_designacao%";
        $params[] = "%$f_designacao%";
        $params[] = "%$f_designacao%";
        $params[] = "%$f_designacao%";
    }
    if ($f_categoria) {
        $where[] = "e.categoria = ?";
        $params[] = $f_categoria;
    }
    if ($f_estado) {
        $where[] = "e.estado = ?";
        $params[] = $f_estado;
    }
    if ($f_criticidade) {
        $where[] = "e.criticidade = ?";
        $params[] = $f_criticidade;
    }
    if ($f_localizacao) {
        $where[] = "e.id_localizacao = ?";
        $params[] = $f_localizacao;
    }
    if ($f_marca) {
        $where[] = "e.marca LIKE ?";
        $params[] = "%$f_marca%";
    }

    if ($f_fornecedor) {
        $where[] = "EXISTS (SELECT 1 FROM equipamento_fornecedor ef WHERE ef.id_equipamento = e.id AND ef.id_fornecedor = ?)";
        $params[] = $f_fornecedor;
    }

    $sql = "
        SELECT e.*, l.servico, l.sala
        FROM equipamentos e
        LEFT JOIN localizacoes l ON e.id_localizacao = l.id
        WHERE " . implode(" AND ", $where) . "
        ORDER BY e.designacao
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll(PDO::FETCH_OBJ);
}

$db = null;

$estado_classes = ['ativo' => 'badge-ativo', 'manutencao' => 'badge-manutencao', 'inativo' => 'badge-inativo', 'calibracao' => 'badge-manutencao', 'quarentena' => 'badge-manutencao', 'abatido' => 'badge-inativo'];
$estado_labels  = ['ativo' => 'Ativo', 'manutencao' => 'Em manutenção', 'inativo' => 'Inativo', 'calibracao' => 'Em calibração', 'quarentena' => 'Em quarentena', 'abatido' => 'Abatido'];
$crit_classes   = ['baixa' => 'badge-baixa', 'media' => 'badge-media', 'alta' => 'badge-alta', 'suporte_vida' => 'badge-suporte'];
$crit_labels    = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <div class="mb-4">
                <h1 class="bo-page-title">
                    <i class="fa-solid fa-magnifying-glass me-2" style="color: var(--mt-blue-dark);"></i>Pesquisa de Equipamentos
                </h1>
                <p class="bo-page-subtitle">Filtre o inventário por múltiplos critérios</p>
            </div>

            <!-- Formulário de pesquisa -->
            <div class="bo-card mb-4">
                <div class="bo-card-header">
                    <h5><i class="fa-solid fa-filter me-2"></i>Filtros</h5>
                    <?php if ($pesquisou): ?>
                        <a href="pesquisa.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-xmark me-1"></i>Limpar filtros
                        </a>
                    <?php endif; ?>
                </div>
                <div class="bo-card-body">
                    <form method="get" action="pesquisa.php" novalidate>
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="bo-form-label">Designação / Código / Marca / Modelo</label>
                                <input type="text" class="form-control bo-form-control" name="designacao"
                                    value="<?= htmlspecialchars($f_designacao) ?>"
                                    placeholder="Pesquisar por texto...">
                            </div>

                            <div class="col-md-3">
                                <label class="bo-form-label">Marca</label>
                                <input type="text" class="form-control bo-form-control" name="marca"
                                    value="<?= htmlspecialchars($f_marca) ?>"
                                    placeholder="ex: Philips">
                            </div>

                            <div class="col-md-3">
                                <label class="bo-form-label">Categoria</label>
                                <select class="form-select bo-form-control" name="categoria">
                                    <option value="">Todas</option>
                                    <?php
                                    $categorias = ['monitorizacao' => 'Monitorização', 'suporte_vida' => 'Suporte de Vida', 'terapia' => 'Terapia', 'diagnostico' => 'Diagnóstico', 'laboratorio' => 'Laboratório', 'esterilizacao' => 'Esterilização', 'reabilitacao' => 'Reabilitação', 'outro' => 'Outro'];
                                    foreach ($categorias as $val => $label):
                                    ?><option value="<?= $val ?>" <?= $f_categoria == $val ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="bo-form-label">Estado</label>
                                <select class="form-select bo-form-control" name="estado">
                                    <option value="">Todos</option>
                                    <?php
                                    foreach ($estado_labels as $val => $label):
                                    ?><option value="<?= $val ?>" <?= $f_estado == $val ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="bo-form-label">Criticidade</label>
                                <select class="form-select bo-form-control" name="criticidade">
                                    <option value="">Todas</option>
                                    <?php
                                    foreach ($crit_labels as $val => $label):
                                    ?><option value="<?= $val ?>" <?= $f_criticidade == $val ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="bo-form-label">Localização</label>
                                <select class="form-select bo-form-control" name="id_localizacao">
                                    <option value="">Todas</option>
                                    <?php foreach ($localizacoes as $loc): ?>
                                        <option value="<?= $loc->id ?>" <?= $f_localizacao == $loc->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($loc->servico . ($loc->sala ? ' — ' . $loc->sala : '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="bo-form-label">Fornecedor</label>
                                <select class="form-select bo-form-control" name="id_fornecedor">
                                    <option value="">Todos</option>
                                    <?php foreach ($fornecedores as $forn): ?>
                                        <option value="<?= $forn->id ?>" <?= $f_fornecedor == $forn->id ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($forn->nome) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-mt-primary">
                                    <i class="fa-solid fa-magnifying-glass me-2"></i>Pesquisar
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

            <!-- Resultados -->
            <?php if ($pesquisou): ?>
                <div class="bo-card">
                    <div class="bo-card-header">
                        <h5><i class="fa-solid fa-list me-2"></i>Resultados</h5>
                        <span class="text-muted" style="font-size:0.85rem;">
                            <strong><?= count($resultados) ?></strong> equipamento(s) encontrado(s)
                        </span>
                    </div>
                    <div class="bo-card-body">
                        <?php if (empty($resultados)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fa-solid fa-circle-info fa-2x mb-2 d-block"></i>
                                Nenhum equipamento encontrado com os filtros selecionados.
                            </div>
                        <?php else: ?>
                            <table id="tabela-pesquisa" class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Designação</th>
                                        <th>Marca / Modelo</th>
                                        <th>Serviço</th>
                                        <th>Estado</th>
                                        <th>Criticidade</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resultados as $eq): ?>
                                        <tr>
                                            <td><code style="font-size:0.8rem;"><?= htmlspecialchars($eq->codigo_inventario) ?></code></td>
                                            <td><?= htmlspecialchars($eq->designacao) ?></td>
                                            <td>
                                                <?= htmlspecialchars($eq->marca ?? '—') ?>
                                                <?php if ($eq->modelo): ?>
                                                    <small class="text-muted d-block"><?= htmlspecialchars($eq->modelo) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($eq->servico ?? '—') ?></td>
                                            <td>
                                                <span class="badge-criticidade <?= $estado_classes[$eq->estado] ?? 'badge-inativo' ?>">
                                                    <?= $estado_labels[$eq->estado] ?? $eq->estado ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge-criticidade <?= $crit_classes[$eq->criticidade] ?? 'badge-baixa' ?>">
                                                    <?= $crit_labels[$eq->criticidade] ?? $eq->criticidade ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="detalhes.php?id=<?= aes_encrypt($eq->id) ?>" class="btn btn-sm btn-outline-primary" title="Ver detalhes">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </a>
                                                    <a href="editar.php?id=<?= aes_encrypt($eq->id) ?>" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <script>
                                $(document).ready(function() {
                                    $('#tabela-pesquisa').DataTable({
                                        pageLength: 10,
                                        pagingType: 'full_numbers',
                                        language: {
                                            search: 'Filtrar:',
                                            info: 'Mostrando _START_ até _END_ de _TOTAL_ registos',
                                            infoEmpty: 'Sem registos',
                                            zeroRecords: 'Nenhum resultado',
                                            paginate: {
                                                first: 'Primeira',
                                                last: 'Última',
                                                next: 'Seguinte',
                                                previous: 'Anterior'
                                            }
                                        }
                                    });
                                });
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>