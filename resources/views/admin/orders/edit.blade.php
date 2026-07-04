@extends('layouts.admin')
@section('title', 'Update Order #'.$order->id)
@section('content')

<div class="ph">
    <div>
        <h4>Update Order #{{ $order->id }}</h4>
        <div class="ph-sub">Change fulfilment state and keep the customer journey moving.</div>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-light"><i class="mdi mdi-arrow-left"></i> Back</a>
    </div>
</div>
@include('admin.partials.alerts')

<div class="row g-4">
    <div class="col-lg-6 col-xl-5">
        <div class="bcard">
            <div class="bcard-head"><span class="bcard-title">Change Status</span></div>
            <div class="bcard-body">
                @if($order->isLocked())
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-0" role="alert">
                        <i class="mdi mdi-lock-outline"></i>
                        <span>This order is <strong>{{ $order->status }}</strong> and is permanently locked. Its status can no longer be changed.</span>
                    </div>
                @else
                    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label class="form-label">Order Status</label>
                            <select name="status" class="form-select">
                                @foreach($statuses as $s)
                                <option value="{{ $s }}" {{ $order->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                            <div class="form-hint" style="margin-top:6px;">Marking an order <strong>Delivered</strong> or <strong>Cancelled</strong> will lock it permanently.</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100"><i class="mdi mdi-check"></i> Update Status</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
