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

// ===== Address Autocomplete (Photon / OpenStreetMap, Slovenia) =====
(function () {
    // Photon supports partial/prefix queries unlike Nominatim.
    // bbox limits results to Slovenia's bounding box.
    const PHOTON = 'https://photon.komoot.io/api/';
    const SI_BBOX = '13.38,45.42,16.61,46.88';
    let debounceTimer = null;
    let activeFocus   = -1;

    function formatAddress(feature) {
        const p = feature.properties;
        // House-level result: has both street and housenumber
        if (p.street && p.housenumber) {
            const city = [p.postcode, p.city || p.county].filter(Boolean).join(' ');
            return [p.street + ' ' + p.housenumber, city].filter(Boolean).join(', ');
        }
        // Street-only result: name is the street name
        if (p.type === 'street' || (!p.housenumber && p.name)) {
            const city = [p.postcode, p.city || p.county].filter(Boolean).join(' ');
            return [p.street || p.name, city].filter(Boolean).join(', ');
        }
        // Fallback: postcode + city
        return [p.postcode, p.city || p.name].filter(Boolean).join(' ');
    }

    // Deduplicate suggestions by formatted text
    function dedupe(features) {
        const seen = new Set();
        return features.filter(f => {
            const t = formatAddress(f);
            if (!t || seen.has(t)) return false;
            seen.add(t);
            return true;
        });
    }

    function initAutocomplete(input) {
        // Wrap input so we can absolutely position the dropdown under it
        const wrapper = document.createElement('div');
        wrapper.className = 'autocomplete-wrapper';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const list = document.createElement('ul');
        list.className = 'autocomplete-list';
        wrapper.appendChild(list);

        function closeList() {
            list.innerHTML = '';
            list.hidden = true;
            activeFocus = -1;
        }

        function highlightItem(items) {
            items.forEach((li, i) =>
                li.classList.toggle('autocomplete-active', i === activeFocus)
            );
            if (activeFocus >= 0) items[activeFocus]?.scrollIntoView({ block: 'nearest' });
        }

        function pickItem(text) {
            input.value = text;
            closeList();
            // Clear any validation error on this field
            clearFieldError(input);
        }

        input.addEventListener('input', () => {
            const q = input.value.trim();
            clearTimeout(debounceTimer);
            if (q.length < 3) { closeList(); return; }

            debounceTimer = setTimeout(async () => {
                try {
                    const url = `${PHOTON}?q=${encodeURIComponent(q)}&limit=6&lang=en&bbox=${SI_BBOX}`;
                    const res  = await fetch(url);
                    const data = await res.json();
                    const features = dedupe(data.features || []);

                    list.innerHTML = '';
                    if (!features.length) { closeList(); return; }

                    features.forEach(place => {
                        const text = formatAddress(place);
                        const li   = document.createElement('li');
                        li.className   = 'autocomplete-item';
                        li.textContent = text;
                        // mousedown fires before blur so we can pick before the list closes
                        li.addEventListener('mousedown', e => { e.preventDefault(); pickItem(text); });
                        list.appendChild(li);
                    });

                    list.hidden = false;
                    activeFocus = -1;
                } catch (_) { closeList(); }
            }, 350);
        });

        input.addEventListener('keydown', e => {
            const items = [...list.querySelectorAll('.autocomplete-item')];
            if (!items.length) return;
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeFocus = Math.min(activeFocus + 1, items.length - 1);
                highlightItem(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeFocus = Math.max(activeFocus - 1, 0);
                highlightItem(items);
            } else if (e.key === 'Enter' && activeFocus >= 0) {
                e.preventDefault();
                pickItem(items[activeFocus].textContent);
            } else if (e.key === 'Escape') {
                closeList();
            }
        });

        input.addEventListener('blur', () => setTimeout(closeList, 150));
        closeList(); // start hidden
    }

    document.addEventListener('DOMContentLoaded', () => {
        const field = document.getElementById('naslov_narocnika');
        if (field) initAutocomplete(field);
    });
})();
