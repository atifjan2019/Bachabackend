# Next.js API Starter (Copy/Paste)

Use these files inside your Next.js app (recommended path: `src/lib/api/`).

## 1) Environment variable

Create `.env.local` in Next.js:

```env
NEXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000/api/v1
```

## 2) Copy files

- `api-types.ts`
- `api-client.ts`
- `services/catalog.ts`
- `services/content.ts`
- `services/checkout.ts`

## 3) Usage examples

```ts
import { getProducts, getProductDetail } from '@/lib/api/services/catalog';
import { createOrder } from '@/lib/api/services/checkout';

const products = await getProducts({ per_page: 12, search: 'shawl' });
const product = await getProductDetail('premium-shawl');

await createOrder({
  customer_name: 'John Doe',
  customer_email: 'john@example.com',
  customer_phone: '923001234567',
  shipping_address: 'Street 1, Lahore',
  city: 'Lahore',
  country: 'Pakistan',
  items: [{ name: 'Premium Shawl', price: 4500, quantity: 1, size: 'M' }],
  subtotal: 4500,
  shipping_fee: 250,
  total_amount: 4750,
  payment_method: 'Cash on Delivery',
});
```

## 4) Backend CORS reminder

Set this in Laravel `.env`:

```env
FRONTEND_URL=http://localhost:3000
```
