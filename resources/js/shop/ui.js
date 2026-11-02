/**
 * Site-wide UI state: toasts and the exclusive overlays (mobile menu, search,
 * cart drawer). Body scroll locks while any overlay is open.
 */
export function registerUiStore(Alpine) {
    Alpine.store('ui', {
        toasts: [],
        menuOpen: false,
        searchOpen: false,
        _seq: 0,

        init() {
            Alpine.effect(() => {
                const cart = Alpine.store('cart');
                const locked = this.menuOpen || this.searchOpen || (cart && cart.isOpen);
                document.documentElement.classList.toggle('overflow-hidden', !!locked);
            });
            // Escape closes whatever is open.
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.closeAll();
            });
        },

        /** toast({ title, description, tone: 'default'|'success'|'error', duration }) */
        toast({ title, description = '', tone = 'default', duration = 3500 }) {
            const id = ++this._seq;
            this.toasts.push({ id, title, description, tone });
            if (duration > 0) setTimeout(() => this.dismiss(id), duration);
            return id;
        },
        dismiss(id) {
            this.toasts = this.toasts.filter((t) => t.id !== id);
        },

        openMenu() {
            this.closeAll();
            this.menuOpen = true;
        },
        closeMenu() {
            this.menuOpen = false;
        },
        openSearch() {
            this.closeAll();
            this.searchOpen = true;
            requestAnimationFrame(() => {
                const el = document.getElementById('site-search-input');
                if (el) { el.value = ''; el.focus(); }
            });
        },
        closeSearch() {
            this.searchOpen = false;
        },
        closeAll() {
            this.menuOpen = false;
            this.searchOpen = false;
            const cart = Alpine.store('cart');
            if (cart) cart.isOpen = false;
        },
    });
}
