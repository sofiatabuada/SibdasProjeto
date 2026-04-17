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

        // Guardar largura original
        const originalWidth = sidebar.offsetWidth;

        // Aplicar estilos base para animação
        sidebar.style.overflow = 'hidden';
        sidebar.style.transition = 'width 0.35s cubic-bezier(0.4,0,0.2,1), opacity 0.35s ease, padding 0.35s ease';
        if (main) main.style.transition = 'all 0.35s cubic-bezier(0.4,0,0.2,1)';

        function hide() {
            sidebar.style.width = originalWidth + 'px';
            sidebar.style.opacity = '1';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    sidebar.style.width = '0';
                    sidebar.style.opacity = '0';
                    sidebar.style.padding = '0';
                    sidebar.style.margin = '0';
                });
            });
            if (main) {
                main.style.maxWidth = '100%';
                main.style.flex = '0 0 100%';
            }
            localStorage.setItem('sidebarHidden', 'true');
        }

        function show() {
            sidebar.style.width = '0';
            sidebar.style.opacity = '0';
            sidebar.style.padding = '0';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    sidebar.style.width = originalWidth + 'px';
                    sidebar.style.opacity = '1';
                    sidebar.style.padding = '';
                    sidebar.style.margin = '';
                });
            });
            if (main) {
                main.style.maxWidth = '';
                main.style.flex = '';
            }
            localStorage.setItem('sidebarHidden', 'false');
        }

        // Restaurar estado guardado
        if (localStorage.getItem('sidebarHidden') === 'true') {
            sidebar.style.width = '0';
            sidebar.style.opacity = '0';
            sidebar.style.padding = '0';
            sidebar.style.margin = '0';
            if (main) {
                main.style.maxWidth = '100%';
                main.style.flex = '0 0 100%';
            }
        }

        btn.addEventListener('click', function() {
            if (sidebar.style.width === '0px' || sidebar.style.width === '0') {
                show();
            } else {
                hide();
            }
        });

        // Fechar ao clicar num link (exceto o ativo)
        sidebar.querySelectorAll('.bo-nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (!this.classList.contains('active')) {
                    hide();
                }
            });
        });

    })();
</script>

</body>

</html>