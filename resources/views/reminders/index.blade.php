@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="mb-0">💰 Payment Reminders</h2>
        <div class="d-flex gap-2 flex-wrap">
            @if(config('reminders.features.manual_refresh'))
                <form action="{{ route('reminders.refresh') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg">
                        🔄 Refresh Now
                    </button>
                </form>
            @endif
            <button onclick="window.print()" class="btn btn-secondary btn-lg">
                🖨️ Print
            </button>
            <a href="{{ route('reminders.settings') }}" class="btn btn-outline-primary btn-lg">
                ⚙️ Settings
            </a>
        </div>
    </div>

    {{-- Last Updated --}}
    @if($lastCalculated)
        <div class="alert alert-info">
            <small>Last updated: {{ $lastCalculated->format('d/m/Y H:i') }} | Next update: Tomorrow at 8:00 AM</small>
        </div>
    @endif

    {{-- Summary Cards --}}
    <div class="row mb-4 g-3">
        <div class="col-12 col-md-6">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <h5 class="text-danger mb-2">🔴 Overdue</h5>
                    <h2 class="mb-1">{{ $summary['overdue_count'] }}</h2>
                    <small class="text-muted">Total: £{{ number_format($summary['overdue_total'], 2) }}</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <h5 class="text-warning mb-2">⚠️ Due Now</h5>
                    <h2 class="mb-1">{{ $summary['due_count'] }}</h2>
                    <small class="text-muted">Total: £{{ number_format($summary['due_total'], 2) }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Overdue Section --}}
    @if($overdue->count() > 0)
        <div class="card mb-4 border-danger shadow">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">🔴 Overdue Payments ({{ $overdue->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-3">Reference</th>
                                <th class="px-3 py-3">Student(s)</th>
                                <th class="px-3 py-3 text-end">Amount</th>
                                <th class="px-3 py-3">Last Paid</th>
                                <th class="px-3 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdue as $reminder)
                            <tr>
                                <td class="px-3 py-3">
                                    <strong class="fs-5">{{ $reminder->reference }}</strong>
                                </td>
                                <td class="px-3 py-3">
                                    @if(is_array($reminder->student_names))
                                        @foreach($reminder->student_names as $name)
                                            <div>{{ $name }}</div>
                                        @endforeach
                                    @else
                                        {{ $reminder->formatted_names }}
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-end">
                                    <strong class="fs-5 text-danger">£{{ number_format($reminder->expected_amount, 2) }}</strong>
                                </td>
                                <td class="px-3 py-3">
                                    @if($reminder->last_payment_date)
                                        {{ $reminder->last_payment_date->format('d/m/Y') }}<br>
                                        <small class="text-danger fw-bold">({{ $reminder->days_since_payment }} days ago)</small>
                                    @else
                                        <span class="text-danger fw-bold">Never paid</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                        <a href="/payments?ref={{ $reminder->reference }}&prefill=1" 
                                           target="_blank"
                                           class="btn btn-success btn-lg px-4">
                                            💰 Record Payment
                                        </a>
                                        <form action="{{ route('reminders.skip', $reminder->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-lg" 
                                                    onclick="return confirm('Skip this reminder?')">
                                                ⏭️ Skip
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-success">
            <strong>✓ No overdue payments!</strong> All students are up to date.
        </div>
    @endif

    {{-- Due Now Section --}}
    @if($due->count() > 0)
        <div class="card mb-4 border-warning shadow">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">⚠️ Due This Week ({{ $due->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 py-3">Reference</th>
                                <th class="px-3 py-3">Student(s)</th>
                                <th class="px-3 py-3 text-end">Amount</th>
                                <th class="px-3 py-3">Last Paid</th>
                                <th class="px-3 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($due as $reminder)
                            <tr>
                                <td class="px-3 py-3">
                                    <strong class="fs-5">{{ $reminder->reference }}</strong>
                                </td>
                                <td class="px-3 py-3">
                                    @if(is_array($reminder->student_names))
                                        @foreach($reminder->student_names as $name)
                                            <div>{{ $name }}</div>
                                        @endforeach
                                    @else
                                        {{ $reminder->formatted_names }}
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-end">
                                    <strong class="fs-5 text-warning">£{{ number_format($reminder->expected_amount, 2) }}</strong>
                                </td>
                                <td class="px-3 py-3">
                                    @if($reminder->last_payment_date)
                                        {{ $reminder->last_payment_date->format('d/m/Y') }}<br>
                                        <small class="text-muted">({{ $reminder->days_since_payment }} days ago)</small>
                                    @else
                                        <span class="text-muted">Never paid</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                        <a href="/payments?ref={{ $reminder->reference }}&prefill=1" 
                                           target="_blank"
                                           class="btn btn-success btn-lg px-4">
                                            💰 Record Payment
                                        </a>
                                        <form action="{{ route('reminders.skip', $reminder->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-lg"
                                                    onclick="return confirm('Skip this reminder?')">
                                                ⏭️ Skip
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <strong>ℹ️ No payments due this week.</strong>
        </div>
    @endif

    {{-- Empty State --}}
    @if($overdue->count() == 0 && $due->count() == 0)
        <div class="text-center py-5">
            <h3 class="text-success">🎉 All Caught Up!</h3>
            <p class="text-muted">No payment reminders at this time.</p>
        </div>
    @endif
</div>

{{-- Print Styles --}}
<style>
    @media print {
        .btn, .alert-info, .card-header { display: none !important; }
        .card { break-inside: avoid; }
        body { font-size: 12px; }
    }
    
    /* Tablet Touch Targets */
    @media (min-width: 768px) and (max-width: 1024px) {
        .btn-lg {
            min-width: 120px;
            padding: 12px 20px;
        }
    }
</style>
@endsection
