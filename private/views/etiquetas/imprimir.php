<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

// Pode receber um ID específico ou imprimir todos
$idEnc = $_GET['id'] ?? null;

if ($idEnc) {
    $id = aes_decrypt($idEnc);
    $stmt = $db->prepare("SELECT e.*, l.servico FROM equipamentos e LEFT JOIN localizacoes l ON e.id_localizacao = l.id WHERE e.id = ? AND e.deleted_at IS NULL");
    $stmt->execute([$id]);
    $equipamentos = $stmt->fetchAll(PDO::FETCH_OBJ);
} else {
    $equipamentos = $db->query("
        SELECT e.*, l.servico FROM equipamentos e
        LEFT JOIN localizacoes l ON e.id_localizacao = l.id
        WHERE e.deleted_at IS NULL ORDER BY e.codigo_inventario
    ")->fetchAll(PDO::FETCH_OBJ);
}
$db = null;

$crit_cor = ['baixa' => '#A8D5BA', 'media' => '#F9D89C', 'alta' => '#F4A7B9', 'suporte_vida' => '#e57373'];
$crit_lab = ['baixa' => 'Baixa', 'media' => 'Média', 'alta' => 'Alta', 'suporte_vida' => 'Suporte de Vida'];
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>MediTrack — Etiquetas</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .no-print {
            background: #2D3748;
            color: white;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .no-print h2 {
            font-size: 16px;
        }

        .no-print .btns {
            display: flex;
            gap: 8px;
        }

        .btn {
            padding: 7px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
        }

        .btn-print {
            background: #7EB5D6;
            color: white;
        }

        .btn-back {
            background: #718096;
            color: white;
        }

        .etiquetas-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 20px;
            justify-content: flex-start;
        }

        .etiqueta {
            width: 200px;
            background: white;
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            padding: 12px;
            page-break-inside: avoid;
        }

        .etiqueta-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .etiqueta-logo {
            font-size: 11px;
            font-weight: bold;
            color: #4A90B8;
        }

        .etiqueta-codigo {
            font-size: 13px;
            font-weight: bold;
            color: #2D3748;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .etiqueta-nome {
            font-size: 10px;
            color: #2D3748;
            margin-bottom: 4px;
            font-weight: bold;
            line-height: 1.3;
        }

        .etiqueta-info {
            font-size: 9px;
            color: #718096;
            margin-bottom: 3px;
        }

        .etiqueta-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: bold;
            margin-top: 5px;
        }

        .qr-placeholder {
            width: 60px;
            height: 60px;
            border: 2px dashed #CBD5E0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7px;
            color: #A0AEC0;
            text-align: center;
            flex-shrink: 0;
            margin-left: 8px;
        }

        .etiqueta-body {
            display: flex;
            align-items: flex-start;
        }

        .etiqueta-text {
            flex: 1;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }

            .etiquetas-grid {
                padding: 0;
                gap: 8px;
            }

            .etiqueta {
                border: 1px solid #ccc;
                width: 190px;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <h2>🏷️ Etiquetas de Inventário — <?= count($equipamentos) ?> equipamento(s)</h2>
        <div class="btns">
            <button class="btn btn-back" onclick="window.history.back()">← Voltar</button>
            <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir Etiquetas</button>
        </div>
    </div>

    <div class="etiquetas-grid">
        <?php foreach ($equipamentos as $eq): ?>
            <div class="etiqueta">
                <div class="etiqueta-header">
                    <span class="etiqueta-logo">❤ MediTrack</span>
                </div>
                <div class="etiqueta-body">
                    <div class="etiqueta-text">
                        <div class="etiqueta-codigo"><?= htmlspecialchars($eq->codigo_inventario) ?></div>
                        <div class="etiqueta-nome"><?= htmlspecialchars($eq->designacao) ?></div>
                        <?php if ($eq->marca || $eq->modelo): ?>
                            <div class="etiqueta-info"><?= htmlspecialchars(trim(($eq->marca ?? '') . ' ' . ($eq->modelo ?? ''))) ?></div>
                        <?php endif; ?>
                        <?php if ($eq->numero_serie): ?>
                            <div class="etiqueta-info">S/N: <?= htmlspecialchars($eq->numero_serie) ?></div>
                        <?php endif; ?>
                        <?php if ($eq->servico): ?>
                            <div class="etiqueta-info">📍 <?= htmlspecialchars($eq->servico) ?></div>
                        <?php endif; ?>
                        <span class="etiqueta-badge" style="background:<?= $crit_cor[$eq->criticidade] ?? '#E2E8F0' ?>;">
                            <?= $crit_lab[$eq->criticidade] ?? $eq->criticidade ?>
                        </span>
                    </div>
                    <div class="qr-placeholder">
                        QR<br>CODE<br><?= htmlspecialchars(substr($eq->codigo_inventario, -3)) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>