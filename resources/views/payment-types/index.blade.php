@extends('layouts.app')
@section('title','Payment Types')
@section('page-title','Payment Types')
@section('topbar-actions')
    <a href="{{ route('payment-types.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus" aria-hidden="true"></i> <span>Add Type</span>
    </a>
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
                    <td style="font-weight:600;color:var(--text-primary)">{{ $type->name }}</td>
                    <td style="font-size:13px;color:var(--text-muted)">{{ $type->description ?? '—' }}</td>
                    <td style="font-weight:700;color:var(--text-primary)">${{ number_format($type->amount,2) }}</td>
                    <td><span class="badge badge-primary">{{ $type->payments_count }}</span></td>
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
                                <i class="fas fa-edit" style="font-size:12px" aria-hidden="true"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-outline delete-btn" title="Delete"
                                    style="color:var(--danger)"
                                    data-action="{{ route('payment-types.destroy', $type) }}"
                                    data-title="Delete Payment Type"
                                    data-body="Delete &quot;{{ e($type->name) }}&quot;? This cannot be undone.">
                                <i class="fas fa-trash" style="font-size:12px" aria-hidden="true"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state">
                        <i class="fas fa-tags" aria-hidden="true"></i>
                        <p>No payment types found. <a href="{{ route('payment-types.create') }}" style="color:var(--primary)">Add one</a></p>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px 16px;border-top:1px solid var(--border)">
        {{ $paymentTypes->links('vendor.pagination.custom') }}
    </div>
</div>
@endsection
