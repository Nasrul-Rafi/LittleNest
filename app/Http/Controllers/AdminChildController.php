<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;

class AdminChildController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $query = Child::with('parentProfile.user');

        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        if ($search !== '') {
            $query->where(function ($childQuery) use ($search) {
                $childQuery
                    ->where('full_name', 'like', '%' . $search . '%')
                    ->orWhereHas('parentProfile.user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        $children = $query
            ->orderBy('full_name')
            ->get();

        return view('admin.children.index', compact(
            'children',
            'search',
            'status'
        ));
    }

    public function show(Request $request, Child $child)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $child->load([
            'parentProfile.user',
            'bookings.service',
        ]);

        return view('admin.children.show', compact('child'));
    }
}
