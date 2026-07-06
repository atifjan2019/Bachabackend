@extends('layouts.admin')
@section('title', 'Compose Newsletter')

@push('styles')
<style>
    .compose-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px;
        align-items: start;
    }
    @media (max-width: 1100px) {
        .compose-grid { grid-template-columns: 1fr; }
    }

    /* Recipient mode cards */
    .rmode-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    @media (max-width: 560px) { .rmode-grid { grid-template-columns: 1fr; } }
    .rmode {
        position: relative;
        border: 1.5px solid var(--bd-strong);
        border-radius: 14px;
        padding: 12px 12px 12px 38px;
        cursor: pointer;
        background: rgba(255,255,255,.6);
        transition: var(--tr);
    }
    .rmode:hover { border-color: rgba(217,45,32,.4); }
    .rmode input { position: absolute; left: 13px; top: 14px; }
    .rmode.active {
        border-color: var(--red);
        background: var(--red-bg);
        box-shadow: 0 0 0 3px rgba(217,45,32,.08);
    }
    .rmode-title { font-weight: 800; font-size: .78rem; color: var(--t1); }
    .rmode-desc { font-size: .66rem; color: var(--t3); margin-top: 2px; }

    /* Subscriber picker */
    .picker {
        border: 1px solid var(--bd);
        border-radius: 14px;
        overflow: hidden;
        margin-top: 12px;
    }
    .picker-head {
        display: flex; gap: 8px; align-items: center;
        padding: 10px 12px;
        background: rgba(247,249,252,.9);
        border-bottom: 1px solid var(--bd);
        flex-wrap: wrap;
    }
    .picker-search { flex: 1; min-width: 160px; }
    .picker-list {
        max-height: 320px;
        overflow-y: auto;
        padding: 6px;
    }
    .picker-row {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 10px;
        border-radius: 10px;
        cursor: pointer;
        font-size: .78rem;
        color: var(--t2);
        transition: background .12s;
    }
    .picker-row:hover { background: rgba(247,249,252,.9); }
    .picker-row input { flex-shrink: 0; }
    .picker-empty { padding: 22px; text-align: center; color: var(--t3); font-size: .78rem; }

    .recipient-count {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 12px; border-radius: 999px;
        background: var(--red-bg); color: var(--red-dk);
        font-weight: 800; font-size: .74rem;
    }

    .btn:disabled,
    .btn[disabled] {
        opacity: .5;
        cursor: not-allowed;
        box-shadow: none;
        filter: grayscale(.4);
        transform: none !important;
    }

    /* Preview */
    .preview-wrap { position: sticky; top: calc(var(--tb-h) + 16px); }
    .mail-chrome {
        background: var(--surf2);
        border-bottom: 1px solid var(--bd);
        padding: 12px 16px;
    }
    .mail-line { display: flex; gap: 8px; font-size: .74rem; margin-bottom: 4px; }
    .mail-line:last-child { margin-bottom: 0; }
    .mail-line .k { color: var(--t4); font-weight: 800; min-width: 54px; text-transform: uppercase; letter-spacing: .06em; font-size: .62rem; padding-top: 2px; }
    .mail-line .v { color: var(--t1); font-weight: 600; word-break: break-word; }
    #previewFrame {
        width: 100%;
        height: 620px;
        border: none;
        display: block;
        background: #f9f9f9;
    }
</style>
@endpush

@section('content')

<div class="ph">
    <div>
        <h4>Compose Newsletter</h4>
        <div class="ph-sub">Write your email, choose who receives it, and watch the live preview update as you type.</div>
    </div>
    <div class="ph-action">
        <a href="{{ route('admin.newsletter.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>

