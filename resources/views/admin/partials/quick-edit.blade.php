{{--
    Inline status switch + sort-order box for list pages. Each control carries
    data-quick-url (the row's quick-update route) and data-quick-field; a change
    PATCHes just that field and flashes the control green or red.
--}}
<style>
.quick-order { width: 72px; height: 32px; padding: 2px 8px; font-size: .8rem; text-align: center; }
.quick-cell { display: flex; align-items: center; gap: 8px; }
.quick-state { font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
.quick-state.on { color: #047857; }
.quick-state.off { color: #9ca3af; }
.quick-saved { box-shadow: 0 0 0 3px rgba(34,197,94,.35) !important; transition: box-shadow .2s; }
.quick-failed { box-shadow: 0 0 0 3px rgba(239,68,68,.4) !important; }
</style>
<script>
(function () {
    function flash(el, cls) {
        el.classList.add(cls);
        setTimeout(function () { el.classList.remove(cls); }, 1200);
    }
    function send(el, payload) {
        return fetch(el.dataset.quickUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(function (r) { if (!r.ok) throw r; return r.json(); });
    }
    document.addEventListener('change', function (e) {
        var el = e.target;
        if (!el.dataset || !el.dataset.quickUrl) return;
        var field = el.dataset.quickField;
        var payload = {};
        if (field === 'is_active') {
            payload.is_active = el.checked ? 1 : 0;
        } else {
            payload.sort_order = el.value === '' ? null : parseInt(el.value, 10);
        }
        el.disabled = true;
        send(el, payload).then(function (d) {
            if (field === 'is_active') {
                var label = el.closest('.quick-cell').querySelector('.quick-state');
                if (label) { label.textContent = d.is_active ? 'Active' : 'Hidden'; label.className = 'quick-state ' + (d.is_active ? 'on' : 'off'); }
            } else {
                el.value = d.sort_order === null ? '' : d.sort_order;
            }
            flash(el, 'quick-saved');
        }).catch(function () {
            if (field === 'is_active') el.checked = !el.checked;
            flash(el, 'quick-failed');
            alert('Could not save. Please try again.');
        }).finally(function () { el.disabled = false; });
    });
})();
</script>
