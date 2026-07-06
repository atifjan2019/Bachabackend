@extends('layouts.admin')
@section('title', 'Edit Admin User')
@section('content')

<div class="ph">
    <div>
        <h4>Edit Admin User</h4>
        <div class="ph-sub">Update account details for <strong>{{ $admin->username }}</strong>.</div>
    </div>
    <div class="ph-action">
        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>

<div class="bcard" style="max-width:640px;">
    <div class="card-body">
        <form action="{{ route('admin.admin-users.update', $admin->id) }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="form-group mb-4">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $admin->username) }}" required>
                @error('username')<div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}" required>
                @error('email')<div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="section-label" style="margin-top:20px;">Change Password</div>

            <div class="form-group mb-4">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" placeholder="Leave blank to keep current password">
                <small class="form-hint">Only fill this in if you want to change the password (min 8 characters).</small>
                @error('password')<div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" minlength="8" placeholder="Re-enter new password">
                <label class="form-check-label" style="display:flex;align-items:center;gap:6px;margin-top:8px;cursor:pointer;">
                    <input type="checkbox" onclick="togglePw(this)"> Show passwords
                </label>
            </div>

            @if($admin->locked_until && $admin->locked_until->isFuture())
            <div class="form-group mb-4">
                <label class="form-check-label" style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 12px;background:#fef2f2;border-radius:12px;">
                    <input type="checkbox" name="unlock" value="1">
                    <span style="color:#dc2626;">This account is locked until {{ $admin->locked_until->format('d M Y, h:i A') }} — unlock it now</span>
                </label>
            </div>
            @endif

            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save-outline"></i> Save Changes</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function togglePw(cb) {
    document.querySelectorAll('input[name="password"], input[name="password_confirmation"]').forEach(function (i) {
        i.type = cb.checked ? 'text' : 'password';
    });
}
</script>
@endpush
@endsection
