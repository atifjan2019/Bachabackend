@extends('layouts.admin')
@section('title', 'Customer: '.$customer->name)
@section('content')

<div class="ph">
    <div>
        <h4>{{ $customer->name }}</h4>
        <div class="ph-sub">Customer profile, spend history, and contact details.</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.customers.index') }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="bcard">
            <div class="bcard-head"><span class="bcard-title">Customer Info</span></div>
            <div class="bcard-body">
                <div class="detail-grid">
                    <div class="detail-item"><div class="detail-label">Name</div><div class="detail-value">{{ $customer->name }}</div></div>
                    <div class="detail-item"><div class="detail-label">Email</div><div class="detail-value">{{ $customer->email }}</div></div>
                    <div class="detail-item"><div class="detail-label">Phone</div><div class="detail-value">{{ $customer->phone ?? '—' }}</div></div>
                    <div class="detail-item"><div class="detail-label">Address</div><div class="detail-value">{{ $customer->address ?? '—' }}</div></div>
                    <div class="detail-item"><div class="detail-label">Total Orders</div><div class="detail-value">{{ $customer->orders_count }}</div></div>
                    <div class="detail-item"><div class="detail-label">Total Spent</div><div class="detail-value">Rs. {{ number_format($customer->total_spent) }}</div></div>
                    <div class="detail-item"><div class="detail-label">Joined</div><div class="detail-value">{{ \Carbon\Carbon::parse($customer->created_at)->format('d M Y') }}</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
