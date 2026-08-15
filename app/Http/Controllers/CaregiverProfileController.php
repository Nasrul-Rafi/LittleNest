<?php

namespace App\Http\Controllers;

use App\Models\CaregiverProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CaregiverProfileController extends Controller
{
    public function show(Request $request): View
    {
        $caregiverProfile = $this->getCaregiverProfile($request);

        return view(
            'caregiver.profile.show',
            compact('caregiverProfile')
        );
    }

    public function edit(Request $request): View
    {
        $caregiverProfile = $this->getCaregiverProfile($request);

        return view(
            'caregiver.profile.edit',
            compact('caregiverProfile')
        );
    }

    public function update(Request $request): RedirectResponse
    {
        $caregiverProfile = $this->getCaregiverProfile($request);
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
            'qualification' => ['required', 'string', 'max:255'],
            'experience_years' => [
                'required',
                'integer',
                'min:0',
                'max:60',
            ],
            'specialization' => ['nullable', 'string', 'max:255'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'availability_status' => [
                'required',
                'in:available,unavailable',
            ],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $caregiverProfile->update([
            'qualification' => $validated['qualification'],
            'experience_years' => $validated['experience_years'],
            'specialization' => $validated['specialization'] ?? null,
            'skills' => $validated['skills'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'availability_status' =>
                $validated['availability_status'],
        ]);

        return redirect()
            ->route('caregiver.profile.show')
            ->with('success', 'Caregiver profile updated successfully.');
    }

    private function getCaregiverProfile(
        Request $request
    ): CaregiverProfile {
        abort_unless(
            $request->user()->role === 'caregiver'
                && $request->user()->status === 'active',
            403
        );

        return $request->user()
            ->caregiverProfile()
            ->firstOrFail();
    }
}
