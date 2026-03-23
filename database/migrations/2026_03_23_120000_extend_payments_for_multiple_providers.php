<?php

use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('provider', 64)->default(Payment::PROVIDER_TELEGRAM)->after('transaction_id');
            $table->string('payment_method', 128)->nullable()->after('provider');
            $table->string('status', 64)->default(Payment::STATUS_SUCCEEDED)->after('payment_method');
            $table->string('external_payment_id', 512)->nullable()->after('status');
            $table->json('payload')->nullable()->after('external_payment_id');
        });

        DB::table('payments')
            ->whereNull('external_payment_id')
            ->update([
                'external_payment_id' => DB::raw('transaction_id'),
            ]);

        DB::table('payments')
            ->whereNull('payment_method')
            ->update([
                'payment_method' => Payment::METHOD_TELEGRAM_STARS,
            ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['subscription_id']);
            $table->integer('subscription_id')->nullable()->change();
            $table->foreign('subscription_id')->references('id')->on('subscriptions');
            $table->unique(['provider', 'external_payment_id'], 'payments_provider_external_payment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_provider_external_payment_unique');
            $table->dropForeign(['subscription_id']);
            $table->integer('subscription_id')->nullable(false)->change();
            $table->foreign('subscription_id')->references('id')->on('subscriptions');
            $table->dropColumn([
                'provider',
                'payment_method',
                'status',
                'external_payment_id',
                'payload',
            ]);
        });
    }
};
