<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

$filtro_estado = $_GET['estado'] ?? '';
$filtro_tipo   = $_GET['tipo']   ?? '';

$where = ['1=1'];
$params = [];

if ($filtro_estado) { $where[] = 'm.estado = ?'; $params[] = $filtro_estado; }
if ($filtro_tipo)   { $where[] = 'm.tipo = ?';   $params[] = $filtro_tipo; }

$stmt = $db->prepare("
    SELECT m.*, e.designacao, e.codigo_inventario, e.estado AS eq_estado
    FROM manutencoes m
    JOIN equipamentos e ON m.id_equipamento = e.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY
        FIELD(m.estado,'em_curso','agendada','concluida','cancelada'),
        m.data_inicio DESC
");
$stmt->execute($params);
$manutencoes = $stmt->fetchAll(PDO::FETCH_OBJ);

$totais = $db->query("SELECT estado, COUNT(*) as n FROM manutencoes GROUP BY estado")->fetchAll(PDO::FETCH_OBJ);
$t = array_column($totais, 'n', 'estado');
$db = null;

$tipo_labels   = ['preventiva'=>'Preventiva','corretiva'=>'Corretiva','calibracao'=>'Calibração','inspecao'=>'Inspeção'];
$estado_labels = ['agendada'=>'Agendada','em_curso'=>'Em Curso','concluida'=>'Concluída','cancelada'=>'Cancelada'];
$estado_badge  = ['agendada'=>'badge-manutencao','em_curso'=>'badge-alta','concluida'=>'badge-ativo','cancelada'=>'badge-inativo'];
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <?php include '../../includes/breadcrumb.php'; ?>


            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="bo-page-title">
                        <i class="fa-solid fa-wrench me-2" style="color:var(--mt-blue-dark);"></i>Manutenções
                    </h1>
                    <p class="bo-page-subtitle">Registo de manutenções de equipamentos</p>
                </div>
            </div>

            <!-- Cards de resumo -->
            <div class="row g-3 mb-4">
                <?php
                $cards = [
                    ['em_curso',  'Em Curso',  'fa-rotate',             'badge-alta',       '#FDF0F3'],
                    ['agendada',  'Agendadas', 'fa-calendar-days',      'badge-manutencao', '#FEF9EE'],
                    ['concluida', 'Concluídas','fa-circle-check',       'badge-ativo',      '#EAF7EF'],
                    ['cancelada', 'Canceladas','fa-circle-xmark',       'badge-inativo',    '#F3F6F9'],
                ];
                foreach ($cards as [$key, $label, $icon, $badge, $bg]):
                ?>
                    <div class="col-6 col-md-3">
                        <div class="dash-card" style="background:<?= $bg ?>;">
                            <div class="dash-icon" style="background:rgba(0,0,0,0.06);">
                                <i class="fa-solid <?= $icon ?>" style="font-size:1.1rem;"></i>
                            </div>
                            <div class="dash-value"><?= $t[$key] ?? 0 ?></div>
                            <div class="dash-label"><?= $label ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Filtros -->
            <form method="get" class="d-flex gap-2 mb-3 flex-wrap">
                <select name="estado" class="form-select form-select-sm bo-form-control" style="width:auto;">
                    <option value="">Todos os estados</option>
                    <?php foreach ($estado_labels as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $filtro_estado == $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tipo" class="form-select form-select-sm bo-form-control" style="width:auto;">
                    <option value="">Todos os tipos</option>
                    <?php foreach ($tipo_labels as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $filtro_tipo == $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-mt-primary">Filtrar</button>
                <?php if ($filtro_estado || $filtro_tipo): ?>
                    <a href="lista.php" class="btn btn-sm btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </form>

            <!-- Tabela -->
            <div class="bo-card">
                <div class="bo-card-body p-0">
                    <?php if (empty($manutencoes)): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-wrench fa-2x mb-2" style="color:var(--mt-border);"></i>
                            <p class="text-muted mb-0">Sem manutenções registadas.</p>
                        </div>
                    <?php else: ?>
                        <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                            <thead>
                                <tr style="border-bottom:2px solid var(--mt-border);">
                                    <th style="padding:0.85rem 1.25rem;font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Equipamento</th>
                                    <th style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Tipo</th>
                                    <th style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Estado</th>
                                    <th style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Início</th>
                                    <th style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Fim</th>
                                    <th style="font-size:0.75rem;font-weight:700;color:var(--mt-text-muted);text-transform:uppercase;letter-spacing:0.5px;">Descrição</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($manutencoes as $m): ?>
                                    <tr style="border-bottom:1px solid var(--mt-border);">
                                        <td style="padding:0.85rem 1.25rem;">
                                            <div style="font-weight:600;"><?= htmlspecialchars($m->designacao) ?></div>
                                            <code style="font-size:0.75rem;background:var(--mt-bg-alt);padding:1px 5px;border-radius:4px;"><?= htmlspecialchars($m->codigo_inventario) ?></code>
                                        </td>
                                        <td>
                                            <span class="badge-criticidade badge-manutencao" style="font-size:0.72rem;">
                                                <?= $tipo_labels[$m->tipo] ?? $m->tipo ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-criticidade <?= $estado_badge[$m->estado] ?? 'badge-inativo' ?>" style="font-size:0.72rem;">
                                                <?= $estado_labels[$m->estado] ?? $m->estado ?>
                                            </span>
                                        </td>
                                        <td style="color:var(--mt-text-muted);"><?= $m->data_inicio ? date('d/m/Y', strtotime($m->data_inicio)) : '—' ?></td>
                                        <td style="color:var(--mt-text-muted);"><?= $m->data_fim ? date('d/m/Y', strtotime($m->data_fim)) : '—' ?></td>
                                        <td style="max-width:220px;color:var(--mt-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <?= htmlspecialchars($m->descricao ?? '—') ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="/MediTrack/private/views/equipamentos/detalhes.php?id=<?= aes_encrypt($m->id_equipamento) ?>"
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

        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
