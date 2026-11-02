/**
 * Shop listing filters and the product detail page.
 */
document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    /** Page-level state for /products: the mobile filter sheet. */
    Alpine.data('productFilters', () => ({
        mobileOpen: false,
        init() {
            Alpine.effect(() => {
                document.documentElement.classList.toggle('overflow-hidden', this.mobileOpen);
            });
        },
    }));

    /**
     * The filter <form>: every change submits it. Changing a category clears the
     * size selection (the sizes on offer belong to the category being browsed),
     * and price inputs at their defaults are dropped so URLs stay clean.
     */
    Alpine.data('filterForm', () => ({
        autoSubmit(event) {
            const form = this.$root;
            const target = event.target;
            if (!(target instanceof HTMLInputElement)) return;
            if (target.dataset.filter === 'category') {
                form.querySelectorAll('input[data-filter="size"]').forEach((el) => { el.checked = false; });
            }
            this.submit();
        },
        submit() {
            const form = this.$root;
            form.querySelectorAll('input[type="range"]').forEach((el) => {
                const v = Number(el.value);
                const atFloor = el.name === 'min' && v <= Number(el.min);
                const atCeil = el.name === 'max' && v >= Number(el.max);
                el.disabled = atFloor || atCeil;
            });
            if (typeof form.requestSubmit === 'function') form.requestSubmit();
            else form.submit();
        },
    }));

    /** Dual-thumb price range built from two overlaid native range inputs. */
    Alpine.data('priceRange', ({ min, max, floor, ceil, step }) => ({
        lo: min,
        hi: max,
        floor,
        ceil,
        step,
        pct(v) {
            return ((v - this.floor) / (this.ceil - this.floor)) * 100;
        },
        clamp(which) {
            if (which === 'lo' && this.lo > this.hi - this.step) this.lo = Math.max(this.floor, this.hi - this.step);
            if (which === 'hi' && this.hi < this.lo + this.step) this.hi = Math.min(this.ceil, this.lo + this.step);
        },
        commit() {
            // Bubble a change the form listens to (the range's own change was stopped
            // so dragging doesn't submit on every tick, only on release).
            this.$root.closest('form')?.dispatchEvent(new Event('change', { bubbles: true }));
        },
    }));

    /** Product page: gallery + purchase panel share the chosen colour. */
    Alpine.data('productDetail', (cfg) => ({
        product: cfg,
        name: cfg.name,
        size: cfg.size_options[0] ?? null,
        color: cfg.color_options[0]?.color ?? null,
        bandKey: null,
        qty: 1,
        index: 0,
        touchX: null,

        init() {
            if (cfg.use_bands && cfg.bands.length) {
                const band = cfg.bands.find((b) => b.sizes.includes(this.size)) ?? cfg.bands[0];
                this.bandKey = band.key;
            }
            // A new variant resets quantity so it can't exceed the new stock.
            this.$watch('size', () => { this.qty = 1; });
            this.$watch('color', () => { this.qty = 1; this.index = 0; });
            this.$watch('index', () => this.scrollThumb());
        },

        /* ---- variants ---- */
        get variant() {
            return this.product.variants.find((v) => v.size === this.size && v.color === this.color) ?? null;
        },
        get currentBandSizes() {
            const band = cfg.bands.find((b) => b.key === this.bandKey) ?? cfg.bands[0];
            return band ? band.sizes : [];
        },
        pickBand(key) {
            this.bandKey = key;
            const band = cfg.bands.find((b) => b.key === key);
            if (band && !band.sizes.includes(this.size)) this.size = band.sizes[0];
        },
        get onSale() {
            return (this.variant?.price ?? null) === null && this.product.sale_price !== null && this.product.sale_price < this.product.price;
        },
        get displayPrice() {
            return this.variant?.price ?? this.product.sale_price ?? this.product.price;
        },
        get stock() {
            return this.variant?.stock ?? 0;
        },
        get status() {
            return window.bsf.stockStatus(this.stock, this.product.low_stock_threshold);
        },

        /* ---- gallery ---- */
        get images() {
            const chosen = this.product.show_colors ? this.product.colors.find((c) => c.name === this.color) : null;
            if (!chosen || !chosen.image) return this.product.images;
            const rest = this.product.images.filter((img) => img.url !== chosen.image);
            return [{ id: -1, url: chosen.image, alt: `${this.product.name} in ${chosen.name}` }, ...rest];
        },
        get active() {
            return this.images[this.index] ?? this.images[0] ?? { url: '', alt: this.product.name };
        },
        go(i) {
            const n = this.images.length;
            if (!n) return;
            this.index = ((i % n) + n) % n;
        },
        prev() { this.go(this.index - 1); },
        next() { this.go(this.index + 1); },
        touchStart(e) { this.touchX = e.touches[0].clientX; },
        touchEnd(e) {
            if (this.touchX === null) return;
            const dx = e.changedTouches[0].clientX - this.touchX;
            if (Math.abs(dx) > 40) (dx < 0 ? this.next() : this.prev());
            this.touchX = null;
        },
        scrollThumb() {
            const strip = this.$refs.thumbs;
            const el = strip && strip.children[this.index];
            if (el && el.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        },

        /* ---- actions ---- */
        get whatsappHref() {
            const details = [
                this.size ? `Size: ${this.size}` : null,
                window.bsf.meaningfulColor(this.color) ? `Colour: ${this.color}` : null,
            ].filter(Boolean).join(' | ');
            const origin = window.BSF?.origin || window.location.origin;
            const message = [
                "Hi! I'd like to order:",
                this.product.name,
                details || null,
                `Qty: ${this.qty} × ${window.bsf.pkr(this.displayPrice)} = ${window.bsf.pkr(this.displayPrice * this.qty)}`,
                `Link: ${origin}/products/${this.product.slug}`,
            ].filter(Boolean).join('\n');
            return `https://wa.me/${cfg.whatsapp_digits}?text=${encodeURIComponent(message)}`;
        },
        add() {
            const v = this.variant;
            if (!v || this.stock <= 0) return;
            Alpine.store('cart').add({
                id: `${this.product.id}-${v.id}-${v.size}-${v.color}`,
                product_id: this.product.id,
                variant_id: v.id,
                sku: this.product.sku,
                variant_sku: v.sku,
                slug: this.product.slug,
                name: this.product.name,
                image: v.image ?? this.product.images[0]?.url ?? '',
                size: v.size,
                color: v.color,
                color_hex: v.color_hex,
                unit_price: this.displayPrice,
                quantity: this.qty,
                max_stock: v.stock,
            }, { toast: false });
            Alpine.store('ui').toast({
                title: 'Added to cart',
                description: `${this.product.name} (${[v.size, this.product.show_colors ? v.color : null].filter(Boolean).join(', ')})`,
                tone: 'success',
            });
        },
    }));

    /** Reviews list + write-review form (POST /api/v1/products/{slug}/reviews). */
    Alpine.data('productReviews', ({ slug, count, average }) => ({
        open: false,
        hover: 0,
        status: 'idle',
        error: '',
        count,
        average,
        added: [],
        form: { author_name: '', rating: 5, comment: '' },
        async submit() {
            if (this.status === 'loading' || !this.form.author_name.trim()) return;
            this.status = 'loading';
            this.error = '';
            try {
                const res = await window.bsf.api(`/products/${encodeURIComponent(slug)}/reviews`, {
                    method: 'POST',
                    body: { author_name: this.form.author_name.trim(), rating: this.form.rating, comment: this.form.comment.trim() },
                });
                const created = res.data;
                const total = this.average * this.count + created.rating;
                this.count += 1;
                this.average = Math.round((total / this.count) * 10) / 10;
                this.added.unshift(created);
                this.form = { author_name: '', rating: 5, comment: '' };
                this.status = 'success';
                this.open = false;
            } catch (e) {
                this.error = e.errors ? Object.values(e.errors).flat()[0] : '';
                this.status = 'error';
            }
        },
    }));
});
