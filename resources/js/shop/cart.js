/**
 * The cart lives in the browser under localStorage "bsf_cart", in the exact
 * shape the Next.js storefront (zustand/persist) wrote:
 *   { "state": { "lines": [...] }, "version": 0 }
 * so visitors keep the cart they already had. Each line:
 *   { id, product_id, variant_id, sku, variant_sku, slug, name, image,
 *     size, color, color_hex, unit_price, quantity, max_stock }
 */
const KEY = 'bsf_cart';

function load() {
    try {
        const raw = localStorage.getItem(KEY);
        if (!raw) return [];
        const parsed = JSON.parse(raw);
        const lines = Array.isArray(parsed)
            ? parsed
            : parsed && parsed.state && Array.isArray(parsed.state.lines)
                ? parsed.state.lines
                : parsed && Array.isArray(parsed.lines)
                    ? parsed.lines
                    : [];
        return lines
            .filter((l) => l && typeof l === 'object' && l.id)
            .map((l) => ({
                ...l,
                quantity: Math.max(1, Number(l.quantity) || 1),
                unit_price: Number(l.unit_price) || 0,
                max_stock: Number.isFinite(Number(l.max_stock)) ? Number(l.max_stock) : 999,
            }));
    } catch {
        return [];
    }
}

function save(lines) {
    try {
        localStorage.setItem(KEY, JSON.stringify({ state: { lines }, version: 0 }));
    } catch {
        // Storage can be unavailable (private mode); the cart still works for the page.
    }
}

export function registerCartStore(Alpine) {
    Alpine.store('cart', {
        lines: [],
        isOpen: false,
        hydrated: false,

        init() {
            this.lines = load();
            this.hydrated = true;
            // Another tab changed the cart.
            window.addEventListener('storage', (e) => {
                if (e.key === KEY) this.lines = load();
            });
        },

        /* ---- derived ---- */
        get count() {
            return this.lines.reduce((s, l) => s + l.quantity, 0);
        },
        get isEmpty() {
            return this.lines.length === 0;
        },
        get subtotal() {
            return this.lines.reduce((s, l) => s + l.unit_price * l.quantity, 0);
        },
        get shipping() {
            return window.bsf.shippingFor(this.subtotal);
        },
        get total() {
            return this.subtotal + this.shipping;
        },
        get remainingForFreeShipping() {
            return window.bsf.remainingForFreeShipping(this.subtotal);
        },
        lineTotal(line) {
            return line.unit_price * line.quantity;
        },
        find(id) {
            return this.lines.find((l) => l.id === id);
        },
        whatsappHref() {
            return window.bsf.whatsappOrderHref({
                lines: this.lines,
                subtotal: this.subtotal,
                shipping: this.shipping,
                total: this.total,
            });
        },
        /** Lines in the shape /api/v1/orders and the web checkout expect. */
        toOrderItems() {
            return this.lines.map((l) => ({
                product_id: l.product_id || undefined,
                name: l.name,
                price: l.unit_price,
                quantity: l.quantity,
                size: l.size,
                color: window.bsf.meaningfulColor(l.color) || undefined,
                slug: l.slug || undefined,
                image: l.image || undefined,
                sku: l.sku || undefined,
                variant_sku: l.variant_sku || undefined,
            }));
        },

        /* ---- drawer ---- */
        open() {
            this.isOpen = true;
        },
        close() {
            this.isOpen = false;
        },
        toggle() {
            this.isOpen = !this.isOpen;
        },

        /* ---- mutations ---- */
        /**
         * Add a full line (see shape above). Existing lines merge by id, capped
         * at max_stock. Shows a toast and opens the drawer unless told not to.
         */
        add(line, { open = true, toast = true } = {}) {
            const qty = Math.max(1, Number(line.quantity) || 1);
            const max = Number.isFinite(Number(line.max_stock)) ? Number(line.max_stock) : 999;
            const existing = this.find(line.id);
            if (existing) {
                existing.quantity = Math.min(existing.quantity + qty, max);
            } else {
                this.lines.push({ ...line, quantity: Math.min(qty, max), max_stock: max });
            }
            this.persist();
            if (toast) {
                Alpine.store('ui').toast({
                    title: 'Added to cart',
                    description: `${line.name}${line.size && line.size !== 'One Size' ? ` · Size ${line.size}` : ''}`,
                    tone: 'success',
                });
            }
            if (open) this.open();
        },
        remove(id) {
            this.lines = this.lines.filter((l) => l.id !== id);
            this.persist();
        },
        setQty(id, qty) {
            const line = this.find(id);
            if (!line) return;
            const n = Number(qty);
            if (!Number.isFinite(n)) return;
            line.quantity = Math.max(1, Math.min(Math.round(n), line.max_stock || 999));
            this.persist();
        },
        increment(id) {
            const line = this.find(id);
            if (line) this.setQty(id, line.quantity + 1);
        },
        decrement(id) {
            const line = this.find(id);
            if (line) this.setQty(id, line.quantity - 1);
        },
        clear() {
            this.lines = [];
            this.persist();
        },
        persist() {
            save(this.lines);
            window.dispatchEvent(new CustomEvent('cart:changed', { detail: { lines: this.lines } }));
        },
    });
}
