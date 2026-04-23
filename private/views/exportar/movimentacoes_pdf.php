<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$movs = $db->query("
    SELECT m.data_movimentacao, m.motivo, m.registado_por, m.observacoes,
           e.designacao, e.codigo_inventario,
           l1.servico AS origem, l2.servico AS destino
    FROM movimentacoes m
    JOIN equipamentos e ON m.id_equipamento = e.id
    LEFT JOIN localizacoes l1 ON m.id_localizacao_origem = l1.id
    LEFT JOIN localizacoes l2 ON m.id_localizacao_destino = l2.id
    ORDER BY m.data_movimentacao DESC
")->fetchAll(PDO::FETCH_OBJ);
$total = count($movs);
$db = null;

$equipamentos_unicos = count(array_unique(array_column((array)$movs, 'codigo_inventario')));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>MediTrack — Histórico de Movimentações</title>
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
        .arrow { color: #4A90B8; font-weight: bold; }

        .footer {
            margin-top: 20px;
            padding: 10px 30px;
            border-top: 1px solid #E2E8F0;
            font-size: 9px;
            color: #718096;
            display: flex;
            justify-content: space-between;
        }

        .no-print {
            position: fixed;
            top: 15px;
            right: 15px;
        }
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
            <h1>Histórico de Movimentações</h1>
        </div>
        <div class="meta">
            Gerado em: <?= date('d/m/Y H:i') ?><br>
            Utilizador: <?= htmlspecialchars($_SESSION['utilizador']) ?>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <strong><?= $total ?></strong>
            <span>Total de movimentações</span>
        </div>
        <div class="summary-card">
            <strong><?= $equipamentos_unicos ?></strong>
            <span>Equipamentos movimentados</span>
        </div>
        <?php if ($total > 0): ?>
        <div class="summary-card">
            <strong><?= date('d/m/Y', strtotime($movs[0]->data_movimentacao)) ?></strong>
            <span>Última movimentação</span>
        </div>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Equipamento</th>
                <th>Origem</th>
                <th></th>
                <th>Destino</th>
                <th>Motivo</th>
                <th>Registado por</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movs as $m): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($m->data_movimentacao)) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($m->designacao) ?></strong>
                        <span style="color:#718096;"> (<?= htmlspecialchars($m->codigo_inventario) ?>)</span>
                    </td>
                    <td><?= htmlspecialchars($m->origem ?? '—') ?></td>
                    <td class="arrow" style="text-align:center;">→</td>
                    <td><?= htmlspecialchars($m->destino ?? '—') ?></td>
                    <td><?= htmlspecialchars($m->motivo ?? '—') ?></td>
                    <td><?= htmlspecialchars($m->registado_por ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <span>MediTrack — Sistema de Gestão de Inventário Hospitalar</span>
        <span>Total: <?= $total ?> movimentação(ões)</span>
    </div>

</body>
</html>
