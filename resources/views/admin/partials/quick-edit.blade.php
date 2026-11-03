{{--
    Inline status switch + sort-order box for list pages. Each control carries
    data-quick-url (the row's quick-update route) and data-quick-field; a change
    PATCHes just that field and flashes the control green or red.
--}}
<style>
.drag-handle { cursor: grab; color: var(--t4); text-align: center; width: 36px; user-select: none; }
.drag-handle i { font-size: 1.2rem; }
tr.dragging { opacity: .45; }
tr.drag-over td { box-shadow: inset 0 2px 0 var(--red); }
tbody[data-reorder-url][data-reorder-disabled] .drag-handle { cursor: not-allowed; opacity: .35; }
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
    /* ---------- Drag & drop reordering (tbody[data-reorder-url]) ---------- */
    var tbody = document.querySelector('tbody[data-reorder-url]');
    if (tbody && !tbody.hasAttribute('data-reorder-disabled')) {
        var dragEl = null;
        var rows = function () { return Array.from(tbody.querySelectorAll('tr[data-id]')); };

        tbody.addEventListener('dragstart', function (e) {
            var row = e.target.closest('tr[data-id]');
            if (!row) return;
            dragEl = row;
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', row.dataset.id); } catch (err) {}
        });
        tbody.addEventListener('dragend', function () {
            if (dragEl) dragEl.classList.remove('dragging');
            rows().forEach(function (r) { r.classList.remove('drag-over'); });
            dragEl = null;
        });
        tbody.addEventListener('dragover', function (e) {
            if (!dragEl) return;
            e.preventDefault();
            var over = e.target.closest('tr[data-id]');
            if (!over || over === dragEl) return;
            rows().forEach(function (r) { r.classList.remove('drag-over'); });
            over.classList.add('drag-over');
            var rect = over.getBoundingClientRect();
            var after = (e.clientY - rect.top) > rect.height / 2;
            tbody.insertBefore(dragEl, after ? over.nextSibling : over);
        });
        tbody.addEventListener('drop', function (e) {
            if (!dragEl) return;
            e.preventDefault();
            rows().forEach(function (r) { r.classList.remove('drag-over'); });
            var ids = rows().map(function (r) { return r.dataset.id; });
            fetch(tbody.dataset.reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: ids, offset: parseInt(tbody.dataset.reorderOffset || '0', 10) })
            }).then(function (r) { if (!r.ok) throw r; return r.json(); }).then(function (d) {
                rows().forEach(function (r) {
                    var box = r.querySelector('[data-quick-field="sort_order"]');
                    if (box && d.positions[r.dataset.id] !== undefined) { box.value = d.positions[r.dataset.id]; flash(box, 'quick-saved'); }
                });
            }).catch(function () {
                alert('Could not save the new order. Please reload and try again.');
            });
        });
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
