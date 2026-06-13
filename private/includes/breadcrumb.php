<?php

start_session();

// Definir o mapa de rotas
$breadcrumb_map = [
    'home' => ['label' => 'Dashboard', 'icon' => 'fa-gauge-high'],
    'equipamentos' => ['label' => 'Equipamentos', 'icon' => 'fa-stethoscope'],
    'equipamentos/novo' => ['label' => 'Novo Equipamento', 'icon' => 'fa-plus'],
    'equipamentos/editar' => ['label' => 'Editar Equipamento', 'icon' => 'fa-pen'],
    'equipamentos/detalhes' => ['label' => 'Detalhes', 'icon' => 'fa-circle-info'],
    'equipamentos/lista' => ['label' => 'Equipamentos', 'icon' => 'fa-list'],
    'equipamentos/pesquisa' => ['label' => 'Pesquisa', 'icon' => 'fa-search'],
    'fornecedores' => ['label' => 'Fornecedores', 'icon' => 'fa-building'],
    'fornecedores/novo' => ['label' => 'Novo Fornecedor', 'icon' => 'fa-plus'],
    'fornecedores/editar' => ['label' => 'Editar Fornecedor', 'icon' => 'fa-pen'],
    'fornecedores/lista' => ['label' => 'Fornecedores', 'icon' => 'fa-list'],
    'fornecedores/detalhes' => ['label' => 'Detalhes', 'icon' => 'fa-circle-info'],
    'localizacoes' => ['label' => 'Localizações', 'icon' => 'fa-map-location-dot'],
    'localizacoes/novo' => ['label' => 'Nova Localização', 'icon' => 'fa-plus'],
    'localizacoes/editar' => ['label' => 'Editar Localização', 'icon' => 'fa-pen'],
    'localizacoes/lista' => ['label' => 'Localizações', 'icon' => 'fa-list'],
    'localizacoes/detalhes' => ['label' => 'Detalhes', 'icon' => 'fa-circle-info'],
    'documentos' => ['label' => 'Documentação', 'icon' => 'fa-file'],
    'documentos/novo' => ['label' => 'Novo Documento', 'icon' => 'fa-plus'],
    'documentos/editar' => ['label' => 'Editar Documento', 'icon' => 'fa-pen'],
    'documentos/detalhes' => ['label' => 'Detalhes', 'icon' => 'fa-circle-info'],
    'documentos/apagar' => ['label' => 'Apagar', 'icon' => 'fa-trash'],
    'documentos/lista' => ['label' => 'Documentação', 'icon' => 'fa-list'],
    'garantias' => ['label' => 'Garantias', 'icon' => 'fa-shield'],
    'garantias/novo' => ['label' => 'Nova Garantia', 'icon' => 'fa-plus'],
    'garantias/editar' => ['label' => 'Editar Garantia', 'icon' => 'fa-pen'],
    'garantias/detalhes' => ['label' => 'Detalhes', 'icon' => 'fa-circle-info'],
    'garantias/apagar' => ['label' => 'Apagar', 'icon' => 'fa-trash'],
    'garantias/lista' => ['label' => 'Garantias', 'icon' => 'fa-list'],
    'componentes' => ['label' => 'Componentes', 'icon' => 'fa-microchip'],
    'componentes/novo' => ['label' => 'Novo Componente', 'icon' => 'fa-plus'],
    'componentes/editar' => ['label' => 'Editar Componente', 'icon' => 'fa-pen'],
    'componentes/apagar' => ['label' => 'Apagar', 'icon' => 'fa-trash'],
    'componentes/lista' => ['label' => 'Componentes', 'icon' => 'fa-list'],
    'movimentacoes' => ['label' => 'Movimentações', 'icon' => 'fa-route'],
    'movimentacoes/novo' => ['label' => 'Nova Movimentação', 'icon' => 'fa-plus'],
    'movimentacoes/editar' => ['label' => 'Editar Movimentação', 'icon' => 'fa-pen'],
    'movimentacoes/detalhes' => ['label' => 'Detalhes', 'icon' => 'fa-circle-info'],
    'movimentacoes/apagar' => ['label' => 'Apagar', 'icon' => 'fa-trash'],
    'emprestimos' => ['label' => 'Empréstimos', 'icon' => 'fa-right-left'],
    'emprestimos/novo' => ['label' => 'Novo Empréstimo', 'icon' => 'fa-plus'],
    'emprestimos/editar' => ['label' => 'Editar Empréstimo', 'icon' => 'fa-pen'],
    'emprestimos/detalhes' => ['label' => 'Detalhes', 'icon' => 'fa-circle-info'],
    'emprestimos/apagar' => ['label' => 'Apagar', 'icon' => 'fa-trash'],
    'manutencoes' => ['label' => 'Manutenções', 'icon' => 'fa-wrench'],
    'manutencoes/lista' => ['label' => 'Manutenções', 'icon' => 'fa-wrench'],
    'manutencoes/nova' => ['label' => 'Nova Manutenção', 'icon' => 'fa-plus'],
    'manutencoes/editar' => ['label' => 'Editar Manutenção', 'icon' => 'fa-pen'],
    'historico' => ['label' => 'Histórico', 'icon' => 'fa-clock-rotate-left'],
];

