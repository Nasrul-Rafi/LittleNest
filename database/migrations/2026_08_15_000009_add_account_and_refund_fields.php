<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 30)
                    ->nullable()
                    ->after('email');
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)
                    ->nullable()
                    ->after('paid_at');
            }

            if (!Schema::hasColumn('payments', 'refunded_at')) {
                $table->dateTime('refunded_at')
                    ->nullable()
                    ->after('refund_amount');
            }

            if (!Schema::hasColumn('payments', 'refund_note')) {
                $table->text('refund_note')
                    ->nullable()
                    ->after('refunded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('payments', 'refund_amount')) {
                $columns[] = 'refund_amount';
            }

            if (Schema::hasColumn('payments', 'refunded_at')) {
                $columns[] = 'refunded_at';
            }

            if (Schema::hasColumn('payments', 'refund_note')) {
                $columns[] = 'refund_note';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        if (Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }
    }
};