<form action="{{ route('admin.newsletter.send') }}" method="POST" id="composeForm">
    @csrf

    <div class="compose-grid">

        {{-- ─── LEFT: EDITOR + RECIPIENTS ─────────────────────── --}}
        <div>
            <div class="bcard">
                <div class="bcard-head"><span class="bcard-title">Message</span></div>
                <div class="bcard-body">
                    <div class="form-group mb-4">
                        <label class="form-label">Email Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="subjectInput"
                               class="form-control @error('subject') is-invalid @enderror"
                               value="{{ old('subject') }}" required
                               placeholder="e.g. New Arrivals at Bacha Stylo!">
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label">Email Body (HTML Supported) <span class="text-danger">*</span></label>
                        <textarea name="body" id="bodyInput" rows="14"
                                  class="form-control @error('body') is-invalid @enderror" required
                                  placeholder="<p>Write your newsletter content here...</p>">{{ old('body') }}</textarea>
                        <small class="form-hint">Plain text or HTML. The content is wrapped in the standard Bacha Stylo email template shown on the right.</small>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="bcard">
                <div class="bcard-head">
                    <span class="bcard-title">Recipients</span>
                    <span class="recipient-count"><i class="mdi mdi-account-multiple"></i> <span id="recipientCount">0</span> recipient(s)</span>
                </div>
                <div class="bcard-body">
                    <div class="rmode-grid">
                        <label class="rmode active" data-mode="all">
                            <input type="radio" name="recipient_mode" value="all" checked>
                            <div class="rmode-title">All subscribers</div>
                            <div class="rmode-desc">Send to everyone ({{ $subscribers->count() }})</div>
                        </label>
                        <label class="rmode" data-mode="include">
                            <input type="radio" name="recipient_mode" value="include">
                            <div class="rmode-title">Include only</div>
                            <div class="rmode-desc">Send to selected people only</div>
                        </label>
                        <label class="rmode" data-mode="exclude">
                            <input type="radio" name="recipient_mode" value="exclude">
                            <div class="rmode-title">Exclude selected</div>
                            <div class="rmode-desc">Send to everyone except selected</div>
                        </label>
                    </div>

                    <div class="picker" id="picker" style="display:none;">
                        <div class="picker-head">
                            <input type="text" class="form-control form-control-sm picker-search" id="pickerSearch" placeholder="Search email...">
                            <button type="button" class="btn btn-light btn-sm" id="selectAllBtn">Select all</button>
                            <button type="button" class="btn btn-light btn-sm" id="clearAllBtn">Clear</button>
                        </div>
                        <div class="picker-list" id="pickerList">
                            @forelse($subscribers as $sub)
                                <label class="picker-row" data-email="{{ strtolower($sub->email) }}">
                                    <input type="checkbox" class="form-check-input recipient-check" name="recipients[]" value="{{ $sub->id }}"
                                        {{ in_array($sub->id, (array) old('recipients', [])) ? 'checked' : '' }}>
                                    <span>{{ $sub->email }}</span>
                                </label>
                            @empty
                                <div class="picker-empty">No subscribers yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg" id="sendBtn">
                    <i class="mdi mdi-send"></i> Send Newsletter to <span id="sendCount">{{ $subscribers->count() }}</span> Subscriber(s)
                </button>
            </div>
        </div>

        {{-- ─── RIGHT: LIVE PREVIEW ───────────────────────────── --}}
        <div class="preview-wrap">
            <div class="bcard" style="margin-bottom:0;">
                <div class="bcard-head">
                    <span class="bcard-title"><i class="mdi mdi-eye-outline"></i> Live Preview</span>
                </div>
                <div class="mail-chrome">
                    <div class="mail-line"><span class="k">Subject</span><span class="v" id="pvSubject">(no subject)</span></div>
                    <div class="mail-line"><span class="k">From</span><span class="v">{{ $brand['name'] }}</span></div>
                </div>
                <iframe id="previewFrame" title="Email preview"></iframe>
            </div>
        </div>

    </div>
</form>

@endsection

