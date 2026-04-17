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
                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="form-group">
                        <label class="form-label">Order Status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ $order->status == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="mdi mdi-check"></i> Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
