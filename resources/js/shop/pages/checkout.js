/**
 * Alpine components for checkout, the success page and account reorder.
 */
document.addEventListener('alpine:init', () => {
    /**
     * checkoutForm({ guest, methods, method, name, email, phone })
     * The form posts normally; Alpine only keeps the payment option UI in sync,
     * serialises the cart into the hidden cart_json field and captures guest
     * abandoned carts once an email has been typed.
     */
    Alpine.data('checkoutForm', (cfg = {}) => ({
        guest: !!cfg.guest,
        methods: cfg.methods || ['cod'],
        method: cfg.method || 'cod',
        name: cfg.name || '',
        email: cfg.email || '',
        phone: cfg.phone || '',
        receiptName: '',
        receiptError: '',
        submitting: false,
        copied: {},

        init() {
            if (!this.methods.includes(this.method)) this.method = this.methods[0];

            if (this.guest) {
                const capture = window.bsf.debounce(() => {
                    const cart = Alpine.store('cart');
                    window.bsf.captureAbandonedCart({
                        name: this.name,
                        email: this.email,
                        phone: this.phone,
                        lines: cart.lines,
                        total: cart.subtotal,
                    });
                }, 3500);
                this.$watch('email', capture);
                this.$watch('name', capture);
                this.$watch('phone', capture);
                window.addEventListener('cart:changed', capture);
            }
        },

        get cartJson() {
            return JSON.stringify(Alpine.store('cart').toOrderItems());
        },
        get requiresReceipt() {
            return this.method !== 'cod';
        },

        onReceipt(event) {
            const file = event.target.files && event.target.files[0];
            this.receiptName = file ? file.name : '';
            if (file) this.receiptError = '';
        },

        async copy(value, key) {
            const ok = await window.bsf.copyText(value);
            if (!ok) return;
            this.copied[key] = true;
            setTimeout(() => { this.copied[key] = false; }, 1500);
        },

        submit(event) {
            const cart = Alpine.store('cart');
            if (cart.lines.length === 0) {
                event.preventDefault();
                Alpine.store('ui').toast({ title: 'Your cart is empty', description: 'Add something before checking out', tone: 'error' });
                return;
            }
            const receipt = this.$refs.receipt;
            if (this.requiresReceipt && !(receipt && receipt.files && receipt.files.length)) {
                event.preventDefault();
                this.receiptError = 'Please upload your payment receipt to continue.';
                Alpine.store('ui').toast({ title: 'Receipt required', description: 'Upload your payment screenshot for this payment method.', tone: 'error' });
                return;
            }
            this.submitting = true;
        },
    }));

    /** Clears the browser cart once per order after a successful checkout. */
    Alpine.data('orderSuccess', (reference) => ({
        init() {
            const key = `bsf_order_cleared_${reference}`;
            let done = false;
            try { done = sessionStorage.getItem(key) === '1'; } catch { done = false; }
            if (done) return;
            Alpine.store('cart').clear();
            try { sessionStorage.setItem(key, '1'); } catch { /* storage unavailable */ }
        },
    }));

    /** "Reorder items" on the account order page: re-adds every line, then goes to checkout. */
    Alpine.data('reorder', (items = [], checkoutUrl = '/checkout') => ({
        run() {
            const cart = Alpine.store('cart');
            items.forEach((item) => {
                const size = item.size || '';
                const color = item.color || '';
                cart.add({
                    id: `${item.product_id || 0}-0-${size}-${color}`,
                    product_id: item.product_id || 0,
                    variant_id: 0,
                    sku: item.sku || '',
                    variant_sku: item.variant_sku || '',
                    slug: item.slug || '',
                    name: item.name,
                    image: item.image || '',
                    size,
                    color,
                    color_hex: '',
                    unit_price: Number(item.price) || 0,
                    quantity: Number(item.quantity) || 1,
                    max_stock: 999,
                }, { open: false, toast: false });
            });
            window.location.assign(checkoutUrl);
        },
    }));
});
