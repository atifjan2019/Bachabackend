@extends('layouts.admin')
@section('title', 'Add Admin User')
@section('content')

<div class="ph">
    <div>
        <h4>Add Admin User</h4>
        <div class="ph-sub">Create a new account with access to this admin panel.</div>
    </div>
    <div class="ph-action">
        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>

<div class="bcard" style="max-width:640px;">
    <div class="card-body">
        <form action="{{ route('admin.admin-users.store') }}" method="POST" autocomplete="off">
            @csrf

            <div class="form-group mb-4">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required placeholder="e.g. sara">
                @error('username')<div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="sara@bachastylo.com">
                @error('email')<div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" id="pw" class="form-control @error('password') is-invalid @enderror" required minlength="8" placeholder="At least 8 characters">
                <small class="form-hint">Minimum 8 characters.</small>
                @error('password')<div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required minlength="8" placeholder="Re-enter password">
                <label class="form-check-label" style="display:flex;align-items:center;gap:6px;margin-top:8px;cursor:pointer;">
                    <input type="checkbox" onclick="togglePw(this)"> Show passwords
                </label>
            </div>

            <button type="submit" class="btn btn-primary"><i class="mdi mdi-account-plus"></i> Create Admin</button>
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
