<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicInquiryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_available(): void
    {
        $service = Service::create([
            'name' => 'Public Test Care',
            'description' => 'A test care service.',
            'price' => 900,
            'duration_minutes' => 120,
            'status' => 'active',
        ]);

        $this->get(route('home'))->assertOk()->assertSee('Safe care');
        $this->get(route('public.services'))->assertOk()->assertSee($service->name);
        $this->get(route('public.services.show', $service))->assertOk()->assertSee($service->name);
        $this->get(route('about'))->assertOk();
        $this->get(route('contact'))->assertOk();
    }

    public function test_guest_can_submit_contact_inquiry(): void
    {
        $this->post(route('contact.store'), [
            'full_name' => 'Ayesha Rahman',
            'email' => 'ayesha@example.com',
            'phone' => '01700000000',
            'subject' => 'Booking question',
            'message' => 'Please tell me about weekend availability.',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ayesha@example.com',
            'subject' => 'Booking question',
            'status' => 'new',
        ]);
    }

    public function test_admin_can_review_and_resolve_inquiry(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $message = ContactMessage::create([
            'full_name' => 'Parent User',
            'email' => 'parent@example.com',
            'subject' => 'Care enquiry',
            'message' => 'Need more information.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.inquiries.show', $message))
            ->assertOk();

        $this->assertDatabaseHas('contact_messages', [
            'message_id' => $message->message_id,
            'status' => 'open',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.inquiries.status', $message), [
                'status' => 'resolved',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'message_id' => $message->message_id,
            'status' => 'resolved',
        ]);
    }
}
