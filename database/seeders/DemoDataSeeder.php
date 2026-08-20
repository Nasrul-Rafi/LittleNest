<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\CaregiverAssignment;
use App\Models\Child;
use App\Models\ChildActivity;
use App\Models\ContactMessage;
use App\Models\ParentProfile;
use App\Models\Payment;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    private string $password = '12345678';

    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::where('email', 'admin@littlenest.test')->firstOrFail();
            $parents = $this->seedParents();
            $caregivers = $this->seedCaregivers();
            $children = $this->seedChildren($parents);
            $services = Service::whereIn('name', [
                'Full-Day Care',
                'Half-Day Care',
                'Hourly Care',
                'Weekend Care',
                'Learning & Play',
                'Emergency Care',
            ])->get()->keyBy('name');
            $slots = $this->seedTimeSlots($services);
            $bookings = $this->seedBookings($children, $slots);
            $assignments = $this->seedAssignments($bookings, $caregivers, $admin);
            $this->seedActivities($bookings['completed'], $assignments);
            $this->seedPayments($bookings);
            $this->seedBookingRequests($bookings, $slots, $admin);
            $this->seedContactMessages();
        });
    }

    private function seedParents(): array
    {
        $parentData = [
            ['Ayesha Rahman', '01711000001', 'Dhanmondi, Dhaka', 'Mahmud Rahman', '01811000001'],
            ['Farhana Islam', '01711000002', 'Mohammadpur, Dhaka', 'Rashed Islam', '01811000002'],
            ['Nabila Chowdhury', '01711000003', 'Mirpur, Dhaka', 'Samiul Chowdhury', '01811000003'],
            ['Sadia Ahmed', '01711000004', 'Uttara, Dhaka', 'Imran Ahmed', '01811000004'],
            ['Nusrat Karim', '01711000005', 'Bashundhara, Dhaka', 'Adnan Karim', '01811000005'],
            ['Tasnim Hasan', '01711000006', 'Banani, Dhaka', 'Fahim Hasan', '01811000006'],
            ['Samira Hossain', '01711000007', 'Badda, Dhaka', 'Nayeem Hossain', '01811000007'],
            ['Rukaiya Sultana', '01711000008', 'Lalmatia, Dhaka', 'Arman Sultana', '01811000008'],
            ['Mahira Khan', '01711000009', 'Wari, Dhaka', 'Sakib Khan', '01811000009'],
            ['Jannatul Ferdous', '01711000010', 'Shantinagar, Dhaka', 'Rezaul Ferdous', '01811000010'],
            ['Rima Akter', '01711000011', 'Khilgaon, Dhaka', 'Tanvir Akter', '01811000011'],
            ['Sharmin Yasmin', '01711000012', 'Rampura, Dhaka', 'Kamal Yasmin', '01811000012'],
        ];

        $parents = [];

        foreach ($parentData as $index => $data) {
            $number = $index + 1;
            $user = User::updateOrCreate(
                ['email' => 'parent' . $number . '@littlenest.test'],
                [
                    'name' => $data[0],
                    'phone' => $data[1],
                    'password' => Hash::make($this->password),
                    'role' => 'parent',
                    'status' => $number === 12 ? 'inactive' : 'active',
                ]
            );

            ParentProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'address' => $data[2],
                    'emergency_contact_name' => $data[3],
                    'emergency_contact_phone' => $data[4],
                ]
            );

            $parents[] = $user->fresh('parentProfile');
        }

        return $parents;
    }

    private function seedCaregivers(): array
    {
        $caregiverData = [
            ['Sarah Ahmed', '01812000001', 'Diploma in Early Childhood Care', 6, 'Early Childhood Care', 'Child development, learning activities, first aid', 'Experienced in structured care routines and early learning support.'],
            ['Nusrat Jahan', '01812000002', 'Child Development', 5, 'Child Development', 'Behavioural support, creative play, meal supervision', 'Focused on child development, communication and positive behaviour support.'],
            ['Shamima Akter', '01812000003', 'Early Childhood Education', 4, 'Early Learning', 'Early learning, storytelling, creative play', 'Supports early learning through structured play and age-appropriate activities.'],
            ['Tanvir Hasan', '01812000004', 'First Aid Certified', 3, 'Safety and Care', 'First aid, health monitoring, outdoor play', 'Provides safe supervision with strong attention to health and daily routines.'],
            ['Meher Nigar', '01812000005', 'Diploma in Child Care', 7, 'Meal and Routine Care', 'Meal supervision, nap routines, child development', 'Experienced in daily care routines, meal support and rest supervision.'],
            ['Sohana Rahman', '01812000006', 'Child Psychology and Development', 8, 'Behavioural Support', 'Behavioural support, mood observation, learning activities', 'Works with children using calm communication and development-focused care.'],
            ['Imran Kabir', '01812000007', 'Early Childhood Care', 4, 'Activity Support', 'Creative play, learning activities, first aid', 'Provides activity-focused care with attention to safety and engagement.'],
            ['Nafisa Chowdhury', '01812000008', 'Child Development', 2, 'Early Learning', 'Learning activities, creative play, meal supervision', 'Supports children through guided play and simple learning routines.'],
        ];

        $caregivers = [];

        foreach ($caregiverData as $index => $data) {
            $number = $index + 1;
            $user = User::updateOrCreate(
                ['email' => 'caregiver' . $number . '@littlenest.test'],
                [
                    'name' => $data[0],
                    'phone' => $data[1],
                    'password' => Hash::make($this->password),
                    'role' => 'caregiver',
                    'status' => $number === 8 ? 'inactive' : 'active',
                ]
            );

            $user->caregiverProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'qualification' => $data[2],
                    'experience_years' => $data[3],
                    'specialization' => $data[4],
                    'skills' => $data[5],
                    'bio' => $data[6],
                    'availability_status' => $number === 8 ? 'unavailable' : 'available',
                ]
            );

            $caregivers[] = $user->fresh('caregiverProfile');
        }

        return $caregivers;
    }

    private function seedChildren(array $parents): array
    {
        $childData = [
            ['Ariana Rahman', 4, 'female', 'Mother', 'Peanuts', null, null, null, 'Call guardian immediately for allergic reaction.'],
            ['Ayaan Rahman', 2, 'male', 'Mother', null, null, null, null, null],
            ['Adam Islam', 5, 'male', 'Mother', null, 'Mild asthma', 'Keep inhaler available if breathing becomes difficult.', null, 'Contact guardian if wheezing continues.'],
            ['Anika Islam', 3, 'female', 'Mother', 'Lactose intolerance', null, null, null, 'Avoid regular milk products.'],
            ['Maliha Chowdhury', 4, 'female', 'Mother', null, null, null, null, null],
            ['Rayan Chowdhury', 6, 'male', 'Mother', 'Dust allergy', null, null, null, 'Keep away from dusty play areas.'],
            ['Zara Ahmed', 3, 'female', 'Mother', null, null, null, null, null],
            ['Sami Ahmed', 7, 'male', 'Mother', null, null, null, 'Needs extra time during group transitions.', null],
            ['Nafisa Karim', 5, 'female', 'Mother', 'Egg allergy', null, null, null, 'Check meal ingredients before serving.'],
            ['Arham Karim', 2, 'male', 'Mother', null, null, null, null, null],
            ['Ira Hasan', 4, 'female', 'Mother', null, 'Seasonal cough', 'Give prescribed syrup only if written approval is provided.', null, 'Inform guardian if coughing increases.'],
            ['Zayan Hasan', 6, 'male', 'Mother', null, null, null, null, null],
            ['Rida Hossain', 3, 'female', 'Mother', 'Peanuts', null, null, null, 'Avoid all peanut-containing snacks.'],
            ['Rafid Hossain', 8, 'male', 'Mother', null, null, null, null, null],
            ['Maira Sultana', 5, 'female', 'Mother', null, null, null, null, null],
            ['Abeer Sultana', 3, 'male', 'Mother', null, null, null, 'Prefers quiet transition before nap.', null],
            ['Inaya Khan', 4, 'female', 'Mother', 'Lactose intolerance', null, null, null, 'Use lactose-free meal option.'],
            ['Rayhan Khan', 7, 'male', 'Mother', null, null, null, null, null],
            ['Nusaiba Ferdous', 2, 'female', 'Mother', null, null, null, null, 'Call guardian for any unusual fever.'],
            ['Adib Ferdous', 5, 'male', 'Mother', null, 'Mild eczema', 'Use guardian-provided cream only when instructed.', null, null],
            ['Afsana Akter', 4, 'female', 'Mother', null, null, null, null, null],
            ['Tahmid Akter', 6, 'male', 'Mother', 'Seafood allergy', null, null, null, 'Avoid seafood completely.'],
            ['Mim Yasmin', 3, 'female', 'Mother', null, null, null, null, null],
            ['Saad Yasmin', 5, 'male', 'Mother', null, null, null, null, null],
        ];

        $children = [];

        foreach ($childData as $index => $data) {
            $parent = $parents[intdiv($index, 2)];
            $birthDate = today()
                ->subYears($data[1])
                ->subMonths(($index % 5) + 1)
                ->format('Y-m-d');

            $child = Child::updateOrCreate(
                [
                    'parent_profile_id' => $parent->parentProfile->parent_profile_id,
                    'full_name' => $data[0],
                ],
                [
                    'date_of_birth' => $birthDate,
                    'gender' => $data[2],
                    'guardian_relation' => $data[3],
                    'photo' => null,
                    'allergies' => $data[4],
                    'medical_notes' => $data[5],
                    'medicine_instructions' => $data[6],
                    'special_needs' => $data[7],
                    'emergency_notes' => $data[8],
                    'status' => 'active',
                ]
            );

            $children[] = $child;
        }

        return $children;
    }

    private function seedTimeSlots($services): array
    {
        $slots = [];
        $futureOffsets = [2, 5, 8, 12];
        $serviceSchedules = [
            'Full-Day Care' => ['08:00', '17:00', [6, 10, 12, 18]],
            'Half-Day Care' => ['08:00', '12:00', [8, 10, 12, 12]],
            'Hourly Care' => ['10:00', '12:00', [8, 8, 10, 10]],
            'Learning & Play' => ['10:30', '12:30', [6, 8, 10, 10]],
            'Emergency Care' => ['14:00', '16:00', [6, 8, 8, 10]],
        ];

        foreach ($serviceSchedules as $serviceName => $schedule) {
            foreach ($futureOffsets as $index => $offset) {
                $status = 'open';

                if (
                    ($serviceName === 'Half-Day Care' && $index === 3)
                    || ($serviceName === 'Emergency Care' && $index === 3)
                ) {
                    $status = 'closed';
                }

                $startTime = $schedule[0];
                $endTime = $schedule[1];

                if ($serviceName === 'Half-Day Care' && $index % 2 === 1) {
                    $startTime = '13:00';
                    $endTime = '17:00';
                }

                $slots['future.' . $this->serviceKey($serviceName) . '.' . ($index + 1)] = $this->saveSlot(
                    $services[$serviceName],
                    today()->addDays($offset),
                    $startTime,
                    $endTime,
                    $schedule[2][$index],
                    $status
                );
            }
        }

        $nextSaturday = today()->next(Carbon::SATURDAY);

        for ($index = 0; $index < 4; $index++) {
            $slots['future.weekend.' . ($index + 1)] = $this->saveSlot(
                $services['Weekend Care'],
                $nextSaturday->copy()->addWeeks($index),
                '09:00',
                '17:00',
                [8, 10, 12, 12][$index],
                'open'
            );
        }

        $historyPlans = [
            ['history.full.1', 'Full-Day Care', -20, '08:00', '17:00', 12],
            ['history.full.2', 'Full-Day Care', -17, '08:00', '17:00', 12],
            ['history.half.1', 'Half-Day Care', -14, '08:00', '12:00', 10],
            ['history.hourly.1', 'Hourly Care', -11, '10:00', '12:00', 8],
            ['history.weekend.1', 'Weekend Care', -9, '09:00', '17:00', 10],
            ['history.learning.1', 'Learning & Play', -7, '10:30', '12:30', 8],
            ['history.emergency.1', 'Emergency Care', -5, '14:00', '16:00', 8],
            ['history.learning.2', 'Learning & Play', -3, '10:30', '12:30', 8],
        ];

        foreach ($historyPlans as $plan) {
            $slots[$plan[0]] = $this->saveSlot(
                $services[$plan[1]],
                today()->addDays($plan[2]),
                $plan[3],
                $plan[4],
                $plan[5],
                'open'
            );
        }

        return $slots;
    }

    private function saveSlot(
        Service $service,
        Carbon $date,
        string $start,
        string $end,
        int $capacity,
        string $status
    ): TimeSlot {
        $startForLookup = Carbon::createFromFormat('H:i', $start)->format('H:i:s');
        $endForLookup = Carbon::createFromFormat('H:i', $end)->format('H:i:s');

        $slot = TimeSlot::query()
            ->where('service_id', $service->service_id)
            ->whereDate('slot_date', $date->format('Y-m-d'))
            ->whereTime('start_time', $startForLookup)
            ->whereTime('end_time', $endForLookup)
            ->first();

        if (!$slot) {
            $slot = new TimeSlot([
                'service_id' => $service->service_id,
                'slot_date' => $date->format('Y-m-d'),
                'start_time' => $start,
                'end_time' => $end,
            ]);
        }

        $slot->capacity = $capacity;
        $slot->status = $status;
        $slot->save();

        return $slot->refresh();
    }

    private function seedBookings(array $children, array $slots): array
    {
        $completed = [];
        $cancelled = [];
        $confirmed = [];
        $pending = [];
        $historyKeys = [
            'history.full.1',
            'history.full.2',
            'history.half.1',
            'history.hourly.1',
            'history.weekend.1',
            'history.learning.1',
            'history.emergency.1',
            'history.learning.2',
        ];

        for ($index = 0; $index < 14; $index++) {
            $completed[] = $this->saveBooking(
                $children[$index],
                $slots[$historyKeys[$index % count($historyKeys)]],
                'completed',
                $index % 3 === 0 ? 'Please keep the guardian updated about meals and rest.' : null
            );
        }

        for ($index = 0; $index < 6; $index++) {
            $cancelled[] = $this->saveBooking(
                $children[14 + $index],
                $slots[$historyKeys[($index + 2) % count($historyKeys)]],
                'cancelled',
                $index % 2 === 0 ? 'Guardian requested a schedule change.' : null
            );
        }

        for ($index = 0; $index < 3; $index++) {
            $confirmed[] = $this->saveBooking(
                $children[$index],
                $slots['future.full.1'],
                'confirmed',
                null
            );
        }

        for ($index = 3; $index < 6; $index++) {
            $pending[] = $this->saveBooking(
                $children[$index],
                $slots['future.full.1'],
                'pending',
                null
            );
        }

        for ($index = 0; $index < 2; $index++) {
            $confirmed[] = $this->saveBooking(
                $children[6 + $index],
                $slots['future.learning.1'],
                'confirmed',
                'Encourage participation in learning activities.'
            );
        }

        $learningPendingChildren = [8, 10, 12];

        foreach ($learningPendingChildren as $childIndex) {
            $pending[] = $this->saveBooking(
                $children[$childIndex],
                $slots['future.learning.1'],
                'pending',
                null
            );
        }

        $confirmedPlans = [
            [11, 'future.half.1'],
            [12, 'future.hourly.1'],
            [13, 'future.weekend.1'],
            [14, 'future.emergency.1'],
            [15, 'future.full.2'],
        ];

        foreach ($confirmedPlans as $plan) {
            $confirmed[] = $this->saveBooking(
                $children[$plan[0]],
                $slots[$plan[1]],
                'confirmed',
                null
            );
        }

        $pendingPlans = [
            [16, 'future.half.2'],
            [17, 'future.hourly.2'],
            [18, 'future.weekend.2'],
            [19, 'future.emergency.2'],
        ];

        foreach ($pendingPlans as $plan) {
            $pending[] = $this->saveBooking(
                $children[$plan[0]],
                $slots[$plan[1]],
                'pending',
                null
            );
        }

        return compact('completed', 'cancelled', 'confirmed', 'pending');
    }

    private function saveBooking(
        Child $child,
        TimeSlot $slot,
        string $status,
        ?string $instructions
    ): Booking {
        $booking = Booking::updateOrCreate(
            [
                'child_id' => $child->child_id,
                'slot_id' => $slot->slot_id,
            ],
            [
                'service_id' => $slot->service_id,
                'booking_date' => $slot->slot_date->format('Y-m-d'),
                'booking_time' => $slot->start_time,
                'special_instructions' => $instructions,
                'status' => $status,
                'total_amount' => $slot->service->price,
            ]
        );

        $createdAt = $slot->slot_date->isPast()
            ? $slot->slot_date->copy()->subDays(4)->setTime(10, 0)
            : now()->subDays(1);

        DB::table('bookings')
            ->where('booking_id', $booking->booking_id)
            ->update([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

        return $booking->refresh();
    }

    private function seedAssignments(array $bookings, array $caregivers, User $admin): array
    {
        $assignmentMap = [];
        $assignableBookings = array_merge(
            $bookings['completed'],
            $bookings['confirmed']
        );
        $caregiverPattern = [0, 0, 1, 2, 0, 3, 1, 4, 0, 5, 2, 1, 6, 0, 3, 1, 0, 2, 4, 5, 0, 1, 2, 3];

        foreach ($assignableBookings as $index => $booking) {
            $caregiver = $caregivers[$caregiverPattern[$index % count($caregiverPattern)]];
            $assignedAt = $booking->booking_date->isPast()
                ? $booking->booking_date->copy()->subDay()->setTime(12, 0)
                : now()->subDays(($index % 3) + 1);

            $assignment = CaregiverAssignment::updateOrCreate(
                ['booking_id' => $booking->booking_id],
                [
                    'caregiver_id' => $caregiver->id,
                    'assigned_by' => $admin->id,
                    'assigned_at' => $assignedAt,
                    'status' => $booking->status === 'completed' ? 'completed' : 'assigned',
                ]
            );

            $assignmentMap[$booking->booking_id] = $assignment;
        }

        return $assignmentMap;
    }

    private function seedActivities(array $completedBookings, array $assignments): void
    {
        $details = [
            'check-in' => 'Arrived cheerful and settled quickly.',
            'check-out' => 'Left with the guardian after a calm care session.',
            'meal' => 'Ate most of the meal and drank water.',
            'nap' => 'Rested comfortably during the scheduled quiet time.',
            'play' => 'Enjoyed supervised play with the group.',
            'learning' => 'Completed a guided learning activity with good participation.',
            'toilet' => 'Completed the regular toilet or diaper routine.',
            'health' => 'General health observation was normal.',
            'medicine' => 'Medicine instruction was reviewed and the care note was recorded.',
            'mood' => 'Mood remained calm and positive during the session.',
            'special-notes' => 'No unusual concern was observed during care.',
        ];

        foreach ($completedBookings as $bookingIndex => $booking) {
            $booking->loadMissing(['service', 'child']);
            $assignment = $assignments[$booking->booking_id];
            $types = $this->activityTypes($booking, $bookingIndex);
            $start = Carbon::parse(
                $booking->booking_date->format('Y-m-d') . ' ' . $booking->booking_time
            );
            $duration = max(30, (int) $booking->service->duration_minutes);
            $offsets = [
                5,
                max(10, (int) round($duration * 0.15)),
                max(15, (int) round($duration * 0.30)),
                max(20, (int) round($duration * 0.45)),
                max(25, (int) round($duration * 0.60)),
                max(30, (int) round($duration * 0.75)),
                max(35, $duration - 5),
            ];

            foreach ($types as $index => $type) {
                $activityTime = $start->copy()->addMinutes($offsets[$index]);

                ChildActivity::updateOrCreate(
                    [
                        'assignment_id' => $assignment->assignment_id,
                        'activity_type' => $type,
                        'activity_time' => $activityTime,
                    ],
                    [
                        'details' => $details[$type],
                        'photo_path' => null,
                    ]
                );
            }
        }
    }

    private function activityTypes(Booking $booking, int $bookingIndex): array
    {
        $types = match ($booking->service->name) {
            'Full-Day Care' => ['check-in', 'learning', 'meal', 'nap', 'play', 'mood', 'check-out'],
            'Half-Day Care' => ['check-in', 'learning', 'play', 'meal', 'toilet', 'mood', 'check-out'],
            'Hourly Care' => ['check-in', 'play', 'learning', 'health', 'mood', 'special-notes', 'check-out'],
            'Weekend Care' => ['check-in', 'play', 'meal', 'learning', 'toilet', 'mood', 'check-out'],
            'Learning & Play' => ['check-in', 'learning', 'play', 'mood', 'toilet', 'special-notes', 'check-out'],
            'Emergency Care' => ['check-in', 'health', 'play', 'toilet', 'mood', 'special-notes', 'check-out'],
            default => ['check-in', 'play', 'learning', 'mood', 'health', 'special-notes', 'check-out'],
        };

        if ($booking->child->medicine_instructions && $bookingIndex % 2 === 0) {
            $types[5] = 'medicine';
        }

        return $types;
    }

    private function seedPayments(array $bookings): void
    {
        $transactionNumber = 1;

        foreach ($bookings['completed'] as $index => $booking) {
            if ($index >= 12) {
                continue;
            }

            $method = $index % 4 === 0 ? 'cash' : ($index % 2 === 0 ? 'card' : 'mobile-banking');
            $transactionId = $method === 'cash'
                ? null
                : 'SEED-LN-' . str_pad((string) $transactionNumber++, 5, '0', STR_PAD_LEFT);

            $paidAt = $booking->booking_date->copy()->setTime(18, 0);
            $payment = Payment::updateOrCreate(
                ['booking_id' => $booking->booking_id],
                [
                    'amount' => $booking->total_amount,
                    'payment_method' => $method,
                    'transaction_id' => $transactionId,
                    'payment_status' => 'paid',
                    'paid_at' => $paidAt,
                    'refund_amount' => null,
                    'refunded_at' => null,
                    'refund_note' => null,
                ]
            );

            $this->setPaymentTimestamps($payment, $paidAt->copy()->subDay());
        }

        foreach ($bookings['confirmed'] as $index => $booking) {
            if ($index < 3) {
                $method = $index === 0 ? 'cash' : 'mobile-banking';
                $transactionId = $method === 'cash'
                    ? null
                    : 'SEED-LN-' . str_pad((string) $transactionNumber++, 5, '0', STR_PAD_LEFT);

                $paidAt = now()->subDay();
                $payment = Payment::updateOrCreate(
                    ['booking_id' => $booking->booking_id],
                    [
                        'amount' => $booking->total_amount,
                        'payment_method' => $method,
                        'transaction_id' => $transactionId,
                        'payment_status' => 'paid',
                        'paid_at' => $paidAt,
                        'refund_amount' => null,
                        'refunded_at' => null,
                        'refund_note' => null,
                    ]
                );

                $this->setPaymentTimestamps($payment, $paidAt->copy()->subHours(3));
            } elseif ($index < 6) {
                $payment = Payment::updateOrCreate(
                    ['booking_id' => $booking->booking_id],
                    [
                        'amount' => $booking->total_amount,
                        'payment_method' => 'mobile-banking',
                        'transaction_id' => 'SEED-LN-' . str_pad((string) $transactionNumber++, 5, '0', STR_PAD_LEFT),
                        'payment_status' => 'pending',
                        'paid_at' => null,
                        'refund_amount' => null,
                        'refunded_at' => null,
                        'refund_note' => null,
                    ]
                );

                $this->setPaymentTimestamps($payment, now()->subHours($index + 2));
            } elseif ($index < 8) {
                $payment = Payment::updateOrCreate(
                    ['booking_id' => $booking->booking_id],
                    [
                        'amount' => $booking->total_amount,
                        'payment_method' => 'card',
                        'transaction_id' => 'SEED-LN-' . str_pad((string) $transactionNumber++, 5, '0', STR_PAD_LEFT),
                        'payment_status' => 'failed',
                        'paid_at' => null,
                        'refund_amount' => null,
                        'refunded_at' => null,
                        'refund_note' => null,
                    ]
                );

                $this->setPaymentTimestamps($payment, now()->subDays(2)->addHours($index));
            }
        }

        foreach (array_slice($bookings['cancelled'], 0, 3) as $index => $booking) {
            $paidAt = $booking->booking_date->copy()->subDays(2)->setTime(15, 0);

            $payment = Payment::updateOrCreate(
                ['booking_id' => $booking->booking_id],
                [
                    'amount' => $booking->total_amount,
                    'payment_method' => 'mobile-banking',
                    'transaction_id' => 'SEED-LN-' . str_pad((string) $transactionNumber++, 5, '0', STR_PAD_LEFT),
                    'payment_status' => 'paid',
                    'paid_at' => $paidAt,
                    'refund_amount' => $booking->total_amount,
                    'refunded_at' => $paidAt->copy()->addDay(),
                    'refund_note' => 'Full refund recorded after approved cancellation.',
                ]
            );

            $this->setPaymentTimestamps($payment, $paidAt->copy()->subHours(2));
        }
    }


    private function setPaymentTimestamps(Payment $payment, Carbon $createdAt): void
    {
        DB::table('payments')
            ->where('payment_id', $payment->payment_id)
            ->update([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
    }

    private function seedBookingRequests(array $bookings, array $slots, User $admin): void
    {
        $requestPlans = [
            [$bookings['cancelled'][0], 'cancellation', null, 'approved', 'Family schedule changed and care was no longer required.', 'Cancellation approved and refund recorded.'],
            [$bookings['cancelled'][1], 'cancellation', null, 'approved', 'The child became unavailable for the booked date.', 'Cancellation approved.'],
            [$bookings['cancelled'][2], 'cancellation', null, 'approved', 'Guardian requested cancellation due to a family commitment.', 'Cancellation approved and payment reviewed.'],
            [$bookings['confirmed'][0], 'cancellation', null, 'rejected', 'Requested cancellation after confirming the care schedule.', 'Request rejected because the care slot had already been finalized.'],
            [$bookings['confirmed'][1], 'cancellation', null, 'pending', 'Family plans may require cancellation.', null],
            [$bookings['confirmed'][2], 'reschedule', $bookings['confirmed'][2]->timeSlot, 'approved', 'Requested a different available schedule.', 'Reschedule approved.'],
            [$bookings['confirmed'][3], 'reschedule', $slots['future.learning.3'], 'rejected', 'Requested a later learning session.', 'Requested schedule could not be approved.'],
            [$bookings['confirmed'][4], 'reschedule', $slots['future.learning.2'], 'pending', 'A later session would be easier for the family.', null],
            [$bookings['confirmed'][5], 'cancellation', null, 'rejected', 'Parent requested cancellation after caregiver planning.', 'Cancellation request rejected after review.'],
            [$bookings['confirmed'][6], 'cancellation', null, 'pending', 'Waiting for confirmation of a family appointment.', null],
        ];

        foreach ($requestPlans as $index => $plan) {
            $booking = $plan[0];
            $requestedSlot = $plan[2];
            $status = $plan[3];
            $reviewed = $status !== 'pending';

            BookingRequest::updateOrCreate(
                [
                    'booking_id' => $booking->booking_id,
                    'request_type' => $plan[1],
                    'reason' => $plan[4],
                ],
                [
                    'requested_slot_id' => $requestedSlot?->slot_id,
                    'requested_date' => $requestedSlot?->slot_date?->format('Y-m-d'),
                    'requested_time' => $requestedSlot?->start_time,
                    'request_status' => $status,
                    'reviewed_by' => $reviewed ? $admin->id : null,
                    'reviewed_at' => $reviewed ? now()->subDays(($index % 3) + 1) : null,
                    'admin_note' => $plan[5],
                ]
            );
        }
    }

    private function seedContactMessages(): void
    {
        $messages = [
            ['Nadia Islam', 'nadia.inquiry@example.com', '01911000001', 'Full-Day Care availability', 'I would like to know about available Full-Day Care dates for next week.', 'new'],
            ['Karim Hasan', 'karim.inquiry@example.com', '01911000002', 'Child allergy support', 'Can caregivers support a child with a peanut allergy during meal time?', 'open'],
            ['Maliha Noor', 'maliha.inquiry@example.com', '01911000003', 'Weekend Care', 'Please share the usual Weekend Care schedule and booking process.', 'resolved'],
            ['Rashed Ahmed', 'rashed.inquiry@example.com', '01911000004', 'Hourly Care', 'Is Hourly Care available for a two-hour afternoon appointment?', 'new'],
            ['Faria Chowdhury', 'faria.inquiry@example.com', '01911000005', 'Payment information', 'Which payment methods can I use after a booking is confirmed?', 'open'],
            ['Sabbir Hossain', 'sabbir.inquiry@example.com', '01911000006', 'Caregiver assignment', 'When can a parent see the assigned caregiver information?', 'resolved'],
            ['Rumana Akter', 'rumana.inquiry@example.com', '01911000007', 'Learning & Play', 'I want to know what activities are included in Learning & Play.', 'new'],
            ['Arif Rahman', 'arif.inquiry@example.com', '01911000008', 'Emergency Care', 'How early should I contact LittleNest for Emergency Care?', 'open'],
            ['Muntaha Karim', 'muntaha.inquiry@example.com', '01911000009', 'Reschedule request', 'How can I request a different time slot after confirmation?', 'resolved'],
            ['Tanjim Ahmed', 'tanjim.inquiry@example.com', '01911000010', 'Activity updates', 'Can parents see meal and learning updates during the care session?', 'new'],
        ];

        foreach ($messages as $data) {
            ContactMessage::updateOrCreate(
                [
                    'email' => $data[1],
                    'subject' => $data[3],
                ],
                [
                    'full_name' => $data[0],
                    'phone' => $data[2],
                    'message' => $data[4],
                    'status' => $data[5],
                ]
            );
        }
    }

    private function serviceKey(string $serviceName): string
    {
        return match ($serviceName) {
            'Full-Day Care' => 'full',
            'Half-Day Care' => 'half',
            'Hourly Care' => 'hourly',
            'Learning & Play' => 'learning',
            'Emergency Care' => 'emergency',
            default => 'service',
        };
    }
}
