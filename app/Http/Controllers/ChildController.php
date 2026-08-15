<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\ParentProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildController extends Controller
{
    public function index(Request $request): View
    {
        $parentProfile = $this->getParentProfile($request);

        $children = $parentProfile->children()
            ->latest('child_id')
            ->get();

        return view('children.index', compact('children'));
    }

    public function create(Request $request): View
    {
        $this->getParentProfile($request);

        return view('children.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $parentProfile = $this->getParentProfile($request);
        $validated = $this->validateChild($request);

        $child = $parentProfile->children()->create($validated);

        return redirect()
            ->route('children.show', $child)
            ->with('success', 'Child profile created successfully.');
    }

    public function show(Request $request, Child $child): View
    {
        $parentProfile = $this->getParentProfile($request);
        $this->ensureOwnership($child, $parentProfile);

        return view('children.show', compact('child'));
    }

    public function edit(Request $request, Child $child): View
    {
        $parentProfile = $this->getParentProfile($request);
        $this->ensureOwnership($child, $parentProfile);

        return view('children.edit', compact('child'));
    }

    public function update(
        Request $request,
        Child $child
    ): RedirectResponse {
        $parentProfile = $this->getParentProfile($request);
        $this->ensureOwnership($child, $parentProfile);

        $validated = $this->validateChild($request);
        $child->update($validated);

        return redirect()
            ->route('children.show', $child)
            ->with('success', 'Child profile updated successfully.');
    }

    public function destroy(
        Request $request,
        Child $child
    ): RedirectResponse {
        $parentProfile = $this->getParentProfile($request);
        $this->ensureOwnership($child, $parentProfile);

        $child->delete();

        return redirect()
            ->route('children.index')
            ->with('success', 'Child profile deleted successfully.');
    }

    private function getParentProfile(Request $request): ParentProfile
    {
        abort_unless($request->user()->role === 'parent', 403);

        return $request->user()
            ->parentProfile()
            ->firstOrCreate();
    }

    private function ensureOwnership(
        Child $child,
        ParentProfile $parentProfile
    ): void {
        abort_unless(
            $child->parent_profile_id === $parentProfile->parent_profile_id,
            403
        );
    }

    private function validateChild(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'date_of_birth' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'gender' => ['nullable', 'in:male,female,other'],
            'guardian_relation' => ['nullable', 'string', 'max:50'],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
            'medicine_instructions' => ['nullable', 'string', 'max:2000'],
            'special_needs' => ['nullable', 'string', 'max:2000'],
            'emergency_notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
