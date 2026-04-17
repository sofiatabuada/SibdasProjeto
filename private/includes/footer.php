<!-- Bootstrap JS -->
<script src="/MediTrack/assets/bootstrap/bootstrap.bundle.min.js"></script>

<!-- JS próprio -->
<script src="/MediTrack/assets/js/1221408.js"></script>

<!-- Sidebar toggle -->
<script>
    (function() {
        const btn = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.bo-sidebar');
        const main = document.querySelector('.bo-content');
        if (!btn || !sidebar) return;

        function hide() {
            sidebar.style.transition = 'all 0.3s ease';
            sidebar.style.opacity = '0';
            sidebar.style.transform = 'translateX(-100%)';
            setTimeout(function() {
                sidebar.style.display = 'none';
                sidebar.style.opacity = '';
                sidebar.style.transform = '';
            }, 300);
            if (main) {
                main.classList.remove('col-md-9', 'col-lg-10');
                main.classList.add('col-12');
            }
            localStorage.setItem('sidebarHidden', 'true');
        }

        function show() {
            sidebar.style.display = '';
            sidebar.style.opacity = '0';
            sidebar.style.transform = 'translateX(-100%)';
            sidebar.style.transition = 'all 0.3s ease';
            setTimeout(function() {
                sidebar.style.opacity = '1';
                sidebar.style.transform = 'translateX(0)';
            }, 10);
            setTimeout(function() {
                sidebar.style.opacity = '';
                sidebar.style.transform = '';
                sidebar.style.transition = '';
            }, 320);
            if (main) {
                main.classList.add('col-md-9', 'col-lg-10');
                main.classList.remove('col-12');
            }
            localStorage.setItem('sidebarHidden', 'false');
        }

        // Restaurar estado guardado
        if (localStorage.getItem('sidebarHidden') === 'true') {
            sidebar.style.display = 'none';
            if (main) {
                main.classList.remove('col-md-9', 'col-lg-10');
                main.classList.add('col-12');
            }
        }

        btn.addEventListener('click', function() {
            if (sidebar.style.display === 'none') {
                show();
            } else {
                hide();
            }
        });

        // Fechar sidebar ao clicar num link do menu
        sidebar.querySelectorAll('.bo-nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                // Só fecha se não for o link ativo (já estamos nessa página)
                if (!this.classList.contains('active')) {
                    hide();
                }
            });
        });

    })();
</script>

</body>

</html>