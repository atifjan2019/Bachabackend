{{--
    GA4 + Meta Pixel, only when the admin-entered IDs validate.

    Both libraries (~400 KB of script) load on the first interaction — touch,
    scroll, key, pointer — or 5 s after the page has loaded, whichever is
    first, so they never compete with the page itself. gtag's queue is set up
    immediately, so the page view and anything pushed before the library
    arrives are still sent.
--}}
@php($gaId = $settings->gaId())
@php($pixelId = $settings->pixelId())
@if($gaId || $pixelId)
    <script>
    (function () {
        @if($gaId)
        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { dataLayer.push(arguments); };
        gtag('js', new Date());
        gtag('config', @json($gaId));
        @endif
        var loaded = false;
        function load() {
            if (loaded) return;
            loaded = true;
            @if($gaId)
            var s = document.createElement('script');
            s.async = true;
            s.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(@json($gaId));
            document.head.appendChild(s);
            @endif
            @if($pixelId)
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($pixelId));
            fbq('track', 'PageView');
            @endif
        }
        ['pointerdown', 'touchstart', 'keydown', 'scroll', 'mousemove'].forEach(function (ev) {
            window.addEventListener(ev, load, { once: true, passive: true });
        });
        window.addEventListener('load', function () { setTimeout(load, 5000); });
    })();
    </script>
    @if($pixelId)
    <noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"></noscript>
    @endif
@endif
