@extends('layouts.app')

@section('title', 'Activity Log')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📋 Activity Log</h1>
            <p class="text-sm text-gray-500">Every change made in the app — who did it, what they did, and when.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">👥 Users &amp; Access →</a>
    </div>

    @include('partials.flash')

    {{-- Filters --}}
    <div class="bg-white shadow-sm rounded-lg p-4 mb-4">
        <form method="GET" action="{{ route('admin.activity') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">User</label>
                <select name="user_id" class="w-full px-2 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">All users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Action</label>
                <select name="action" class="w-full px-2 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">All actions</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}" @selected(request('action') === $a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="w-full px-2 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="w-full px-2 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="name or description"
                       class="w-full px-2 py-2 border border-gray-300 rounded-md text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 text-sm">Filter</button>
                <a href="{{ route('admin.activity') }}" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Clear</a>
            </div>
        </form>
    </div>

    {{-- Log --}}
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b text-sm text-gray-600">
            {{ number_format($logs->total()) }} record(s)
        </div>

        @if($logs->count())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">When</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Who</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">What happened</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($logs as $log)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                {{ $log->logged_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 whitespace-nowrap">
                                {{ $log->user_name ?? '—' }}
                                @unless($log->user_id)
                                    <span class="block text-xs text-gray-400">deleted user</span>
                                @endunless
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $log->description }}</td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap">
                                @php
                                    $destructive = str_contains($log->action, 'destroy');
                                    $signin      = str_starts_with($log->action, 'login') || $log->action === 'logout';
                                @endphp
                                <span class="px-2 py-1 rounded-full font-semibold
                                    {{ $destructive ? 'bg-red-100 text-red-800' : ($signin ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-800') }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $log->ip }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center p-10 text-gray-500">No activity recorded for these filters.</div>
        @endif
    </div>

    <div class="mt-4">{{ $logs->appends(request()->query())->links() }}</div>
</div>
@endsection
