<?php
require_once '../config/config.php';
try {
    $db = new PDO(
        "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DATABASE . ";charset=utf8mb4",
        MYSQL_USERNAME,
        MYSQL_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $c = $db->query("SELECT chave, valor FROM conteudos_publicos")->fetchAll(PDO::FETCH_KEY_PAIR);
    $db = null;
} catch (Exception $e) {
    $c = [];
}

// Valores por defeito caso a BD não tenha dados
$c += [
    'hero_titulo'       => 'Inventário médico inteligente e centralizado',
    'hero_descricao'    => 'A MediTrack transforma a gestão dispersa em folhas de Excel numa plataforma web centralizada, segura e fácil de usar.',
    'sobre_texto'       => 'A MediTrack foi desenvolvida para centralizar toda a informação numa plataforma web intuitiva.',
    'contacto_morada'   => 'Rua Dr. António Bernardino de Almeida, 431, 4249-015 Porto',
    'contacto_telefone' => '+351 222 000 000',
    'contacto_email'    => 'info@meditrack.pt',
    'contacto_horario'  => 'Segunda a Sexta, 09h00 – 18h00',
];
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediTrack — Gestão de Inventário Hospitalar</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../assets/fontawesome/all.min.css">

    <!-- CSS próprio -->
    <link rel="stylesheet" href="../assets/css/1221408.css">
</head>

<body>

    <!-- ======= NAVBAR ======= -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top mt-navbar" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#hero">
                <div class="brand-icon">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <span class="brand-name">MediTrack</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    <li class="nav-item"><a class="nav-link" href="#sobre">Sobre</a></li>
                    <li class="nav-item"><a class="nav-link" href="#funcionalidades">Funcionalidades</a></li>
                    <li class="nav-item"><a class="nav-link" href="#modulos">Módulos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contactos">Contactos</a></li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-mt-primary" href="login.php">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Entrar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ======= HERO ======= -->
    <section id="hero" class="hero-section">
        <div class="hero-blob hero-blob-1"></div>
        <div class="hero-blob hero-blob-2"></div>
        <div class="hero-blob hero-blob-3"></div>

        <div class="container hero-content">
            <div class="row align-items-center min-vh-100 py-5">
                <div class="col-lg-6 hero-text" data-animate>
                    <span class="hero-tag">
                        <i class="fa-solid fa-circle-check me-2"></i>Sistema certificado para uso hospitalar
                    </span>
                    <h1 class="hero-title">
                        <?= htmlspecialchars($c['hero_titulo']) ?>
                    </h1>
                    <p class="hero-desc">
                        <?= htmlspecialchars($c['hero_descricao']) ?>
                    </p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="login.php" class="btn btn-mt-primary btn-lg">
                            <i class="fa-solid fa-play me-2"></i>Experimentar agora
                        </a>
                        <a href="#funcionalidades" class="btn btn-mt-outline btn-lg">
                            Saber mais <i class="fa-solid fa-arrow-down ms-2"></i>
                        </a>
                    </div>
                    <div class="hero-stats mt-5">
                        <div class="stat-item">
                            <span class="stat-number">1500+</span>
                            <span class="stat-label">Equipamentos geridos</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-number">98%</span>
                            <span class="stat-label">Satisfação dos clientes</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-number">24/7</span>
                            <span class="stat-label">Disponibilidade</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 hero-visual" data-animate data-delay="200">
                    <div class="dashboard-mockup">
                        <div class="mockup-header">
                            <div class="mockup-dots">
                                <span></span><span></span><span></span>
                            </div>
                            <span class="mockup-title">MediTrack Dashboard</span>
                        </div>
                        <div class="mockup-body">
                            <div class="mockup-cards">
                                <div class="mockup-card card-blue">
                                    <i class="fa-solid fa-stethoscope"></i>
                                    <div>
                                        <strong>247</strong>
                                        <small>Equipamentos ativos</small>
                                    </div>
                                </div>
                                <div class="mockup-card card-green">
                                    <i class="fa-solid fa-building-columns"></i>
                                    <div>
                                        <strong>18</strong>
                                        <small>Localizações</small>
                                    </div>
                                </div>
                                <div class="mockup-card card-pink">
                                    <i class="fa-solid fa-truck-medical"></i>
                                    <div>
                                        <strong>34</strong>
                                        <small>Fornecedores</small>
                                    </div>
                                </div>
                                <div class="mockup-card card-yellow">
                                    <i class="fa-solid fa-file-medical"></i>
                                    <div>
                                        <strong>512</strong>
                                        <small>Documentos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mockup-chart">
                                <div class="chart-bar" style="height: 60%;" data-label="UCI"></div>
                                <div class="chart-bar" style="height: 85%;" data-label="Urgência"></div>
                                <div class="chart-bar" style="height: 45%;" data-label="Cirurgia"></div>
                                <div class="chart-bar" style="height: 70%;" data-label="Pediatria"></div>
                                <div class="chart-bar" style="height: 55%;" data-label="Medicina"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= SOBRE ======= -->
    <section id="sobre" class="section-sobre">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5" data-animate>
                    <div class="sobre-visual">
                        <div class="sobre-card sobre-card-1">
                            <i class="fa-solid fa-shield-halved"></i>
                            <div>
                                <strong>Dados protegidos</strong>
                                <small>Encriptação AES-256</small>
                            </div>
                        </div>
                        <div class="sobre-card sobre-card-2">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <div>
                                <strong>Histórico completo</strong>
                                <small>Rastreabilidade total</small>
                            </div>
                        </div>
                        <div class="sobre-main-icon">
                            <i class="fa-solid fa-hospital"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7" data-animate data-delay="150">
                    <span class="section-tag">Sobre a MediTrack</span>
                    <h2 class="section-title">Nascemos para resolver um problema real dos hospitais portugueses</h2>
                    <p class="section-text">
                        Em muitas instituições de saúde, a gestão do inventário de equipamentos médicos ainda
                        é feita com folhas de Excel dispersas, pastas físicas e registos manuais. Esta realidade
                        compromete a rastreabilidade, a segurança e a eficiência operacional.
                    </p>
                    <p class="section-text">
                        <?= htmlspecialchars($c['sobre_texto']) ?>
                    </p>
                    <div class="sobre-features">
                        <div class="sobre-feature">
                            <i class="fa-solid fa-check"></i>
                            <span>Desenvolvida em Portugal, para hospitais portugueses</span>
                        </div>
                        <div class="sobre-feature">
                            <i class="fa-solid fa-check"></i>
                            <span>Conforme com requisitos legais e de certificação</span>
                        </div>
                        <div class="sobre-feature">
                            <i class="fa-solid fa-check"></i>
                            <span>Base para futura integração com sistemas CMMS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= FUNCIONALIDADES ======= -->
    <section id="funcionalidades" class="section-funcionalidades">
        <div class="container">
            <div class="text-center mb-5" data-animate>
                <span class="section-tag">O que fazemos</span>
                <h2 class="section-title">Tudo o que precisa, num só lugar</h2>
                <p class="section-subtitle">Desenhado para simplificar a gestão diária dos equipamentos médicos</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4" data-animate>
                    <div class="feature-card">
                        <div class="feature-icon icon-blue">
                            <i class="fa-solid fa-clipboard-list"></i>
                        </div>
                        <h3>Inventário completo</h3>
                        <p>Registe todos os equipamentos com ficha técnica detalhada, estado, criticidade e localização em tempo real.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-animate data-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon icon-green">
                            <i class="fa-solid fa-magnifying-glass-chart"></i>
                        </div>
                        <h3>Pesquisa avançada</h3>
                        <p>Filtre por categoria, serviço, estado, fornecedor ou criticidade. Encontre qualquer equipamento em segundos.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-animate data-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon icon-pink">
                            <i class="fa-solid fa-file-contract"></i>
                        </div>
                        <h3>Gestão documental</h3>
                        <p>Associe manuais, certificados, contratos e relatórios técnicos a cada equipamento. Tudo acessível com um clique.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-animate data-delay="100">
                    <div class="feature-card">
                        <div class="feature-icon icon-yellow">
                            <i class="fa-solid fa-handshake"></i>
                        </div>
                        <h3>Gestão de fornecedores</h3>
                        <p>Registe fabricantes, distribuidores e empresas de assistência técnica. Saiba sempre quem contactar.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-animate data-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon icon-blue">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <h3>Dashboard inteligente</h3>
                        <p>Visualize indicadores chave do parque tecnológico hospitalar em tempo real, com gráficos e alertas automáticos.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-animate data-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon icon-green">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <h3>Localização física</h3>
                        <p>Saiba em qualquer momento onde está cada equipamento — por edifício, piso, serviço e sala.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= MÓDULOS ======= -->
    <section id="modulos" class="section-modulos">
        <div class="container">
            <div class="text-center mb-5" data-animate>
                <span class="section-tag">Módulos do sistema</span>
                <h2 class="section-title">Uma solução modular e completa</h2>
            </div>

            <div class="accordion modulos-accordion" id="acordeonModulos">

                <div class="accordion-item modulo-item" data-animate>
                    <h2 class="accordion-header">
                        <button class="accordion-button modulo-btn" type="button" data-bs-toggle="collapse" data-bs-target="#mod1">
                            <div class="modulo-icon icon-blue"><i class="fa-solid fa-stethoscope"></i></div>
                            <div>
                                <strong>Módulo de Equipamentos</strong>
                                <small>Núcleo principal do sistema</small>
                            </div>
                        </button>
                    </h2>
                    <div id="mod1" class="accordion-collapse collapse show" data-bs-parent="#acordeonModulos">
                        <div class="accordion-body modulo-body">
                            Registe e gira toda a informação dos equipamentos médicos: código interno, designação, categoria,
                            marca, modelo, número de série, fabricante, data de aquisição, estado atual e nível de criticidade clínica.
                            O módulo suporta os estados Ativo, Em manutenção, Inativo, Em calibração, Em quarentena e Abatido.
                        </div>
                    </div>
                </div>

                <div class="accordion-item modulo-item" data-animate data-delay="100">
                    <h2 class="accordion-header">
                        <button class="accordion-button modulo-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mod2">
                            <div class="modulo-icon icon-green"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <strong>Módulo de Localizações</strong>
                                <small>Rastreabilidade física completa</small>
                            </div>
                        </button>
                    </h2>
                    <div id="mod2" class="accordion-collapse collapse" data-bs-parent="#acordeonModulos">
                        <div class="accordion-body modulo-body">
                            Organize os equipamentos por edifício, piso, serviço/departamento e sala. Saiba em qualquer momento
                            exatamente onde se encontra cada dispositivo médico da instituição.
                        </div>
                    </div>
                </div>

                <div class="accordion-item modulo-item" data-animate data-delay="200">
                    <h2 class="accordion-header">
                        <button class="accordion-button modulo-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mod3">
                            <div class="modulo-icon icon-pink"><i class="fa-solid fa-truck-medical"></i></div>
                            <div>
                                <strong>Módulo de Fornecedores</strong>
                                <small>Gestão de fabricantes e assistência técnica</small>
                            </div>
                        </button>
                    </h2>
                    <div id="mod3" class="accordion-collapse collapse" data-bs-parent="#acordeonModulos">
                        <div class="accordion-body modulo-body">
                            Registe fabricantes, distribuidores, empresas de assistência técnica e fornecedores de consumíveis.
                            Associe múltiplos fornecedores a cada equipamento e mantenha todos os contactos centralizados.
                        </div>
                    </div>
                </div>

                <div class="accordion-item modulo-item" data-animate data-delay="300">
                    <h2 class="accordion-header">
                        <button class="accordion-button modulo-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mod4">
                            <div class="modulo-icon icon-yellow"><i class="fa-solid fa-folder-open"></i></div>
                            <div>
                                <strong>Módulo de Documentação</strong>
                                <small>Centralização de toda a documentação técnica</small>
                            </div>
                        </button>
                    </h2>
                    <div id="mod4" class="accordion-collapse collapse" data-bs-parent="#acordeonModulos">
                        <div class="accordion-body modulo-body">
                            Associe documentos a equipamentos: manuais de utilizador, manuais de serviço, certificados de calibração,
                            contratos de manutenção, faturas, declarações de conformidade e relatórios técnicos.
                        </div>
                    </div>
                </div>

                <div class="accordion-item modulo-item" data-animate data-delay="400">
                    <h2 class="accordion-header">
                        <button class="accordion-button modulo-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#mod5">
                            <div class="modulo-icon icon-blue"><i class="fa-solid fa-file-signature"></i></div>
                            <div>
                                <strong>Módulo de Garantias e Contratos</strong>
                                <small>Controlo de datas e alertas</small>
                            </div>
                        </button>
                    </h2>
                    <div id="mod5" class="accordion-collapse collapse" data-bs-parent="#acordeonModulos">
                        <div class="accordion-body modulo-body">
                            Registe datas de início e fim de garantia, contratos de manutenção, periodicidade e entidade responsável.
                            Receba alertas automáticos para garantias prestes a expirar.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ======= CONTACTOS ======= -->
    <section id="contactos" class="section-contactos">
        <div class="container">
            <div class="row g-5 align-items-start">
                <div class="col-lg-5" data-animate>
                    <span class="section-tag">Fale connosco</span>
                    <h2 class="section-title">Pronto para transformar a gestão do seu hospital?</h2>
                    <p class="section-text">
                        A nossa equipa está disponível para apresentar a plataforma, responder às suas
                        questões e adaptar a solução às necessidades da sua instituição.
                    </p>

                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                            <div>
                                <strong>Morada</strong>
                                <span><?= htmlspecialchars($c['contacto_morada']) ?></span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
                            <div>
                                <strong>Telefone</strong>
                                <span><?= htmlspecialchars($c['contacto_telefone']) ?></span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
                            <div>
                                <strong>Email</strong>
                                <span><?= htmlspecialchars($c['contacto_email']) ?></span>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fa-solid fa-clock"></i></div>
                            <div>
                                <strong>Horário de apoio</strong>
                                <span><?= htmlspecialchars($c['contacto_horario']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7" data-animate data-delay="200">
                    <div class="contact-form-card">
                        <h4 class="mb-4">Envie-nos uma mensagem</h4>
                        <form id="contactForm" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nome <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control mt-input" name="nome" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Instituição</label>
                                    <input type="text" class="form-control mt-input" name="instituicao">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control mt-input" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Telefone</label>
                                    <input type="tel" class="form-control mt-input" name="telefone">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Assunto <span class="text-danger">*</span></label>
                                    <select class="form-select mt-input" name="assunto" required>
                                        <option value="">Selecione um assunto</option>
                                        <option>Pedido de demonstração</option>
                                        <option>Informações sobre preços</option>
                                        <option>Suporte técnico</option>
                                        <option>Outro</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mensagem <span class="text-danger">*</span></label>
                                    <textarea class="form-control mt-input" name="mensagem" rows="4" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-mt-primary w-100">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Enviar mensagem
                                    </button>
                                </div>
                            </div>
                            <div id="formMsg" class="mt-3 d-none"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======= FOOTER ======= -->
    <footer class="mt-footer">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-md-4">
                    <a class="d-flex align-items-center gap-2 text-decoration-none" href="#hero">
                        <div class="brand-icon brand-icon-sm">
                            <i class="fa-solid fa-heart-pulse"></i>
                        </div>
                        <span class="brand-name">MediTrack</span>
                    </a>
                    <p class="footer-desc mt-2">Gestão de inventário hospitalar — desenvolvido em Portugal.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="footer-links">
                        <a href="#sobre">Sobre</a>
                        <a href="#funcionalidades">Funcionalidades</a>
                        <a href="#contactos">Contactos</a>
                        <a href="login.php">Acesso</a>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="footer-copy">© 2025 MediTrack. Todos os direitos reservados.</p>
                    <p class="footer-copy">NIF: 123 456 789 · Porto, Portugal</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <!-- JS próprio -->
    <script src="../assets/js/1221408.js"></script>
</body>

</html>