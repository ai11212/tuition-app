@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Payment Verification & Tracking</h1>
        <a href="{{ route('accounts.summary') }}" class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50 text-sm">
            ← Back to Accounts
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-xl border bg-white">
            <div class="text-sm text-gray-600">Total Students</div>
            <div class="text-3xl font-semibold mt-1">{{ $summary['total_students'] }}</div>
        </div>
        <div class="p-4 rounded-xl border bg-green-50">
            <div class="text-sm text-green-700">Paid Up</div>
            <div class="text-3xl font-semibold mt-1 text-green-700">{{ $summary['paid_up'] }}</div>
        </div>
        <div class="p-4 rounded-xl border bg-orange-50">
            <div class="text-sm text-orange-700">Pending</div>
            <div class="text-3xl font-semibold mt-1 text-orange-700">{{ $summary['pending'] }}</div>
            <div class="text-xs text-orange-600 mt-1">£{{ number_format($summary['total_pending'], 2) }} outstanding</div>
        </div>
        <div class="p-4 rounded-xl border bg-blue-50">
            <div class="text-sm text-blue-700">Total Expected</div>
            <div class="text-2xl font-semibold mt-1 text-blue-700">£{{ number_format($summary['total_expected'], 2) }}</div>
            <div class="text-xs text-blue-600 mt-1">Received: £{{ number_format($summary['total_received'], 2) }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="grid md:grid-cols-4 gap-3 mb-6 bg-white p-4 rounded-xl border">
        <div class="md:col-span-2">
            <label class="text-sm text-gray-600">Search Student</label>
            <input type="text" name="search" value="{{ $search }}" class="w-full mt-1 rounded-lg border border-gray-300 px-3 py-2" placeholder="Reference or name...">
        </div>
        <div>
            <label class="text-sm text-gray-600">Payment Status</label>
            <select name="status" class="w-full mt-1 rounded-lg border border-gray-300 px-3 py-2">
                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Students</option>
                <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid Up</option>
                <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Payment Pending</option>
                <option value="overpaid" {{ $status == 'overpaid' ? 'selected' : '' }}>Overpaid</option>
            </select>
        </div>
        <div class="flex items-end">
            <button class="w-full px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Filter</button>
        </div>
    </form>

    {{-- Students Table --}}
    <div class="rounded-xl border bg-white overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left">Reference</th>
                    <th class="px-4 py-3 text-right">Expected</th>
                    <th class="px-4 py-3 text-right">Paid</th>
                    <th class="px-4 py-3 text-right">Balance</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-left">Last Payment</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $student->reference }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="font-medium">£{{ number_format($student->expected_total, 2) }}</div>
                            <div class="text-xs text-gray-500">
                                Payment: £{{ number_format($student->payment, 2) }}
                                @if($student->deposit > 0)
                                    <br><span class="text-gray-400">(Deposit: £{{ number_format($student->deposit, 2) }} - reference only)</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-green-700">
                            £{{ number_format($student->total_paid, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $student->balance > 10 ? 'text-orange-600' : ($student->balance < -10 ? 'text-blue-600' : 'text-green-600') }}">
                            {{ $student->balance > 0 ? '-' : '+' }}£{{ number_format(abs($student->balance), 2) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($student->status == 'paid')
                                <span class="inline-block px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-medium">
                                    ✓ Paid Up
                                </span>
                            @elseif($student->status == 'pending')
                                <span class="inline-block px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800 font-medium">
                                    ⚠ Pending
                                </span>
                            @else
                                <span class="inline-block px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">
                                    💰 Overpaid
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $student->last_payment_date ? \Carbon\Carbon::parse($student->last_payment_date)->format('d/m/Y') : 'No payments' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('payments') }}?ref={{ $student->reference }}" 
                               class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                View Payments →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            No students found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Legend --}}
    <div class="mt-6 p-4 bg-gray-50 rounded-lg text-sm">
        <div class="font-medium text-gray-700 mb-2">Legend:</div>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <span class="text-green-700 font-medium">✓ Paid Up:</span> Balance within ±£10
            </div>
            <div>
                <span class="text-orange-700 font-medium">⚠ Pending:</span> Outstanding balance > £10
            </div>
            <div>
                <span class="text-blue-700 font-medium">💰 Overpaid:</span> Excess payment > £10
            </div>
        </div>
        <div class="mt-2 text-xs text-gray-600">
            <strong>Expected</strong> = Payment (from student record) | 
            <strong>Paid</strong> = Total of all payment transactions | 
            <strong>Balance</strong> = Pending Amount (manually tracked amount owed)
        </div>
    </div>
</div>
@endsection
