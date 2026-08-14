<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;

class AdminChildController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $children = Child::with('parentProfile.user')
            ->orderBy('full_name')
            ->get();

        return view('admin.children.index', compact('children'));
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
