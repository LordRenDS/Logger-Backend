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
        Schema::table('processes', function (Blueprint $table) {
            // We use a custom name to avoid potential length issues with auto-generated names
            $table->unique(['pc_id', 'process_start', 'process_name', 'window_name'], 'processes_unique_sync_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processes', function (Blueprint $table) {
            $table->dropUnique('processes_unique_sync_index');
        });
    }
};
