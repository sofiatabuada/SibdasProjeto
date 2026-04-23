<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$emprestimos = $db->query("
    SELECT em.data_saida, em.data_retorno_prevista, em.data_retorno_real,
           em.servico_destino, em.responsavel, em.observacoes,
           e.designacao, e.codigo_inventario,
           l.servico AS origem
    FROM emprestimos em
    JOIN equipamentos e ON em.id_equipamento = e.id
    LEFT JOIN localizacoes l ON em.id_localizacao_origem = l.id
    ORDER BY em.data_saida DESC
")->fetchAll(PDO::FETCH_OBJ);
$total = count($emprestimos);
$db = null;

$hoje = date('Y-m-d');
$em_curso  = count(array_filter($emprestimos, fn($em) => !$em->data_retorno_real));
$atrasados = count(array_filter($emprestimos, fn($em) => !$em->data_retorno_real && $em->data_retorno_prevista && $em->data_retorno_prevista < $hoje));
$devolvidos = count(array_filter($emprestimos, fn($em) => $em->data_retorno_real));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>MediTrack — Relatório de Empréstimos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #2D3748; background: white; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 3px solid #7EB5D6;
            margin-bottom: 20px;
        }
        .header h1 { font-size: 20px; color: #2D3748; }
        .header .brand { font-size: 24px; font-weight: bold; color: #4A90B8; }
        .header .meta { font-size: 10px; color: #718096; text-align: right; }

        .summary {
            display: flex;
            gap: 15px;
            padding: 0 30px;
            margin-bottom: 20px;
        }
        .summary-card {
            background: #EBF5FB;
            border-radius: 8px;
            padding: 10px 15px;
            flex: 1;
            text-align: center;
        }
        .summary-card strong { display: block; font-size: 22px; color: #4A90B8; }
        .summary-card strong.danger { color: #e53e3e; }
        .summary-card span { font-size: 10px; color: #718096; }

        table {
            width: calc(100% - 60px);
            margin: 0 30px;
            border-collapse: collapse;
        }
        thead th {
            background: #4A90B8;
            color: white;
            padding: 7px 6px;
            text-align: left;
            font-size: 10px;
        }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td {
            padding: 6px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-emcurso  { background: #BEE3F8; color: #2B6CB0; }
        .badge-atrasado { background: #FED7D7; color: #9B2C2C; }
        .badge-devolvido { background: #C6F6D5; color: #276749; }

        .footer {
            margin-top: 20px;
            padding: 10px 30px;
            border-top: 1px solid #E2E8F0;
            font-size: 9px;
            color: #718096;
            display: flex;
            justify-content: space-between;
        }

        .no-print { position: fixed; top: 15px; right: 15px; }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            background: #4A90B8;
            color: white;
        }

        @media print {
            .no-print { display: none; }
            body { font-size: 10px; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    </div>

    <div class="header">
        <div>
            <div class="brand">❤ MediTrack</div>
            <h1>Relatório de Empréstimos entre Serviços</h1>
        </div>
        <div class="meta">
            Gerado em: <?= date('d/m/Y H:i') ?><br>
            Utilizador: <?= htmlspecialchars($_SESSION['utilizador']) ?>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <strong><?= $total ?></strong>
            <span>Total de empréstimos</span>
        </div>
        <div class="summary-card">
            <strong><?= $em_curso ?></strong>
            <span>Em curso</span>
        </div>
        <div class="summary-card">
            <strong class="<?= $atrasados > 0 ? 'danger' : '' ?>"><?= $atrasados ?></strong>
            <span>Atrasados</span>
        </div>
        <div class="summary-card">
            <strong><?= $devolvidos ?></strong>
            <span>Devolvidos</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Equipamento</th>
                <th>Origem</th>
                <th>Emprestado a</th>
                <th>Responsável</th>
                <th>Saída</th>
                <th>Retorno previsto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($emprestimos as $em): ?>
                <?php
                    $atrasado = !$em->data_retorno_real && $em->data_retorno_prevista && $em->data_retorno_prevista < $hoje;
                    if ($em->data_retorno_real) {
                        $estado = '<span class="badge badge-devolvido">Devolvido</span>';
                    } elseif ($atrasado) {
                        $estado = '<span class="badge badge-atrasado">Atrasado ⚠</span>';
                    } else {
                        $estado = '<span class="badge badge-emcurso">Em curso</span>';
                    }
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($em->designacao) ?></strong>
                        <span style="color:#718096;"> (<?= htmlspecialchars($em->codigo_inventario) ?>)</span>
                    </td>
                    <td><?= htmlspecialchars($em->origem ?? '—') ?></td>
                    <td><?= htmlspecialchars($em->servico_destino) ?></td>
                    <td><?= htmlspecialchars($em->responsavel ?? '—') ?></td>
                    <td><?= date('d/m/Y', strtotime($em->data_saida)) ?></td>
                    <td><?= $em->data_retorno_prevista ? date('d/m/Y', strtotime($em->data_retorno_prevista)) : '—' ?></td>
                    <td><?= $estado ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <span>MediTrack — Sistema de Gestão de Inventário Hospitalar</span>
        <span>Total: <?= $total ?> empréstimo(s)</span>
    </div>

</body>
</html>
