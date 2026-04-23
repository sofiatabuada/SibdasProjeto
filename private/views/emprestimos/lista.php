<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$emprestimos = $db->query("
    SELECT em.*, e.designacao, e.codigo_inventario,
           l.servico AS servico_origem
    FROM emprestimos em
    JOIN equipamentos e ON em.id_equipamento = e.id
    LEFT JOIN localizacoes l ON em.id_localizacao_origem = l.id
    ORDER BY em.data_saida DESC
")->fetchAll(PDO::FETCH_OBJ);

$db = null;

$hoje = new DateTime();
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
                        <i class="fa-solid fa-right-left me-2" style="color: var(--mt-blue-dark);"></i>Empréstimos entre Serviços
                    </h1>
                    <p class="bo-page-subtitle">Registo de equipamentos emprestados entre serviços</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="/MediTrack/private/views/exportar/emprestimos_csv.php" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-file-excel me-1"></i>Excel
                    </a>
                    <a href="/MediTrack/private/views/exportar/emprestimos_pdf.php" class="btn btn-outline-secondary" target="_blank">
                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                    </a>
                    <a href="novo.php" class="btn btn-mt-primary">
                        <i class="fa-solid fa-plus me-2"></i>Novo empréstimo
                    </a>
                </div>
            </div>

            <div class="bo-card">
                <div class="bo-card-body">
                    <table id="tabela-emprestimos" class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Equipamento</th>
                                <th>Serviço de Origem</th>
                                <th>Emprestado a</th>
                                <th>Data Saída</th>
                                <th>Retorno Previsto</th>
                                <th>Estado</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emprestimos as $em):
                                $atrasado = !$em->data_retorno_real && $em->data_retorno_prevista && new DateTime($em->data_retorno_prevista) < $hoje;
                                $devolvido = !empty($em->data_retorno_real);
                                $row_class = $atrasado ? 'table-danger' : ($devolvido ? '' : '');
                            ?>
                                <tr class="<?= $row_class ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($em->designacao) ?></strong>
                                        <small class="text-muted d-block"><code><?= htmlspecialchars($em->codigo_inventario) ?></code></small>
                                    </td>
                                    <td><?= htmlspecialchars($em->servico_origem ?? '—') ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($em->servico_destino) ?></strong>
                                        <?php if ($em->responsavel): ?>
                                            <small class="text-muted d-block"><?= htmlspecialchars($em->responsavel) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($em->data_saida)) ?></td>
                                    <td>
                                        <?php if ($em->data_retorno_prevista): ?>
                                            <span class="<?= $atrasado ? 'text-danger fw-semibold' : '' ?>">
                                                <?= date('d/m/Y', strtotime($em->data_retorno_prevista)) ?>
                                            </span>
                                            <?php else: ?>—<?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($devolvido): ?>
                                            <span class="badge-criticidade badge-ativo">Devolvido</span>
                                            <small class="text-muted d-block"><?= date('d/m/Y', strtotime($em->data_retorno_real)) ?></small>
                                        <?php elseif ($atrasado): ?>
                                            <span class="badge-criticidade badge-suporte">Atrasado</span>
                                        <?php else: ?>
                                            <span class="badge-criticidade badge-manutencao">Em curso</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!$devolvido): ?>
                                            <a href="devolver.php?id=<?= aes_encrypt($em->id) ?>" class="btn-action btn-action-return" title="Devolver">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex gap-3 mt-3" style="font-size:0.82rem;">
                <span><span class="badge bg-danger me-1">&nbsp;</span> Atrasado</span>
                <span><span class="badge bg-warning text-dark me-1">&nbsp;</span> Em curso</span>
                <span><span class="badge bg-success me-1">&nbsp;</span> Devolvido</span>
            </div>

        </main>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#tabela-emprestimos').DataTable({
            pageLength: 10,
            language: {
                search: 'Filtrar:',
                info: 'Mostrando _START_ até _END_ de _TOTAL_ registos',
                infoEmpty: 'Sem registos',
                zeroRecords: 'Nenhum empréstimo encontrado',
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

<?php include '../../includes/footer.php'; ?>