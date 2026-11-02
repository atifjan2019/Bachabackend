{{--
    SafeImage behaviour for plain <img> tags. Defined inline in <head> so the
    onerror handler exists before the browser starts fetching images. A failed
    image becomes a branded gradient panel with a monogram (data-fallback-label),
    after first retrying data-fallback-src when given. data-fallback="none"
    hides the image instead, for decorative backdrops.
--}}
<script>
(function () {
    function panel(img) {
        var label = (img.getAttribute('data-fallback-label') || img.getAttribute('alt') || '').trim();
        var div = document.createElement('div');
        div.className = (img.getAttribute('data-fallback-class') || img.className || '') + ' img-fallback';
        div.setAttribute('role', 'img');
        div.setAttribute('aria-label', (img.getAttribute('alt') || '').trim() || 'Image unavailable');
        if (!/\babsolute\b/.test(div.className)) div.className += ' relative';
        if (label) {
            var span = document.createElement('span');
            span.textContent = label.charAt(0).toUpperCase();
            div.appendChild(span);
        }
        return div;
    }
    window.bsfImgError = function (img) {
        if (!img || img.__bsfHandled) return;
        var fallbackSrc = img.getAttribute('data-fallback-src');
        if (fallbackSrc && img.getAttribute('src') !== fallbackSrc) {
            img.setAttribute('src', fallbackSrc);
            img.removeAttribute('data-fallback-src');
            return;
        }
        img.__bsfHandled = true;
        if (window.console) console.warn('[media] image failed to load: ' + img.currentSrc);
        window.dispatchEvent(new CustomEvent('media:image-error', { detail: { src: img.currentSrc } }));
        if (img.getAttribute('data-fallback') === 'none') {
            img.style.visibility = 'hidden';
            return;
        }
        if (img.parentNode) img.parentNode.replaceChild(panel(img), img);
    };
    // Images that failed before this script could attach are complete with no width.
    document.addEventListener('DOMContentLoaded', function () {
        var imgs = document.querySelectorAll('img[data-bsf-img]');
        for (var i = 0; i < imgs.length; i++) {
            var img = imgs[i];
            if (!img.getAttribute('src')) { window.bsfImgError(img); continue; }
            if (img.complete && img.naturalWidth === 0) window.bsfImgError(img);
        }
    });
})();
</script>
