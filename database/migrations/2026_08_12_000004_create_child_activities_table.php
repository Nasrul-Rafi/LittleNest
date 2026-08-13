<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('child_activities', function (Blueprint $table) {
            $table->id('activity_id');
            $table->unsignedBigInteger('assignment_id');
            $table->string('activity_type', 50);
            $table->text('details')->nullable();
            $table->dateTime('activity_time');
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->foreign('assignment_id')
                ->references('assignment_id')
                ->on('caregiver_assignments')
                ->cascadeOnDelete();

            $table->index([
                'assignment_id',
                'activity_time',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_activities');
    }
};
