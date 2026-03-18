<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_inbounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('xui_inbound_id');
            $table->string('remark')->nullable();
            $table->string('protocol', 64)->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('tag')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_default')->default(false);
            $table->longText('settings_json')->nullable();
            $table->longText('stream_settings_json')->nullable();
            $table->longText('sniffing_json')->nullable();
            $table->longText('raw_payload_json')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['server_id', 'xui_inbound_id']);
            $table->index(['server_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_inbounds');
    }
};
