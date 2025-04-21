<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->integer('customer_id');
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');

            $table->integer('subscription_id');
            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions');

            $table->decimal('amount', 10);
            $table->string('currency', 256)->nullable();
            $table->string('transaction_id', 512)->nullable();

            $table->integer('payment_method_id');
            $table->foreign('payment_method_id')
                ->references('id')
                ->on('payment_methods');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
