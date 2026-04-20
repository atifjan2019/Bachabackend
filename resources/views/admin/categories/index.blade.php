@extends('layouts.admin')
@section('title', 'Categories')
@section('content')

<div class="ph">
    <div>
        <h4>Categories</h4>
        <div class="ph-sub">{{ $categories->total() }} total categories</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="mdi mdi-plus"></i> Add Category
        </a>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Slug</th>
                    <th>Meta Title</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td data-label="Category">
                        <div class="entity">
                            @if($cat->image)
                                <div class="entity-thumb"><img src="{{ $cat->image }}" alt=""></div>
                            @else
                                <div class="entity-thumb"><i class="mdi mdi-shape-outline"></i></div>
                            @endif
                            <div class="entity-copy">
                                <div class="entity-title">
                                    {{ $cat->name }}
                                    @if($cat->parent)
                                        <span class="soft-badge ms-2" style="font-size: 0.6rem; padding: 2px 6px;">{{ $cat->parent->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Slug" class="text-muted">{{ $cat->slug }}</td>
                    <td data-label="Meta Title" class="text-muted">{{ Str::limit($cat->meta_title, 40) }}</td>
                    <td data-label="Actions">
                        <div class="action-group">
                            <a href="{{ route('admin.categories.edit', $cat->id) }}" class="btn btn-sm btn-light btn-icon"><i class="mdi mdi-pencil-outline"></i></a>
                            <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" onsubmit="return confirm('Delete category?')">
                                @csrf @method('DELETE')
                                <button class="btn-ghost-del"><i class="mdi mdi-trash-can-outline"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="empty-state">
                        <i class="mdi mdi-shape-outline"></i>
                        <strong>No categories yet</strong>
                        Create category groupings to keep the catalog organized.
                        <div class="mt-3">
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-light">Add Category</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div class="card-footer">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection
