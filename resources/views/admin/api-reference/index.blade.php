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
        <div class="form-hint mb-2">Set this in your frontend <strong>.env.local</strong>:</div>
        <pre class="m-0" style="background:var(--surf2);border:1px solid var(--bd);border-radius:10px;padding:12px;overflow:auto;">NEXT_PUBLIC_API_BASE_URL={{ rtrim(config('app.url'), '/') }}/api/v1</pre>
        <div class="form-hint mt-2">
            <strong>Live API:</strong> <a href="{{ rtrim(config('app.url'), '/') }}/api/v1/health" target="_blank" style="color:var(--red);">{{ rtrim(config('app.url'), '/') }}/api/v1/health</a>
        </div>
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
                    <th style="width:70px;">Method</th>
                    <th>Path</th>
                    <th>Purpose</th>
                    <th style="width:100px;">Auth</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><span class="status-badge" style="background:#f0fdf4;color:#15803d;">GET</span></td><td>/health</td><td>Health check</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#f0fdf4;color:#15803d;">GET</span></td><td>/settings</td><td>Storefront settings, logo, branding</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#f0fdf4;color:#15803d;">GET</span></td><td>/categories</td><td>Category list with images</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#f0fdf4;color:#15803d;">GET</span></td><td>/products</td><td>Product list (paginated)</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#f0fdf4;color:#15803d;">GET</span></td><td>/products/{slugOrId}</td><td>Product detail</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#f0fdf4;color:#15803d;">GET</span></td><td>/blog-posts</td><td>Published blog posts</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#f0fdf4;color:#15803d;">GET</span></td><td>/blog-posts/{slug}</td><td>Single published post</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#eff6ff;color:#2563eb;">POST</span></td><td>/orders</td><td>Create checkout order</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#eff6ff;color:#2563eb;">POST</span></td><td>/abandoned-carts</td><td>Capture abandoned cart</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#eff6ff;color:#2563eb;">POST</span></td><td>/auth/register</td><td>Customer registration</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#eff6ff;color:#2563eb;">POST</span></td><td>/auth/login</td><td>Customer login</td><td>—</td></tr>
                <tr><td><span class="status-badge" style="background:#f0fdf4;color:#15803d;">GET</span></td><td>/auth/me</td><td>Current customer profile</td><td>Bearer</td></tr>
                <tr><td><span class="status-badge" style="background:#eff6ff;color:#2563eb;">POST</span></td><td>/auth/logout</td><td>Customer logout</td><td>Bearer</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="bcard">
            <div class="bcard-head">
                <span class="bcard-title">Frontend Fetch Helper</span>
            </div>
            <div class="bcard-body">
<pre class="m-0" style="background:var(--surf2);border:1px solid var(--bd);border-radius:10px;padding:12px;overflow:auto;font-size:.72rem;">// lib/api.ts
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
    throw new Error(payload?.message || `POST ${path} failed`);
  }

  return res.json();
}</pre>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="bcard">
            <div class="bcard-head">
                <span class="bcard-title">Order Payload Example</span>
            </div>
            <div class="bcard-body">
<pre class="m-0" style="background:var(--surf2);border:1px solid var(--bd);border-radius:10px;padding:12px;overflow:auto;font-size:.72rem;">await apiPost('/orders', {
  customer_name: 'John Doe',
  customer_email: 'john@example.com',
  customer_phone: '923001234567',
  shipping_address: 'Street 1, Lahore',
  city: 'Lahore',
  country: 'Pakistan',
  items: [
    {
      name: 'Premium Shawl',
      price: 4500,
      quantity: 1,
      size: 'M'
    }
  ],
  subtotal: 4500,
  shipping_fee: 250,
  total_amount: 4750,
  payment_method: 'Cash on Delivery'
});</pre>
            </div>
        </div>
    </div>
</div>

<div class="bcard">
    <div class="bcard-head">
        <span class="bcard-title">Quick Test</span>
    </div>
    <div class="bcard-body">
        <div class="form-hint mb-2">Try these URLs in your browser to verify the API:</div>
        <div class="metric-list">
            <div class="metric-row">
                <span class="metric-row-label">Settings</span>
                <a href="{{ rtrim(config('app.url'), '/') }}/api/v1/settings" target="_blank" class="metric-row-value" style="color:var(--red);font-size:.72rem;">/api/v1/settings ↗</a>
            </div>
            <div class="metric-row">
                <span class="metric-row-label">Categories</span>
                <a href="{{ rtrim(config('app.url'), '/') }}/api/v1/categories" target="_blank" class="metric-row-value" style="color:var(--red);font-size:.72rem;">/api/v1/categories ↗</a>
            </div>
            <div class="metric-row">
                <span class="metric-row-label">Products</span>
                <a href="{{ rtrim(config('app.url'), '/') }}/api/v1/products" target="_blank" class="metric-row-value" style="color:var(--red);font-size:.72rem;">/api/v1/products ↗</a>
            </div>
            <div class="metric-row">
                <span class="metric-row-label">Blog Posts</span>
                <a href="{{ rtrim(config('app.url'), '/') }}/api/v1/blog-posts" target="_blank" class="metric-row-value" style="color:var(--red);font-size:.72rem;">/api/v1/blog-posts ↗</a>
            </div>
        </div>
    </div>
</div>

<div class="bcard">
    <div class="bcard-head">
        <span class="bcard-title">Media / Image URLs</span>
    </div>
    <div class="bcard-body">
        <p class="m-0" style="font-size:.8rem;color:var(--t2);">All media URLs (product images, category images, logo, favicon) are served from:</p>
        <pre class="m-0 mt-2" style="background:var(--surf2);border:1px solid var(--bd);border-radius:10px;padding:12px;overflow:auto;">{{ env('MEDIA_URL', config('app.url').'/storage') }}</pre>
        <p class="m-0 mt-2 form-hint">These URLs are returned as absolute paths in all API responses — no need to prepend anything on the frontend.</p>
    </div>
</div>

@endsection
