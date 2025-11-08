<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Reminders Module
    |--------------------------------------------------------------------------
    |
    | This configuration controls the Payment Reminders module.
    | Set 'enabled' to false to completely disable the module.
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Settings (if not in database)
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'weekly' => [
            'due_after_days' => 7,
            'overdue_after_days' => 10,
        ],
        'monthly' => [
            'due_after_days' => 30,
            'overdue_after_days' => 33,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    */

    'features' => [
        'show_upcoming' => false,          // Hide upcoming section (Phase 1)
        'allow_skip' => true,              // Allow skip this week
        'allow_adjust' => false,           // Disable amount adjustment (Phase 1)
        'batch_actions' => false,          // No batch processing yet
        'manual_refresh' => true,          // Show refresh button
    ],

    /*
    |--------------------------------------------------------------------------
    | Display
    |--------------------------------------------------------------------------
    */

    'display' => [
        'items_per_page' => 50,
        'show_paid_students' => false,
        'badge_count' => 'overdue',        // 'overdue', 'due', or 'all'
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduling
    |--------------------------------------------------------------------------
    */

    'schedule' => [
        'time' => '08:00',                 // Daily calculation time
        'timezone' => 'UTC',
    ],

    /*
    |--------------------------------------------------------------------------
    | Prefill Settings
    |--------------------------------------------------------------------------
    */

    'prefill' => [
        'open_in_new_tab' => true,
        'default_purpose' => 'tuition',
        'auto_calculate_period' => true,
    ],
];
