@extends('layouts.app')
@section('title','Payment Types')
@section('page-title','Payment Types')
@section('topbar-actions')
    <a href="{{ route('payment-types.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Type</a>
@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">All Payment Types ({{ $paymentTypes->total() }})</div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Payments</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentTypes as $type)
                <tr>
                    <td><div style="font-weight:600">{{ $type->name }}</div></td>
                    <td style="font-size:13px;color:var(--gray-500)">{{ $type->description ?? '—' }}</td>
                    <td style="font-weight:700">${{ number_format($type->amount,2) }}</td>
                    <td><span class="badge badge-primary">{{ $type->payments_count }} records</span></td>
                    <td>
                        @if($type->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-gray">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('payment-types.edit', $type) }}" class="btn btn-icon btn-outline" title="Edit">
                                <i class="fas fa-edit" style="font-size:12px"></i>
                            </a>
                            <form action="{{ route('payment-types.destroy', $type) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-outline" title="Delete" style="color:var(--danger)" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash" style="font-size:12px"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <p>No payment types found</p>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px;border-top:1px solid var(--gray-200)">
        {{ $paymentTypes->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
