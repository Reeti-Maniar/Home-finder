(() => {
    const form = document.querySelector('[data-payment-form]');
    if (!form) return;
    form.addEventListener('submit', (event) => {
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Processing...';
        }
    });
})();
