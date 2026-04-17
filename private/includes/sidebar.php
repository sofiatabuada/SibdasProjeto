<!-- Sidebar -->
<aside class="col-md-3 col-lg-2 bo-sidebar">

    <nav class="mt-2">

        <!-- Dashboard -->
        <p class="bo-nav-section">Principal</p>
        <a href="/MediTrack/private/home.php"
            class="bo-nav-link <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>

        <!-- Equipamentos -->
        <p class="bo-nav-section">Inventário</p>
        <a href="/MediTrack/private/views/equipamentos/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'equipamentos') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-stethoscope"></i> Equipamentos
        </a>

        <a href="/MediTrack/private/views/equipamentos/pesquisa.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'pesquisa') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-magnifying-glass"></i> Pesquisa
        </a>

        <a href="/MediTrack/private/views/localizacoes/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'localizacoes') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-location-dot"></i> Localizações
        </a>

        <a href="/MediTrack/private/views/documentos/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'documentos') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-folder-open"></i> Documentos
        </a>

        <a href="/MediTrack/private/views/garantias/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'garantias') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-file-signature"></i> Garantias
        </a>

        <!-- Fornecedores -->
        <p class="bo-nav-section">Gestão</p>
        <a href="/MediTrack/private/views/fornecedores/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'fornecedores') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-truck-medical"></i> Fornecedores
        </a>

        <a href="/MediTrack/private/views/movimentacoes/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'movimentacoes') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-route"></i> Movimentações
        </a>

        <a href="/MediTrack/private/views/emprestimos/lista.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'emprestimos') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-right-left"></i> Empréstimos
        </a>

        <!-- Exportar / Etiquetas -->
        <p class="bo-nav-section">Exportar</p>
        <a href="/MediTrack/private/views/exportar/equipamentos_pdf.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'exportar') !== false && strpos($_SERVER['PHP_SELF'], 'pdf') !== false ? 'active' : '' ?>" target="_blank">
            <i class="fa-solid fa-file-pdf"></i> Relatório PDF
        </a>
        <a href="/MediTrack/private/views/exportar/equipamentos_csv.php"
            class="bo-nav-link">
            <i class="fa-solid fa-file-excel"></i> Exportar Excel
        </a>
        <a href="/MediTrack/private/views/etiquetas/imprimir.php"
            class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'etiquetas') !== false ? 'active' : '' ?>" target="_blank">
            <i class="fa-solid fa-tag"></i> Etiquetas
        </a>

        <!-- Backoffice — só admin -->
        <?php if (($_SESSION['profile'] ?? '') === 'admin'): ?>
            <p class="bo-nav-section">Administração</p>
            <a href="/MediTrack/private/views/backoffice/conteudos.php"
                class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'conteudos') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-pen-to-square"></i> Área Pública
            </a>
            <a href="/MediTrack/private/views/backoffice/mensagens.php"
                class="bo-nav-link <?= strpos($_SERVER['PHP_SELF'], 'mensagens') !== false ? 'active' : '' ?>">
                <i class="fa-solid fa-envelope"></i> Mensagens
            </a>
        <?php endif; ?>

        <!-- Voltar ao site -->
        <p class="bo-nav-section">Site</p>
        <a href="/MediTrack/public/index.php" class="bo-nav-link" target="_blank">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver site
        </a>

    </nav>
</aside>