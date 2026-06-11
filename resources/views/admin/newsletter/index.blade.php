@extends('layouts.admin')
@section('title', 'Newsletter')
@section('content')

<div class="ph">
    <div>
        <h4>Newsletter Subscribers</h4>
        <div class="ph-sub">{{ $subscribers->total() }} subscribers</div>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>EMAIL</th>
                    <th>SUBSCRIBED</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscribers as $subscriber)
                <tr>
                    <td data-label="Email">
                        <div class="entity">
                            <div class="entity-avatar">
                                {{ strtoupper(substr($subscriber->email ?? 'U', 0, 1)) }}
                            </div>
                            <div class="entity-copy">
                                <div class="entity-title">{{ $subscriber->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Subscribed" class="text-muted">{{ \Carbon\Carbon::parse($subscriber->created_at)->format('d M Y') }}</td>
                    <td data-label="Actions">
                        <form action="{{ route('admin.newsletter.destroy', $subscriber->id) }}" method="POST" onsubmit="return confirm('Remove this subscriber?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light btn-icon"><i class="mdi mdi-trash-can-outline"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="empty-state">
                        <i class="mdi mdi-email-newsletter"></i>
                        <strong>No subscribers yet</strong>
                        Emails captured from the storefront newsletter form will appear here.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($subscribers->hasPages())
    <div class="card-footer">{{ $subscribers->links() }}</div>
    @endif
</div>
@endsection
