<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vpn_keys', function (Blueprint $table) {
            $table->string('server_user_id', 512)->nullable()->change();
            $table->string('access_key', 2048)->change();
            $table->string('server_type', 512)->change();
            $table->string('external_uuid', 1024)->nullable()->change();
            $table->string('external_email', 512)->nullable()->change();
            $table->string('external_sub_id', 512)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vpn_keys', function (Blueprint $table) {
            $table->string('server_user_id')->nullable()->change();
            $table->string('access_key')->change();
            $table->string('server_type')->change();
            $table->string('external_uuid')->nullable()->change();
            $table->string('external_email')->nullable()->change();
            $table->string('external_sub_id')->nullable()->change();
        });
    }
};
