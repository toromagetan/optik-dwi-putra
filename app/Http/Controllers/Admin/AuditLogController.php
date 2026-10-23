<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q');
                $query->where(fn ($w) => $w->where('action', 'like', "%{$q}%")->orWhere('entity_type', 'like', "%{$q}%"));
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }
}
