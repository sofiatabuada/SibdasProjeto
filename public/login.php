<?php
session_start();

if (isset($_SESSION['utilizador'])) {
    header('Location: ../private/home.php');
    exit;
}

$validation_errors = [];
if (!empty($_SESSION['validation_errors'])) {
    $validation_errors = $_SESSION['validation_errors'];
    unset($_SESSION['validation_errors']);
}

$server_error = '';
if (!empty($_SESSION['server_error'])) {
    $server_error = $_SESSION['server_error'];
    unset($_SESSION['server_error']);
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediTrack — Iniciar Sessão</title>

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

<body class="login-page">

    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-xl-4 col-lg-5 col-md-7 col-sm-10 col-12">

                <div class="text-center mb-4">
                    <a href="index.php" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                        <div class="brand-icon">
                            <i class="fa-solid fa-heart-pulse"></i>
                        </div>
                        <span class="brand-name">MediTrack</span>
                    </a>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">

                    <h2 class="text-center mb-1" style="font-family: var(--font-display); font-size: 1.6rem;">Iniciar sessão</h2>
                    <p class="text-center text-muted mb-4" style="font-size: 0.9rem;">Introduza as suas credenciais para aceder à plataforma</p>

                    <form name="formulario" action="../private/processa_login.php" method="post">

                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.88rem;">Email</label>
                            <input type="email" class="form-control mt-input" name="text_username"
                                placeholder="utilizador@meditrack.pt"
                                value="<?= htmlspecialchars($_POST['text_username'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.88rem;">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control mt-input border-end-0"
                                    name="text_password" id="inputPassword" placeholder="••••••••">
                                <button class="btn btn-outline-secondary border-start-0 mt-input"
                                    type="button" id="togglePassword" tabindex="-1">
                                    <i class="fa-solid fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-mt-primary" id="btnLogin">
                                Entrar <i class="fa-solid fa-right-to-bracket ms-2"></i>
                            </button>
                        </div>

                        <div class="mt-3 text-center">
                            <small class="text-muted d-block mb-2">Acesso rápido para testes:</small>
                            <button type="button" id="preencher_adm" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fa-solid fa-user-shield me-1"></i>Admin
                            </button>
                            <button type="button" id="preencher_agnt" class="btn btn-outline-secondary btn-sm">
                                <i class="fa-solid fa-user me-1"></i>Agente
                            </button>
                        </div>

                        <?php if (!empty($validation_errors)): ?>
                            <div class="alert alert-danger p-2 text-center mt-3">
                                <?php foreach ($validation_errors as $error): ?>
                                    <div><?= htmlspecialchars($error) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($server_error)): ?>
                            <div class="alert alert-danger p-2 text-center mt-3">
                                <div><?= htmlspecialchars($server_error) ?></div>
                            </div>
                        <?php endif; ?>

                    </form>
                </div>

                <div class="text-center mt-3">
                    <a href="index.php" class="text-muted text-decoration-none" style="font-size: 0.85rem;">
                        <i class="fa-solid fa-arrow-left me-1"></i>Voltar ao site
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script src="../assets/bootstrap/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const input = document.getElementById('inputPassword');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-solid fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-solid fa-eye';
            }
        });

        document.querySelector('#preencher_adm').addEventListener('click', () => {
            document.forms['formulario']['text_username'].value = 'admin@meditrack.pt';
            document.forms['formulario']['text_password'].value = '$2y$10$aia';
        });

        document.querySelector('#preencher_agnt').addEventListener('click', () => {
            document.forms['formulario']['text_username'].value = 'agente1@meditrack.pt';
            document.forms['formulario']['text_password'].value = '$2y$10$O';
        });

        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.getElementById('btnLogin');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>A entrar...';
        });
    </script>

</body>

</html>