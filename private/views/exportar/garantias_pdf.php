<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$garantias = $db->query("
    SELECT g.*, e.designacao, e.codigo_inventario
    FROM garantias g JOIN equipamentos e ON g.id_equipamento = e.id
    WHERE e.deleted_at IS NULL ORDER BY g.data_fim ASC
")->fetchAll(PDO::FETCH_OBJ);
$total = count($garantias);
$db = null;

$hoje = date('Y-m-d');
$ativas    = count(array_filter($garantias, fn($g) => $g->data_fim && $g->data_fim >= $hoje));
$expiradas = count(array_filter($garantias, fn($g) => $g->data_fim && $g->data_fim < $hoje));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>MediTrack — Relatório de Garantias</title>
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
        .badge-ativa    { background: #C6F6D5; color: #276749; }
        .badge-expirada { background: #FED7D7; color: #9B2C2C; }
        .badge-sem      { background: #E2E8F0; color: #4A5568; }
        .expirado { color: #e53e3e; font-weight: bold; }

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
            <h1>Relatório de Garantias e Contratos</h1>
        </div>
        <div class="meta">
            Gerado em: <?= date('d/m/Y H:i') ?><br>
            Utilizador: <?= htmlspecialchars($_SESSION['utilizador']) ?>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <strong><?= $total ?></strong>
            <span>Total de garantias</span>
        </div>
        <div class="summary-card">
            <strong><?= $ativas ?></strong>
            <span>Ativas</span>
        </div>
        <div class="summary-card">
            <strong class="<?= $expiradas > 0 ? 'danger' : '' ?>"><?= $expiradas ?></strong>
            <span>Expiradas</span>
        </div>
        <div class="summary-card">
            <strong><?= count(array_filter($garantias, fn($g) => $g->tem_contrato)) ?></strong>
            <span>Com contrato</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Equipamento</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Estado</th>
                <th>Contrato</th>
                <th>Entidade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($garantias as $g): ?>
                <?php $exp = $g->data_fim && $g->data_fim < $hoje; ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($g->designacao) ?></strong>
                        <span style="color:#718096;"> (<?= htmlspecialchars($g->codigo_inventario) ?>)</span>
                    </td>
                    <td><?= $g->data_inicio ? date('d/m/Y', strtotime($g->data_inicio)) : '—' ?></td>
                    <td class="<?= $exp ? 'expirado' : '' ?>">
                        <?= $g->data_fim ? date('d/m/Y', strtotime($g->data_fim)) . ($exp ? ' ⚠' : '') : '—' ?>
                    </td>
                    <td>
                        <?php if (!$g->data_fim): ?>
                            <span class="badge badge-sem">—</span>
                        <?php elseif ($exp): ?>
                            <span class="badge badge-expirada">Expirada</span>
                        <?php else: ?>
                            <span class="badge badge-ativa">Ativa</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $g->tem_contrato ? htmlspecialchars($g->tipo_contrato ?? 'Sim') : 'Não' ?></td>
                    <td><?= htmlspecialchars($g->entidade_responsavel ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <span>MediTrack — Sistema de Gestão de Inventário Hospitalar</span>
        <span>Total: <?= $total ?> garantia(s)</span>
    </div>

</body>
</html>
