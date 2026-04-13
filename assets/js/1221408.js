/* =============================================
   MediTrack — 1221408.js
   ============================================= */

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

            const nome = form.querySelector('[name="nome"]').value.trim();
            const email = form.querySelector('[name="email"]').value.trim();
            const assunto = form.querySelector('[name="assunto"]').value;
            const mensagem = form.querySelector('[name="mensagem"]').value.trim();
            const msgDiv = document.getElementById('formMsg');

            // Validação básica
            if (!nome || !email || !assunto || !mensagem) {
                msgDiv.className = 'mt-3 alert alert-danger';
                msgDiv.textContent = 'Por favor preencha todos os campos obrigatórios.';
                msgDiv.classList.remove('d-none');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                msgDiv.className = 'mt-3 alert alert-danger';
                msgDiv.textContent = 'Por favor introduza um endereço de email válido.';
                msgDiv.classList.remove('d-none');
                return;
            }

            // Simular envio com sucesso
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>A enviar...';

            setTimeout(function () {
                msgDiv.className = 'mt-3 alert alert-success';
                msgDiv.textContent = 'Mensagem enviada com sucesso! Entraremos em contacto brevemente.';
                msgDiv.classList.remove('d-none');
                form.reset();
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane me-2"></i>Enviar mensagem';
            }, 1200);
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