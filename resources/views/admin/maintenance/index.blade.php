@extends('layouts.admin')
@section('title', 'Database Updates')
@section('content')

<div class="ph">
    <div>
        <h4>Database Updates</h4>
        <div class="ph-sub">Apply database changes that came with the latest code deployment</div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bcard">
            <div class="bcard-head"><span class="bcard-title">Pending updates</span></div>
            <div class="bcard-body">
                @if(count($pending) === 0)
                    <div class="d-flex align-items-center gap-2" style="color:#065f46;">
                        <i class="mdi mdi-check-circle-outline" style="font-size:1.3rem;"></i>
                        <div>
                            <strong>Database is up to date.</strong>
                            <div class="form-hint">Nothing to run. Products, orders and settings are untouched.</div>
                        </div>
                    </div>
                @else
                    <p>The latest deployment added database changes that have not been applied yet. These updates only add new columns and settings; existing products, orders and customers are not modified or deleted.</p>
                    <ul style="margin:12px 0 18px 18px;">
                        @foreach($pending as $m)
                            <li><code>{{ $m }}</code></li>
                        @endforeach
                    </ul>
                    <form method="POST" action="{{ route('admin.maintenance.migrate') }}" onsubmit="return confirm('Apply {{ count($pending) }} database update(s) now?');">
                        @csrf
                        <button type="submit" class="btn btn-primary"><i class="mdi mdi-database-arrow-up-outline"></i> Run updates now</button>
                    </form>
                @endif

                @if(session('migrate_output'))
                    <pre style="margin-top:18px;background:var(--surf2,#f6f6f7);padding:12px 14px;border-radius:6px;font-size:12px;white-space:pre-wrap;">{{ session('migrate_output') }}</pre>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bcard">
            <div class="bcard-head"><span class="bcard-title">How deployments work</span></div>
            <div class="bcard-body">
                <p class="form-hint" style="margin-bottom:8px;">1. The new code is pulled onto the server.</p>
                <p class="form-hint" style="margin-bottom:8px;">2. If that release needs new database columns, a banner appears at the top of every admin page.</p>
                <p class="form-hint" style="margin-bottom:0;">3. Click <strong>Run updates now</strong> once. Take a backup from Cloudways first if you want an extra safety net.</p>
            </div>
        </div>
    </div>
</div>
@endsection
