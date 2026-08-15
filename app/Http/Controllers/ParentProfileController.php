<?php

namespace App\Http\Controllers;

use App\Models\ParentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentProfileController extends Controller
{
    public function show(Request $request): View
    {
        $parentProfile = $this->getParentProfile($request);

        return view('profile.show', compact('parentProfile'));
    }

    public function edit(Request $request): View
    {
        $parentProfile = $this->getParentProfile($request);

        return view('profile.edit', compact('parentProfile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $parentProfile = $this->getParentProfile($request);
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => [
                'nullable',
                'string',
                'max:100',
            ],
            'emergency_contact_phone' => [
                'nullable',
                'string',
                'max:20',
            ],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $parentProfile->update([
            'address' => $validated['address'] ?? null,
            'emergency_contact_name' =>
                $validated['emergency_contact_name'] ?? null,
            'emergency_contact_phone' =>
                $validated['emergency_contact_phone'] ?? null,
        ]);

        return redirect()
            ->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    private function getParentProfile(Request $request): ParentProfile
    {
        abort_unless($request->user()->role === 'parent', 403);

        return $request->user()
            ->parentProfile()
            ->firstOrCreate();
    }
}
