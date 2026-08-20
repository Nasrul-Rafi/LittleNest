<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('gateway_name', 50)->nullable()->after('payment_method');
            $table->string('gateway_status', 50)->nullable()->after('gateway_name');
            $table->string('gateway_session_key', 100)->nullable()->after('gateway_status');
            $table->string('validation_id', 100)->nullable()->after('gateway_session_key');
            $table->string('bank_transaction_id', 100)->nullable()->after('validation_id');
            $table->string('card_type', 100)->nullable()->after('bank_transaction_id');
            $table->string('refund_reference', 100)->nullable()->after('refund_note');
            $table->string('refund_gateway_status', 50)->nullable()->after('refund_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_name',
                'gateway_status',
                'gateway_session_key',
                'validation_id',
                'bank_transaction_id',
                'card_type',
                'refund_reference',
                'refund_gateway_status',
            ]);
        });
    }
};
