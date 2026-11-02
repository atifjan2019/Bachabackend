/**
 * Captures a signed-in customer's cart whenever it changes (6 s debounce), so
 * a recovery record exists the moment they walk away. Guests are captured on
 * the checkout page once they type an email (see the checkout component).
 */
export function registerAbandonedCartTracker(Alpine) {
    document.addEventListener('alpine:initialized', () => {
        const customer = (window.BSF || {}).customer;
        if (!customer || !customer.email) return;

        const send = window.bsf.debounce((lines, total) => {
            window.bsf.captureAbandonedCart({
                name: customer.name,
                email: customer.email,
                phone: customer.phone,
                lines,
                total,
            });
        }, 6000);

        let first = true;
        Alpine.effect(() => {
            const cart = Alpine.store('cart');
            const lines = cart.lines.map((l) => ({ ...l }));
            const total = cart.subtotal;
            // The initial hydration isn't a change the customer made.
            if (first) { first = false; return; }
            if (lines.length === 0) return;
            send(lines, total);
        });
    });
}
