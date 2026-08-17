<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\TimeSlot;
use Illuminate\Http\Request;

class ParentServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::where('status', 'active')
            ->withCount([
                'timeSlots as available_slots_count' => function ($query) {
                    $query->where('status', 'open')
                        ->whereDate('slot_date', '>=', today());
                },
            ])
            ->orderBy('name')
            ->get();

        return view('parent.services.index', compact('services'));
    }

    public function show(Request $request, Service $service)
    {
        abort_unless($service->status === 'active', 404);

        $timeSlots = TimeSlot::with('service')
            ->withCount([
                'bookings as active_bookings_count' => function ($query) {
                    $query->whereIn('status', ['pending', 'confirmed']);
                },
            ])
            ->where('service_id', $service->service_id)
            ->where('status', 'open')
            ->whereDate('slot_date', '>=', today())
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get()
            ->filter(function (TimeSlot $timeSlot) {
                return $timeSlot->isBookable();
            })
            ->values();

        return view('parent.services.show', compact('service', 'timeSlots'));
    }
}
