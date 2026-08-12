<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caregiver_profiles', function (Blueprint $table) {
            $table->id('caregiver_profile_id');

            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('qualification');
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->string('specialization')->nullable();
            $table->text('skills')->nullable();
            $table->text('bio')->nullable();

            $table->enum('availability_status', [
                'available',
                'unavailable',
            ])->default('available');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caregiver_profiles');
    }
};
