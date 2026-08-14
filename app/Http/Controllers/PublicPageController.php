<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Service;
use Illuminate\Http\Request;

class PublicPageController extends Controller
{
    public function home()
    {
        $services = Service::where('status', 'active')
            ->latest('service_id')
            ->take(3)
            ->get();

        return view('public.home', compact('services'));
    }

    public function services()
    {
        $services = Service::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('public.services', compact('services'));
    }

    public function service(Service $service)
    {
        abort_unless($service->status === 'active', 404);

        $nextSlots = $service->timeSlots()
            ->where('status', 'open')
            ->whereDate('slot_date', '>=', today())
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        return view('public.service-show', compact('service', 'nextSlots'));
    }

    public function about()
    {
        return view('public.about');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function sendContact(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        return back()->with('success', 'Your enquiry has been sent successfully.');
    }
}
