<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('time_slots')) {
            Schema::create('time_slots', function (Blueprint $table) {
                $table->id('slot_id');
                $table->unsignedBigInteger('service_id');
                $table->date('slot_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->unsignedInteger('capacity');
                $table->enum('status', ['open', 'closed'])->default('open');
                $table->timestamps();

                $table->foreign('service_id')
                    ->references('service_id')
                    ->on('services')
                    ->cascadeOnDelete();

                $table->unique(
                    ['service_id', 'slot_date', 'start_time', 'end_time'],
                    'time_slots_unique_schedule'
                );

                $table->index(['slot_date', 'status']);
            });
        }

        if (!Schema::hasColumn('bookings', 'booking_reference')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('booking_reference', 30)
                    ->nullable()
                    ->unique()
                    ->after('booking_id');
            });
        }

        if (!Schema::hasColumn('bookings', 'slot_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->unsignedBigInteger('slot_id')
                    ->nullable()
                    ->after('service_id');

                $table->foreign('slot_id')
                    ->references('slot_id')
                    ->on('time_slots')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('booking_requests', 'requested_slot_id')) {
            Schema::table('booking_requests', function (Blueprint $table) {
                $table->unsignedBigInteger('requested_slot_id')
                    ->nullable()
                    ->after('request_type');

                $table->foreign('requested_slot_id')
                    ->references('slot_id')
                    ->on('time_slots')
                    ->nullOnDelete();
            });
        }

        $bookings = DB::table('bookings')
            ->select('booking_id', 'booking_date')
            ->whereNull('booking_reference')
            ->orderBy('booking_id')
            ->get();

        foreach ($bookings as $booking) {
            $year = date('Y', strtotime($booking->booking_date));
            $reference = 'LN-' . $year . '-' . str_pad(
                (string) $booking->booking_id,
                4,
                '0',
                STR_PAD_LEFT
            );

            DB::table('bookings')
                ->where('booking_id', $booking->booking_id)
                ->update(['booking_reference' => $reference]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('booking_requests', 'requested_slot_id')) {
            Schema::table('booking_requests', function (Blueprint $table) {
                $table->dropForeign(['requested_slot_id']);
                $table->dropColumn('requested_slot_id');
            });
        }

        if (Schema::hasColumn('bookings', 'slot_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['slot_id']);
                $table->dropColumn('slot_id');
            });
        }

        if (Schema::hasColumn('bookings', 'booking_reference')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropUnique(['booking_reference']);
                $table->dropColumn('booking_reference');
            });
        }

        Schema::dropIfExists('time_slots');
    }
};
