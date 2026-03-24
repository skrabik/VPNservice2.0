<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('source_channel', 32)
                ->default('telegram')
                ->after('customer_id');
            $table->string('status', 32)
                ->default('new')
                ->after('message');
            $table->foreignId('assigned_user_id')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('answered_at')
                ->nullable()
                ->after('assigned_user_id');
            $table->timestamp('closed_at')
                ->nullable()
                ->after('answered_at');
            $table->timestamp('last_reply_at')
                ->nullable()
                ->after('closed_at');

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropConstrainedForeignId('assigned_user_id');
            $table->dropColumn([
                'source_channel',
                'status',
                'answered_at',
                'closed_at',
                'last_reply_at',
            ]);
        });
    }
};