@push('scripts')
<script>
(function () {
    const subjectInput   = document.getElementById('subjectInput');
    const bodyInput      = document.getElementById('bodyInput');
    const pvSubject      = document.getElementById('pvSubject');
    const frame          = document.getElementById('previewFrame');
    const picker         = document.getElementById('picker');
    const pickerSearch   = document.getElementById('pickerSearch');
    const pickerList     = document.getElementById('pickerList');
    const recipientCount = document.getElementById('recipientCount');
    const sendCount      = document.getElementById('sendCount');
    const totalSubs      = {{ $subscribers->count() }};
    const currentYear    = @json(date('Y'));
    const brand          = @json($brand);

    /* ---------- Live email preview ---------- */
    function escapeHtml(s) {
        return (s || '').replace(/[&<>"]/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' }[c]));
    }

    function buildEmail(subject, body) {
        // Mirrors resources/views/emails/layout.blade.php + newsletter.blade.php
        const name    = escapeHtml(brand.name || 'Bacha Stylo');
        const address = brand.address ? `<p style="margin:0 0 3px; font-size:12px; line-height:1.7; color:#9a9a9a;">${escapeHtml(brand.address)}</p>` : '';
        const contact = [
            brand.phone ? 'Phone: ' + escapeHtml(brand.phone) : '',
            brand.email ? escapeHtml(brand.email) : ''
        ].filter(Boolean).join(' &middot; ');

        const content = body || '<p style="color:#bbb;">Your email content will appear here as you type...</p>';

        return `<!DOCTYPE html><html><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"><title>${escapeHtml(subject)}</title></head>
<body style="margin:0; padding:0; background-color:#f3f3f4; font-family:Arial, Helvetica, sans-serif; color:#141414;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f3f4; border-collapse:collapse;">
  <tr><td align="center" style="padding:24px 10px;">
    <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:100%; background-color:#ffffff; border-collapse:collapse;">
      <tr><td style="background-color:#141414; border-top:3px solid #e81d25; padding:26px 32px;">
        <span style="font-family:Georgia,'Times New Roman',serif; font-size:23px; font-weight:bold; color:#ffffff; letter-spacing:0.5px;">${name}</span><span style="display:inline-block; width:7px; height:7px; background-color:#e81d25; border-radius:50%; margin-left:5px; vertical-align:middle;">&nbsp;</span>
        <div style="margin-top:4px; font-size:10px; letter-spacing:2px; text-transform:uppercase; color:#9a9a9a;">Premium Pakistani Fashion &amp; Lifestyle</div>
      </td></tr>
      <tr><td style="padding:34px 32px;">
        <div style="font-size:15px; line-height:1.7; color:#3d3d3d;">${content}</div>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:30px 0 0; border-top:1px solid #e6e6e8; border-collapse:collapse;">
          <tr><td style="padding:18px 0 0; font-size:12px; line-height:1.7; color:#9a9a9a;">
            You're receiving this email because you subscribed to the ${name} newsletter.
            <a href="#" style="color:#e81d25; text-decoration:none;">Unsubscribe</a>
          </td></tr>
        </table>
      </td></tr>
      <tr><td style="background-color:#141414; padding:26px 32px;">
        <p style="margin:0 0 6px; font-size:14px; font-weight:bold; color:#ffffff;">${name}</p>
        ${address}
        <p style="margin:0; font-size:12px; line-height:1.7; color:#9a9a9a;">${contact}</p>
        <p style="margin:14px 0 0; font-size:11px; color:#6b6b6b;">&copy; ${currentYear} ${name}. All rights reserved.</p>
      </td></tr>
    </table>
  </td></tr>
</table>
</body></html>`;
    }

    function renderPreview() {
        const subject = subjectInput.value.trim();
        pvSubject.textContent = subject || '(no subject)';
        frame.srcdoc = buildEmail(subject, bodyInput.value);
    }

    subjectInput.addEventListener('input', renderPreview);
    bodyInput.addEventListener('input', renderPreview);

    /* ---------- Recipient mode ---------- */
    const modeCards = document.querySelectorAll('.rmode');
    const checks    = () => Array.from(document.querySelectorAll('.recipient-check'));

    const sendBtn = document.getElementById('sendBtn');

    function updateCount() {
        const mode = document.querySelector('input[name="recipient_mode"]:checked').value;
        const selected = checks().filter(c => c.checked).length;
        let n = totalSubs;
        if (mode === 'include') n = selected;
        else if (mode === 'exclude') n = totalSubs - selected;
        n = Math.max(0, n);
        recipientCount.textContent = n;
        sendCount.textContent = n;

        // Block sending when nobody would receive it.
        sendBtn.disabled = (n === 0);
        sendBtn.title = (n === 0) ? 'Select at least one recipient' : '';
    }

    modeCards.forEach(card => {
        card.addEventListener('click', () => {
            modeCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            card.querySelector('input').checked = true;
            const mode = card.dataset.mode;
            picker.style.display = (mode === 'all') ? 'none' : 'block';
            updateCount();
        });
    });

    pickerList.addEventListener('change', updateCount);

    /* ---------- Picker search + bulk actions ---------- */
    pickerSearch && pickerSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        pickerList.querySelectorAll('.picker-row').forEach(row => {
            row.style.display = row.dataset.email.includes(q) ? 'flex' : 'none';
        });
    });
    document.getElementById('selectAllBtn')?.addEventListener('click', () => {
        pickerList.querySelectorAll('.picker-row').forEach(row => {
            if (row.style.display !== 'none') row.querySelector('input').checked = true;
        });
        updateCount();
    });
    document.getElementById('clearAllBtn')?.addEventListener('click', () => {
        checks().forEach(c => c.checked = false);
        updateCount();
    });

    /* ---------- Guard against accidental empty send ---------- */
    document.getElementById('composeForm').addEventListener('submit', function (e) {
        const n = parseInt(recipientCount.textContent, 10) || 0;
        if (n === 0) {
            e.preventDefault();
            alert('This newsletter would reach 0 subscribers. Adjust your recipient selection.');
            return;
        }
        if (!confirm('Send this email to ' + n + ' subscriber(s)?')) e.preventDefault();
    });

    // Restore state (old input on validation error)
    (function init() {
        const checkedMode = document.querySelector('input[name="recipient_mode"]:checked').value;
        modeCards.forEach(c => c.classList.toggle('active', c.dataset.mode === checkedMode));
        picker.style.display = (checkedMode === 'all') ? 'none' : 'block';
        renderPreview();
        updateCount();
    })();
})();
</script>
@endpush
