<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

$equipamentos = $db->query("
    SELECT e.*, l.servico, l.sala
    FROM equipamentos e
    LEFT JOIN localizacoes l ON e.id_localizacao = l.id
    WHERE e.deleted_at IS NULL
    ORDER BY e.codigo_inventario
")->fetchAll(PDO::FETCH_OBJ);

$total = count($equipamentos);
$db = null;

$estados  = ['ativo' => 'Ativo', 'manutencao' => 'Em manutenção', 'inativo' => 'Inativo', 'calibracao' => 'Em calibração', 'quarentena' => 'Em quarentena', 'abatido' => 'Abatido'];
$crits    = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
$crit_cor = ['baixa' => '#A8D5BA', 'media' => '#F9D89C', 'alta' => '#F4A7B9', 'suporte_vida' => '#e57373'];
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>MediTrack — Relatório de Equipamentos</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #2D3748;
            background: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 3px solid #7EB5D6;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            color: #2D3748;
        }

        .header .brand {
            font-size: 24px;
            font-weight: bold;
            color: #4A90B8;
        }

        .header .meta {
            font-size: 10px;
            color: #718096;
            text-align: right;
        }

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

        .summary-card strong {
            display: block;
            font-size: 22px;
            color: #4A90B8;
        }

        .summary-card span {
            font-size: 10px;
            color: #718096;
        }

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

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

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

        .btn-print {
            background: #4A90B8;
            color: white;
        }

        .btn-back {
            background: #E2E8F0;
            color: #2D3748;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                font-size: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button class="btn btn-back" onclick="window.history.back()">← Voltar</button>
        <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
    </div>

    <div class="header">
        <div>
            <div class="brand">❤ MediTrack</div>
            <h1>Relatório de Equipamentos</h1>
        </div>
        <div class="meta">
            Gerado em: <?= date('d/m/Y H:i') ?><br>
            Utilizador: <?= htmlspecialchars($_SESSION['utilizador']) ?>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <strong><?= $total ?></strong>
            <span>Total de equipamentos</span>
        </div>
        <div class="summary-card">
            <strong><?= count(array_filter($equipamentos, fn($e) => $e->estado === 'ativo')) ?></strong>
            <span>Ativos</span>
        </div>
        <div class="summary-card">
            <strong><?= count(array_filter($equipamentos, fn($e) => $e->estado === 'manutencao')) ?></strong>
            <span>Em manutenção</span>
        </div>
        <div class="summary-card">
            <strong><?= count(array_filter($equipamentos, fn($e) => in_array($e->criticidade, ['alta', 'suporte_vida']))) ?></strong>
            <span>Críticos</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Designação</th>
                <th>Marca / Modelo</th>
                <th>Serviço</th>
                <th>Estado</th>
                <th>Criticidade</th>
                <th>Aquisição</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($equipamentos as $eq): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($eq->codigo_inventario) ?></strong></td>
                    <td><?= htmlspecialchars($eq->designacao) ?></td>
                    <td>
                        <?= htmlspecialchars($eq->marca ?? '—') ?>
                        <?php if ($eq->modelo): ?><br><span style="color:#718096"><?= htmlspecialchars($eq->modelo) ?></span><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($eq->servico ?? '—') ?></td>
                    <td>
                        <span class="badge" style="background:#E2E8F0;">
                            <?= $estados[$eq->estado] ?? $eq->estado ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge" style="background:<?= $crit_cor[$eq->criticidade] ?? '#E2E8F0' ?>;">
                            <?= $crits[$eq->criticidade] ?? $eq->criticidade ?>
                        </span>
                    </td>
                    <td><?= $eq->data_aquisicao ? date('d/m/Y', strtotime($eq->data_aquisicao)) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <span>MediTrack — Sistema de Gestão de Inventário Hospitalar</span>
        <span>Total: <?= $total ?> equipamento(s)</span>
    </div>

</body>

</html>