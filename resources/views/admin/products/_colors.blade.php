{{-- Colour options editor. Posts a JSON list in the hidden `colors` field; see Product::parseColors(). --}}
@php
    $existingColors = old('colors');
    if (is_string($existingColors) && $existingColors !== '') {
        $existingColors = json_decode($existingColors, true) ?: [];
    } elseif (isset($product) && is_array($product->colors)) {
        $existingColors = $product->colors;
    } else {
        $existingColors = [];
    }
@endphp
<style>
.color-rows { display:flex; flex-direction:column; gap:10px; }
.color-row { display:grid; grid-template-columns: 44px 1.2fr 1.4fr 0.8fr 0.7fr 1fr 32px; gap:8px; align-items:center; padding:10px; border:1px solid var(--line,#e5e7eb); border-radius:8px; background:var(--surf2,#f8f8f9); }
.color-row input[type="color"] { width:44px; height:38px; padding:2px; border-radius:6px; border:1px solid var(--line,#e5e7eb); background:#fff; cursor:pointer; }
.color-row .color-img { display:flex; gap:6px; align-items:center; }
.color-row .color-img img { width:36px; height:36px; object-fit:cover; border-radius:4px; border:1px solid #ddd; }
.color-row .color-img .btn-library { margin:0; white-space:nowrap; }
.color-row .btn-ghost-del { background:none; border:0; color:#b91c1c; cursor:pointer; }
.color-head { display:grid; grid-template-columns: 44px 1.2fr 1.4fr 0.8fr 0.7fr 1fr 32px; gap:8px; font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:var(--t2,#6b7280); padding:0 10px; }
@media (max-width: 900px) { .color-row, .color-head { grid-template-columns: 44px 1fr 1fr; } .color-head span:nth-child(n+4) { display:none; } }
</style>
<div class="bcard">
    <div class="bcard-head">
        <span class="bcard-title">Colour Options</span>
        <button type="button" class="btn btn-sm btn-light" onclick="addColorRow()"><i class="mdi mdi-plus"></i> Add colour</button>
    </div>
    <div class="bcard-body">
        <p class="form-hint" style="margin-bottom:10px;">Optional. Each colour can have its own photo, price, stock and a short quality note. Leave price blank to use the product price; leave stock blank for unlimited. Customers pick a colour on the product page and it is saved with the order.</p>
        <div class="color-head"><span></span><span>Colour name</span><span>Photo</span><span>Price (Rs.)</span><span>Stock</span><span>Quality / note</span><span></span></div>
        <div class="color-rows" id="colorRows"></div>
        <div class="form-hint" id="colorEmptyHint" style="margin-top:8px;">No colour options yet. The product is sold as a single colour.</div>
        <input type="hidden" name="colors" id="colorsField" value="">
    </div>
</div>
<template id="colorRowTemplate">
    <div class="color-row" data-color-row>
        <input type="color" data-field="hex" value="#333333" title="Swatch colour">
        <input type="text" class="form-control" data-field="name" placeholder="e.g. Maroon" maxlength="60">
        <div class="color-img">
            <img data-field="preview" alt="" style="display:none;">
            <input type="hidden" data-field="image" value="">
            <span class="btn-library" onclick="pickColorImage(this)"><i class="mdi mdi-folder-image"></i> Photo</span>
            <span class="btn-library" onclick="clearColorImage(this)" title="Remove photo"><i class="mdi mdi-close"></i></span>
        </div>
        <input type="number" class="form-control" data-field="price" placeholder="Same" min="0" step="1">
        <input type="number" class="form-control" data-field="stock" placeholder="Any" min="0" step="1">
        <input type="text" class="form-control" data-field="note" placeholder="e.g. Premium wool" maxlength="120">
        <button type="button" class="btn-ghost-del" onclick="removeColorRow(this)" title="Remove colour"><i class="mdi mdi-trash-can-outline" style="font-size:1.05rem;"></i></button>
    </div>
</template>
<script>
const _initialColors = @json($existingColors);

function addColorRow(data) {
    const tpl = document.getElementById('colorRowTemplate');
    const row = tpl.content.firstElementChild.cloneNode(true);
    data = data || {};
    row.querySelector('[data-field="hex"]').value = data.hex || '#333333';
    row.querySelector('[data-field="name"]').value = data.name || '';
    row.querySelector('[data-field="price"]').value = data.price != null ? data.price : '';
    row.querySelector('[data-field="stock"]').value = data.stock != null ? data.stock : '';
    row.querySelector('[data-field="note"]').value = data.note || '';
    setColorImage(row, data.image || '');
    document.getElementById('colorRows').appendChild(row);
    syncColorHint();
}
function removeColorRow(btn) {
    btn.closest('[data-color-row]').remove();
    syncColorHint();
}
function setColorImage(row, url) {
    row.querySelector('[data-field="image"]').value = url || '';
    const img = row.querySelector('[data-field="preview"]');
    if (url) { img.src = url; img.style.display = ''; } else { img.removeAttribute('src'); img.style.display = 'none'; }
}
function pickColorImage(el) {
    const row = el.closest('[data-color-row]');
    openMediaPicker(null, false, function (urls) { if (urls && urls[0]) setColorImage(row, urls[0]); });
}
function clearColorImage(el) { setColorImage(el.closest('[data-color-row]'), ''); }
function syncColorHint() {
    const n = document.querySelectorAll('#colorRows [data-color-row]').length;
    document.getElementById('colorEmptyHint').style.display = n ? 'none' : '';
}
function collectColors() {
    const rows = [];
    document.querySelectorAll('#colorRows [data-color-row]').forEach(function (row) {
        const name = row.querySelector('[data-field="name"]').value.trim();
        if (!name) return;
        const price = row.querySelector('[data-field="price"]').value;
        const stock = row.querySelector('[data-field="stock"]').value;
        rows.push({
            name: name,
            hex: row.querySelector('[data-field="hex"]').value,
            image: row.querySelector('[data-field="image"]').value || null,
            price: price === '' ? null : Number(price),
            stock: stock === '' ? null : Number(stock),
            note: row.querySelector('[data-field="note"]').value.trim() || null,
        });
    });
    const json = JSON.stringify(rows);
    document.getElementById('colorsField').value = json;
    return json;
}
(_initialColors || []).forEach(addColorRow);
syncColorHint();
</script>
