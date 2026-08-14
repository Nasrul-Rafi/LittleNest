<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminParentController extends Controller
{
    public function index(Request $request)
    {
        $this->adminOnly($request);

        $parents = User::with('parentProfile.children')
            ->where('role', 'parent')
            ->orderBy('name')
            ->get();

        return view('admin.parents.index', compact('parents'));
    }

    public function show(Request $request, User $parent)
    {
        $this->adminOnly($request);
        abort_unless($parent->role === 'parent', 404);

        $parent->load([
            'parentProfile.children.bookings.service',
        ]);

        return view('admin.parents.show', compact('parent'));
    }

    private function adminOnly(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }
}