// Obter a página atual
$current_page = $_SERVER['PHP_SELF'];
$parts = explode('/', $current_page);
$page_name = str_replace('.php', '', end($parts));

// Tentar determinar a rota
$route = '';
$base_module = '';

$modules = ['equipamentos', 'fornecedores', 'localizacoes', 'documentos', 'garantias', 'componentes', 'movimentacoes', 'emprestimos', 'manutencoes', 'historico'];

foreach ($modules as $mod) {
    if (strpos($current_page, '/' . $mod . '/') !== false) {
        $base_module = $mod;
        // Se é a lista, o módulo já é a página atual — não duplicar
        $route = ($page_name === 'lista') ? $mod : $mod . '/' . $page_name;
        break;
    }
}

if (empty($base_module)) {
    $route = ($page_name === 'home') ? 'home' : $page_name;
}

?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb" style="background: transparent; padding: 0.5rem 0; margin-bottom: 0;">
        <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>/private/home.php" class="breadcrumb-link" style="text-decoration: none; color: var(--mt-blue, #0066cc);">
                <i class="fa-solid fa-home me-1"></i> Home
            </a>
        </li>

        <?php if (!empty($base_module) && isset($breadcrumb_map[$base_module])): ?>
            <?php if ($route === $base_module): ?>
                <!-- Na lista: módulo como item ativo, sem link -->
                <li class="breadcrumb-item active" aria-current="page" style="color: var(--mt-text, #333); font-weight: 500;">
                    <i class="fa-solid <?= $breadcrumb_map[$base_module]['icon'] ?> me-1"></i>
                    <?= $breadcrumb_map[$base_module]['label'] ?>
                </li>
            <?php else: ?>
                <!-- Noutra página: módulo como link -->
                <li class="breadcrumb-item">
                    <a href="<?= BASE_URL ?>/private/views/<?= $base_module ?>/lista.php" class="breadcrumb-link" style="text-decoration: none; color: var(--mt-blue, #0066cc);">
                        <i class="fa-solid <?= $breadcrumb_map[$base_module]['icon'] ?> me-1"></i>
                        <?= $breadcrumb_map[$base_module]['label'] ?>
                    </a>
                </li>
                <?php if (!empty($breadcrumb_map[$route])): ?>
                    <li class="breadcrumb-item active" aria-current="page" style="color: var(--mt-text, #333); font-weight: 500;">
                        <i class="fa-solid <?= $breadcrumb_map[$route]['icon'] ?? 'fa-circle' ?> me-1"></i>
                        <?= $breadcrumb_map[$route]['label'] ?? ucfirst($page_name) ?>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </ol>
</nav>

<style>
    nav[aria-label="breadcrumb"] {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
    }

    .breadcrumb {
        font-size: 0.9rem;
    }

    .breadcrumb-item+.breadcrumb-item::before {
        content: "/ ";
        color: var(--mt-border, #ddd);
        margin: 0 0.3rem;
    }

    .breadcrumb-link {
        color: var(--mt-blue, #0066cc);
        text-decoration: none;
        transition: color 0.2s;
    }

    .breadcrumb-link:hover {
        color: var(--mt-blue-dark, #0052a3);
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: var(--mt-text, #333);
        font-weight: 500;
    }

    /* Responde ao sidebar aberto/fechado */
    @media (max-width: 991px) {
        nav[aria-label="breadcrumb"] {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
</style>