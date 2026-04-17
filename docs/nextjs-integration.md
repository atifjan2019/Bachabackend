# Next.js Integration Guide

## Base URL

Use your backend host as the API base, for example:

- local: `http://127.0.0.1:8000/api/v1`

## Environment Variables

Set this in backend `.env` so CORS allows your Next.js app:

- `FRONTEND_URL=http://localhost:3000`

## Public API Endpoints

### Health

- `GET /health`
- Response:

```json
{
  "ok": true
}
```

### Settings

- `GET /settings`
- Response:

```json
{
  "data": {
    "business_name": "...",
    "logo_url": "...",
    "shipping_fee": "250"
  }
}
```

### Categories

- `GET /categories`
- Response:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Shawls",
      "slug": "shawls",
      "description": "...",
      "image": "..."
    }
  ]
}
```

### Products (paginated)

- `GET /products?category=Shawls&search=wool&is_new=1&per_page=20`
- Response is Laravel paginator JSON:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "...",
      "slug": "...",
      "price": "4500",
      "is_new": true
    }
  ],
  "last_page": 1,
  "total": 1
}
```

### Product Detail

- `GET /products/{slugOrId}`
- Response:

```json
{
  "data": {
    "id": 1,
    "name": "...",
    "slug": "...",
    "description": "...",
    "price": "...",
    "items": []
  }
}
```

### Blog Posts (published only)

- `GET /blog-posts?per_page=20`
- Response is paginator JSON.

### Blog Post Detail (published only)

- `GET /blog-posts/{slug}`
- Response:

```json
{
  "data": {
    "id": 1,
    "title": "...",
    "slug": "...",
    "content": "..."
  }
}
```

### Create Order

- `POST /orders`
- Body:

```json
{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "923001234567",
  "shipping_address": "Street 1, Lahore",
  "city": "Lahore",
  "country": "Pakistan",
  "items": [
    {
      "name": "Product Name",
      "price": 4500,
      "quantity": 1,
      "size": "M"
    }
  ],
  "subtotal": 4500,
  "shipping_fee": 250,
  "total_amount": 4750,
  "payment_method": "Cash on Delivery"
}
```

- Success response:

```json
{
  "message": "Order created successfully.",
  "data": {
    "id": 123,
    "status": "Pending"
  }
}
```

### Capture Abandoned Cart

- `POST /abandoned-carts`
- Body:

```json
{
  "email": "john@example.com",
  "phone": "923001234567",
  "cart_data": {
    "items": [
      { "id": 1, "name": "...", "qty": 1 }
    ]
  }
}
```

- Success response:

```json
{
  "message": "Abandoned cart captured.",
  "data": {
    "id": 55
  }
}
```

## Suggested Next.js Wiring Pattern

1. Create a shared API client with `NEXT_PUBLIC_API_BASE_URL`.
2. Fetch categories/products/blog in server components where possible.
3. Use client-side mutation for order submission and abandoned cart capture.
4. Handle backend validation errors by surfacing `422` response messages on form fields.

## Notes

- This API is currently public and does not require auth tokens.
- Admin pages still use Laravel session auth and web routes.
