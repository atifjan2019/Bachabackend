@extends('layouts.admin')
@section('title', 'API Reference')
@section('content')

<div class="ph">
    <div>
        <h4>API Reference</h4>
        <div class="ph-sub">Copy and paste these endpoints and examples directly into your Next.js frontend.</div>
    </div>
</div>

<div class="bcard">
    <div class="bcard-head">
        <span class="bcard-title">Base URL</span>
    </div>
    <div class="bcard-body">
        <div class="form-hint mb-2">Set this in your frontend environment:</div>
        <pre class="m-0" style="background:var(--surf2);border:1px solid var(--bd);border-radius:10px;padding:12px;overflow:auto;">NEXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000/api/v1</pre>
        <div class="form-hint mt-2">Backend CORS env: FRONTEND_URL=http://localhost:3000</div>
    </div>
</div>

<div class="bcard">
    <div class="bcard-head">
        <span class="bcard-title">Endpoints</span>
    </div>
    <div class="table-wrap">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Method</th>
                    <th>Path</th>
                    <th>Purpose</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>GET</td><td>/health</td><td>Health check</td></tr>
                <tr><td>GET</td><td>/settings</td><td>Storefront settings</td></tr>
                <tr><td>GET</td><td>/categories</td><td>Category list</td></tr>
                <tr><td>GET</td><td>/products</td><td>Product list (paginated)</td></tr>
                <tr><td>GET</td><td>/products/{slugOrId}</td><td>Product detail</td></tr>
                <tr><td>GET</td><td>/blog-posts</td><td>Published blog posts</td></tr>
                <tr><td>GET</td><td>/blog-posts/{slug}</td><td>Single published post</td></tr>
                <tr><td>POST</td><td>/orders</td><td>Create checkout order</td></tr>
                <tr><td>POST</td><td>/abandoned-carts</td><td>Capture abandoned cart</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="bcard">
    <div class="bcard-head">
        <span class="bcard-title">Frontend Fetch Helper</span>
    </div>
    <div class="bcard-body">
<pre class="m-0" style="background:var(--surf2);border:1px solid var(--bd);border-radius:10px;padding:12px;overflow:auto;">// lib/api.ts
const API_BASE = process.env.NEXT_PUBLIC_API_BASE_URL!;

export async function apiGet&lt;T&gt;(path: string): Promise&lt;T&gt; {
  const res = await fetch(`${API_BASE}${path}`, {
    headers: { 'Content-Type': 'application/json' },
    next: { revalidate: 60 },
  });

  if (!res.ok) {
    throw new Error(`GET ${path} failed: ${res.status}`);
  }

  return res.json();
}

export async function apiPost&lt;T&gt;(path: string, body: unknown): Promise&lt;T&gt; {
  const res = await fetch(`${API_BASE}${path}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  });

  if (!res.ok) {
    const payload = await res.json().catch(() =&gt; ({}));
    throw new Error(payload?.message || `POST ${path} failed: ${res.status}`);
  }

  return res.json();
}</pre>
    </div>
</div>

<div class="bcard">
    <div class="bcard-head">
        <span class="bcard-title">Order Payload Example</span>
    </div>
    <div class="bcard-body">
<pre class="m-0" style="background:var(--surf2);border:1px solid var(--bd);border-radius:10px;padding:12px;overflow:auto;">await apiPost('/orders', {
  customer_name: 'John Doe',
  customer_email: 'john@example.com',
  customer_phone: '923001234567',
  shipping_address: 'Street 1, Lahore',
  city: 'Lahore',
  country: 'Pakistan',
  items: [
    { name: 'Premium Shawl', price: 4500, quantity: 1, size: 'M' }
  ],
  subtotal: 4500,
  shipping_fee: 250,
  total_amount: 4750,
  payment_method: 'Cash on Delivery'
});</pre>
    </div>
</div>

<div class="bcard">
    <div class="bcard-head">
        <span class="bcard-title">Full Contract File</span>
    </div>
    <div class="bcard-body">
        <p class="m-0">See detailed request and response examples in <strong>docs/nextjs-integration.md</strong>.</p>
        <p class="m-0 mt-2">Ready-to-copy typed Next.js client files are available in <strong>docs/nextjs-starter</strong>.</p>
    </div>
</div>
@endsection
