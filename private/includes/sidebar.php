<?php
$user_name = $_SESSION['utilizador'] ?? 'Utilizador';
$is_admin  = ($_SESSION['profile'] ?? '') === 'admin';
$user_initial = strtoupper(mb_substr($user_name, 0, 1));
?>
<aside class="col-md-3 col-lg-2 bo-sidebar sidebar-mini" id="sidebar">

    <!-- Header: Brand -->
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>/private/home.php" class="sidebar-brand">
            <div class="brand-icon brand-icon-sm flex-shrink-0">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <span class="sidebar-brand-name">MediTrack</span>
        </a>
    </div>

    <!-- Navegação -->
    <nav class="sidebar-nav">

        <p class="sidebar-section-label">Principal</p>
        <a href="<?= BASE_URL ?>/private/home.php"
            class="bo-nav-link <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>"
            title="Dashboard">
            <i class="fa-solid fa-gauge-high"></i>
            <span class="nav-label">Dashboard</span>
        </a>

        <p class="sidebar-section-label">Inventário</p>
        <a href="<?= BASE_URL ?>/private/views/equipamentos/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'equipamentos') !== false && strpos($_SERVER['PHP_SELF'], 'pesquisa') === false ? 'active' : '' ?>"
            title="Equipamentos">
            <i class="fa-solid fa-stethoscope"></i>
            <span class="nav-label">Equipamentos</span>
        </a>

        <a href="<?= BASE_URL ?>/private/views/equipamentos/pesquisa.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'pesquisa') !== false ? 'active' : '' ?>"
            title="Pesquisa">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span class="nav-label">Pesquisa</span>
        </a>

        <a href="<?= BASE_URL ?>/private/views/localizacoes/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'localizacoes') !== false ? 'active' : '' ?>"
            title="Localizações">
            <i class="fa-solid fa-location-dot"></i>
            <span class="nav-label">Localizações</span>
        </a>

        <a href="<?= BASE_URL ?>/private/views/documentos/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'documentos') !== false ? 'active' : '' ?>"
            title="Documentos">
            <i class="fa-solid fa-folder-open"></i>
            <span class="nav-label">Documentos</span>
        </a>

        <a href="<?= BASE_URL ?>/private/views/manutencoes/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'manutencoes') !== false ? 'active' : '' ?>"
            title="Manutenções">
            <i class="fa-solid fa-wrench"></i>
            <span class="nav-label">Manutenções</span>
        </a>

        <a href="<?= BASE_URL ?>/private/views/garantias/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'garantias') !== false ? 'active' : '' ?>"
            title="Garantias">
            <i class="fa-solid fa-file-signature"></i>
            <span class="nav-label">Garantias</span>
        </a>

        <p class="sidebar-section-label">Gestão</p>
        <a href="<?= BASE_URL ?>/private/views/fornecedores/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'fornecedores') !== false ? 'active' : '' ?>"
            title="Fornecedores">
            <i class="fa-solid fa-truck-medical"></i>
            <span class="nav-label">Fornecedores</span>
        </a>

        <a href="<?= BASE_URL ?>/private/views/movimentacoes/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'movimentacoes') !== false ? 'active' : '' ?>"
            title="Movimentações">
            <i class="fa-solid fa-route"></i>
            <span class="nav-label">Movimentações</span>
        </a>

        <a href="<?= BASE_URL ?>/private/views/emprestimos/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'emprestimos') !== false ? 'active' : '' ?>"
            title="Empréstimos">
            <i class="fa-solid fa-right-left"></i>
            <span class="nav-label">Empréstimos</span>
        </a>

        <?php if ($is_admin): ?>
            <p class="sidebar-section-label">Administração</p>
            <a href="<?= BASE_URL ?>/private/views/backoffice/conteudos.php"
                class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'conteudos') !== false ? 'active' : '' ?>"
                title="Área Pública">
                <i class="fa-solid fa-pen-to-square"></i>
                <span class="nav-label">Área Pública</span>
            </a>
            <a href="<?= BASE_URL ?>/private/views/backoffice/mensagens.php"
                class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'mensagens') !== false ? 'active' : '' ?>"
                title="Mensagens">
                <i class="fa-solid fa-envelope"></i>
                <span class="nav-label">Mensagens</span>
            </a>
            <a href="<?= BASE_URL ?>/private/views/historico/lista.php"
                class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'historico') !== false ? 'active' : '' ?>"
                title="Histórico">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span class="nav-label">Histórico</span>
            </a>
        <?php endif; ?>

        <p class="sidebar-section-label">Site</p>
        <a href="<?= BASE_URL ?>/public/index.php" class="bo-nav-link" target="_blank" title="Ver site">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span class="nav-label">Ver site</span>
        </a>

    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?= $user_initial ?></div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name"><?= htmlspecialchars($user_name) ?></span>
                <span class="sidebar-user-role"><?= $is_admin ? 'Admin' : 'Agente' ?></span>
            </div>
        </div>
    </div>

</aside>