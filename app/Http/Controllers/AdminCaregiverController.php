<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminCaregiverController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $caregivers = User::where('role', 'caregiver')
            ->with('caregiverProfile')
            ->orderBy('name')
            ->get();

        return view('admin.caregivers.index', compact('caregivers'));
    }

    public function create(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.caregivers.create');
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'qualification' => ['required', 'string', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'availability_status' => ['required', 'in:available,unavailable'],
        ]);

        $caregiver = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'caregiver',
            'status' => 'active',
        ]);

        $caregiver->caregiverProfile()->create([
            'qualification' => $validated['qualification'],
            'experience_years' => $validated['experience_years'],
            'specialization' => $validated['specialization'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'availability_status' => $validated['availability_status'],
        ]);

        return redirect()
            ->route('admin.caregivers.show', $caregiver)
            ->with('success', 'Caregiver created successfully.');
    }

    public function show(Request $request, User $caregiver)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if ($caregiver->role !== 'caregiver') {
            abort(404);
        }

        $caregiver->load('caregiverProfile');

        return view('admin.caregivers.show', compact('caregiver'));
    }

    public function edit(Request $request, User $caregiver)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if ($caregiver->role !== 'caregiver') {
            abort(404);
        }

        $caregiver->load('caregiverProfile');

        return view('admin.caregivers.edit', compact('caregiver'));
    }

    public function update(Request $request, User $caregiver)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if ($caregiver->role !== 'caregiver') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $caregiver->id,
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'qualification' => ['required', 'string', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'availability_status' => ['required', 'in:available,unavailable'],
        ]);

        $caregiver->name = $validated['name'];
        $caregiver->email = $validated['email'];
        $caregiver->phone = $validated['phone'] ?? null;

        if (!empty($validated['password'])) {
            $caregiver->password = Hash::make($validated['password']);
        }

        $caregiver->save();

        $caregiver->caregiverProfile->update([
            'qualification' => $validated['qualification'],
            'experience_years' => $validated['experience_years'],
            'specialization' => $validated['specialization'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'availability_status' => $validated['availability_status'],
        ]);

        return redirect()
            ->route('admin.caregivers.show', $caregiver)
            ->with('success', 'Caregiver updated successfully.');
    }

    public function changeStatus(Request $request, User $caregiver)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if ($caregiver->role !== 'caregiver') {
            abort(404);
        }

        if ($caregiver->status === 'active') {
            $caregiver->status = 'inactive';
            $message = 'Caregiver deactivated successfully.';
        } else {
            $caregiver->status = 'active';
            $message = 'Caregiver activated successfully.';
        }

        $caregiver->save();

        return back()->with('success', $message);
    }
}
