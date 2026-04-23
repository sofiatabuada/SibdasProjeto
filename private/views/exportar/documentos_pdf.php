<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$docs = $db->query("
    SELECT d.nome, d.tipo, d.data_documento, d.data_validade, d.ficheiro, d.observacoes,
           e.codigo_inventario, e.designacao
    FROM documentos d
    JOIN equipamentos e ON d.id_equipamento = e.id
    WHERE e.deleted_at IS NULL
    ORDER BY e.codigo_inventario, d.nome
")->fetchAll(PDO::FETCH_OBJ);
$total = count($docs);
$db = null;

$tipos = [
    'manual_utilizador'       => 'Manual Utilizador',
    'manual_servico'          => 'Manual Serviço',
    'certificado_calibracao'  => 'Cert. Calibração',
    'contrato_manutencao'     => 'Contrato Manutenção',
    'fatura'                  => 'Fatura',
    'declaracao_conformidade' => 'Decl. Conformidade',
    'relatorio_tecnico'       => 'Relatório Técnico',
    'outro'                   => 'Outro',
];

$expirados = count(array_filter($docs, fn($d) => $d->data_validade && strtotime($d->data_validade) < time()));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>MediTrack — Relatório de Documentos</title>
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
            background: #EBF5FB;
            color: #4A90B8;
        }
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
            <h1>Relatório de Documentos</h1>
        </div>
        <div class="meta">
            Gerado em: <?= date('d/m/Y H:i') ?><br>
            Utilizador: <?= htmlspecialchars($_SESSION['utilizador']) ?>
        </div>
    </div>

    <div class="summary">
        <div class="summary-card">
            <strong><?= $total ?></strong>
            <span>Total de documentos</span>
        </div>
        <div class="summary-card">
            <strong><?= count(array_filter($docs, fn($d) => $d->data_validade && strtotime($d->data_validade) >= time())) ?></strong>
            <span>Com validade activa</span>
        </div>
        <div class="summary-card">
            <strong class="<?= $expirados > 0 ? 'danger' : '' ?>"><?= $expirados ?></strong>
            <span>Expirados</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nome do Documento</th>
                <th>Tipo</th>
                <th>Equipamento</th>
                <th>Data</th>
                <th>Validade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($docs as $doc): ?>
                <?php $exp = $doc->data_validade && strtotime($doc->data_validade) < time(); ?>
                <tr>
                    <td><strong><?= htmlspecialchars($doc->nome) ?></strong></td>
                    <td><span class="badge"><?= $tipos[$doc->tipo] ?? ucfirst($doc->tipo) ?></span></td>
                    <td>
                        <?= htmlspecialchars($doc->designacao) ?>
                        <span style="color:#718096;"> (<?= htmlspecialchars($doc->codigo_inventario) ?>)</span>
                    </td>
                    <td><?= $doc->data_documento ? date('d/m/Y', strtotime($doc->data_documento)) : '—' ?></td>
                    <td class="<?= $exp ? 'expirado' : '' ?>">
                        <?= $doc->data_validade ? date('d/m/Y', strtotime($doc->data_validade)) . ($exp ? ' ⚠' : '') : '—' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        <span>MediTrack — Sistema de Gestão de Inventário Hospitalar</span>
        <span>Total: <?= $total ?> documento(s)</span>
    </div>

</body>
</html>
