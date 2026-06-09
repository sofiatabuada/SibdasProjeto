<?php
$_nav_user    = htmlspecialchars($_SESSION['utilizador'] ?? '');
$_nav_initial = strtoupper(mb_substr($_SESSION['utilizador'] ?? 'U', 0, 1));
$_nav_role    = ($_SESSION['profile'] ?? '') === 'admin' ? 'Admin' : 'Agente';
?>
<nav class="bo-topnav">
    <div class="topnav-inner">

        <!-- Esquerda: toggle + brand -->
        <div class="topnav-left">
            <button class="topnav-icon-btn" id="sidebarToggle" title="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a class="topnav-brand" href="/MediTrack/private/home.php">
                <div class="brand-icon brand-icon-sm">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <span class="topnav-brand-name">MediTrack</span>
            </a>
        </div>

        <!-- Direita -->
        <div class="topnav-right">

            <!-- Notificações -->
            <div class="dropdown">
                <button class="topnav-icon-btn" id="notifToggle"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside"
                        title="Notificações">
                    <i class="fa-solid fa-bell" id="notifBellIcon"></i>
                    <span class="notif-badge d-none" id="notifBadge"></span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <span class="notif-header-title">
                            <i class="fa-solid fa-bell"></i>Notificações
                        </span>
                        <span class="notif-header-count d-none" id="notifHeaderCount"></span>
                    </div>
                    <div id="notifList">
                        <div class="notif-empty">
                            <i class="fa-solid fa-circle-check fa-lg text-success"></i>
                            <p class="mb-0 mt-2">Sem avisos pendentes</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="topnav-divider"></div>

            <!-- Perfil -->
            <div class="topnav-user">
                <div class="topnav-avatar"><?= $_nav_initial ?></div>
                <div class="topnav-user-info">
                    <span class="topnav-user-name"><?= $_nav_user ?></span>
                    <span class="topnav-user-role"><?= $_nav_role ?></span>
                </div>
            </div>

            <!-- Logout -->
            <a class="topnav-icon-btn topnav-logout" href="/MediTrack/public/logout.php" title="Sair">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>

        </div>
    </div>
</nav>
