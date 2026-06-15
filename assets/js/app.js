/* Valuer.si — client-side logic */

// ===== AJAX Delete =====
document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('valuations-tbody');
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;

        const id   = btn.dataset.id;
        const name = btn.dataset.name;

        if (!confirm(`Ste prepričani, da želite izbrisati cenitev za "${name}"?`)) return;

        btn.disabled = true;
        btn.textContent = '…';

        fetch('/valuer-app/public/api/valuation_delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + encodeURIComponent(id),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById('row-' + id);
                if (row) {
                    row.classList.add('row-removing');
                    row.addEventListener('transitionend', () => row.remove());
                }
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Napaka pri brisanju.', 'error');
                btn.disabled = false;
                btn.textContent = 'Izbriši';
            }
        })
        .catch(() => {
            showToast('Napaka pri brisanju. Preverite povezavo.', 'error');
            btn.disabled = false;
            btn.textContent = 'Izbriši';
        });
    });
});

// ===== Toast notification =====
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'flash flash-' + type;
    toast.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;max-width:360px;box-shadow:0 4px 12px rgba(0,0,0,.15);';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ===== Client-side form validation =====
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('valuation-form') || document.getElementById('register-form') || document.getElementById('login-form');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        let valid = true;
        form.querySelectorAll('[required]').forEach(field => {
            clearFieldError(field);
            if (field.value.trim() === '') {
                showFieldError(field, 'To polje je obvezno.');
                valid = false;
            }
        });

        const emailField = form.querySelector('input[type="email"]');
        if (emailField && emailField.value.trim() !== '') {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailField.value)) {
                showFieldError(emailField, 'Vnesite veljaven e-poštni naslov.');
                valid = false;
            }
        }

        const p1 = form.querySelector('#geslo');
        const p2 = form.querySelector('#geslo2');
        if (p1 && p1.value.length > 0 && p1.value.length < 8) {
            showFieldError(p1, 'Geslo mora imeti vsaj 8 znakov.');
            valid = false;
        }
        if (p1 && p2 && p1.value !== p2.value && p2.value !== '') {
            showFieldError(p2, 'Gesli se ne ujemata.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
});

function showFieldError(field, message) {
    field.closest('.form-group')?.classList.add('has-error');
    let span = field.parentElement.querySelector('.error-msg-js');
    if (!span) {
        span = document.createElement('span');
        span.className = 'error-msg error-msg-js';
        field.insertAdjacentElement('afterend', span);
    }
    span.textContent = message;
}

function clearFieldError(field) {
    field.closest('.form-group')?.classList.remove('has-error');
    field.parentElement.querySelector('.error-msg-js')?.remove();
}
