document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (e) => {
            const id = link.getAttribute('href');
            if (!id || id === '#') {
                return;
            }
            const target = document.querySelector(id);
            if (!target) {
                return;
            }
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            const mobile = document.querySelector('.mobile-menu');
            const toggle = document.querySelector('.mobile-menu-toggle');
            if (mobile && !mobile.classList.contains('hidden')) {
                mobile.classList.add('hidden');
                toggle?.setAttribute('aria-expanded', 'false');
            }
        });
    });

    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            const expanded = !mobileMenu.classList.contains('hidden');
            mobileToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    }

    const revealEls = document.querySelectorAll('.nerpa-reveal');
    if (revealEls.length) {
        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('nerpa-reveal-visible');
                        io.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
        );
        revealEls.forEach((el) => io.observe(el));
    }

    const counterEls = document.querySelectorAll('[data-nerpa-counter]');
    const runCounters = () => {
        counterEls.forEach((el) => {
            const raw = el.getAttribute('data-nerpa-counter');
            const suffix = el.getAttribute('data-nerpa-suffix') ?? '';
            const target = parseInt(raw, 10);
            if (Number.isNaN(target)) {
                return;
            }
            const duration = 900;
            const start = performance.now();

            const tick = (now) => {
                const t = Math.min(1, (now - start) / duration);
                const eased = 1 - (1 - t) ** 3;
                const value = Math.round(target * eased);
                el.textContent = `${value}${suffix}`;
                if (t < 1) {
                    requestAnimationFrame(tick);
                }
            };
            requestAnimationFrame(tick);
        });
    };

    const hero = document.querySelector('.nerpa-hero');
    if (hero && counterEls.length) {
        const heroIo = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        runCounters();
                        heroIo.disconnect();
                    }
                });
            },
            { threshold: 0.35 }
        );
        heroIo.observe(hero);
    }
});
