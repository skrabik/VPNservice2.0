<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vpn_keys', function (Blueprint $table) {
            $table->foreignId('server_inbound_id')
                ->nullable()
                ->after('server_id')
                ->constrained('server_inbounds')
                ->nullOnDelete();
            $table->string('external_uuid')->nullable()->after('server_type');
            $table->string('external_email')->nullable()->after('external_uuid');
            $table->string('external_sub_id')->nullable()->after('external_email');
            $table->unsignedBigInteger('traffic_limit_bytes')->nullable()->after('external_sub_id');
            $table->unsignedBigInteger('traffic_used_bytes')->nullable()->after('traffic_limit_bytes');
            $table->longText('panel_payload_json')->nullable()->after('traffic_used_bytes');
        });
    }

    public function down(): void
    {
        Schema::table('vpn_keys', function (Blueprint $table) {
            $table->dropConstrainedForeignId('server_inbound_id');
            $table->dropColumn([
                'external_uuid',
                'external_email',
                'external_sub_id',
                'traffic_limit_bytes',
                'traffic_used_bytes',
                'panel_payload_json',
            ]);
        });
    }
};
