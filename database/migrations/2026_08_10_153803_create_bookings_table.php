<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');

            $table->unsignedBigInteger('child_id');
            $table->unsignedBigInteger('service_id');

            $table->date('booking_date');
            $table->time('booking_time');

            $table->text('special_instructions')->nullable();

            $table->enum('status', [
                'pending',
                'confirmed',
                'completed',
                'cancelled',
            ])->default('pending');

            $table->decimal('total_amount', 10, 2);

            $table->timestamps();

            $table->foreign('child_id')
                ->references('child_id')
                ->on('children')
                ->cascadeOnDelete();

            $table->foreign('service_id')
                ->references('service_id')
                ->on('services')
                ->restrictOnDelete();

            $table->index([
                'booking_date',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};