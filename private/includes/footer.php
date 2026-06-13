<!-- Bootstrap JS -->
<script src="<?= BASE_URL ?>/assets/bootstrap/bootstrap.bundle.min.js"></script>

<!-- JS próprio -->
<script src="<?= BASE_URL ?>/assets/js/1221408.js?v=<?= filemtime(BASE_PATH . '/assets/js/1221408.js') ?>"></script>

<!-- Notificações -->
<script>
    (function() {
        const badge = document.getElementById('notifBadge');
        const list = document.getElementById('notifList');
        if (!badge || !list) return;

        const corClass = {
            danger: 'danger',
            warning: 'warning',
            info: 'info'
        };

        const headerCount = document.getElementById('notifHeaderCount');
        const btn = document.getElementById('notifToggle');

        function render(data) {
            if (data.total > 0) {
                var label = data.total > 99 ? '99+' : data.total;
                badge.textContent = label;
                badge.classList.remove('d-none');
                if (headerCount) {
                    headerCount.textContent = label;
                    headerCount.classList.remove('d-none');
                }
                if (btn) btn.classList.add('has-notifs');

                list.innerHTML = data.items.map(function(n) {
                    return '<a href="' + n.url + '" class="notif-item">' +
                        '<span class="notif-icon ' + (corClass[n.cor] || '') + '">' +
                        '<i class="fa-solid ' + n.icon + '"></i>' +
                        '</span>' +
                        '<span class="notif-label">' + n.label + '</span>' +
                        '<span class="notif-count">' + n.count + '</span>' +
                        '</a>';
                }).join('');
            } else {
                badge.classList.add('d-none');
                if (headerCount) headerCount.classList.add('d-none');
                if (btn) btn.classList.remove('has-notifs');
                list.innerHTML = '<div class="notif-empty">' +
                    '<i class="fa-solid fa-circle-check fa-lg text-success"></i>' +
                    '<p class="mb-0 mt-2">Sem avisos pendentes</p>' +
                    '</div>';
            }
        }

        function fetch_notifs() {
            fetch('<?= BASE_URL ?>/private/ajax/notificacoes.php')
                .then(function(r) {
                    return r.json();
                })
                .then(render)
                .catch(function() {});
        }

        fetch_notifs();
        setInterval(fetch_notifs, 60000); // actualiza a cada minuto
    })();
</script>

<!-- Sidebar toggle -->
<script>
    (function() {
        const btn = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.bo-sidebar');
        const main = document.querySelector('.bo-content');
        if (!btn || !sidebar) return;

        function hide() {
            sidebar.style.display = 'none';
            if (main) main.style.marginLeft = '0';
            localStorage.setItem('sidebarHidden', 'true');
        }

        function show() {
            sidebar.style.display = '';
            if (main) main.style.marginLeft = '62px';
            localStorage.setItem('sidebarHidden', 'false');
        }

        if (localStorage.getItem('sidebarHidden') === 'true') {
            hide();
        }

        btn.addEventListener('click', function() {
            sidebar.style.display === 'none' ? show() : hide();
        });
    })();
</script>

</body>

</html>