@extends('layouts.admin')
@section('title', 'Customers')
@section('content')

<div class="ph">
    <div>
        <h4>Customers</h4>
        <div class="ph-sub">{{ $customers->total() }} registered customers</div>
    </div>
</div>

<div class="bcard">
    <div class="table-wrap">
        <table class="table table-stack mb-0">
            <thead>
                <tr>
                    <th>CUSTOMER</th>
                    <th>PHONE</th>
                    <th>ORDERS</th>
                    <th>TOTAL SPENT</th>
                    <th>JOINED</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td data-label="Customer">
                        <div class="entity">
                            <div class="entity-avatar">
                                {{ strtoupper(substr($customer->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="entity-copy">
                                <div class="entity-title">{{ $customer->name }}</div>
                                <div class="entity-meta">{{ $customer->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Phone" class="text-muted">{{ $customer->phone ?? '—' }}</td>
                    <td data-label="Orders"><span class="soft-badge">{{ $customer->orders_count }} orders</span></td>
                    <td data-label="Total Spent" class="text-strong">Rs. {{ number_format($customer->total_spent) }}</td>
                    <td data-label="Joined" class="text-muted">{{ \Carbon\Carbon::parse($customer->created_at)->format('d M Y') }}</td>
                    <td data-label="Actions">
                        <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-light btn-icon"><i class="mdi mdi-eye-outline"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="mdi mdi-account-group-outline"></i>
                        <strong>No customers yet</strong>
                        Customer accounts will appear here after signups and orders.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="card-footer">{{ $customers->links() }}</div>
    @endif
</div>
@endsection
