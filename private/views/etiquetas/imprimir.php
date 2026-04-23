<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

$idEnc = $_GET['id'] ?? null;

if ($idEnc) {
    $id = aes_decrypt($idEnc);
    $stmt = $db->prepare("SELECT e.*, l.servico, l.sala FROM equipamentos e LEFT JOIN localizacoes l ON e.id_localizacao = l.id WHERE e.id = ? AND e.deleted_at IS NULL");
    $stmt->execute([$id]);
    $equipamentos = $stmt->fetchAll(PDO::FETCH_OBJ);
} else {
    $equipamentos = $db->query("
        SELECT e.*, l.servico, l.sala FROM equipamentos e
        LEFT JOIN localizacoes l ON e.id_localizacao = l.id
        WHERE e.deleted_at IS NULL ORDER BY e.codigo_inventario
    ")->fetchAll(PDO::FETCH_OBJ);
}
$db = null;

$crit_cor = ['baixa' => '#27ae60', 'media' => '#f39c12', 'alta' => '#e74c3c', 'suporte_vida' => '#c0392b'];
$crit_lab = ['baixa' => 'BAIXA', 'media' => 'MÉDIA', 'alta' => 'ALTA', 'suporte_vida' => 'SUPORTE DE VIDA'];
$estados  = ['ativo' => 'Ativo', 'manutencao' => 'Manutenção', 'inativo' => 'Inativo', 'calibracao' => 'Calibração', 'quarentena' => 'Quarentena', 'abatido' => 'Abatido'];

// Gerar QR code via API pública do Google Charts (não precisa de JS)
function qr_url($text)
{
    return 'https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=' . urlencode($text);
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>MediTrack — Etiquetas de Inventário</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4f8;
            color: #1a202c;
        }

        .toolbar {
            background: #1a202c;
            color: white;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .toolbar h2 {
            font-size: 15px;
            font-weight: 600;
        }

        .toolbar .info {
            font-size: 12px;
            color: #a0aec0;
            margin-top: 2px;
        }

        .toolbar .btns {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 18px;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-print {
            background: #4A90B8;
            color: white;
        }

        .btn-back {
            background: #4a5568;
            color: white;
        }

        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            padding: 24px;
            justify-content: flex-start;
        }

        .etiqueta {
            width: 230px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.10);
            overflow: hidden;
            page-break-inside: avoid;
            border: 1px solid #e2e8f0;
        }

        .etiqueta-header {
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .etiqueta-brand {
            font-size: 11px;
            font-weight: 700;
            color: white;
        }

        .etiqueta-crit {
            font-size: 9px;
            font-weight: 700;
            color: white;
            background: rgba(0, 0, 0, 0.2);
            padding: 2px 7px;
            border-radius: 20px;
        }

        .etiqueta-body {
            padding: 10px 12px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .etiqueta-info {
            flex: 1;
            min-width: 0;
        }

        .etiqueta-codigo {
            font-size: 13px;
            font-weight: 700;
            color: #1a202c;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            font-family: 'Courier New', monospace;
        }

        .etiqueta-nome {
            font-size: 10px;
            color: #2d3748;
            font-weight: 600;
            line-height: 1.35;
            margin-bottom: 5px;
        }

        .etiqueta-detalhe {
            font-size: 9px;
            color: #718096;
            line-height: 1.6;
        }

        .etiqueta-detalhe span {
            display: block;
        }

        .qr-wrapper {
            width: 68px;
            height: 68px;
            flex-shrink: 0;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            background: white;
        }

        .qr-wrapper img {
            width: 68px;
            height: 68px;
            display: block;
        }

        .etiqueta-footer {
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            padding: 5px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .etiqueta-estado {
            font-size: 9px;
            color: #4a5568;
            font-weight: 600;
        }

        .etiqueta-data {
            font-size: 8px;
            color: #a0aec0;
        }

        @media print {
            .toolbar {
                display: none;
            }

            body {
                background: white;
            }

            .grid {
                padding: 8px;
                gap: 8px;
            }

            .etiqueta {
                box-shadow: none;
                border: 1px solid #ccc;
                width: 210px;
            }
        }
    </style>
</head>

<body>

    <div class="toolbar">
        <div>
            <h2>🏷️ Etiquetas de Inventário MediTrack</h2>
            <div class="info"><?= count($equipamentos) ?> etiqueta(s) gerada(s)</div>
        </div>
        <div class="btns">
            <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir / PDF</button>
        </div>
    </div>

    <div class="grid">
        <?php foreach ($equipamentos as $eq):
            $cor    = $crit_cor[$eq->criticidade] ?? '#4A90B8';
            $crit   = $crit_lab[$eq->criticidade] ?? strtoupper($eq->criticidade);
            $estado = $estados[$eq->estado] ?? $eq->estado;
            $qr_txt = $eq->codigo_inventario . ' | ' . $eq->designacao . ($eq->numero_serie ? ' | SN:' . $eq->numero_serie : '');
        ?>
            <div class="etiqueta">
                <div class="etiqueta-header" style="background:<?= $cor ?>;">
                    <span class="etiqueta-brand">❤ MediTrack</span>
                    <span class="etiqueta-crit"><?= $crit ?></span>
                </div>
                <div class="etiqueta-body">
                    <div class="etiqueta-info">
                        <div class="etiqueta-codigo"><?= htmlspecialchars($eq->codigo_inventario) ?></div>
                        <div class="etiqueta-nome"><?= htmlspecialchars($eq->designacao) ?></div>
                        <div class="etiqueta-detalhe">
                            <?php if ($eq->marca || $eq->modelo): ?>
                                <span><?= htmlspecialchars(trim(($eq->marca ?? '') . ' ' . ($eq->modelo ?? ''))) ?></span>
                            <?php endif; ?>
                            <?php if ($eq->numero_serie): ?>
                                <span>S/N: <?= htmlspecialchars($eq->numero_serie) ?></span>
                            <?php endif; ?>
                            <?php if ($eq->servico): ?>
                                <span>📍 <?= htmlspecialchars($eq->servico) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="qr-wrapper">
                        <img src="<?= qr_url($qr_txt) ?>" alt="QR <?= htmlspecialchars($eq->codigo_inventario) ?>">
                    </div>
                </div>
                <div class="etiqueta-footer">
                    <span class="etiqueta-estado">● <?= $estado ?></span>
                    <span class="etiqueta-data"><?= date('m/Y') ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>