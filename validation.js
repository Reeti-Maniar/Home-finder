(() => {
    const forms = document.querySelectorAll('[data-validate]');
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRe = /^[6-9]\d{9}$/;
    const passRe = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

    const markError = (field, message) => {
        field.classList.add('error');
        const wrapper = field.parentElement || field;
        let msg = wrapper.querySelector?.('.error-msg');
        if (!msg) {
            msg = document.createElement('small');
            msg.className = 'error-msg';
            wrapper.appendChild(msg);
        }
        msg.textContent = message;
    };

    const clearError = (field) => {
        field.classList.remove('error');
        const wrapper = field.parentElement || field;
        const msg = wrapper.querySelector?.('.error-msg');
        if (msg) msg.remove();
    };

    const validateGroup = (form, name) => !!form.querySelector(`input[name="${CSS.escape(name)}"]:checked`);

    const validateField = (form, field) => {
        if (field.type === 'radio') {
            if (field.required && !validateGroup(form, field.name)) {
                markError(field, 'Please select an option.');
                return false;
            }
            clearError(field);
            return true;
        }

        if (field.type === 'checkbox') {
            clearError(field);
            return true;
        }

        if (field.required && !String(field.value || '').trim()) {
            markError(field, 'This field is required.');
            return false;
        }
        if (field.type === 'email' && field.value && !emailRe.test(field.value)) {
            markError(field, 'Enter a valid email address.');
            return false;
        }
        if (field.type === 'tel' && field.value && !phoneRe.test(field.value.replace(/\D+/g, ''))) {
            markError(field, 'Enter a valid 10-digit mobile number.');
            return false;
        }
        if (field.type === 'password' && field.value && !passRe.test(field.value)) {
            markError(field, 'Use 8+ chars with one uppercase letter and one number.');
            return false;
        }
        clearError(field);
        return true;
    };

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            let valid = true;
            const seenRadioGroups = new Set();
            form.querySelectorAll('input, select, textarea').forEach((field) => {
                if (field.type === 'radio') {
                    if (seenRadioGroups.has(field.name)) return;
                    seenRadioGroups.add(field.name);
                }
                if (!validateField(form, field)) valid = false;
            });

            const password = form.querySelector('input[name="password"]');
            const confirm = form.querySelector('input[name="confirm_password"]');
            if (password && confirm && password.value !== confirm.value) {
                markError(confirm, 'Passwords do not match.');
                valid = false;
            }

            const minBudget = form.querySelector('input[name="min_budget"]');
            const maxBudget = form.querySelector('input[name="max_budget"]');
            if (minBudget && maxBudget && minBudget.value && maxBudget.value && Number(maxBudget.value) < Number(minBudget.value)) {
                markError(maxBudget, 'Max budget must be greater than min budget.');
                valid = false;
            }

            if (!valid) event.preventDefault();
        });

        form.addEventListener('input', (event) => {
            if (event.target && event.target.matches('input, select, textarea')) {
                validateField(form, event.target);
            }
        });
    });
})();
