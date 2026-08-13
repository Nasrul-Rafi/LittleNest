<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class AdminServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $services = Service::withCount('bookings')
            ->orderBy('name')
            ->get();

        return view('admin.services.index', compact('services'));
    }

    public function create(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:services,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $service = Service::create($validated);

        return redirect()
            ->route('admin.services.show', $service)
            ->with('success', 'Service created successfully.');
    }

    public function show(Request $request, Service $service)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $service->loadCount('bookings');

        return view('admin.services.show', compact('service'));
    }

    public function edit(Request $request, Service $service)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:services,name,'
                    . $service->service_id
                    . ',service_id',
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $service->update($validated);

        return redirect()
            ->route('admin.services.show', $service)
            ->with('success', 'Service updated successfully.');
    }

    public function changeStatus(Request $request, Service $service)
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        if ($service->status === 'active') {
            $service->status = 'inactive';
            $message = 'Service deactivated successfully.';
        } else {
            $service->status = 'active';
            $message = 'Service activated successfully.';
        }

        $service->save();

        return back()->with('success', $message);
    }
}
