<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [];

        if (Schema::hasColumn('payments', 'gateway_name')) {
            $columns[] = 'gateway_name';
        }

        if (Schema::hasColumn('payments', 'gateway_payment_id')) {
            $columns[] = 'gateway_payment_id';
        }

        if (Schema::hasColumn('payments', 'gateway_status')) {
            $columns[] = 'gateway_status';
        }

        if ($columns !== []) {
            Schema::table('payments', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
    }
};
