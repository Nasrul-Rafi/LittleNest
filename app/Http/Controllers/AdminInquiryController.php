<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class AdminInquiryController extends Controller
{
    public function index(Request $request)
    {
        $this->adminOnly($request);

        $query = ContactMessage::query()->latest('message_id');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $messages = $query->get();

        return view('admin.inquiries.index', compact('messages'));
    }

    public function show(Request $request, ContactMessage $message)
    {
        $this->adminOnly($request);

        if ($message->status === 'new') {
            $message->update(['status' => 'open']);
        }

        return view('admin.inquiries.show', compact('message'));
    }

    public function updateStatus(Request $request, ContactMessage $message)
    {
        $this->adminOnly($request);

        $validated = $request->validate([
            'status' => ['required', 'in:new,open,resolved'],
        ]);

        $message->update($validated);

        return back()->with('success', 'Inquiry status updated.');
    }

    private function adminOnly(Request $request): void
    {
        abort_unless($request->user()->role === 'admin', 403);
    }
}
