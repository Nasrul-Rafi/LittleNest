<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requests', function (Blueprint $table) {
            $table->id('request_id');

            $table->unsignedBigInteger('booking_id');

            $table->enum('request_type', [
                'cancellation',
                'reschedule',
            ]);

            $table->date('requested_date')->nullable();
            $table->time('requested_time')->nullable();
            $table->text('reason');

            $table->enum('request_status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->cascadeOnDelete();

            $table->index([
                'booking_id',
                'request_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_requests');
    }
};
