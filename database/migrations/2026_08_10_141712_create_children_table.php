<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('children', function (Blueprint $table) {
            $table->id('child_id');

            $table->foreignId('parent_profile_id')
                ->constrained(
                    table: 'parent_profiles',
                    column: 'parent_profile_id'
                )
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('full_name', 100);
            $table->date('date_of_birth');

            $table->enum('gender', [
                'male',
                'female',
                'other'
            ])->nullable();

            $table->string('photo')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_notes')->nullable();
            $table->text('special_needs')->nullable();

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');

            $table->timestamps();

            $table->index(
                'parent_profile_id',
                'idx_children_parent'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};