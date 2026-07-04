@extends('layouts.admin')
@section('title', 'Contact Messages')
@section('content')

<div class="ph">
    <div>
        <h4>Contact Messages</h4>
        <div class="ph-sub">{{ $messages->total() }} message{{ $messages->total() === 1 ? '' : 's' }}@if($unread) · <span style="color:var(--red);font-weight:600;">{{ $unread }} unread</span>@endif</div>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>FROM</th>
                    <th>SUBJECT</th>
                    <th>RECEIVED</th>
                    <th>STATUS</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                <tr>
                    <td data-label="From" class="text-strong">
                        {{ $msg->name }}
                        <div class="entity-meta">{{ $msg->email }}</div>
                    </td>
                    <td data-label="Subject">
                        <a href="{{ route('admin.contact-messages.show', $msg->id) }}" class="table-link" style="{{ $msg->is_read ? '' : 'font-weight:700;' }}">{{ \Illuminate\Support\Str::limit($msg->subject, 60) }}</a>
                    </td>
                    <td data-label="Received" class="text-muted">{{ \Carbon\Carbon::parse($msg->created_at)->format('d M Y, h:i A') }}</td>
                    <td data-label="Status">
                        @if($msg->is_read)
                            <span class="status-badge" style="background:#f3f4f6;color:var(--t2);">Read</span>
                        @else
                            <span class="status-badge" style="background:#fee2e2;color:#dc2626;">Unread</span>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="action-group">
                            <a href="{{ route('admin.contact-messages.show', $msg->id) }}" class="btn btn-sm btn-light btn-icon" title="View"><i class="mdi mdi-eye-outline"></i></a>
                            <form action="{{ route('admin.contact-messages.destroy', $msg->id) }}" method="POST" onsubmit="return confirm('Delete this message?')">
                                @csrf @method('DELETE')
                                <button class="btn-ghost-del" title="Delete"><i class="mdi mdi-trash-can-outline"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="mdi mdi-email-outline"></i>
                        <strong>No messages yet</strong>
                        Contact-form submissions from the website will appear here.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($messages->hasPages())
    <div class="card-footer">{{ $messages->links() }}</div>
    @endif
</div>
@endsection
