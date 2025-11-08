@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">⚙️ Payment Reminders Settings</h2>
        <a href="{{ route('reminders.index') }}" class="btn btn-secondary">
            ← Back to Reminders
        </a>
    </div>

    {{-- Settings Form --}}
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Grace Period Settings</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('reminders.updateSettings') }}">
                @csrf

                <div class="row g-4">
                    {{-- Weekly Settings --}}
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">📅 Weekly Payments</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Due After (days)</label>
                            <input type="number" 
                                   name="weekly_due_days" 
                                   class="form-control form-control-lg @error('weekly_due_days') is-invalid @enderror" 
                                   value="{{ old('weekly_due_days', $settings['weekly_due_days'] ?? 7) }}"
                                   min="1" max="30" required>
                            <small class="text-muted">Payment is due this many days after last payment</small>
                            @error('weekly_due_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Overdue After (days)</label>
                            <input type="number" 
                                   name="weekly_overdue_days" 
                                   class="form-control form-control-lg @error('weekly_overdue_days') is-invalid @enderror" 
                                   value="{{ old('weekly_overdue_days', $settings['weekly_overdue_days'] ?? 10) }}"
                                   min="1" max="30" required>
                            <small class="text-muted">Payment becomes overdue after this many days</small>
                            @error('weekly_overdue_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <strong>Current:</strong> Due at {{ $settings['weekly_due_days'] ?? 7 }} days, 
                                Overdue at {{ $settings['weekly_overdue_days'] ?? 10 }} days
                                <br>({{ ($settings['weekly_overdue_days'] ?? 10) - ($settings['weekly_due_days'] ?? 7) }} day grace period)
                            </small>
                        </div>
                    </div>

                    {{-- Monthly Settings --}}
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">📅 Monthly Payments</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Due After (days)</label>
                            <input type="number" 
                                   name="monthly_due_days" 
                                   class="form-control form-control-lg @error('monthly_due_days') is-invalid @enderror" 
                                   value="{{ old('monthly_due_days', $settings['monthly_due_days'] ?? 30) }}"
                                   min="1" max="60" required>
                            <small class="text-muted">Payment is due this many days after last payment</small>
                            @error('monthly_due_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Overdue After (days)</label>
                            <input type="number" 
                                   name="monthly_overdue_days" 
                                   class="form-control form-control-lg @error('monthly_overdue_days') is-invalid @enderror" 
                                   value="{{ old('monthly_overdue_days', $settings['monthly_overdue_days'] ?? 33) }}"
                                   min="1" max="60" required>
                            <small class="text-muted">Payment becomes overdue after this many days</small>
                            @error('monthly_overdue_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <strong>Current:</strong> Due at {{ $settings['monthly_due_days'] ?? 30 }} days, 
                                Overdue at {{ $settings['monthly_overdue_days'] ?? 33 }} days
                                <br>({{ ($settings['monthly_overdue_days'] ?? 33) - ($settings['monthly_due_days'] ?? 30) }} day grace period)
                            </small>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        💾 Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Holiday Periods (Phase 1 - Simple) --}}
    <div class="card shadow mt-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">🏖️ Holiday Periods (Coming Soon)</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">
                Holiday management will be available in Phase 2. 
                For now, use the "Skip" button on individual reminders.
            </p>
        </div>
    </div>

    {{-- Module Info --}}
    <div class="card shadow mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">ℹ️ Module Information</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4">Module Status:</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-success">Active</span>
                </dd>

                <dt class="col-sm-4">Update Schedule:</dt>
                <dd class="col-sm-8">Daily at 8:00 AM</dd>

                <dt class="col-sm-4">Manual Refresh:</dt>
                <dd class="col-sm-8">
                    @if(config('reminders.features.manual_refresh'))
                        <span class="badge bg-success">Enabled</span>
                    @else
                        <span class="badge bg-secondary">Disabled</span>
                    @endif
                </dd>

                <dt class="col-sm-4">Payment Redirect:</dt>
                <dd class="col-sm-8">Opens in new tab (tablet-friendly)</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
