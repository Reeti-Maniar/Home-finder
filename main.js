(() => {
    const navToggle = document.querySelector('[data-nav-toggle]');
    const navLinks = document.querySelector('[data-nav-links]');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', String(navLinks.classList.contains('open')));
        });
    }

    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length) {
        const animate = (el) => {
            const target = Number(el.dataset.target || 0);
            const duration = 1400;
            const start = performance.now();
            const step = (time) => {
                const pct = Math.min((time - start) / duration, 1);
                el.textContent = `${Math.floor(target * pct).toLocaleString('en-IN')}+`;
                if (pct < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach((counter) => observer.observe(counter));
    }

    const toggleButton = document.querySelector('[data-toggle-form]');
    const prefForm = document.querySelector('[data-preferences-form]');
    if (toggleButton && prefForm) {
        toggleButton.addEventListener('click', () => prefForm.classList.toggle('hidden'));
    }

    document.querySelectorAll('.thumb-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const main = document.querySelector('#main-image');
            const image = button.dataset.image;
            if (main && image) main.src = image;
        });
    });

    document.querySelectorAll('.interest-toggle').forEach((button) => {
        button.addEventListener('click', async () => {
            const response = await fetch('/homefinder/php/interests.php?action=toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': button.dataset.csrf || '' },
                body: JSON.stringify({ property_id: button.dataset.propertyId })
            });
            const data = await response.json();
            if (data.status === 'not_logged_in') {
                window.location.href = data.redirect;
                return;
            }
            if (data.status === 'locked') {
                button.classList.add('active');
                button.textContent = 'Saved';
                alert('This property already has a confirmed payment, so it cannot be removed from your interests.');
                return;
            }
            button.classList.toggle('active', data.status === 'added');
            button.textContent = data.status === 'added' ? 'Saved' : 'Save';
        });
    });
})();
