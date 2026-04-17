{{-- Upload Progress Overlay - Include in any form with file uploads --}}
<style>
.upload-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.upload-overlay.active { display: flex; }
.upload-card {
    background: #fff;
    border-radius: 12px;
    padding: 32px 40px;
    min-width: 380px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.upload-card h5 { margin: 0 0 6px; font-size: 18px; }
.upload-card .upload-status { font-size: 13px; color: #666; margin-bottom: 16px; }
.progress-track {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 10px;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #e74c3c, #f39c12);
    border-radius: 8px;
    width: 0%;
    transition: width 0.2s ease;
}
.upload-pct { font-size: 24px; font-weight: 700; color: #333; }
</style>

<div class="upload-overlay" id="uploadOverlay">
    <div class="upload-card">
        <h5>Uploading...</h5>
        <div class="upload-status" id="uploadStatus">Preparing files...</div>
        <div class="progress-track">
            <div class="progress-fill" id="uploadProgressBar"></div>
        </div>
        <div class="upload-pct" id="uploadPct">0%</div>
    </div>
</div>

<script>
function initUploadProgress(formId, redirectUrl) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const fileInputs = form.querySelectorAll('input[type="file"]');
        let hasFiles = false;
        fileInputs.forEach(inp => { if (inp.files && inp.files.length > 0) hasFiles = true; });
        if (!hasFiles) return;

        e.preventDefault();

        const overlay = document.getElementById('uploadOverlay');
        const progressBar = document.getElementById('uploadProgressBar');
        const pctText = document.getElementById('uploadPct');
        const statusText = document.getElementById('uploadStatus');
        const submitBtn = form.querySelector('button[type="submit"]');
        const origBtnText = submitBtn ? submitBtn.innerHTML : '';

        overlay.classList.add('active');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Uploading...';
        }

        const formData = new FormData(form);

        // Sync Quill editor if present
        if (typeof quill !== 'undefined') {
            const descField = document.getElementById('descriptionField');
            if (descField) {
                formData.set('description', quill.root.innerHTML);
            }
        }

        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = pct + '%';
                pctText.textContent = pct + '%';
                if (pct < 30) statusText.textContent = 'Uploading files...';
                else if (pct < 70) statusText.textContent = 'Transferring to cloud...';
                else if (pct < 100) statusText.textContent = 'Almost done...';
                else statusText.textContent = 'Processing...';
            }
        });

        xhr.addEventListener('load', function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                statusText.textContent = 'Done! Redirecting...';
                pctText.textContent = '100%';
                progressBar.style.width = '100%';
                window.location.href = xhr.responseURL || redirectUrl || window.location.href;
            } else {
                overlay.classList.remove('active');
                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = origBtnText; }
                alert('Upload failed. Please try again.');
            }
        });

        xhr.addEventListener('error', function() {
            overlay.classList.remove('active');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = origBtnText; }
            alert('Upload failed. Check your connection.');
        });

        xhr.open('POST', form.action);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    });
}
</script>
