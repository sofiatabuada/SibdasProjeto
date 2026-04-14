document.addEventListener('DOMContentLoaded', function () {

    // ---- NAVBAR: scroll effect ----
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ---- SCROLL ANIMATIONS ----
    const animatedEls = document.querySelectorAll('[data-animate]');
    if (animatedEls.length > 0) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    const delay = entry.target.getAttribute('data-delay') || 0;
                    setTimeout(function () {
                        entry.target.classList.add('visible');
                    }, parseInt(delay));
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        animatedEls.forEach(function (el) {
            observer.observe(el);
        });
    }

    // ---- SMOOTH SCROLL para âncoras ----
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                const offset = 80;
                const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });

                // fechar navbar mobile se aberta
                const navMenu = document.getElementById('navMenu');
                if (navMenu && navMenu.classList.contains('show')) {
                    const toggler = document.querySelector('.navbar-toggler');
                    if (toggler) toggler.click();
                }
            }
        });
    });

    // ---- FORMULÁRIO DE CONTACTO ----
    const form = document.getElementById('contactForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const msgDiv = document.getElementById('formMsg');
            const btn = form.querySelector('button[type="submit"]');

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>A enviar...';
            msgDiv.classList.add('d-none');

            const formData = new FormData(form);

            fetch('processa_contacto.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.sucesso) {
                        msgDiv.className = 'mt-3 alert alert-success';
                        form.reset();
                    } else {
                        msgDiv.className = 'mt-3 alert alert-danger';
                    }
                    msgDiv.textContent = data.mensagem;
                    msgDiv.classList.remove('d-none');
                })
                .catch(() => {
                    msgDiv.className = 'mt-3 alert alert-danger';
                    msgDiv.textContent = 'Erro ao enviar. Tente novamente.';
                    msgDiv.classList.remove('d-none');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Enviar mensagem';
                });
        });
    }

    // ---- ACTIVE NAV LINK on scroll ----
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link[href^="#"]');

    if (sections.length > 0 && navLinks.length > 0) {
        window.addEventListener('scroll', function () {
            let current = '';
            sections.forEach(function (section) {
                const sectionTop = section.offsetTop - 100;
                if (window.pageYOffset >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(function (link) {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        });
    }

});