@extends('layouts.admin')
@section('title', 'Compose Newsletter')
@section('content')

<div class="ph">
    <div>
        <h4>Compose Newsletter</h4>
        <div class="ph-sub">Send an email to all active subscribers.</div>
    </div>
    <div class="ph-action">
        <a href="{{ route('admin.newsletter.index') }}" class="btn btn-light">Cancel</a>
    </div>
</div>

<div class="bcard">
    <div class="card-body">
        <form action="{{ route('admin.newsletter.send') }}" method="POST">
            @csrf
            
            <div class="form-group mb-4">
                <label class="form-label">Email Subject <span class="text-danger">*</span></label>
                <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required placeholder="e.g. New Arrivals at Bacha Stylo!">
                @error('subject')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Email Body (HTML Supported) <span class="text-danger">*</span></label>
                <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="15" required placeholder="<p>Write your newsletter content here...</p>">{{ old('body') }}</textarea>
                <small class="text-muted">You can write plain text or HTML. The content will be wrapped in the standard Bacha Stylo email template.</small>
                @error('body')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to queue this email to all subscribers?');">
                    <i class="mdi mdi-send"></i> Send Newsletter to All Subscribers
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
