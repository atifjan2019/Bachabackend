@extends('layouts.admin')
@section('title', 'Blog Posts')
@section('content')

<div class="ph">
    <div>
        <h4>Blog Posts</h4>
        <div class="ph-sub">{{ $posts->total() }} posts</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.blog.create') }}" class="btn btn-primary"><i class="mdi mdi-plus"></i> New Post</a>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>TITLE</th>
                    <th>SLUG</th>
                    <th>STATUS</th>
                    <th>DATE</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($posts as $post)
                <tr>
                    <td data-label="Title">
                        <div class="entity">
                            @if($post->image)
                                <div class="entity-thumb"><img src="{{ $post->image }}" alt=""></div>
                            @else
                                <div class="entity-thumb"><i class="mdi mdi-image-outline"></i></div>
                            @endif
                            <div class="entity-copy">
                                <div class="entity-title">{{ $post->title }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Slug" class="text-muted">{{ $post->slug }}</td>
                    <td data-label="Status">
                        @if($post->status)
                            <span class="status-badge" style="background:#d1fae5;color:#065f46;">Published</span>
                        @else
                            <span class="status-badge" style="background:#f1f5f9;color:#475569;">Draft</span>
                        @endif
                    </td>
                    <td data-label="Date" class="text-muted">{{ \Carbon\Carbon::parse($post->created_at)->format('d M Y') }}</td>
                    <td data-label="Actions">
                        <div class="action-group">
                            <a href="{{ route('admin.blog.edit', $post->id) }}" class="btn btn-sm btn-light btn-icon"><i class="mdi mdi-pencil-outline"></i></a>
                            <form action="{{ route('admin.blog.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn-ghost-del"><i class="mdi mdi-trash-can-outline"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="mdi mdi-post-outline"></i>
                        <strong>No blog posts yet</strong>
                        Write your first post to start publishing content.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($posts->hasPages())
    <div class="card-footer">{{ $posts->links() }}</div>
    @endif
</div>
@endsection
