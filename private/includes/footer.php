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
            sidebar.style.display = 'none';
            if (main) {
                main.classList.remove('col-md-9', 'col-lg-10');
                main.classList.add('col-12');
            }
            localStorage.setItem('sidebarHidden', 'true');
        }

        function show() {
            sidebar.style.display = '';
            if (main) {
                main.classList.add('col-md-9', 'col-lg-10');
                main.classList.remove('col-12');
            }
            localStorage.setItem('sidebarHidden', 'false');
        }

        // Restaurar estado guardado
        if (localStorage.getItem('sidebarHidden') === 'true') {
            hide();
        }

        btn.addEventListener('click', function() {
            if (sidebar.style.display === 'none') {
                show();
            } else {
                hide();
            }
        });
    })();
</script>

</body>

</html>