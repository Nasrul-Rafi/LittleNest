<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\TimeSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminTimeSlotController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAdmin($request);

        $timeSlots = TimeSlot::with('service')
            ->withCount([
                'bookings as active_bookings_count' => function ($query) {
                    $query->whereIn('status', ['pending', 'confirmed']);
                },
            ])
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();

        return view(
            'admin.time-slots.index',
            compact('timeSlots')
        );
    }

    public function create(Request $request): View
    {
        $this->ensureAdmin($request);

        $services = Service::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view(
            'admin.time-slots.create',
            compact('services')
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $validated = $this->validateTimeSlot($request);

        $service = Service::whereKey($validated['service_id'])
            ->where('status', 'active')
            ->first();

        if (!$service) {
            return back()
                ->withErrors([
                    'service_id' => 'Please select an active service.',
                ])
                ->withInput();
        }

        $timeSlot = TimeSlot::create($validated);

        return redirect()
            ->route('admin.time-slots.index')
            ->with(
                'success',
                'Time slot created successfully.'
            );
    }

    public function edit(
        Request $request,
        TimeSlot $timeSlot
    ): View {
        $this->ensureAdmin($request);

        $services = Service::where('status', 'active')
            ->orWhere('service_id', $timeSlot->service_id)
            ->orderBy('name')
            ->get();

        return view(
            'admin.time-slots.edit',
            compact('timeSlot', 'services')
        );
    }

    public function update(
        Request $request,
        TimeSlot $timeSlot
    ): RedirectResponse {
        $this->ensureAdmin($request);

        $validated = $this->validateTimeSlot(
            $request,
            $timeSlot
        );

        $activeBookings = $timeSlot->activeBookingsCount();

        if ((int) $validated['capacity'] < $activeBookings) {
            return back()
                ->withErrors([
                    'capacity' =>
                        'Capacity cannot be lower than the number of active bookings.',
                ])
                ->withInput();
        }

        if ($activeBookings > 0) {
            $scheduleChanged =
                (int) $validated['service_id'] !== $timeSlot->service_id
                || $validated['slot_date'] !== $timeSlot->slot_date->format('Y-m-d')
                || $validated['start_time'] !== substr($timeSlot->start_time, 0, 5)
                || $validated['end_time'] !== substr($timeSlot->end_time, 0, 5);

            if ($scheduleChanged) {
                return back()
                    ->withErrors([
                        'slot_date' =>
                            'Service, date and time cannot be changed after bookings use this slot.',
                    ])
                    ->withInput();
            }
        }

        $timeSlot->update($validated);

        return redirect()
            ->route('admin.time-slots.index')
            ->with(
                'success',
                'Time slot updated successfully.'
            );
    }

    public function changeStatus(
        Request $request,
        TimeSlot $timeSlot
    ): RedirectResponse {
        $this->ensureAdmin($request);

        $newStatus = $timeSlot->status === 'open'
            ? 'closed'
            : 'open';

        $timeSlot->update([
            'status' => $newStatus,
        ]);

        return redirect()
            ->route('admin.time-slots.index')
            ->with(
                'success',
                'Time slot status updated successfully.'
            );
    }

    private function validateTimeSlot(
        Request $request,
        ?TimeSlot $timeSlot = null
    ): array {
        $uniqueSchedule = Rule::unique('time_slots')
            ->where(function ($query) use ($request) {
                return $query
                    ->where('service_id', $request->input('service_id'))
                    ->where('slot_date', $request->input('slot_date'))
                    ->where('start_time', $request->input('start_time'))
                    ->where('end_time', $request->input('end_time'));
            });

        if ($timeSlot) {
            $uniqueSchedule->ignore(
                $timeSlot->slot_id,
                'slot_id'
            );
        }

        return $request->validate([
            'service_id' => [
                'required',
                'integer',
                'exists:services,service_id',
                $uniqueSchedule,
            ],
            'slot_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'capacity' => [
                'required',
                'integer',
                'min:1',
                'max:500',
            ],
            'status' => [
                'required',
                'in:open,closed',
            ],
        ], [
            'service_id.unique' =>
                'This service already has the same time slot.',
            'end_time.after' =>
                'Please select an end time that is later than the start time.',
        ]);
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }
}
