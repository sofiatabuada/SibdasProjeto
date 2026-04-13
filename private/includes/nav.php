<!-- Navbar topo -->
<nav class="navbar navbar-expand-lg navbar-dark bo-topnav">
    <div class="container-fluid px-4">

        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="/MediTrack/private/home.php">
            <div class="brand-icon brand-icon-sm">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <span class="brand-name text-white">MediTrack</span>
        </a>

        <!-- Toggle mobile -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPrivate">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarPrivate">
            <ul class="navbar-nav ms-auto align-items-center gap-2">

                <!-- Perfil -->
                <li class="nav-item">
                    <span class="nav-link text-white-50" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-user me-1"></i>
                        <?= htmlspecialchars($_SESSION['utilizador'] ?? '') ?>
                        <span class="badge bg-secondary ms-1" style="font-size: 0.7rem;">
                            <?= ($_SESSION['profile'] ?? '') === 'admin' ? 'Admin' : 'Agente' ?>
                        </span>
                    </span>
                </li>

                <!-- Sair -->
                <li class="nav-item">
                    <a class="btn btn-sm btn-outline-light" href="/MediTrack/private/logout.php">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Sair
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>