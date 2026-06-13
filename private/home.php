<?php
require_once __DIR__ . '/includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();

$total_equipamentos  = $db->query("SELECT COUNT(*) FROM equipamentos WHERE deleted_at IS NULL")->fetchColumn();
$total_ativos        = $db->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'ativo' AND deleted_at IS NULL")->fetchColumn();
$total_manutencao    = $db->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'manutencao' AND deleted_at IS NULL")->fetchColumn();
$total_inativos      = $db->query("SELECT COUNT(*) FROM equipamentos WHERE estado = 'inativo' AND deleted_at IS NULL")->fetchColumn();

$total_garantia_exp  = $db->query("SELECT COUNT(*) FROM garantias WHERE data_fim < CURDATE()")->fetchColumn();

$total_garantia_30   = $db->query("
    SELECT COUNT(*) FROM garantias
    WHERE data_fim >= CURDATE()
    AND data_fim <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetchColumn();

$total_criticos      = $db->query("
    SELECT COUNT(*) FROM equipamentos
    WHERE deleted_at IS NULL
    AND criticidade IN ('alta', 'suporte_vida')
")->fetchColumn();

$total_sem_docs      = $db->query("
    SELECT COUNT(*) FROM equipamentos e
    WHERE deleted_at IS NULL
    AND NOT EXISTS (SELECT 1 FROM documentos d WHERE d.id_equipamento = e.id)
")->fetchColumn();

$por_categoria = $db->query("
    SELECT categoria, COUNT(*) as total
    FROM equipamentos WHERE deleted_at IS NULL
    GROUP BY categoria
")->fetchAll(PDO::FETCH_ASSOC);

$por_estado = $db->query("
    SELECT estado, COUNT(*) as total
    FROM equipamentos WHERE deleted_at IS NULL
    GROUP BY estado
")->fetchAll(PDO::FETCH_ASSOC);

$por_criticidade = $db->query("
    SELECT criticidade, COUNT(*) as total
    FROM equipamentos WHERE deleted_at IS NULL
    GROUP BY criticidade
")->fetchAll(PDO::FETCH_ASSOC);

$por_servico = $db->query("
    SELECT l.servico, COUNT(e.id) as total
    FROM equipamentos e
    JOIN localizacoes l ON e.id_localizacao = l.id
    WHERE e.deleted_at IS NULL
    GROUP BY l.servico
    ORDER BY total DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$categorias_labels = array_map(fn($r) => ucfirst(str_replace('_', ' ', $r['categoria'])), $por_categoria);
$categorias_data   = array_column($por_categoria, 'total');

$estados_labels = array_map(fn($r) => ucfirst($r['estado']), $por_estado);
$estados_data   = array_column($por_estado, 'total');

$criticidade_labels = array_map(fn($r) => ucfirst(str_replace('_', ' ', $r['criticidade'])), $por_criticidade);
$criticidade_data   = array_column($por_criticidade, 'total');

$servico_labels = array_column($por_servico, 'servico');
$servico_data   = array_column($por_servico, 'total');

$db = null;
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">

        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <div class="mb-4">
                <h1 class="bo-page-title">
                    <i class="fa-solid fa-gauge-high me-2" style="color: var(--mt-blue-dark);"></i>Dashboard
                </h1>
                <p class="bo-page-subtitle">Visão geral do inventário hospitalar — <?= date('d/m/Y') ?></p>
            </div>

            <div class="row g-3 mb-4">

                <div class="col-6 col-md-3">
                    <div class="dash-card">
                        <div class="dash-icon icon-blue">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>
                        <div class="dash-value"><?= $total_equipamentos ?></div>
                        <div class="dash-label">Total de equipamentos</div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="dash-card">
                        <div class="dash-icon icon-green">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="dash-value"><?= $total_ativos ?></div>
                        <div class="dash-label">Equipamentos ativos</div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="dash-card">
                        <div class="dash-icon icon-yellow">
                            <i class="fa-solid fa-wrench"></i>
                        </div>
                        <div class="dash-value"><?= $total_manutencao ?></div>
                        <div class="dash-label">Em manutenção</div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="dash-card">
                        <div class="dash-icon icon-pink">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <div class="dash-value"><?= $total_inativos ?></div>
                        <div class="dash-label">Inativos</div>
                    </div>
                </div>

            </div>

            <div class="row g-3 mb-4">

                <div class="col-6 col-md-4">
                    <div class="dash-card">
                        <div class="dash-icon icon-pink">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="dash-value"><?= $total_garantia_exp ?></div>
                        <div class="dash-label">Garantias expiradas</div>
                    </div>
                </div>

                <div class="col-6 col-md-4">
                    <div class="dash-card">
                        <div class="dash-icon icon-yellow">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="dash-value"><?= $total_garantia_30 ?></div>
                        <div class="dash-label">Garantias a expirar (30 dias)</div>
                    </div>
                </div>

                <div class="col-6 col-md-4">
                    <div class="dash-card">
                        <div class="dash-icon icon-pink">
                            <i class="fa-solid fa-heart-pulse"></i>
                        </div>
                        <div class="dash-value"><?= $total_criticos ?></div>
                        <div class="dash-label">Equipamentos críticos</div>
                    </div>
                </div>

            </div>

            <?php if ($total_garantia_exp > 0 || $total_garantia_30 > 0 || $total_sem_docs > 0): ?>
                <div class="row g-3 mb-4">
                    <?php if ($total_garantia_exp > 0): ?>
                        <div class="col-md-6">
                            <div class="alert alert-danger d-flex align-items-center gap-3 rounded-3 border-0 shadow-sm" role="alert">
                                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                                <div>
                                    <strong><?= $total_garantia_exp ?> equipamento(s)</strong> com garantia expirada.
                                    <a href="views/garantias/lista.php" class="alert-link ms-1">Ver garantias</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($total_garantia_30 > 0): ?>
                        <div class="col-md-6">
                            <div class="alert alert-warning d-flex align-items-center gap-3 rounded-3 border-0 shadow-sm" role="alert">
                                <i class="fa-solid fa-clock fa-lg"></i>
                                <div>
                                    <strong><?= $total_garantia_30 ?> garantia(s)</strong> a expirar nos próximos 30 dias.
                                    <a href="views/garantias/lista.php" class="alert-link ms-1">Ver garantias</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($total_sem_docs > 0): ?>
                        <div class="col-md-6">
                            <div class="alert alert-info d-flex align-items-center gap-3 rounded-3 border-0 shadow-sm" role="alert">
                                <i class="fa-solid fa-folder-open fa-lg"></i>
                                <div>
                                    <strong><?= $total_sem_docs ?> equipamento(s)</strong> sem documentação associada.
                                    <a href="views/equipamentos/lista.php" class="alert-link ms-1">Ver lista</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="row g-4 mb-4">

                <div class="col-md-6 col-lg-4">
                    <div class="bo-card h-100">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-circle-half-stroke me-2"></i>Por Estado</h5>
                        </div>
                        <div class="bo-card-body d-flex align-items-center justify-content-center">
                            <canvas id="graficoEstado" style="max-height: 220px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="bo-card h-100">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-heart-pulse me-2"></i>Por Criticidade</h5>
                        </div>
                        <div class="bo-card-body d-flex align-items-center justify-content-center">
                            <canvas id="graficoCriticidade" style="max-height: 220px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 col-lg-4">
                    <div class="bo-card h-100">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-tags me-2"></i>Por Categoria</h5>
                        </div>
                        <div class="bo-card-body" style="position:relative; height:220px;">
                            <canvas id="graficoCategoria"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="bo-card">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-building me-2"></i>Equipamentos por Serviço (Top 5)</h5>
                        </div>
                        <div class="bo-card-body">
                            <canvas id="graficoServico" style="max-height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const mtBlue = '#7EB5D6';
    const mtGreen = '#A8D5BA';
    const mtPink = '#F4A7B9';
    const mtYellow = '#F9D89C';
    const mtPurple = '#C3B1E1';
    const mtOrange = '#FBBF7C';

    new Chart(document.getElementById('graficoEstado'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($estados_labels) ?>,
            datasets: [{
                data: <?= json_encode($estados_data) ?>,
                backgroundColor: [mtGreen, mtYellow, '#E2E8F0', mtBlue, mtPink, '#718096'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        },
                        padding: 10
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('graficoCriticidade'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($criticidade_labels) ?>,
            datasets: [{
                data: <?= json_encode($criticidade_data) ?>,
                backgroundColor: [mtGreen, mtYellow, mtPink, '#E57373'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 11
                        },
                        padding: 10
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('graficoCategoria'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($categorias_labels) ?>,
            datasets: [{
                data: <?= json_encode($categorias_data) ?>,
                backgroundColor: [mtBlue, mtGreen, mtPink, mtYellow, mtPurple, mtOrange, '#A0AEC0'],
                borderRadius: 6,
                borderWidth: 0
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        color: '#F3F6F9'
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });

    new Chart(document.getElementById('graficoServico'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($servico_labels) ?>,
            datasets: [{
                label: 'Equipamentos',
                data: <?= json_encode($servico_data) ?>,
                backgroundColor: mtBlue,
                borderRadius: 8,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F3F6F9'
                    },
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 11
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>