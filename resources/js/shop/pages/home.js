/**
 * Alpine components for the home and content pages: promo popup, hero launch
 * banner, newsletter and contact forms, and the 404 page's mouse-tracking glow.
 */
document.addEventListener('alpine:init', () => {
    // Admin-controlled promotion shown over the home page on open.
    Alpine.data('promoPopup', () => ({
        open: false,
        init() {
            this.open = true;
            this._onKey = (e) => { if (e.key === 'Escape') this.close(); };
            window.addEventListener('keydown', this._onKey);
            this.$watch('open', (v) => document.documentElement.classList.toggle('overflow-hidden', v));
            document.documentElement.classList.add('overflow-hidden');
        },
        close() {
            this.open = false;
            window.removeEventListener('keydown', this._onKey);
        },
        destroy() {
            window.removeEventListener('keydown', this._onKey);
            document.documentElement.classList.remove('overflow-hidden');
        },
    }));

    // Launch banner over the hero: video/image until skipped, else the default image.
    Alpine.data('heroBanner', ({ video = false, image = false } = {}) => ({
        dismissed: false,
        bannerFailed: false,
        get showVideo() { return video && !this.dismissed; },
        get showImage() { return image && !this.dismissed && !this.bannerFailed; },
        get showDefault() { return !this.showVideo && !this.showImage; },
    }));

    Alpine.data('newsletterForm', () => ({
        email: '',
        status: 'idle',
        message: '',
        async submit() {
            if (this.status === 'loading') return;
            this.status = 'loading';
            this.message = '';
            try {
                await window.bsf.api('/newsletter', { method: 'POST', body: { email: this.email } });
                this.status = 'success';
                this.message = "You're in. Welcome to Bacha Stylo.";
                this.email = '';
            } catch {
                this.status = 'error';
                this.message = 'Something went wrong. Please try again.';
            }
        },
    }));

    Alpine.data('contactForm', () => ({
        status: 'idle',
        error: '',
        async submit(event) {
            this.error = '';
            const fd = new FormData(event.target);
            this.status = 'sending';
            try {
                await window.bsf.api('/contact', {
                    method: 'POST',
                    body: {
                        name: String(fd.get('name') || ''),
                        email: String(fd.get('email') || ''),
                        subject: String(fd.get('subject') || ''),
                        message: String(fd.get('message') || ''),
                    },
                });
                this.status = 'sent';
            } catch {
                this.error = 'Sorry, your message could not be sent. Please try again or reach us on WhatsApp.';
                this.status = 'idle';
            }
        },
    }));

    Alpine.data('notFoundPage', () => ({
        x: 50,
        y: 50,
        move(e) {
            this.x = (e.clientX / window.innerWidth) * 100;
            this.y = (e.clientY / window.innerHeight) * 100;
        },
        get background() {
            return `radial-gradient(600px circle at ${this.x}% ${this.y}%, rgba(232,29,37,0.06), transparent 50%), radial-gradient(800px circle at 20% 80%, rgba(232,29,37,0.03), transparent 50%)`;
        },
    }));
});
