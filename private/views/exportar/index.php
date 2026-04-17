<?php
require_once __DIR__ . '/../../includes/funcoes.php';
redirect_if_not_logged();

$db = get_db();
$total_eq   = $db->query("SELECT COUNT(*) FROM equipamentos WHERE deleted_at IS NULL")->fetchColumn();
$total_forn = $db->query("SELECT COUNT(*) FROM fornecedores WHERE deleted_at IS NULL")->fetchColumn();
$total_loc  = $db->query("SELECT COUNT(*) FROM localizacoes")->fetchColumn();
$total_docs = $db->query("SELECT COUNT(*) FROM documentos")->fetchColumn();
$db = null;
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/nav.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 bo-content">

            <div class="mb-4">
                <h1 class="bo-page-title">
                    <i class="fa-solid fa-file-export me-2" style="color: var(--mt-blue-dark);"></i>Exportar Dados
                </h1>
                <p class="bo-page-subtitle">Exporte os dados do sistema para Excel ou PDF</p>
            </div>

            <div class="row g-4">

                <!-- Equipamentos -->
                <div class="col-md-6">
                    <div class="bo-card h-100">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-stethoscope me-2"></i>Equipamentos</h5>
                            <span class="badge bg-light text-dark border"><?= $total_eq ?> registos</span>
                        </div>
                        <div class="bo-card-body">
                            <p class="text-muted mb-4" style="font-size:0.9rem;">
                                Exportar listagem completa de equipamentos com código, designação, marca, modelo, estado, criticidade e localização.
                            </p>
                            <div class="d-flex gap-2">
                                <a href="equipamentos_csv.php" class="btn btn-mt-primary">
                                    <i class="fa-solid fa-file-excel me-2"></i>Exportar Excel (CSV)
                                </a>
                                <a href="equipamentos_pdf.php" class="btn btn-outline-secondary" target="_blank">
                                    <i class="fa-solid fa-file-pdf me-2"></i>Relatório PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fornecedores -->
                <div class="col-md-6">
                    <div class="bo-card h-100">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-truck-medical me-2"></i>Fornecedores</h5>
                            <span class="badge bg-light text-dark border"><?= $total_forn ?> registos</span>
                        </div>
                        <div class="bo-card-body">
                            <p class="text-muted mb-4" style="font-size:0.9rem;">
                                Exportar listagem de fornecedores com contactos, tipo e pessoa de contacto.
                            </p>
                            <div class="d-flex gap-2">
                                <a href="fornecedores_csv.php" class="btn btn-mt-primary">
                                    <i class="fa-solid fa-file-excel me-2"></i>Exportar Excel (CSV)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Etiquetas -->
                <div class="col-md-6">
                    <div class="bo-card h-100">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-tag me-2"></i>Etiquetas de Inventário</h5>
                            <span class="badge bg-light text-dark border"><?= $total_eq ?> etiquetas</span>
                        </div>
                        <div class="bo-card-body">
                            <p class="text-muted mb-4" style="font-size:0.9rem;">
                                Gerar etiquetas para todos os equipamentos com código de inventário, designação, localização e nível de criticidade.
                            </p>
                            <div class="d-flex gap-2">
                                <a href="/MediTrack/private/views/etiquetas/imprimir.php" class="btn btn-mt-primary" target="_blank">
                                    <i class="fa-solid fa-print me-2"></i>Imprimir todas as etiquetas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Localizações -->
                <div class="col-md-6">
                    <div class="bo-card h-100">
                        <div class="bo-card-header">
                            <h5><i class="fa-solid fa-location-dot me-2"></i>Localizações</h5>
                            <span class="badge bg-light text-dark border"><?= $total_loc ?> registos</span>
                        </div>
                        <div class="bo-card-body">
                            <p class="text-muted mb-4" style="font-size:0.9rem;">
                                Exportar listagem de localizações com edifício, piso, serviço e sala.
                            </p>
                            <div class="d-flex gap-2">
                                <a href="localizacoes_csv.php" class="btn btn-mt-primary">
                                    <i class="fa-solid fa-file-excel me-2"></i>Exportar Excel (CSV)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>