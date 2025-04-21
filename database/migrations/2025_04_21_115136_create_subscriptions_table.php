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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->integer('customer_id');
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');

            $table->integer('plan_id');
            $table->foreign('plan_id')
                ->references('id')
                ->on('plans');

            $table->timestamp('date_start')->nullable();
            $table->timestamp('date_end')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
