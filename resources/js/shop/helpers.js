/**
 * Small storefront runtime shared by every Alpine component: money formatting,
 * the delivery rule, WhatsApp order message and a JSON fetch helper for the
 * same-origin /api/v1 endpoints. Configuration arrives from the layout as
 * window.BSF (shipping rule, WhatsApp digits, site origin, signed-in customer).
 */
const config = () => window.BSF || {};

/** "Rs. 12,500" — same output as the PHP pkr() helper. */
export function pkr(amount) {
    const n = Number(amount);
    if (!Number.isFinite(n)) return 'Rs. 0';
    return `Rs. ${Math.round(n).toLocaleString('en-PK')}`;
}

export function shippingRule() {
    const s = config().shipping || {};
    return {
        fee: Number.isFinite(Number(s.fee)) ? Number(s.fee) : 250,
        threshold: Number.isFinite(Number(s.threshold)) ? Number(s.threshold) : 5000,
        freeEnabled: s.freeEnabled !== false,
    };
}

export function shippingFor(subtotal) {
    if (subtotal <= 0) return 0;
    const rule = shippingRule();
    return rule.freeEnabled && subtotal >= rule.threshold ? 0 : rule.fee;
}

/** Rupees still needed to unlock free shipping, or null when it doesn't apply. */
export function remainingForFreeShipping(subtotal) {
    const rule = shippingRule();
    if (!rule.freeEnabled || subtotal <= 0 || subtotal >= rule.threshold) return null;
    return rule.threshold - subtotal;
}

/** Colour is only meaningful when it isn't the placeholder "Default". */
export function meaningfulColor(color) {
    const c = (color || '').trim();
    if (!c || c.toLowerCase() === 'default') return null;
    return c;
}

export function whatsappHref(message) {
    const digits = (config().whatsapp && config().whatsapp.digits) || '923409002068';
    return `https://wa.me/${digits}?text=${encodeURIComponent(message)}`;
}

/** Full pre-filled "Order via WhatsApp" link for a cart (lib/utils/whatsapp.ts). */
export function whatsappOrderHref({ lines, subtotal, shipping, total }) {
    const base = config().origin || window.location.origin;
    const parts = ["Hello Bacha Stylo! I'd like to place this order:", ''];

    lines.forEach((line, i) => {
        const attrs = [
            line.size && line.size.trim() ? `Size: ${line.size.trim()}` : null,
            meaningfulColor(line.color) ? `Colour: ${meaningfulColor(line.color)}` : null,
        ].filter(Boolean).join(' | ');

        parts.push(`${i + 1}. ${line.name}`);
        if (line.variant_sku || line.sku) parts.push(`   Code: ${line.variant_sku || line.sku}`);
        if (attrs) parts.push(`   ${attrs}`);
        parts.push(`   ${line.quantity} × ${pkr(line.unit_price)} = ${pkr(line.unit_price * line.quantity)}`);
        if (base && line.slug) parts.push(`   Link: ${base}/products/${line.slug}`);
    });

    parts.push('');
    parts.push(`Subtotal: ${pkr(subtotal)}`);
    parts.push(`Shipping: ${shipping === 0 ? 'Free' : pkr(shipping)}`);
    parts.push(`Total: ${pkr(total)}`);
    parts.push('');
    parts.push('Please confirm availability and delivery. Thank you!');

    return whatsappHref(parts.join('\n'));
}

/**
 * JSON request to the same-origin API. Resolves with the parsed body; rejects
 * with an Error carrying `status` and `errors` (Laravel validation shape).
 */
export async function api(path, { method = 'GET', body, headers = {} } = {}) {
    const base = config().apiBase || '/api/v1';
    const url = path.startsWith('http') ? path : `${base}${path.startsWith('/') ? '' : '/'}${path}`;
    const init = {
        method,
        headers: { Accept: 'application/json', ...headers },
        credentials: 'same-origin',
    };
    if (body instanceof FormData) {
        init.body = body;
    } else if (body !== undefined) {
        init.headers['Content-Type'] = 'application/json';
        init.body = JSON.stringify(body);
    }

    const res = await fetch(url, init);
    let data = null;
    try { data = await res.json(); } catch { data = null; }

    if (!res.ok) {
        const err = new Error((data && data.message) || `Request failed (${res.status})`);
        err.status = res.status;
        err.errors = (data && data.errors) || {};
        throw err;
    }
    return data;
}

const EMAIL_RE = /.+@.+\..+/;

/**
 * Best-effort, silent capture of the cart for recovery. Needs a valid email
 * and at least one line; the backend keeps one row per email and deletes it
 * once the customer completes checkout.
 */
export async function captureAbandonedCart({ name, email, phone, lines, total }) {
    const e = (email || '').trim();
    if (!e || !EMAIL_RE.test(e) || !lines || lines.length === 0) return;
    try {
        await api('/abandoned-carts', {
            method: 'POST',
            body: {
                name: (name || '').trim() || undefined,
                email: e,
                phone: (phone || '').trim() || undefined,
                total,
                cart_data: lines.map((l) => ({
                    name: l.name,
                    quantity: l.quantity,
                    price: l.unit_price,
                    size: l.size,
                    slug: l.slug,
                })),
            },
        });
    } catch {
        // Recovery capture must never interrupt the shopping experience.
    }
}

export function debounce(fn, wait) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), wait);
    };
}

export async function copyText(text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        let ok = false;
        try { ok = document.execCommand('copy'); } catch { ok = false; }
        ta.remove();
        return ok;
    }
}

/** Validate a Pakistani mobile number the way the checkout schema does. */
export const PK_PHONE = /^(\+92|0)?3[0-9]{9}$/;

/** Stock badge label/tone for a variant stock count. */
/**
 * srcset of resized variants for a media-bucket image (mirrors
 * App\Support\Storefront\ImageVariant). Returns null for any other source,
 * which leaves the plain src in charge.
 */
export function srcset(url, widths = [320, 360, 480, 640, 960, 1280], format = 'webp') {
    const origin = (config().media || '').replace(/\/$/, '');
    const allowed = config().imgWidths || [];
    if (!url || !origin || typeof url !== 'string' || !url.startsWith(origin + '/')) return null;
    const path = url.slice(origin.length + 1);
    if (/[?#]/.test(path) || path.includes('..') || !/^[A-Za-z0-9_\-./%]+\.(jpe?g|png|webp|avif|gif|jfif)$/i.test(path)) return null;
    const parts = widths.filter((w) => allowed.includes(w)).map((w) => `/img/${w}/m/${path}.${format} ${w}w`);
    return parts.length ? parts.join(', ') : null;
}

export function stockStatus(stock, lowThreshold = 3) {
    if (stock <= 0) return { label: 'Out of Stock', tone: 'gray' };
    if (stock <= lowThreshold) return { label: `Only ${stock} left`, tone: 'amber' };
    return { label: 'In Stock', tone: 'green' };
}

window.bsf = {
    srcset,
    pkr,
    shippingRule,
    shippingFor,
    remainingForFreeShipping,
    meaningfulColor,
    whatsappHref,
    whatsappOrderHref,
    api,
    captureAbandonedCart,
    debounce,
    copyText,
    stockStatus,
    PK_PHONE,
    config,
};
