<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caregiver_assignments', function (Blueprint $table) {
            $table->id('assignment_id');

            $table->unsignedBigInteger('booking_id')->unique();
            $table->unsignedBigInteger('caregiver_id');
            $table->unsignedBigInteger('assigned_by');

            $table->dateTime('assigned_at');

            $table->enum('status', [
                'assigned',
                'completed',
            ])->default('assigned');

            $table->timestamps();

            $table->foreign('booking_id')
                ->references('booking_id')
                ->on('bookings')
                ->cascadeOnDelete();

            $table->foreign('caregiver_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();

            $table->foreign('assigned_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caregiver_assignments');
    }
};
