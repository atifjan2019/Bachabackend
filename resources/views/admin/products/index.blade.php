@extends('layouts.admin')
@section('title', 'Products')
@section('content')

<div class="ph">
    <div>
        <h4>Products</h4>
        <div class="ph-sub">{{ $products->total() }} total products</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
            <i class="mdi mdi-plus"></i> Add Product
        </a>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Original</th>
                    <th>Badge</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td data-label="Product">
                        <div class="entity">
                            @if($product->image)
                                <div class="entity-thumb"><img src="{{ $product->image }}" alt=""></div>
                            @else
                                <div class="entity-thumb"><i class="mdi mdi-image-outline"></i></div>
                            @endif
                            <div class="entity-copy">
                                <div class="entity-title">{{ Str::limit($product->name, 45) }}</div>
                                <div class="entity-meta">{{ $product->slug }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Category">
                        @if($product->category)
                            <span class="soft-badge">{{ $product->category }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td data-label="Price" class="text-strong">Rs. {{ number_format($product->price) }}</td>
                    <td data-label="Original" class="text-muted" style="text-decoration:line-through;">{{ $product->original_price ? 'Rs. '.number_format($product->original_price) : '—' }}</td>
                    <td data-label="Badge">
                        @if($product->is_new)
                            <span class="status-badge" style="background:#d1fae5;color:#065f46;">NEW</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="action-group">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-light btn-icon"><i class="mdi mdi-pencil-outline"></i></a>
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete this product?')">
                                @csrf @method('DELETE')
                                <button class="btn-ghost-del"><i class="mdi mdi-trash-can-outline" style="font-size:1.05rem;"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="mdi mdi-package-variant"></i>
                        <strong>No products yet</strong>
                        Add your first product to start building the catalog.
                        <div class="mt-3">
                            <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-light">Add Product</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="card-footer">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
