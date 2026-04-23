<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$fornecedores = $db->query("
    SELECT f.*, COUNT(ef.id_equipamento) as total_equipamentos
    FROM fornecedores f
    LEFT JOIN equipamento_fornecedor ef ON f.id = ef.id_fornecedor
    WHERE f.deleted_at IS NULL
    GROUP BY f.id
    ORDER BY f.nome
")->fetchAll(PDO::FETCH_OBJ);
$total = count($fornecedores);
$db = null;

$tipos = [
    'fabricante'         => 'Fabricante',
    'distribuidor'       => 'Distribuidor',
    'assistencia_tecnica'=> 'Assistência Técnica',
    'consumiveis'        => 'Consumíveis',
    'outro'              => 'Outro',
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>MediTrack — Relatório de Fornecedores</title>
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
            <h1>Relatório de Fornecedores</h1>
        </div>
        <div class="meta">
            Gerado em: <?= date('d/m/Y H:i') ?><br>
            Utilizador: <?= htmlspecialchars($_SESSION['utilizador']) ?>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <strong><?= $total ?></strong>
            <span>Total de fornecedores</span>
        </div>
        <div class="summary-card">
            <strong><?= count(array_filter($fornecedores, fn($f) => $f->email)) ?></strong>
            <span>Com email</span>
        </div>
        <div class="summary-card">
            <strong><?= array_sum(array_column((array)$fornecedores, 'total_equipamentos')) ?></strong>
            <span>Equipamentos associados</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Tipo</th>
                <th>NIF</th>
                <th>Telefone</th>
                <th>Email</th>
                <th>Pessoa de Contacto</th>
                <th style="text-align:center;">Equipamentos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fornecedores as $f): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($f->nome) ?></strong></td>
                    <td><span class="badge"><?= $tipos[$f->tipo] ?? ucfirst($f->tipo) ?></span></td>
                    <td><?= htmlspecialchars($f->nif ?? '—') ?></td>
                    <td><?= htmlspecialchars($f->telefone ?? '—') ?></td>
                    <td><?= htmlspecialchars($f->email ?? '—') ?></td>
                    <td>
                        <?= htmlspecialchars($f->pessoa_contacto ?? '—') ?>
                        <?php if ($f->telefone_contacto): ?>
                            <span style="color:#718096;"> · <?= htmlspecialchars($f->telefone_contacto) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <span class="badge"><?= $f->total_equipamentos ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <span>MediTrack — Sistema de Gestão de Inventário Hospitalar</span>
        <span>Total: <?= $total ?> fornecedor(es)</span>
    </div>

</body>
</html>
