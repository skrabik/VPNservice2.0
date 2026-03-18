<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('servers')
            ->where(function ($query) {
                $query->whereNull('type')
                    ->orWhere('type', '!=', '3xui');
            })
            ->update([
                'type' => '3xui',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This conversion is intentionally irreversible because legacy server types are removed.
    }
};
