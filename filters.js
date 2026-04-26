(() => {
    const form = document.querySelector('#filter-form');
    if (!form) return;
    form.addEventListener('submit', () => {
        const data = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, value] of data.entries()) {
            if (value !== '') params.append(key, value);
        }
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    });
})();
