@extends('layouts.admin')
@section('title', 'Admin Users')
@section('content')

<div class="ph">
    <div>
        <h4>Admin Users</h4>
        <div class="ph-sub">{{ $admins->total() }} admin user(s) with access to this panel</div>
    </div>
    <div class="ph-action">
        <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary"><i class="mdi mdi-account-plus"></i> Add Admin</a>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>USER</th>
                    <th>EMAIL</th>
                    <th>STATUS</th>
                    <th>LAST LOGIN</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($admins as $admin)
                <tr>
                    <td data-label="User">
                        <div class="entity">
                            <div class="entity-avatar">{{ strtoupper(substr($admin->username ?? 'A', 0, 1)) }}</div>
                            <div class="entity-copy">
                                <div class="entity-title">
                                    {{ $admin->username }}
                                    @if((int) $admin->id === (int) Auth::guard('admin')->id())
                                        <span class="soft-badge" style="margin-left:6px;">You</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Email" class="text-muted">{{ $admin->email }}</td>
                    <td data-label="Status">
                        @if($admin->locked_until && $admin->locked_until->isFuture())
                            <span class="status-badge" style="background:#fef2f2;color:#dc2626;">Locked</span>
                        @else
                            <span class="status-badge" style="background:#f0fdf4;color:#15803d;">Active</span>
                        @endif
                    </td>
                    <td data-label="Last Login" class="text-muted">
                        {{ $admin->last_login ? $admin->last_login->format('d M Y, h:i A') : 'Never' }}
                    </td>
                    <td data-label="Actions">
                        <div class="action-group">
                            <a href="{{ route('admin.admin-users.edit', $admin->id) }}" class="btn btn-sm btn-light btn-icon" title="Edit"><i class="mdi mdi-pencil-outline"></i></a>
                            @if((int) $admin->id !== (int) Auth::guard('admin')->id())
                            <form action="{{ route('admin.admin-users.destroy', $admin->id) }}" method="POST" onsubmit="return confirm('Delete admin user &quot;{{ $admin->username }}&quot;? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-ghost-del" title="Delete"><i class="mdi mdi-trash-can-outline"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="mdi mdi-account-key-outline"></i>
                        <strong>No admin users</strong>
                        Add an admin to grant access to this panel.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admins->hasPages())
    <div class="card-footer">{{ $admins->links() }}</div>
    @endif
</div>
@endsection
