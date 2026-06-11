@extends('layouts.admin')
@section('title', 'Reviews')
@section('content')

<div class="ph">
    <div>
        <h4>Customer Reviews</h4>
        <div class="ph-sub">{{ $reviews->total() }} reviews</div>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>PRODUCT</th>
                    <th>REVIEWER</th>
                    <th>RATING</th>
                    <th>COMMENT</th>
                    <th>STATUS</th>
                    <th>DATE</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td data-label="Product">
                        <div class="entity-title">{{ $review->product->name ?? '—' }}</div>
                    </td>
                    <td data-label="Reviewer" class="text-strong">{{ $review->author_name }}</td>
                    <td data-label="Rating" class="text-muted">
                        {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                    </td>
                    <td data-label="Comment" class="text-muted">{{ \Illuminate\Support\Str::limit($review->comment, 80) ?: '—' }}</td>
                    <td data-label="Status">
                        @if($review->is_approved)
                            <span class="soft-badge">Approved</span>
                        @else
                            <span class="soft-badge" style="background:#fde8e8;color:#b8141b;">Hidden</span>
                        @endif
                    </td>
                    <td data-label="Date" class="text-muted">{{ \Carbon\Carbon::parse($review->created_at)->format('d M Y') }}</td>
                    <td data-label="Actions">
                        <div style="display:flex; gap:6px;">
                            <form action="{{ route('admin.reviews.toggle', $review->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-light btn-icon" title="{{ $review->is_approved ? 'Hide' : 'Approve' }}">
                                    <i class="mdi {{ $review->is_approved ? 'mdi-eye-off-outline' : 'mdi-check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Delete this review?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light btn-icon"><i class="mdi mdi-trash-can-outline"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="mdi mdi-star-outline"></i>
                        <strong>No reviews yet</strong>
                        Customer reviews submitted from the storefront will appear here.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
    <div class="card-footer">{{ $reviews->links() }}</div>
    @endif
</div>
@endsection
