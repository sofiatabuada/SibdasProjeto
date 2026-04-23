<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$localizacoes = $db->query("
    SELECT l.edificio, l.piso, l.servico, l.sala, l.observacoes,
           COUNT(e.id) as total_equipamentos
    FROM localizacoes l
    LEFT JOIN equipamentos e ON e.id_localizacao = l.id AND e.deleted_at IS NULL
    GROUP BY l.id ORDER BY l.servico
")->fetchAll(PDO::FETCH_OBJ);
$total = count($localizacoes);
$db = null;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>MediTrack — Relatório de Localizações</title>
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
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
            background: #EBF5FB;
            color: #4A90B8;
        }

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
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }
        .btn-print { background: #4A90B8; color: white; }
        .btn-back { background: #E2E8F0; color: #2D3748; }

        @media print {
            .no-print { display: none; }
            body { font-size: 10px; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    </div>

    <div class="header">
        <div>
            <div class="brand">❤ MediTrack</div>
            <h1>Relatório de Localizações</h1>
        </div>
        <div class="meta">
            Gerado em: <?= date('d/m/Y H:i') ?><br>
            Utilizador: <?= htmlspecialchars($_SESSION['utilizador']) ?>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <strong><?= $total ?></strong>
            <span>Total de localizações</span>
        </div>
        <div class="summary-card">
            <strong><?= count(array_unique(array_column((array)$localizacoes, 'edificio'))) ?></strong>
            <span>Edifícios</span>
        </div>
        <div class="summary-card">
            <strong><?= array_sum(array_column((array)$localizacoes, 'total_equipamentos')) ?></strong>
            <span>Equipamentos associados</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Serviço / Departamento</th>
                <th>Sala / Gabinete</th>
                <th>Piso</th>
                <th>Edifício</th>
                <th style="text-align:center;">Equipamentos</th>
                <th>Observações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($localizacoes as $loc): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($loc->servico) ?></strong></td>
                    <td><?= htmlspecialchars($loc->sala ?? '—') ?></td>
                    <td><?= htmlspecialchars($loc->piso ?? '—') ?></td>
                    <td><?= htmlspecialchars($loc->edificio ?? '—') ?></td>
                    <td style="text-align:center;">
                        <span class="badge"><?= $loc->total_equipamentos ?></span>
                    </td>
                    <td><?= htmlspecialchars($loc->observacoes ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <span>MediTrack — Sistema de Gestão de Inventário Hospitalar</span>
        <span>Total: <?= $total ?> localização(ões)</span>
    </div>

</body>
</html>
