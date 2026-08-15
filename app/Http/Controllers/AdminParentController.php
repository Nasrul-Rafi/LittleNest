<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminParentController extends Controller
{
    public function index(Request $request)
    {
        $this->adminOnly($request);

        $query = User::with('parentProfile.children')
            ->where('role', 'parent');

        $search = trim((string) $request->input('search'));
        $status = $request->input('status');

        if ($search !== '') {
            $query->where(function ($parentQuery) use ($search) {
                $parentQuery
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        $parents = $query
            ->orderBy('name')
            ->get();

        return view('admin.parents.index', compact(
            'parents',
            'search',
            'status'
        ));
    }

    public function create(Request $request)
    {
        $this->adminOnly($request);

        return view('admin.parents.create');
    }

    public function store(Request $request)
    {
        $this->adminOnly($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $parent = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'parent',
            'status' => 'active',
        ]);

        $parent->parentProfile()->create([
            'address' => $validated['address'] ?? null,
            'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
        ]);

        return redirect()
            ->route('admin.parents.show', $parent)
            ->with('success', 'Parent account created successfully.');
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

    public function edit(Request $request, User $parent)
    {
        $this->adminOnly($request);
        abort_unless($parent->role === 'parent', 404);

        $parent->load('parentProfile');

        return view('admin.parents.edit', compact('parent'));
    }

    public function update(Request $request, User $parent)
    {
        $this->adminOnly($request);
        abort_unless($parent->role === 'parent', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $parent->id,
            ],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $parent->name = $validated['name'];
        $parent->email = $validated['email'];
        $parent->phone = $validated['phone'];

        if (!empty($validated['password'])) {
            $parent->password = Hash::make($validated['password']);
        }

        $parent->save();

        $parent->parentProfile()->updateOrCreate(
            ['user_id' => $parent->id],
            [
                'address' => $validated['address'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.parents.show', $parent)
            ->with('success', 'Parent account updated successfully.');
    }

    public function changeStatus(Request $request, User $parent)
    {
        $this->adminOnly($request);
        abort_unless($parent->role === 'parent', 404);

        if ($parent->status === 'active') {
            $parent->status = 'inactive';
            $message = 'Parent account deactivated successfully.';
        } else {
            $parent->status = 'active';
            $message = 'Parent account activated successfully.';
        }

        $parent->save();

        return back()->with('success', $message);
    }

    private function adminOnly(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }
}
