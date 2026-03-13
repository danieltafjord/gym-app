<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->unsignedInteger('max_capacity')->nullable()->after('is_active');
            $table->boolean('occupancy_tracking_enabled')->default(false)->after('max_capacity');
        });
    }

    public function down(): void
    {
        Schema::table('gyms', function (Blueprint $table) {
            $table->dropColumn(['max_capacity', 'occupancy_tracking_enabled']);
        });
    }
};
