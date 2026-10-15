<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $r)
    {
        $query = ActivityLog::query()->orderByDesc('id');

        if ($r->filled('user_id')) {
            $query->where('user_id', $r->input('user_id'));
        }
        if ($r->filled('action')) {
            $query->where('action', 'like', '%'.$r->input('action').'%');
        }
        if ($r->filled('search')) {
            $search = $r->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }
        if ($r->filled('from')) {
            $query->whereDate('created_at', '>=', $r->input('from'));
        }
        if ($r->filled('to')) {
            $query->whereDate('created_at', '<=', $r->input('to'));
        }

        return view('admin.activity.index', [
            'logs'    => $query->paginate(50)->withQueryString(),
            'users'   => User::orderBy('name')->get(['id', 'name']),
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
