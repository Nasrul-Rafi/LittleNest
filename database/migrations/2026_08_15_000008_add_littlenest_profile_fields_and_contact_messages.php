<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            if (!Schema::hasColumn('children', 'guardian_relation')) {
                $table->string('guardian_relation', 50)->nullable()->after('gender');
            }

            if (!Schema::hasColumn('children', 'medicine_instructions')) {
                $table->text('medicine_instructions')->nullable()->after('medical_notes');
            }

            if (!Schema::hasColumn('children', 'emergency_notes')) {
                $table->text('emergency_notes')->nullable()->after('special_needs');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'min_age')) {
                $table->unsignedTinyInteger('min_age')->nullable()->after('description');
            }

            if (!Schema::hasColumn('services', 'max_age')) {
                $table->unsignedTinyInteger('max_age')->nullable()->after('min_age');
            }
        });

        if (!Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id('message_id');
                $table->string('full_name', 100);
                $table->string('email');
                $table->string('phone', 30)->nullable();
                $table->string('subject', 150);
                $table->text('message');
                $table->enum('status', ['new', 'open', 'resolved'])
                    ->default('new');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');

        Schema::table('services', function (Blueprint $table) {
            $columns = [];
            foreach (['min_age', 'max_age'] as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $columns[] = $column;
                }
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('children', function (Blueprint $table) {
            $columns = [];
            foreach (['guardian_relation', 'medicine_instructions', 'emergency_notes'] as $column) {
                if (Schema::hasColumn('children', $column)) {
                    $columns[] = $column;
                }
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
