@extends('layouts.admin')
@section('title', 'Message from '.$message->name)
@section('content')

<div class="ph">
    <div>
        <h4>{{ $message->subject }}</h4>
        <div class="ph-sub">From {{ $message->name }} · {{ \Carbon\Carbon::parse($message->created_at)->format('d M Y, h:i A') }}</div>
    </div>
    <div class="page-actions">
        <a href="mailto:{{ $message->email }}?subject=RE: {{ rawurlencode($message->subject) }}" class="btn btn-primary"><i class="mdi mdi-reply-outline"></i> Reply</a>
        <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="bcard">
            <div class="bcard-head"><span class="bcard-title">Message</span></div>
            <div class="bcard-body">
                <div style="white-space:pre-line;font-size:15px;line-height:1.7;color:var(--t1,#141414);">{{ $message->message }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="bcard mb-4">
            <div class="bcard-head"><span class="bcard-title">Sender</span></div>
            <div class="bcard-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Name</div>
                        <div class="detail-value">{{ $message->name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><a href="mailto:{{ $message->email }}" class="table-link">{{ $message->email }}</a></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Subject</div>
                        <div class="detail-value">{{ $message->subject }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Received</div>
                        <div class="detail-value">{{ \Carbon\Carbon::parse($message->created_at)->format('d M Y, h:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bcard">
            <div class="bcard-body">
                <form action="{{ route('admin.contact-messages.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Delete this message?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-light w-100" style="color:#dc2626;"><i class="mdi mdi-trash-can-outline"></i> Delete Message</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
