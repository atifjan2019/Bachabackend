{{-- Media Picker Modal - Include once per page, use openMediaPicker(targetFieldName, multiple) --}}
<style>
.mp-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.55);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}
.mp-overlay.active { display: flex; }
.mp-panel {
    background: #fff;
    border-radius: 12px;
    width: 90vw;
    max-width: 900px;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 24px 80px rgba(0,0,0,0.3);
}
.mp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #eee;
}
.mp-header h5 { margin: 0; font-size: 16px; }
.mp-header .mp-close {
    width: 32px; height: 32px;
    border: none; background: #f1f1f1;
    border-radius: 50%; cursor: pointer;
    font-size: 18px; line-height: 32px;
    text-align: center;
}
.mp-header .mp-close:hover { background: #ddd; }
.mp-filters {
    display: flex;
    gap: 6px;
    padding: 12px 20px;
    flex-wrap: wrap;
    border-bottom: 1px solid #f5f5f5;
}
.mp-pill {
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    border: 1px solid #ddd;
    background: #fff;
    cursor: pointer;
    transition: all 0.15s;
}
.mp-pill:hover, .mp-pill.active {
    background: #2d3436;
    color: #fff;
    border-color: #2d3436;
}
.mp-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
}
.mp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 10px;
}
.mp-item {
    position: relative;
    border: 2px solid transparent;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.15s;
}
.mp-item:hover { border-color: #aaa; }
.mp-item.selected { border-color: #e74c3c; }
.mp-item.selected::after {
    content: '✓';
    position: absolute;
    top: 6px; right: 6px;
    width: 22px; height: 22px;
    background: #e74c3c;
    color: #fff;
    border-radius: 50%;
    font-size: 12px;
    line-height: 22px;
    text-align: center;
}
.mp-item img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    display: block;
}
.mp-item .mp-name {
    font-size: 10px;
    padding: 4px 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #666;
}
.mp-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    border-top: 1px solid #eee;
}
.mp-footer .mp-count { font-size: 13px; color: #666; }
.mp-footer .mp-btn {
    padding: 8px 28px;
    background: #e74c3c;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.15s;
}
.mp-footer .mp-btn:hover { background: #c0392b; }
.mp-footer .mp-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.mp-loading {
    text-align: center;
    padding: 40px;
    color: #999;
}
</style>

<div class="mp-overlay" id="mediaPickerOverlay">
    <div class="mp-panel">
        <div class="mp-header">
            <h5>Select from Media Library</h5>
            <button class="mp-close" onclick="closeMediaPicker()">&times;</button>
        </div>
        <div class="mp-filters" id="mpFilters"></div>
        <div class="mp-body">
            <div class="mp-loading" id="mpLoading">Loading images...</div>
            <div class="mp-grid" id="mpGrid"></div>
        </div>
        <div class="mp-footer">
            <span class="mp-count" id="mpCount">0 selected</span>
            <button class="mp-btn" id="mpContinueBtn" disabled onclick="confirmMediaPick()">Continue</button>
        </div>
    </div>
</div>

<script>
let _mpFiles = [];
let _mpSelected = [];
let _mpTarget = null;
let _mpMultiple = false;
let _mpCallback = null;

function openMediaPicker(targetFieldName, multiple, callback) {
    _mpTarget = targetFieldName;
    _mpMultiple = !!multiple;
    _mpSelected = [];
    _mpCallback = callback || null;

    document.getElementById('mediaPickerOverlay').classList.add('active');
    document.getElementById('mpLoading').style.display = 'block';
    document.getElementById('mpGrid').innerHTML = '';
    document.getElementById('mpFilters').innerHTML = '';
    updateMpCount();

    fetch('{{ route("admin.media.api.list") }}')
        .then(r => r.json())
        .then(files => {
            _mpFiles = files;
            renderMpGrid(files);
            renderMpFilters(files);
            document.getElementById('mpLoading').style.display = 'none';
        })
        .catch(() => {
            document.getElementById('mpLoading').textContent = 'Failed to load. Try again.';
        });
}

function renderMpFilters(files) {
    const folders = [...new Set(files.map(f => f.folder).filter(Boolean))].sort();
    let html = '<span class="mp-pill active" onclick="filterMp(\'all\', this)">All</span>';
    folders.forEach(f => {
        html += '<span class="mp-pill" onclick="filterMp(\'' + f + '\', this)">' + f + '</span>';
    });
    document.getElementById('mpFilters').innerHTML = html;
}

function filterMp(folder, el) {
    document.querySelectorAll('.mp-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.mp-item').forEach(item => {
        item.style.display = (folder === 'all' || item.dataset.folder === folder) ? '' : 'none';
    });
}

function renderMpGrid(files) {
    let html = '';
    files.forEach((f, i) => {
        html += '<div class="mp-item" data-url="' + f.url + '" data-folder="' + f.folder + '" onclick="toggleMpSelect(this)">';
        html += '<img src="' + f.url + '" alt="' + f.name + '" loading="lazy">';
        html += '<div class="mp-name">' + f.name + '</div>';
        html += '</div>';
    });
    document.getElementById('mpGrid').innerHTML = html;
}

function toggleMpSelect(el) {
    const url = el.dataset.url;

    if (!_mpMultiple) {
        document.querySelectorAll('.mp-item.selected').forEach(e => e.classList.remove('selected'));
        _mpSelected = [url];
        el.classList.add('selected');
    } else {
        if (el.classList.contains('selected')) {
            el.classList.remove('selected');
            _mpSelected = _mpSelected.filter(u => u !== url);
        } else {
            el.classList.add('selected');
            _mpSelected.push(url);
        }
    }
    updateMpCount();
}

function updateMpCount() {
    const count = _mpSelected.length;
    document.getElementById('mpCount').textContent = count + ' selected';
    document.getElementById('mpContinueBtn').disabled = count === 0;
}

function closeMediaPicker() {
    document.getElementById('mediaPickerOverlay').classList.remove('active');
    _mpSelected = [];
}

function confirmMediaPick() {
    if (_mpCallback) {
        _mpCallback(_mpSelected);
    } else if (_mpTarget) {
        const field = document.querySelector('[name="' + _mpTarget + '"]');
        if (field) {
            if (field.tagName === 'TEXTAREA') {
                field.value = JSON.stringify(_mpSelected);
            } else {
                field.value = _mpSelected[0] || '';
            }
        }
    }
    closeMediaPicker();
}
</script>
